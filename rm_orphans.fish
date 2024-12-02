#!/usr/bin/env fish
# given list of file paths, delete them
set file $argv[1]
set -gx NS moo-prod

for path in (cat $file)
    echo "Deleting $path"
    # relies on librares-k8s project & NS var above
    k cp (k8 pod) -- rm $path
end
