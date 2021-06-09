#!/usr/bin/env bash
# create a sandbox courses for a department

if [ $1 = '-h' -o $1 = 'help' -o $1 = '--help' ]; then
    echo "usage: sandbox.sh DEPARTMENT_CODE DEPARTMENT_NAME [FACULTY_USERNAME]"
    echo "example: sandbox.sh ANIMA Animation ephetteplace"
    echo "Faculty username is optional. Course is created under the Sandboxes category."
    exit
fi

SANDBOXES_CATEGORY_ID="872"
DEPARTMENT_CODE="$1"
COURSE_SHORTNAME="${DEPARTMENT_CODE}-SANDBOX"
DEPARTMENT_NAME="$2"
FACULTY_USERNAME="$3"

moosh () { /opt/moosh/moosh.php -n $@; }

moosh course-create --category=${SANDBOXES_CATEGORY_ID} --fullname="${DEPARTMENT_NAME} Sandbox" --idnumber="${COURSE_SHORTNAME}" "${COURSE_SHORTNAME}"

if [ -n ${FACULTY_USERNAME} ]; then
    moosh course-enrol -r editingteacher -s ${COURSE_SHORTNAME} ${FACULTY_USERNAME}
fi
