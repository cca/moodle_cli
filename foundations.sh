#!/usr/bin/env bash
# Create a practice course in CATEGORY for USERNAME

create_course () {
    CATEGORY="$1"
    USERNAME="$2"
    SHORTNAME="SANDBOX-${USERNAME}"

    EXISTING_COURSE=$(moosh -n course-list "shortname = '$SHORTNAME'" | tr -d '\n')
    if [[ -n "$EXISTING_COURSE" ]]; then
        # we do not print a message because this happens for every pre-existing user
        return 0
    fi

    # Create the course. Command output has ID but parsing it is a trap,
    # so we run a separate moosh command below to find its ID.
    moosh -n course-create --category="${CATEGORY}" \
        --fullname="${USERNAME} Practice Course" \
        --idnumber="${SHORTNAME}" "${SHORTNAME}"

    # Get the ID of the newly created course, this returns 0 even if there are no matches.
    ID=$(moosh -n course-list --id "shortname = '$SHORTNAME'")

    # If we found the newly created course, configure it.
    if [[ -n "${ID}" ]]; then
        echo "Created course ${ID} ${USERNAME} Practice Course"
        # enrol user as instructor and a test student
        # set the course start date to the past, no end date, & make it visible
        # 1628406000 => 2021-08-08 00:00 PT
        moosh -n course-enrol -r editingteacher -s "${SHORTNAME}" "${USERNAME}" \
        && moosh -n course-enrol -s "${SHORTNAME}" library-test-student-1 \
        && moosh -n course-config-set course "${ID}" startdate 1628406000 \
        && moosh -n course-config-set course "${ID}" enddate 0 \
        && moosh -n course-config-set course "${ID}" visible 1 \
        && echo "Successfully created & configured practice course for ${USERNAME}"
    else
        echo "Error creating course ${SHORTNAME} ${USERNAME} Practice Course'"
    fi
}

create_course "$1" "$2"
