moodle_cli
==========

Misc command-line management scripts for Moodle. Install these (preferably via moodle_build) to `admin/cli/*`.

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
