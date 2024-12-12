#!/usr/bin/env bash
# Create a practice course in CATEGORY for USERNAME

create_course () {
    CATEGORY="$1"
    USERNAME="$2"
    COURSE_ID="SANDBOX-${USERNAME}"

    EXISTING_COURSE=$(moosh -n course-list "shortname = '$COURSE_ID'" | tr -d '\n')
    if [[ -n "$EXISTING_COURSE" ]]; then
        return 0
    fi

    # create a course & store the created ID number, which we have to `grep`
    # for because moosh includes error text in stdout
    ID=$(moosh -n course-create --category="${CATEGORY}" \
        --fullname="${USERNAME} Practice Course" \
        --idnumber="${COURSE_ID}" "${COURSE_ID}" 2>/dev/null | grep -x '^[0-9]*$')

    # if we created a new course, configure it
    if [[ $? && -n "${ID}" ]]; then
        echo "Created course ${ID} ${USERNAME} Practice Course"
        # enrol user as instructor and a test student
        # set the course start date to the past, no end date, & make it visible
        # 1628406000 => 2021-08-08 00:00 PT
        moosh -n course-enrol -r editingteacher -s "${COURSE_ID}" "${USERNAME}" \
        && moosh -n course-enrol -s "${COURSE_ID}" library-test-student-1 \
        && moosh -n course-config-set course "${ID}" startdate 1628406000 \
        && moosh -n course-config-set course "${ID}" enddate 0 \
        && moosh -n course-config-set course "${ID}" visible 1 \
        && echo "Successfully created & configured practice course for ${USERNAME}"
    fi
}

create_course "$1" "$2"
