moodle_cli
==========

Misc command-line management scripts and web interfaces for Moodle. Install these (preferably via moodle_build) to `admin/cli/*`.

Also includes a couple of tools with web wrappers for convenience. Put the `cca_tools` directory under `admin` and superusers will be able to access them at `yourdomain.edu/admin/cca_tools/[queries/unenroll]`.

## cca_set_cas_logins.php
Sets all logins to type "cas" except for "guest" and "etadmin".
Needed because Moodle's "external database enrollment" plugin sets all to "db" with no config option,
so we run this via cron after the enroller runs.

## cca_check_course.php
Prompts for a course ID and returns complete list of all teachers and students.

Sanity check: If teacher count + student count does not equal the output of
`get_enrolled_users`, throw an alert.

## cca_check_person.php
Prompts for a username and returns a list of all courses in which that user is currently enrolled.

## cca_drop_inactive.php
Removes the enrolment record from user_enrolments for all inactive students (true drops).

## cca_drop_duplicate_enrolments
 In some cases, a student may be manually enrolled by an instructor, then later enrolled again via datatel, giving them a duplicate enrolment (same user, course, and role) but with a different enrolment type ("manual" vs "ccaroles"). The ccaenrol data importer catches these duplicates during the import stage; this script is run over the entire database and cleans up the older ones.

## cca_show_meta_courses
Detects and groups all metacourses with their child courses, printing out a reference list.

## cca_hide_child_courses
Detects and groups all metacourses with their child courses, then sets child courses to invisible. This was written as a repair script and should seldom be needed.

## cca_unenrol_user
Given a Moodle user ID and a Moodle course ID, unenrols that user from that course.

## cca_fix_redundant_enrol_types
Due to an early error by a Moodle consultant, many courses had multiple sets of redundant enrolment types, e.g. two or three entries for guest enrolments, two or three for ccaroles enrolments, etc. This caused various strange errors throughout the system. This one-time fixit script detects and consolidates those entries.

## cca_get_parent_course
Asks for a Moodle course ID as input and returns the ID of the parent/metacourse, if one exists.

## cca_ensure_manual_enrols
Enable manual and guest enrolments for all courses

## queries
Generates statistical data through a web interface accessible only to logged-in site administrators. Result columns can be sorted by clicking headers, and data can be output to .csv for further processing with pivot tables, etc.
