#!/usr/bin/env bash
# Create practice courses, using the foundations.sh script, for all students
# enrolled in the Moodle Foundations (id=2116) course.
# Run on a nightly schedule
# @TODO check PATH on production, some of these may not be needed
export PATH="/opt/bitnami/php/bin:/usr/bin:/usr/local/bin:$PATH"
export TZ="America/Los_Angeles"
cd /bitnami/moodle || (echo "Error: unable to cd into /bitnami/moodle, does directory exist?" >&2 || exit)
# List of student usernames, ignoring test accounts
# NOTE: `sed` must use extended regular expressions (-E)
FOUNDATIONS_ID=$(moosh -n course-list -i "fullname LIKE '%Moodle Foundations%'")
STUDENTS=$(moosh -n user-list --course ${FOUNDATIONS_ID} --course-role student \
    | cut -f 1 -d ' ' | sed -E '/^(s|f)test1?$/d')

date
echo "Creating practice courses for Moodle Foundations. There are" $(echo "${STUDENTS}" | wc -w) "students."

for STUDENT in ${STUDENTS}; do
    /bitnami/moodle/admin/cca_cli/foundations.sh "${STUDENT}"
done

# see note at bottom of enrollment_cron.sh for explanation
find /opt/moodledata -user root -exec chown daemon {} \;
