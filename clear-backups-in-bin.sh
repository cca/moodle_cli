#!/usr/bin/env bash
# Fall 2020 we saw a problem with numerous backups being stored in the recyle bin
# even though we had automatic backups off. This script is meant to be run from
# a nightly cron job to clear out the largest of those backup files.
# report: https://moodle.cca.edu/report/customsql/view.php?id=15
FILES=$(moosh -n file-list -i 'filename LIKE "%.mbz" AND filesize > 104857600 AND (component = "tool_recyclebin" OR filearea = "recyclebin_course") ORDER BY filesize DESC' | head -n20)

for FILE in ${FILES}; do
    moosh -n file-delete ${FILE}
done

moosh -n file-delete --flush
