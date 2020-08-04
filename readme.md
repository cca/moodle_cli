# CCA Moodle customizations

Misc command-line management scripts and web assets for Moodle. Install these to `admin/*` and ensure that their directories are listed in the .git/info/exclude file so that our Moodle git repo doesn't complain about them being untracked.

The moodle.logrotate file configures how logrotate handles Moodle's logs (cron.log and enroll.log) and should be copied to /etc/logrotate.d/moodle.

Many of these scripts are outdated or were used one specific time in the past. The ones that are still frequently employed (as of Fall 2019) are enrollment_cron.sh, cca_set_cas_logins, & create_course_cats.php (which creates categories with a structured `idnumber` while the older moosh_create_course_cats.sh does not).

## CLI

These are various maintenance scripts meant to be run on the Moodle server. Most are not in use and have been moved to the "unused" folder. Many can be replaced with one-off `moosh` commands.

**cca_set_cas_logins.php**

Sets all logins to type `cas` except for the "guest" and "etadmin" users. Needed because Moodle's "external database enrollment" plugin sets all to "db" with no config option, so we run this in the enrollment script after syncing with the enrollment database.

**enrollment_cron.sh**

Cron calls this script which in turns uses a few moosh commands, the auth/db/cli/sync_users.php script, and the enrol/database/cli/sync.php script to sync our enrollments with the external enrollments database.

**create_course_cats.php**

This creates all the needed course categories for a term (e.g. the parent semester category and then all the children departmental categories as well as the "Metacourses" category), with a prompt asking you to specify the term on the command line. We run this once per term a few months before it begins; it is a prerequisite to the term being added to the `MOODLE_ENROLLER_TERMS` setting on the portal integrations server which, in turn, serves as our external enrollments database.

**sandbox.sh**

Create a "sandbox" (test) course site for a particular CCA program. You pass the program department code, name, and optionally the username of an managing instructor to the script.

## web assets

**scss**

SCSS which you insert this code in the text area under Site Administration > Appearance > Themes > Boost > Advanced Settings.

We display the "CCA" logo via an SVG background image copied from the main CCA site and then URL-encoded (# -> %23).

**js**

We insert custom scripts into the `additionalhtml > footer` settings area. Our scripts are all under the additionalhtml directory and there is a gulp process for compiling them.
