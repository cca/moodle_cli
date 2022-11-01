#!/usr/bin/env bash
# delete all courses in a category, usage:
# ./del-crs-in-cat.sh $CATEGORY_NAME

if [[ -n $1 ]]; then
    CATEGORY=$(moosh category-list "$1" | tail -n1 | cut -d ' ' -f 1)
    if [[ -n $CATEGORY ]]; then
         /usr/bin/moosh -n course-list -c "$CATEGORY" -i | xargs /usr/bin/moosh -n course-delete
    else
        echo "Unable to find category with name = $1"
        exit 1
    fi
else
    echo "Must provide the name of a category."
    exit 1
fi
