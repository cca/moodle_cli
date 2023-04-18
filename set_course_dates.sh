#!/usr/bin/env bash
# set the start & end date for all courses within a category
# https://moosh-online.com/commands/
START=$1
END=$2
# default to Course Templates > Program Templates category
CATEGORY=${CATEGORY:-877}

if [ -z "${START}" ] || [ -z "${END}" ]; then
    echo -e 'Error: you must provide both a start and end date in UNIX timestamps. Category defaults to 877 (Program Templates). Usage example:\n\n\tCATEGORY=877 ./set-course-date.sh 1610956800 1621061940' >&2
    exit 1
fi

moosh -n course-config-set category "${CATEGORY}" startdate "${START}"
moosh -n course-config-set category "${CATEGORY}" enddate "${END}"
