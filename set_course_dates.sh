#!/usr/bin/env bash
# set the start & end date for all courses within a category
# usage: ./set_course_dates.sh "2024-01-01" "2024-05-15"

# Bash strict mode
set -euo pipefail

START=$1
END=$2
# default to Course Templates > Program Templates category
CATEGORY=${CATEGORY:-877}

date_to_unix() {
    # convert timestamp to PST
    date -d "$1" +%s | awk '{print $1 - 8*60*60}'
}

# convert dates to UNIX timestamps
START=$(date_to_unix "${START}")
END=$(date_to_unix "${END}")

moosh -n course-config-set category "${CATEGORY}" startdate "${START}"
moosh -n course-config-set category "${CATEGORY}" enddate "${END}"
