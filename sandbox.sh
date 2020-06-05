#!/usr/bin/env bash

# create a sandbox courses for a department
moosh () { sudo /usr/local/bin/moosh -n $@; }

if [ $1 = '-h' -o $1 = 'help' -o $1 = '--help' ]; then
    echo "usage: sandbox.sh DEPARTMENT_CODE DEPARTMENT_NAME"
    echo "example: sandbox.sh ANIMA Animation"
    exit
fi

SANDBOXES_CATEGORY_ID="872"
DEPARTMENT_CODE="$1"
COURSE_SHORTNAME="${DEPARTMENT_CODE}-SANDBOX"
DEPARTMENT_NAME="$2"

moosh course-create --category ${SANDBOXES_CATEGORY_ID} --fullname "${DEPARTMENT_NAME} Sandbox" --idnumber ${COURSE_SHORTNAME} ${COURSE_SHORTNAME}
