#!/usr/bin/env bash
# enroll a user in all of a program's courses in a particular semester
# including metacourses
usage () {
    echo -e 'Usage:\n\t./enroll_in_all.sh $SEMESTER $PROGRAM $USER [ $ROLE ]\n'
    echo -e 'Examples:\n\t./enroll_in_all.sh 2022SP GAMES ephetteplace'
    echo -e '\t./enroll_in_all.sh 2022FA ANIMA nchan exportonlyteacher\n'
    echo 'ROLE defaults to editingteacher if not provided. All other arguments are required.'
    echo 'Note that this script only works for regular courses with correctly formatted shortnames, but that does include metacourses.'
}

if [[ -z ${1} || ${1} = "-h" || ${1} = "--help" || ${1} = "help" ]]; then
    usage
    exit 0
fi

SEMESTER=$1
PROGRAM=$2
USER=$3
ROLE=${4:-editingteacher}

if [[ -z ${SEMESTER} || -z ${PROGRAM} || -z ${USER} ]]; then
    echo -e "ERROR: semester, program, and user arguments are required.\n" 1>&2
    usage
    exit 1
fi

cd /bitnami/moodle || exit
# Ensure moosh is on PATH
export PATH=${PATH}:/usr/bin:/usr/local/bin

echo "Enrolling user ${USER} in all ${SEMESTER} ${PROGRAM} courses with role ${ROLE}"

for course in $(moosh -n course-list -i "shortname LIKE \"%${PROGRAM}%-${SEMESTER}\""); do
    message=$(moosh -n course-enrol -r "${ROLE}" "${course}" "${USER}")
    if [[ -n "${message}" ]]; then
        echo "${message}"
        echo "Above error was with course number $course"
        echo "https://moodle.cca.edu/course/view.php?id=$course"
    fi
done
