#!/usr/bin/env bash

export PATH="/opt/bitnami/php/bin:$PATH"
export TZ="America/Los_Angeles"

# pipe fails if any command in it fails (needed for sync_users | sed cmd)
set -o pipefail
MOODLE_DIR='/bitnami/moodle'
# we silence these common but harmless errors (or else the log file fills up)
CAS_MSG="user with this username was already created through 'cas' plugin."
UN_MSG="error: skipping unknown user username "

echo "$(date) - running Moodle enrollment script"
cd $MOODLE_DIR
echo 'Setting "unenroll action" to "unenroll user from course"'
moosh -n config-set unenrolaction 0 enrol_database
# we sync users first and then enrollments
# we enable auth db plugin to sync users, then disable it because we won't want
# people actually signing in via external database
# we'll change all their accounts to CAS later
moosh -n auth-manage enable db
php admin/tool/task/cli/schedule_task.php \
    --execute='\auth_db\task\sync_users' | sed "/$CAS_MSG/d"
# @TODO is the below still true with the scheduled task? test on dev
# unfortunately sync_users returns 0 if it cannot access its db but this will
# catch some other errors possibly
SYNC_STATUS=$?
moosh -n auth-manage disable db
php admin/cca_cli/cca_set_cas_logins.php

php admin/tool/task/cli/schedule_task.php \
    --execute='\enrol_database\task\sync_enrolments' | sed "/$UN_MSG/d"
ENROL_STATUS=$?
echo 'Setting "unenroll action" back to "keep user enrolled"'
moosh -n config-set unenrolaction 1 enrol_database

# add users who are instructors of a course but not already in the FACULTY
# (id = 2) cohort, nor in the Faculty EXCEPTION (id = 3) cohort, to the Faculty
# cohort. Run after user and enrollment sync.
echo 'Checking users for instructors who are not in the FACULTY cohort...'

# see the "List of Instructors not in Faculty Cohort" ad-hoc database query
# for this SQL - https://moodle.cca.edu/report/customsql/view.php?id=8
# `moosh sql-run` returns text formatted like this:
# Record 8
# stdClass Object
# (
#     [id] => 25152
# )
# so we use `sed` to get only the user ID numbers
USERS=$(moosh -n sql-run 'SELECT user.id FROM {user} user JOIN {role_assignments} ra ON user.id = ra.userid JOIN (SELECT * FROM {context} WHERE contextlevel = 50) context ON ra.contextid = context.id JOIN {role} role ON ra.roleid = role.id LEFT JOIN (SELECT cms.id, cms.userid FROM {cohort_members} cms JOIN {cohort} coh ON cms.cohortid = coh.id WHERE coh.name = "Faculty" OR coh.name = "Faculty Exceptions") cm ON user.id = cm.userid WHERE role.id = 3 AND cm.id IS NULL GROUP BY username ORDER BY username ASC' | sed -n "/\[id\] =>/s/ \+\[id\] => //p")

if test -n "${USERS}"; then
    echo "Found $(echo "${USERS}" | wc -l) missing faculty."
    for USER in ${USERS}; do
        moosh -n cohort-enrol -u ${USER} "FACULTY"
    done
else
    echo 'No users missing from Faculty cohort.'
fi

echo "$(date) - enrollment script finished"

# if either sync task failed, write a notice about it
if [ $SYNC_STATUS -ne 0 -o $ENROL_STATUS -ne 0 ]; then
    echo "A Moodle executed task failed."
    echo "\auth_db\task\sync_users exit code: ${SYNC_STATUS}"
    echo "\enrol_database\task\sync_enrolments exit code: ${ENROL_STATUS}"
fi

echo
