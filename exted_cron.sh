#!/usr/bin/env bash
# Create practice courses for all students enrolled in CCA Extension Moodle Essentials
# https://moodle.cca.edu/course/edit.php?id=7411 based on foundations scripts
# Runs on a nightly schedule as part of foundations_cron.sh
export PATH="/opt/bitnami/php/bin:/usr/bin:/usr/local/bin:$PATH"
export TZ="America/Los_Angeles"
cd /bitnami/moodle || (echo "Error: unable to cd into /bitnami/moodle, does directory exist?" >&2 || exit 1)
echo "$(date) - creating Extension Moodle Essentials practice courses"

COURSE_ID=$(moosh -n course-list -i "shortname = 'EXTED-MOODLE-ESSENTIALS'")

if [[ -z "$COURSE_ID" ]]; then
    echo "Error: unable to find Extension Essentials course." >&2
    exit 1
fi

# omit test students
# ! `sed` must use extended regular expressions (-E)
STUDENTS=$(moosh -n user-list --course "${COURSE_ID}" --course-role student \
    | cut -f 1 -d ' ' | sed -E '/^library-test-/d')
CATEGORY_ID=$(moosh -n category-list FOUNDATIONS | grep FOUNDATIONS | cut -f 1 -d ' ')

echo "There are" "$(echo "${STUDENTS}" | wc -w)" "students."

create_course () {
    USERNAME="$1"
    COURSE_SHORTNAME="EXTED-PRACTICE-${USERNAME}"

    EXISTING_COURSE=$(moosh -n course-list "shortname = '${COURSE_SHORTNAME}'" | tr -d '\n')
    if [[ -n "${EXISTING_COURSE}" ]]; then
        echo "${COURSE_SHORTNAME} already exists"
        return 0
    fi

    # create a course & store the created ID number, which we have to `grep`
    # for because moosh includes error text in stdout
    ID=$(moosh -n course-create --category="${CATEGORY_ID}" \
        --fullname="${USERNAME} Practice Course" \
        --idnumber="${COURSE_SHORTNAME}" "${COURSE_SHORTNAME}" 2>/dev/null | grep -x '^[0-9]*$')

    # if we created a new course, configure it
    if [[ $? && -n "${ID}" ]]; then
        echo "Created course ${ID} '${USERNAME} Practice Course'"
        # enrol user as instructor
        moosh -n course-enrol -r editingteacher -s "${COURSE_SHORTNAME}" "${USERNAME}"
    else
        echo "Error creating course ${COURSE_SHORTNAME} ${USERNAME} Practice Course'" >&2
    fi
}

for STUDENT in ${STUDENTS}; do
    create_course "$STUDENT"
done
