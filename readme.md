# CCA Moodle customizations

Misc command-line management scripts and web assets for Moodle. Install these to `admin/*` and ensure that their directories are listed in the .git/info/exclude file so that our Moodle git repo doesn't complain about them being untracked.

Also includes a couple of tools with web wrappers for convenience. Put the `cca_tools` directory under `admin` and superusers will be able to access them at `moodle.cca.edu/admin/cca_tools/[queries/unenroll]`.

The moodle.logrotate file configures how logrotate handles Moodle's logs (cron.log and enroll.log) and should be copied to /etc/logrotate.d/moodle.

Many of these scripts are outdated or were used one specific time in the past. The ones that are still frequently employed (as of Fall 2019) are enrollment_cron.sh, cca_set_cas_logins, & moosh_create_course_cats.sh.

## cca_tools

These are add-ons to Moodle's admin side which help us extract analytics from the software.

**queries**

Generates statistical data through a web interface accessible only to logged-in site administrators. Result columns can be sorted by clicking headers, and data can be output to .csv for further processing with pivot tables, etc.

**unenroll**

Unenroll a user from a class by User ID and Course ID. It is unclear why this is needed when enrollments are synced from an external database; we should prefer fixing enrollment issues upstream when possible.

## API Tools

These are example scripts for accessing different portions of the Moodle REST APIs. The Moodle APIs are very comprehensive but sometimes a bit tricky to use. These scripts are both examples and not intended for production use, though similar code is running in other CCA apps. The easiest way to run them is to obtain a Web Services token at https://moodle.cca.edu/admin/settings.php?section=webservicetokens for a Service with the appropriate API permissions, then save that token in a ".token" file in the root of this project. These scripts check for the existence of that file and use it as the "wstoken" parameter in API calls.

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

----

**cca_append_term_longname.php**

Retroactively appends term ID to course full names for SP16. It appears this was a one-time operation needed for this term, perhaps because we decided to change the way that course names were formatted.

**cca_bulk_delete.php**

Bulk-delete all courses in the system. **We should probably stay far, far away from this one**. Likely related to some older workflow wherein courses were cleaned out and recreated periodically.

**cca_check_course.php**

Prompts for a course ID and returns complete list of all teachers and students. Sanity check: If teacher count + student count does not equal the output of `get_enrolled_users`, throw an alert.

**cca_check_person.php**

Prompts for a username and returns a list of all courses in which that user is currently enrolled.

**cca_drop_duplicate_enrolments**

In some cases, a student may be manually enrolled by an instructor, then later enrolled again via our enrollment script, giving them a duplicate enrollment (same user, course, and role) but with a different enrollment type ("manual" vs "ccaroles"). The ccaenrol data importer catches these duplicates during the import stage; this script is run over the entire database and cleans up the older ones.

**cca_drop_inactive.php**

Removes the enrollment record from user_enrolments for all inactive students (true drops).

**cca_ensure_manual_enrols**

Enable manual and guest enrollments for all courses.

**cca_fix_redundant_enrol_types**

Due to an early error by a Moodle consultant, many courses had multiple sets of redundant enrollment types, e.g. two or three entries for guest enrollments, two or three for ccaroles enrollments, etc. This caused various strange errors throughout the system. This one-time fixit script detects and consolidates those entries.

**cca_get_parent_course**

Asks for a Moodle course ID as input and returns the ID of the parent/metacourse, if one exists.

**cca_hide_child_courses**

Detects and groups all metacourses with their child courses, then sets child courses to invisible. This was written as a repair script and should seldom be needed.

**cca_show_meta_courses**

Detects and groups all metacourses with their child courses, printing out a reference list.

**cca_unenrol_user**

Given a Moodle user ID and a Moodle course ID, unenrolls that user from that course.

**dmba-cohort.sh**

Takes a text file of usernames (one per line) named "dmba.txt" in the working directory and adds them all to the DMBA Community course. We used this once but then DMBA moved to Google Classroom so cohorts are no longer managed in Moodle.

## web assets

**theme custom.css**

A (very small) snippet of CSS that just display a logo in the theme's header right now. You insert this code in the text area under Site Administration > Appearance > Themes > [Clean](https://moodle.cca.edu/admin/settings.php?section=themesettingclean).

The SVG image here is copied from the main CCA site and then URL-encoded (# -> %23).
