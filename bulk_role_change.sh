#!/usr/bin/env bash
# sets enrollments of a series of courses (e.g. a whole category) to a given role
# can use the MOODLE_ROLE environment variable. Used in Fall 2021 to bulk change
# all the 2021SP template course enrollments to a read-only role. It is probably
# easiest to just edit this script to do the role/courses list (e.g. using a
# `moosh -n course-list` query) rather than make it take CLI arguments.
ROLE=${MOODLE_ROLE:-exportonlyteacher}
# default to Course Templates > Program Templates category
CATEGORY=${MOODLE_CATEGORY:-877}

COURSES=$(moosh -n course-list -c "${CATEGORY}" -i)

for COURSE in ${COURSES[@]}; do
    # get all enrolled users, unenroll them, re-enroll them in the chosen role
    echo "Setting all users enrolled in course ID ${COURSE} to '${ROLE}'"
    IDS=$(moosh -n user-list -i --course="${COURSE}")
    if [[ -n $IDS ]]; then
        moosh -n course-unenrol "${COURSE}" ${IDS}
        moosh -n course-enrol -r "${ROLE}" -i "${COURSE}" ${IDS}
    fi
done
