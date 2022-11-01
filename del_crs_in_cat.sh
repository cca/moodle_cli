#!/usr/bin/env bash
# delete all courses in a category, usage:
# ./del-crs-in-cat.sh $CATEGORY_NAME
moosh () { /usr/bin/moosh -n $@; }

if [[ -n $1 ]]; then
    CATEGORY=$(moosh category-list "$1" | tail -n1 | cut -d ' ' -f 1)
    if [[ -n $CATEGORY ]]; then
        # shell functions can't be passed to xargs so we have to use the path to moosh
        moosh course-list -c "$CATEGORY" -i | xargs /usr/bin/moosh -n course-delete
    else
        echo "Unable to find category with name = $1"
        exit 1
    fi
else
    echo "Must provide the name of a category."
    exit 1
fi
