#!/bin/bash

# We don't want "apply-" users, who are applicants in portal, to end up in Moodle.
# We can't control that Moodle imports all users from the Portal users table,
# so we delete them all later. Moosh makes this a lot easier.

data=$(/usr/local/bin/moosh -n user-list "username LIKE 'apply-%'")
elems=$(echo $data | tr "," "\n")
usernames=()

for i in $elems
    do
        if [[ "$i" =~ ^apply-.* ]]; then
            usernames=("${usernames[@]}" $i)
        fi
done

echo "usernames array is : "
echo ${usernames[@]}

for n in ${usernames[@]}
    do
        echo $n
        sleep 1
        echo
        /usr/local/bin/moosh -n user-delete $n
done
