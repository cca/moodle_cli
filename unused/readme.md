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

**moosh_delete_excluded_users.sh**

This script deletes users with usernames starting with `apply-` which the portal creates for CCA applicants. It is no longer needed because now we sync users from the Portal Integrations server which does not included applicants. The process of importing and then deleting applicant users proved problematic because Moodle's database doesn't truly remove the user tuple, they slowly build up and balloon the database so much that user operations begin to slow down.
