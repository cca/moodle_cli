#!/usr/bin/env bash

echo "$(date) - running Moodle enrollment script"
# set "unenroll action" to "unenroll user from course"
moosh -n config-set unenrolaction 0 enrol_database
# sync users first and then enrollments
sudo -u www-data /usr/bin/php /opt/moodle/auth/db/cli/sync_users.php -v
sudo -u www-data /usr/bin/php /opt/moodle/enrol/database/cli/sync.php -v
# set unenroll action _back_ to "keep user enrolled"
moosh -n config-set unenrolaction 1 enrol_database
echo "$(date) - enrollment script finished"
