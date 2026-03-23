#!/usr/bin/env bash
# Fix ownership of files in /opt/moodledata and /bitnami/moodle to be owned by daemon, not root.
for DIR in /opt/moodledata /bitnami/moodle; do
    find "${DIR}" -user root -exec chown daemon {} \;
done
