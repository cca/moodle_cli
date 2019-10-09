#!/usr/bin/env bash

# pipe fails if any command in it fails (needed for sync_users.php | sed cmd)
set -o pipefail
ADMINS="ephetteplace@cca.edu,bobbywhite@cca.edu,nlammiller@cca.edu"
# we will silence this auth db plugin message (else enroll.log fills up)
MSG="user with this username was already created through 'cas' plugin."
moosh () { sudo -u www-data /usr/local/bin/moosh $@; }
php () { sudo -u www-data /usr/bin/php $@; }

echo "$(date) - running Moodle enrollment script"
cd /opt/moodle
# set "unenroll action" to "unenroll user from course"
moosh config-set unenrolaction 0 enrol_database
# we sync users first and then enrollments
# we enable auth db plugin to sync users, then disable it because we won't want
# people actually signing in via external database
# we'll change all their accounts to CAS later
moosh auth-manage enable db
php auth/db/cli/sync_users.php -v | sed "/$MSG/d"
SYNC_STATUS=$?
moosh auth-manage disable db
php admin/cca_cli/cca_set_cas_logins.php

php enrol/database/cli/sync.php -v
ENROL_STATUS=$?
# set unenroll action _back_ to "keep user enrolled"
moosh config-set unenrolaction 1 enrol_database
echo "$(date) - enrollment script finished"

# if either command failed, email administrators
if [ $SYNC_STATUS -ne 0 -o $ENROL_STATUS -ne 0 ]; then
    echo "Script failure. auth/db/cli/sync_users.php status: ${SYNC_STATUS} | enrol/database/cli/sync.php status: ${ENROL_STATUS}"
    echo "Emailing ${ADMINS}"
    mail -s 'Moodle: Enrollment Script Error' -a /var/log/moodle/enroll.log $ADMINS < echo 'See attached log file.'
fi
