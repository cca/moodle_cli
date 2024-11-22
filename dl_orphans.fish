#!/usr/bin/env fish
set file $argv[1]
set -gx NS moo-prod

for hash in (cat $file)
    echo "Downloading $hash"
    # relies on librares-k8s project & NS var above
    k cp (k8 pod):$hash data/(basename $hash)
end
