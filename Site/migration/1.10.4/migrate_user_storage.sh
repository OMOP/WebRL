#!/bin/bash
###############################################################################
#
#   OMOP - Cloud Research Lab
# 
#   Observational Medical Outcomes Partnership
#   (c)2009-2011 Foundation for the National Institutes of Health (FNIH)
# 
#   Licensed under the Apache License, Version 2.0 (the "License"); you may not
#   use this file except in compliance with the License. You may obtain a copy
#   of the License at http://omop.fnih.org/publiclicense.
# 
#   Unless required by applicable law or agreed to in writing, software
#   distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
#   WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. Any
#   redistributions of this work or any derivative work or modification based on
#   this work should be accompanied by the following source attribution: "This
#   work is based on work by the Observational Medical Outcomes Partnership
#   (OMOP) and used under license from the FNIH at
#   http://omop.fnih.org/publiclicense.
# 
#   Any scientific publication that is based on this work should include a
#   reference to http://omop.fnih.org.
# 
#   Date:       2011/02/07
#
#   Script that migrate existing user storages from old filesystem layout 
#   to new one.
# 
############################################################################### 


script=`basename $0`
USAGE="Usage: $script -h <svn_host> -d <local_directory> -u <usernam> -p <password>"

MAX_TRIES=5

set -- `getopt h:d:u:p: $* 2>/dev/null`
if [ $# -eq 1 ]; then
    echo $USAGE; exit 1
fi

KEEP_FLAG=0
for opt in $*
do
    case "$opt" in
        -k) KEEP_FLAG=1; export KEEP_FLAG
            shift 2;;
        -u) USERNAME=$2; export USERNAME
            shift 2;;
        --) shift; break;;
    esac
done

if [ $# -ne 0 ]; then
    echo $USAGE; exit 1
fi

OLD_LOCATION=/var/storage/${USERNAME}
OLD_DEVICE=$(df -h | grep "${OLD_LOCATION}" | awk '{print $1 }')

SUBITEMS=$(ls ${FULL_PATH} | grep -v "lost+found")
if [ -z "${SUBITEMS}" ]; then
    DEVICE=$(df | grep "${FULL_PATH}" | awk '{print $1 }')            
    FULL_PATH="${FULL_PATH}                                "
    echo "${FULL_PATH:0:40}" ${DEVICE}
fi

INSTANCE=`curl http://169.254.169.254/latest/meta-data/instance-id 2> /dev/null`

OLD_DEVICE_SIZE=$(df | grep "${OLD_LOCATION}" | awk '{print $2 }')   # Size of EBS volume in Kbytes.

NEW_DEVICE=/dev/sdh1

NEW_LOCATION=/var/storage/${USERNAME}

