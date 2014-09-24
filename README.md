moodle_cli
==========

Misc command-line management scripts for Moodle. Install these (preferably via moodle_build) to `admin/cli/*`.

## cca_check_course.php
Prompts for a course ID and returns complete list of all teachers and students.

Sanity check: If teacher count + student count does not equal the output of
`get_enrolled_users`, throw an alert.

## cca_check_person.php
Prompts for a username and returns a list of all courses in which that user is currently enrolled.
