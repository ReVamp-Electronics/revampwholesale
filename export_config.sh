#!/bin/bash
# Confirm permissions
if [ $USER != 'devrw' ]; then
    echo "Please run as user devrw"
    exit 1
fi

# Set CWD to dir of script
cd "$(dirname "$0")"
php bin/magento config:data:export --filename="/../../config/store/base/general" --hierarchical --filePerNameSpace