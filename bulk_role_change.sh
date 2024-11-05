#!/usr/bin/env bash
# sets enrollments of a series of courses (e.g. a whole category) to a given role
# can use the MOODLE_ROLE environment variable. We use this every semester to set
# all template course enrollments to EOI or to Instructor.
ROLE=${MOODLE_ROLE:-exportonlyteacher}
# default to Course Templates > Program Templates category
CATEGORY=${MOODLE_CATEGORY:-877}

COURSES=$(moosh -n course-list -c "${CATEGORY}" -i)

# shellcheck disable=SC2068
for COURSE in ${COURSES[@]}; do
    # get all enrolled users, unenroll them, re-enroll them in the chosen role
    echo "Setting all users enrolled in course ID ${COURSE} to '${ROLE}'"
    IDS=$(moosh -n user-list --id --course="${COURSE}")
    if [[ -n $IDS ]]; then
        # shellcheck disable=SC2086
        moosh -n course-unenrol "${COURSE}" ${IDS}
        # shellcheck disable=SC2086
        moosh -n course-enrol -r "${ROLE}" --id "${COURSE}" ${IDS}
    fi
done
