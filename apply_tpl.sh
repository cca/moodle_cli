#!/usr/bin/env bash
# Two usage profiles. To apply a template across a whole category (including subcategories):
# ./apply_tpl.sh --tpl TEMPLATE_COURSE_ID --category CATEGORY_ID
# To apply a template to a set of specific courses:
# ./apply_tpl.sh --tpl TEMPLATE_COURSE_ID COURSE_ID_1 COURSE_ID_2 ...
set -euo pipefail
IFS=$'\n\t'

# Argument parsing
TPL=""
CATEGORY=""
COURSES=()

if [[ $# -lt 2 || "$1" = "-h" || "$1" = "--help" ]]; then
    echo "Usage: $0 --tpl TEMPLATE_COURSE_ID [--category CATEGORY_ID | COURSE_ID ...]"
    exit 0
fi

while [[ $# -gt 0 ]]; do
    case "$1" in
        --tpl)
            shift
            if [[ $# -eq 0 ]]; then
                echo "Error: --tpl requires an argument" >&2
                exit 1
            fi
            TPL="$1"
            shift
            ;;
        --category)
            shift
            if [[ $# -eq 0 ]]; then
                echo "Error: --category requires an argument" >&2
                exit 1
            fi
            CATEGORY="$1"
            shift
            ;;
        --help|-h)
            echo "Usage: $0 --tpl TEMPLATE_COURSE_ID [--category CATEGORY_ID | COURSE_ID ...]"
            exit 0
            ;;
        *)
            # Assume any other argument is a course ID
            COURSES+=("$1")
            shift
            ;;
    esac
done

if [[ -z "$TPL" ]]; then
    echo "Error: --tpl TEMPLATE_COURSE_ID is required" >&2
    exit 1
fi

if [[ -n "$CATEGORY" && ${#COURSES[@]} -gt 0 ]]; then
    echo "Error: Cannot specify both --category and course IDs" >&2
    exit 1
fi

if [[ -z "$CATEGORY" && ${#COURSES[@]} -eq 0 ]]; then
    echo "Error: Must specify either --category or at least one course ID" >&2
    exit 1
fi

cd /bitnami/moodle || exit 1
# Create backup of the template course
echo "Backing up template course with id ${TPL}"
BACKUP_FILE="/bitnami/moodledata/${TPL}_backup.mbz"
rm "$BACKUP_FILE" 2>/dev/null || true
# --template backup excludes user data (users, logs, grades, roles...)
nice moosh -n course-backup --filename "$BACKUP_FILE" --template "${TPL}"

if [[ ! -f "$BACKUP_FILE" ]]; then
    echo "Error: Backup file not found: $BACKUP_FILE" >&2
    echo "Are you sure course no. ${TPL} exists?" >&2
    exit 1
fi

if [[ -n "$CATEGORY" ]]; then
    readarray -t COURSES < <(moosh -n course-list --categorysearch "${CATEGORY}" --id)
    if [[ ${#COURSES[@]} -eq 0 ]]; then
        echo "Error: zero courses in category ${CATEGORY}" >&2
        exit 1
    fi
fi

for COURSE in "${COURSES[@]}"; do
    echo "Restoring the template into course ${COURSE}"
    # the --existing flag would merge the backup instead of overwriting
    nice moosh -n course-restore --overwrite "${BACKUP_FILE}" "${COURSE}"
    # main admin (ephetteplace) is automatically enrolled in every restore
    nice moosh -n course-unenrol "${COURSE}" "$(nice moosh -n user-list --id "username = 'ephetteplace'")"
done

echo "Done. You may want to delete the backup file: ${BACKUP_FILE}"
