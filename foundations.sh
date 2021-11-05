#!/usr/bin/env bash

if [[ "$1" = "-h" || "$1" =~ "-help" || -z "$1" ]]; then
    echo -e "Create sandbox course for faculty enrolled in Moodle Foundations.

Usage:
    ./foundations.sh faculty.csv
    ./foundations.sh USERNAME [SURNAME]

USERNAME is the faculty member's CCA username (not email address, no
'@cca.edu'). SURNAME can have spaces in it but would need to quoted in that
case, e.g., 'Van Halen'. If you don't provide a surname then it defaults to
the USERNAME. The faculty.csv file is a list of these faculty username &
surname pairs separated by commas, e.g., 'ephetteplace,Phetteplace'."
    exit 0
fi

SANDBOXES_CATEGORY_ID="${MOODLE_CATEGORY:-1115}"

create_course () {
    USERNAME="$1"
    SURNAME="$2"
    if [ -z ${SURNAME} ]; then
        SURNAME=${USERNAME}
    fi
    COURSE_ID="SANDBOX-${USERNAME}"
    # NOTE: two instructors with the same surname will cause an error
    /usr/bin/moosh -n course-create --category=${SANDBOXES_CATEGORY_ID} \
        --fullname="${SURNAME} Practice Course" \
        --idnumber=${COURSE_ID} ${COURSE_ID} 2>/dev/null \
        && /usr/bin/moosh -n course-enrol -r editingteacher -s ${COURSE_ID} ${USERNAME} \
        && /usr/bin/moosh -n course-enrol -s ${COURSE_ID} stest \
        && echo "Successfully created practice course for ${USERNAME}"
}

# case 1: first argument is a CSV of faculty
if [[ -f $1 ]]; then
    IFS=$'\n'
    for LINE in $(cat $1); do
        if [[ ! -z ${LINE} ]]; then
            USERNAME=$(echo "${LINE}" | cut -d , -f 1)
            SURNAME=$(echo "${LINE}" | cut -d , -f 2)
            create_course ${USERNAME} "${SURNAME}"
        fi
    done
else
    # case 2: only a single course
    create_course $1 "$2"
fi
