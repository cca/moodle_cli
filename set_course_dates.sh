#!/usr/bin/env bash
# set the start & end date for all courses within a category
# usage: ./set_course_dates.sh "2024-01-01" "2024-05-15"

# Bash strict mode
set -euo pipefail

# convert dates to UNIX timestamps
date_to_unix() {
    date -d "$1" +%s
}

export TZ="America/Los_Angeles"
# default to Course Templates > Program Templates category
CATEGORY=${CATEGORY:-877}
START=$(date_to_unix "$1")
END=$(date_to_unix "$2")

moosh -n course-config-set category "${CATEGORY}" startdate "${START}"
moosh -n course-config-set category "${CATEGORY}" enddate "${END}"
