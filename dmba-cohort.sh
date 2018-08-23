#!/usr/bin/env #!/usr/bin/env bash

# https://moosh-online.com/commands/

echo 'Requires a file "dmba.txt" of usernames on separate lines'
read ''
for user in $(cat dmba.txt); do
    # 775 = DMBA Community course ID
    sudo -u www-data moosh course-enrol -r student 775 $user
done
