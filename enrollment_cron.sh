#!/usr/bin/env bash

# pipe fails if any command in it fails (needed for sync_users.php | sed cmd)
set -o pipefail
ADMINS="ephetteplace@cca.edu,bobbywhite@cca.edu,nlammiller@cca.edu"
# we will silence this auth db plugin message (else enroll.log fills up)
MSG="user with this username was already created through 'cas' plugin."
moosh () { sudo /usr/local/bin/moosh -n $@; }
php () { sudo -u www-data /usr/bin/php $@; }

echo "$(date) - running Moodle enrollment script"
cd /opt/moodle38
echo 'Setting "unenroll action" to "unenroll user from course"'
moosh config-set unenrolaction 0 enrol_database
# we sync users first and then enrollments
# we enable auth db plugin to sync users, then disable it because we won't want
# people actually signing in via external database
# we'll change all their accounts to CAS later
moosh auth-manage enable db
php admin/tool/task/cli/schedule_task.php --execute='\auth_db\task\sync_users' | sed "/$MSG/d"
# unfortunately sync_users.php returns 0 if it cannot access its db but this will
# catch some other errors possibly
SYNC_STATUS=$?
moosh auth-manage disable db
php admin/cca_cli/cca_set_cas_logins.php

php admin/tool/task/cli/schedule_task.php --execute='\enrol_database\task\sync_enrolments'
ENROL_STATUS=$?
echo 'Setting "unenroll action" back to "keep user enrolled"'
moosh config-set unenrolaction 1 enrol_database

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
# so we use `sed` to get only the ID number itself
USERS=$(moosh -n sql-run 'SELECT user.id FROM {user} user JOIN {role_assignments} ra ON user.id = ra.userid JOIN (SELECT * FROM {context} WHERE contextlevel = 50) context ON ra.contextid = context.id JOIN {role} role ON ra.roleid = role.id LEFT JOIN (SELECT * FROM {cohort_members} cms WHERE cms.cohortid = 2 OR cms.cohortid = 3) cm ON user.id = cm.userid WHERE role.id = 3 AND cm.id IS NULL GROUP BY username ORDER BY username ASC' | sed -n "/\[id\] =>/s/ \+\[id\] => //p")

if test -n "${USERS}"; then
    echo "Found $(echo "${USERS}" | wc -l) missing faculty."
    for USER in ${USERS}; do
        moosh -n cohort-enrol -u ${USER} "FACULTY"
    done
else
    echo 'No users missing from Faculty cohort.'
fi

echo "$(date) - enrollment script finished"

# if either command failed, email administrators
if [ $SYNC_STATUS -ne 0 -o $ENROL_STATUS -ne 0 ]; then
    echo "Script failure. auth/db/cli/sync_users.php status: ${SYNC_STATUS} | enrol/database/cli/sync.php status: ${ENROL_STATUS}"
    echo "Emailing ${ADMINS}"
    echo 'See attached log file.' | mail -s 'Moodle: Enrollment Script Error' -a /var/log/moodle/enroll.log $ADMINS
fi

echo
