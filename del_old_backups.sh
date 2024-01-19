#!/usr/bin/env bash
# Identify backups older than 1 year old and delete them
cd /bitnami/moodle || (echo "Error: unable to cd into /bitnami/moodle, does directory exist?" >&2 || exit)
# Ensure moosh is on PATH
export PATH=${PATH}:/bin:/usr/bin:/usr/local/bin
QUERY="component='backup' and timecreated<$(date -d '1 year ago' +%s)"
echo "$(date)" "deleting backup files more than a year old"
if [[ "$1" != "-d" && "$1" != "--delete" ]]; then
    echo "DRY RUN files will not be deleted. Add a -d or --delete flag to delete files"
    moosh -n file-list "${QUERY}"
else
    echo "Deleting files"
    moosh -n file-list -i "${QUERY}" | xargs -n1 nice moosh -n file-delete
fi
