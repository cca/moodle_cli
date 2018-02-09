#!/usr/bin/env bash

# set "unenroll action" to "unenroll user from course"
moosh -n config-set unenrolaction 0 enrol_database
# sync users first and then enrollments
/usr/bin/php auth/db/cli/sync_users.php
/usr/bin/php enrol/database/cli/sync.php
# set unenroll action _back_ to "keep user enrolled"
moosh -n config-set unenrolaction 1 enrol_database
