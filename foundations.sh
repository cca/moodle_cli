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

# moosh category-list claims it can accept a "search" parameter but
# in my testing that did not work so this is the best we can do
SANDBOXES_CATEGORY_ID=$(moosh -n category-list | grep FOUNDATIONS | cut -f 1 -d ' ')

create_course () {
    USERNAME="$1"
    SURNAME="$2"
    if [ -z "${SURNAME}" ]; then
        SURNAME="${USERNAME}"
    fi
    COURSE_ID="SANDBOX-${USERNAME}"

    # create a course & store the created ID number, which we have to `grep`
    # for because moosh includes error text in stdout
    ID=$(moosh -n course-create --category="${SANDBOXES_CATEGORY_ID}" \
        --fullname="${SURNAME} Practice Course" \
        --idnumber="${COURSE_ID}" "${COURSE_ID}" 2>/dev/null | grep -x '^[0-9]*$')

    # if we created a new course, configure it
    if [[ $? && -n "${ID}" ]]; then
        echo "Created course ${ID} '${SURNAME} Practice Course'"
        # enrol user as instructor and a test student
        # set the course start date to the past, no end date, & make it visible
        # 1628406000 => 2021-08-08 00:00 PT
        moosh -n course-enrol -r editingteacher -s "${COURSE_ID}" "${USERNAME}" \
        && moosh -n course-enrol -s "${COURSE_ID}" stest \
        && moosh -n course-config-set course "${ID}" startdate 1628406000 \
        && moosh -n course-config-set course "${ID}" enddate 0 \
        && moosh -n course-config-set course "${ID}" visible 1 \
        && echo "Successfully created & configured practice course for ${USERNAME}"
    fi
}

# case 1: first argument is a CSV of faculty
if [[ -f "$1" ]]; then
    grep -v '^ *#' "$1" | while IFS= read -r LINE; do
        if [[ -n ${LINE} ]]; then
            USERNAME=$(echo "${LINE}" | cut -d , -f 1)
            SURNAME=$(echo "${LINE}" | cut -d , -f 2)
            create_course "${USERNAME}" "${SURNAME}"
        fi
    done
else
    # case 2: only a single course
    create_course "$1" "$2"
fi
