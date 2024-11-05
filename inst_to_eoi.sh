#!/usr/bin/env bash
# Given a CSV (with no header) of username,course ID pairs
# change the user's enrollments to EOI. We feed this a list
# of all manually-enrolled Instructors at the end of the term
# to make them EOI so they are skipped during course evaluations.
CSV="$1"

if [ ! -f "$CSV" ]; then
    echo "ERROR: '$CSV' is not a file. Pass the path to a CSV of instructors and courses to this script as the first argument."
    exit 1
fi

change_to_eoi () {
    USER="$1"
    COURSE="$2"

    result=$(moosh -n course-unenrol "$COURSE" "$USER")
    # no meaningful exit status but we get "Succesfully unenroled user $ID" msg or nothing
    if [ -n "$result" ]; then
        moosh -n course-enrol -r exportonlyteacher --id "$COURSE" "$USER"
    fi
}

while IFS= read -r line; do
    username=$(echo "$line" | cut -d ',' -f 1)
    # course-unenrol needs numeric user id so we get it this way
    user=$(moosh -n user-list -i "username = '$username'")
    course=$(echo "$line" | cut -d ',' -f 2)

    if ! echo "$line" | grep ',' >/dev/null || [ -z "$user" ] || [ -z "$course" ]; then
        echo "ERROR: username or course ID missing for this line of the CSV: $line"
    else
        change_to_eoi "$user" "$course"
    fi
done < "$CSV"
