#!/usr/bin/env bash
# Enable External Database auth plugin which is connected to Moodle Support Database
# run its sync user task, disable it, then change the auth type for all users to saml2
# This runs as a sub-task of enrollment_cron.sh
export PATH="/opt/bitnami/php/bin:/usr/bin:/usr/local/bin:$PATH"
export TZ="America/Los_Angeles"
cd /bitnami/moodle || (echo "Error: unable to cd into /bitnami/moodle, does directory exist?" >&2; exit)

REAL_AUTH_PLUGIN="saml2"
ERR_MSG="user with this username was already created through '${REAL_AUTH_PLUGIN}' plugin."
moosh -n auth-manage enable db
php admin/cli/scheduled_task --execute=\\auth_db\\task\\sync_users | sed "/${ERR_MSG}/d"
moosh -n auth-manage disable db
moosh -n sql-run "UPDATE {user} SET auth = '${REAL_AUTH_PLUGIN}' WHERE auth = 'db'"

find /opt/moodledata -user root -exec chown daemon {} \;
