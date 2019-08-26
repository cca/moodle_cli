#!/usr/bin/env bash

echo "$(date) - running Moodle enrollment script"
cd /opt/moodle
# set "unenroll action" to "unenroll user from course"
sudo -u www-data /usr/local/bin/moosh config-set unenrolaction 0 enrol_database
# we sync users first and then enrollments
# we enable auth db plugin to sync users, then disable it because we won't want
# people actually signing in via external database
# we'll change all their accounts to CAS later
sudo -u www-data /usr/local/bin/moosh auth-manage enable db
sudo -u www-data /usr/bin/php /opt/moodle/auth/db/cli/sync_users.php -v
sudo -u www-data /usr/local/bin/moosh auth-manage disable db
sudo -u www-data /usr/bin/php /opt/moodle/admin/cca_cli/cca_set_cas_logins.php

sudo -u www-data /usr/bin/php /opt/moodle/enrol/database/cli/sync.php -v
# set unenroll action _back_ to "keep user enrolled"
sudo -u www-data /usr/local/bin/moosh config-set unenrolaction 1 enrol_database
echo "$(date) - enrollment script finished"
