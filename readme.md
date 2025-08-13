# CCA Moodle customizations

Misc command-line management scripts and web assets for Moodle. Install these to `${MOODLE_ROOT}/admin/cca_cli`. Many of these scripts are outdated or were used one specific time in the past. The ones that are still frequently employed are listed below.

## Cronjobs on Kubernetes

We cannot configure our Moodle cronjobs as kubernetes cronjobs, instead they are copied into the system cron folders in our Dockerfile. Below, we detail a few tricky issues which didn't exist on our locally hosted Moodle instances.

**Check the cron PATH**. We can run a test job like `echo $PATH >> test.txt` or `which php >> test.txt`. The execution context of a shell on the container is not the same as cron's. In particular, cron uses the system php `/usr/bin/php` which is missing some necessary modules, while login shells (and Moodle itself) use Bitnami's packaged php `/opt/bitnami/php/bin/php`. There are several ways to address this problem but we've added the shell's `PATH` to the top of the crontab, like `PATH=/opt/bitnami/php/bin:/opt/bitnami/php/sbin:...`. Note that you cannot use shell expansion, `PATH=/opt/bitnami/php/bin:$PATH` does not work, nor does running cron scripts as a login shell with `bash -lc {/path/to/script.sh}`.

The system crontab has an extra field, the name of the user that executes the job. Normally, crontabs have six fields: five temporal ones and the command to run. The system cron has five temporal fields, the user, and finally the command. Bitnami Moodle runs as a "daemon" user, so running Moodle's cron every minute looks like `* * * * * daemon /opt/bitnami/php/bin/php /bitnami/moodle/admin/cli/cron.php` (note the full path to Bitnami's PHP to address the problem described above).

## CLI

These are various maintenance scripts meant to be run on the Moodle server. Most are not in use and have been moved to the "unused" folder. Many can be replaced with one-off `moosh` commands. Every `moosh` command needs the `-n` flag because the user that owns Moodle files is not the one running the script.

### unenroll all students from a course

Not a script but this is a single command with `moosh`:

```sh
# where 2666 is the course ID number
moosh -n course-unenrol 2666 $(moosh -n user-list --course 2666 --course-role student -i)
```

Obviously, the `--course-role` could be changed or removed (to unenroll everyone).

### bulk-role-change.sh

Change all users enrolled in courses in a particular category to a particular role. The `MOODLE_CATEGORY` and `MOODLE_ROLE` environment variables are used for these two values and they default to 877 (Templates > Program Templates) and `exportonlyteacher` because the primary application of this script is to turn editorial access to template courses on and off according to our deadlines.

### create_course_cats.php

This creates all the needed course categories for a term (e.g. the parent semester category and then all the children departmental categories as well as the "Metacourses" category), with a prompt asking you to specify the term on the command line. We run this once per term a few months before it begins; it is a prerequisite to the term being added to the `MOODLE_ENROLLER_TERMS` setting on the CCA Integrations project which populates our external enrollments database.

### enroll_in_all.sh

Enroll a user in all of a program's courses in a given term. Usage: `./enroll_in_all.sh $SEMESTER $PROGRAM $USER [ $ROLE ]`. Role defaults to Instructor if not provided.

### enrollment_cron.sh

Cron calls this script which uses a few `moosh` commands and the enrol/database/cli/sync.php script to sync our enrollments with the external enrollments database.

We run this script manually for the initial sync of a semester. However, it takes a long time on the first run (so many courses to create) and problems can occur if it is interrupted (duplicate enrollment instances). Run in a safe manner like `nohup bash admin/cca_cli/enrollment_cron.sh >> /bitnami/moodledata/enroll.log &`. You can `tail -f enroll.log` to see messages.

### exted_cron.sh

Create practice courses for each student enrolled in the CCA Extension Moodle Essentials course. This script does not have its own cronjob but is run by foundations_cron.sh. In the future, it would be nice to make this use foundations.sh (perhaps renamed to something like "practice_course.sh") and eliminate the high amount of redundant code between the two.

### foundations_cron.sh

Meant to run nightly, creates practice courses for each student enrolled in the Moodle Foundations course. This script uses `moosh user-list` to retrieve the student enrollments and passes the results to the foundations.sh script below.

### foundations.sh

Create a single Moodle Foundation "practice course" in a provided category for a given username.

### orphaned_local_files.php

We use the object file storage plugin to store uploaded files in cloud storage. We set the plugin's `sizethreshold` to 0 to upload _all_ files to cloud storage, yet our local data directory at `/opt/moodledata` has continued to grow in size. This script aims to identify files that exist in local storage which can be safely deleted.

The script checks a file containing a list of hashes of local files to see if they are referenced in the database in the `mdl_files` and/or `mdl_tool_objectfs_objects` tables or check if they are in cloud storage, with the option to delete them if all checks pass.

```sh
# create a list of local files over two weeks old (could specify a minimum size with -size)
find /opt/moodledata/filedir -type f -mtime +14 > /bitnami/moodledata/hashes.txt
# deletes local files older than two weeks old with copies in cloud storage while
php admin/cca_cli/orphaned_local_files.php --cloud --delete -f=/bitnami/moodledata/hashes.txt
# deletes local files not referenced anywhere in the database
php admin/cca_cli/orphaned_local_files.php --local_table --objectfs_table --delete -f=/bitnami/moodledata/hashes.txt
# this is similar—checks for local files not in the db—but without checking against the objects table
moosh -n file-dbcheck
```

The `--debug` flag prints more information and the `--delete` flag deletes the files _if they are more than two weeks old_.

### set_course_date.sh

Set the start (first argument) & end (second argument) date for all courses within a category (the `CATEGORY` environment variable, which defaults to 877 for the Program Templates category). Both arguments should be UNIX timestamps. This script is useful for updating the dates of template courses for the next semester. If you want to enforce an end date for a whole semester (or other category), use the `moosh -n course-config-set category ${CATEGORY} enddate ${END}` command.

## CAS Login Workaround

Is there a major problem breaking the Moodle login? Here is how to work around SSO authentication being down:

1. Run a shell on the Moodle GKE pod - `kubectl -n $NAMESPACE exec (kubectl -n $NAMESPACE get pods -o custom-columns=":metadata.name" | grep moodle) -it -- /bin/bash`
2. Disable SSO logins using [moosh](https://moosh-online.com/commands/) - `cd /bitnami/moodle; moosh -n auth-manage disable cas`
3. Go to the Moodle website and login with the manual administrator account (shared with the appropriate people in Dashlane)
4. Proceed with steps above
5. Remember to re-enable SSO once it's fixed, either using the admin site under [Authentication Plugins](https://moodle.cca.edu/admin/category.php?category=authsettings) or with `moosh -n auth-manage enable cas`

We can also put in Moodle in maintenance mode with `moosh -n maintenance-on`. It's recommended to do this while setting up the alert so users don't land on the manual login page and become frustrated when their CCA credentials don't work.

## LICENSE

[ECL Version 2.0](https://opensource.org/licenses/ECL-2.0)
