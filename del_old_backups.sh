#!/usr/bin/env bash
# Identify backups older than 1 year old and delete them
cd /bitnami/moodle
moosh () { /usr/bin/moosh -n $@; }
echo $(date) "deleting backup files more than a year old"
echo "DRY RUN files will not be deleted"
moosh -n file-list -i "component='backup' and timecreated<$(date -d '1 year ago' +%s)" \
    xargs -n1 echo
    # xargs -n1 moosh -n file-delete
