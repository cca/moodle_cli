# CCA Moodle customizations

Misc command-line management scripts and web assets for Moodle. Install these to `admin/*` and ensure that their directories are listed in the .git/info/exclude file so that our Moodle git repo doesn't complain about them being untracked.

Also includes a couple of tools with web wrappers for convenience. Put the `cca_tools` directory under `admin` and superusers will be able to access them at `moodle.cca.edu/admin/cca_tools/[queries/unenroll]`.

The moodle.logrotate file configures how logrotate handles Moodle's logs (cron.log and enroll.log) and should be copied to /etc/logrotate.d/moodle.

Many of these scripts are outdated or were used one specific time in the past. The ones that are still frequently employed (as of Fall 2019) are enrollment_cron.sh, cca_set_cas_logins, & moosh_create_course_cats.sh.

## API Tools

These are example scripts for accessing different portions of the Moodle REST APIs. The Moodle APIs are very comprehensive but sometimes a bit tricky to use. These scripts are both examples and not intended for production use, though similar code is running in other CCA apps. The easiest way to run them is to obtain a Web Services token at https://moodle.cca.edu/admin/settings.php?section=webservicetokens for a Service with the appropriate API permissions, then save that token in a config.py file (see example.config.py for details) in the root of this project.

**get_mdl_course_id**

This script was an example provided to the Portal team during Learning Hub development so that links to Moodle course sites could be established. The Portal has moved away from having direct access to the Moodle database but Moodle course links require knowledge of Moodle's internal IDs; this script returns those IDs when given a course "shortname" of form `ANIMA-1000-1-2019FA`.

**get_mdl_categories**

This script was an example provided to the Integration Engineer so that a Moodle course database could be built which includes structured enrollment data with knowledge of Moodle course category IDs. This makes it so courses are added under the appropriate categories when they appear in the database. It takes a "filter" dict of course properties (e.g. `{"name": "2019SU"}`) and returns an array of all categories that match and their children.

## CLI

These are various maintenance scripts meant to be run on the Moodle server. Most are not in use and have been moved to the "unused" folder. Many can be replaced with one-off `moosh` commands.

**cca_set_cas_logins.php**

Sets all logins to type `cas` except for the "guest" and "etadmin" users. Needed because Moodle's "external database enrollment" plugin sets all to "db" with no config option, so we run this in the enrollment script after syncing with the enrollment database.

**enrollment_cron.sh**

Cron calls this script which in turns uses a few moosh commands, the auth/db/cli/sync_users.php script, and the enrol/database/cli/sync.php script to sync our enrollments with the external enrollments database.

**moosh_create_course_cats.sh**

This creates all the needed course categories for a term (e.g. the parent semester category and then all the children departmental categories as well as the "Metacourses" category), with a prompt asking you to specify the term on the command line. We run this once per term a few months before it begins; it is a prerequisite to the term being added to the `MOODLE_ENROLLER_TERMS` setting on the portal integrations server which, in turn, serves as our external enrollments database.

**moosh_delete_excluded_users.sh**

This script deletes users with usernames starting with `apply-` which the portal creates for CCA applicants. It is no longer needed because now we sync users from the Portal Integrations server which does not included applicants. The process of importing and then deleting applicant users proved problematic because Moodle's database doesn't truly remove the user tuple, they slowly build up and balloon the database so much that user operations begin to slow down.

## web assets

**scss**

SCSS which you insert this code in the text area under Site Administration > Appearance > Themes > Boost > Advanced Settings.

We display the "CCA" logo via an SVG background image copied from the main CCA site and then URL-encoded (# -> %23).

**js**

We insert custom scripts into the `additionalhtml > footer` settings area. Our scripts are all under the additionalhtml directory and there is a gulp process for compiling them.
