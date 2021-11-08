# CCA Moodle customizations

Misc command-line management scripts and web assets for Moodle. Install these to `${MOODLE_ROOT}/admin/cca_cli`. Many of these scripts are outdated or were used one specific time in the past. The ones that are still frequently employed are listed below.

## Cronjobs on Kubernetes

We've not yet configured our Moodle cronjobs as [kubernetes cronjobs](https://kubernetes.io/docs/concepts/workloads/controllers/cron-jobs/), which we should do eventually, instead they are run by a combination of being copied into the system cron folders or manually added to a crontab by running a shell on the container and running `crontab -e`. The latter method needs to be re-applied each time the Moodle application is rebuilt. Below, we detail a few tricky issues which didn't exist on our locally hosted Moodle instances.

**Check the cron PATH**. We can run a test job like `echo $PATH >> test.txt` or `which php >> test.txt`. The execution context of a shell on the container is not the same as cron's. In particular, cron uses the system php `/usr/bin/php` which is missing some necessary modules, while login shells (and Moodle itself) use Bitnami's packaged php `/opt/bitnami/php/bin/php`. There are several ways to address this problem but we've added the shell's `PATH` to the top of the crontab, like `PATH=/opt/bitnami/php/bin:/opt/bitnami/php/sbin:...`. Note that you cannot use shell expansion, `PATH=/opt/bitnami/php/bin:$PATH` does not work, nor does running cron scripts as a login shell with `bash -lc {/path/to/script.sh}`.

The system crontab has an extra field, the name of the user that executes the job. Normally, crontabs have six fields: five temporal ones and the command to run. The system cron has five temporal fields, the user, and finally the command. Bitnami Moodle provides a "daemon" user to run scheduled jobs, so running Moodle's cron every minute looks like `* * * * * daemon /opt/bitnami/php/bin/php /opt/bitnami/moodle/admin/cli/cron.php` (note the full path to Bitnami's PHP to address the problem described above).

## CLI

These are various maintenance scripts meant to be run on the Moodle server. Most are not in use and have been moved to the "unused" folder. Many can be replaced with one-off `moosh` commands.

### bulk-role-change.sh

Change all users enrolled in courses in a particular category to a particular role. The `MOODLE_CATEGORY` and `MOODLE_ROLE` environment variables are used for these two values and they default to 877 (Templates > Program Templates) and `exportonlyteacher` because the primary application of this script is to turn editorial access to template courses on and off according to our deadlines.

### cca_set_cas_logins.php

Sets all logins to type `cas` except for the "guest" and "etadmin" users. Needed because Moodle's "external database enrollment" plugin sets all to "db" with no config option, so we run this in the enrollment script after syncing with the enrollment database.

### create_course_cats.php

This creates all the needed course categories for a term (e.g. the parent semester category and then all the children departmental categories as well as the "Metacourses" category), with a prompt asking you to specify the term on the command line. We run this once per term a few months before it begins; it is a prerequisite to the term being added to the `MOODLE_ENROLLER_TERMS` setting on the CCA Integrations project which populates our external enrollments database.

### enrollment_cron.sh

Cron calls this script which uses a few `moosh` commands, the auth/db/cli/sync_users.php script, and the enrol/database/cli/sync.php script to sync our enrollments with the external enrollments database.

### foundations_cron.sh

Meant to run nightly, creates practice courses for everyone enrolled in the Moodle Foundations course (id = 2116) in a `student` role. This script uses `moosh user-list` to retrieve the student enrollments and passes the results to the foundations.sh script below.

### foundations.sh

Create Moodle Foundations "practice courses" for faculty members. The script has two modes: you can pass it a username and surname for it to create a single practice course or you can pass it a CSV of username/surname pairs to create a set of them. The username is used in the shortname so duplicates are not created; the surname is used in the course title and, if not provided, it defaults back to the username.

### sandbox.sh

Create a "sandbox" (test) course site for a particular CCA program. You pass the program department code, name, and optionally the username of an managing instructor to the script.

### set_course_date.sh

Set the start (first argument) & end (second argument) date for all courses within a category (the `CATEGORY` environment variable, which defaults to 877 for the Program Templates category). Both arguments should be UNIX timestamps. This script is useful for updating the dates of template courses for the next semester. If you want to enforce an end date for a whole semester (or other category), use the `moosh -n course-config-set category ${CATEGORY} enddate ${END}` command.
