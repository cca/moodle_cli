#!/usr/bin/env bash
# Create practice courses, using the foundations.sh script, for all students
# enrolled in the Moodle Foundations (id=2116) course.
# Run on a nightly schedule
cd /bitnami/moodle
# List of student usernames, ignoring test accounts
# NOTE: `sed` must use extended regular expressions (-E)
STUDENTS=$(/usr/bin/moosh -n user-list --course 2116 --course-role student \
    | cut -f 1 -d ' ' | sed -E '/^(s|f)test1?$/d')

TZ=America/Los_Angeles date
echo "Creating practice courses for Moodle Foundations. There are" $(echo ${STUDENTS} | wc -w) "students."

for STUDENT in ${STUDENTS}; do
    /bitnami/moodle/admin/cca_cli/foundations.sh ${STUDENT}
done
