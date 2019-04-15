# CCA Moodle customizations

Misc command-line management scripts and web assets for Moodle. Install these to `admin/*` and ensure that their directories are listed in the .git/info/exclude file so that our Moodle git repo doesn't complain about them being untracked.

Also includes a couple of tools with web wrappers for convenience. Put the `cca_tools` directory under `admin` and superusers will be able to access them at `moodle.cca.edu/admin/cca_tools/[queries/unenroll]`.

Many of these scripts are outdated or were used one specific time in the past. The ones that are still frequently employed are dmba-cohort.sh, enrollment_cron.sh, & moosh_create_course_cats.sh.

## cca_tools

**queries**

Generates statistical data through a web interface accessible only to logged-in site administrators. Result columns can be sorted by clicking headers, and data can be output to .csv for further processing with pivot tables, etc.

**unenroll**

Unenroll a user from a class by User ID and Course ID. It is unclear why this is needed when enrollments are synced from an external database; we should prefer fixing enrollment issues upstream when possible.

## CLI

**cca_append_term_longname.php**

Retroactively appends term ID to course full names for SP16. It appears this was a one-time operation needed for this term, perhaps because we decided to change the way that course names were formatted.

**cca_bulk_delete.php**

Bulk-delete all courses in the system. **We should probably stay far, far away from this one**. Likely related to some older workflow wherein courses were cleaned out and recreated periodically.

**cca_check_course.php**

Prompts for a course ID and returns complete list of all teachers and students.

Sanity check: If teacher count + student count does not equal the output of `get_enrolled_users`, throw an alert.

**cca_check_person.php**

Prompts for a username and returns a list of all courses in which that user is currently enrolled.

**cca_drop_duplicate_enrolments**

In some cases, a student may be manually enrolled by an instructor, then later enrolled again via datatel, giving them a duplicate enrolment (same user, course, and role) but with a different enrolment type ("manual" vs "ccaroles"). The ccaenrol data importer catches these duplicates during the import stage; this script is run over the entire database and cleans up the older ones.

**cca_drop_inactive.php**

Removes the enrolment record from user_enrolments for all inactive students (true drops).

**cca_ensure_manual_enrols**

Enable manual and guest enrolments for all courses.

**cca_fix_redundant_enrol_types**

Due to an early error by a Moodle consultant, many courses had multiple sets of redundant enrolment types, e.g. two or three entries for guest enrolments, two or three for ccaroles enrolments, etc. This caused various strange errors throughout the system. This one-time fixit script detects and consolidates those entries.

**cca_get_parent_course**

Asks for a Moodle course ID as input and returns the ID of the parent/metacourse, if one exists.

**cca_hide_child_courses**

Detects and groups all metacourses with their child courses, then sets child courses to invisible. This was written as a repair script and should seldom be needed.

**cca_set_cas_logins.php**

Sets all logins to type `cas` except for the "guest" and "etadmin" users.

Needed because Moodle's "external database enrollment" plugin sets all to "db" with no config option, so we run this via cron after the enroller runs.

**cca_show_meta_courses**

Detects and groups all metacourses with their child courses, printing out a reference list.

**cca_unenrol_user**

Given a Moodle user ID and a Moodle course ID, unenrols that user from that course.

**dmba-cohort.sh**

Takes a text file of usernames (one per line) named "dmba.txt" in the working directory and adds them all to the DMBA Community course.

**enrollment_cron.sh**

Cron calls this script which in turns uses a few moosh commands, the auth/db/cli/sync_users.php script, and the enrol/database/cli/sync.php script to sync our enrollments with the external enrollments database.

**moosh_create_course_cats.sh**

This creates all the needed course categories for a term (e.g. the parent semester category and then all the children departmental categories as well as the "Metacourses" category), with a prompt asking you to specify the term on the command line. We run this once per term a few months before it begins; it is a prerequisite to the term being added to the `MOODLE_ENROLLER_TERMS` setting on the portal integrations server which, in turn, serves as our external enrollments database.

**moosh_delete_excluded_users.sh**

This script deletes users with usernames starting with `apply-` which the portal creates for CCA applicants. It is no longer needed because now we sync users from the Portal Integrations server which does not included applicants. The process of importing and then deleting applicant users proved problematic because Moodle's database doesn't truly remove the user tuple, they slowly build up and balloon the database so much that user operations begin to slow down.

## web assets

**theme custom.css**

A (very small) snippet of CSS that just display a logo in the theme's header right now. You insert this code in the text area under Site Administration > Appearance > Themes > [Clean](https://moodle.cca.edu/admin/settings.php?section=themesettingclean).

The SVG image here is copied from the main CCA site and then URL-encoded (# -> %23).
