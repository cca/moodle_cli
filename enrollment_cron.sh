#!/usr/bin/env bash

export PATH="/opt/bitnami/php/bin:/usr/bin:/usr/local/bin:$PATH"
export TZ="America/Los_Angeles"

cd /bitnami/moodle || (echo "Error: unable to cd into /bitnami/moodle, does directory exist?" >&2; exit)
echo "$(date) - running Moodle enrollment script"
echo 'Setting "unenroll action" to "unenroll user from course"'
moosh -n config-set unenrolaction 0 enrol_database
# this script no longer syncs users from the external database
# users are created when they login by the auth_saml2 plugin

# ensure local course template plugin is configured to overwrite course configuration
# or else only some of our template courses' settings aren't inherited
sed -i "s/'overwrite_conf' => 0/'overwrite_conf' => 1/" local/course_template/classes/backup.php \
    || (echo "Error: unable to ensure overwrite_conf = true in local/course_template plugin. Exiting without synchronizing enrollments." >&2; exit)

# silence common but harmless error (numeric username accounts, often in EXT courses)
UN_MSG="error: skipping unknown user username "
php admin/cli/scheduled_task.php --execute='\enrol_database\task\sync_enrolments' | sed "/$UN_MSG/d"
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
        moosh -n cohort-enrol -u "${USER}" "FACULTY"
    done
else
    echo 'No users missing from Faculty cohort.'
fi

# we run this script as root but it touches files in Moodle's data dir
# and then later the application cannot interact with those files, so
# below we reset the ownership of all files in the data directory
find /opt/moodledata -user root -exec chown daemon {} \;

echo "$(date) - enrollment script finished"
echo
