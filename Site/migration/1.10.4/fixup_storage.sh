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
#   Termiantes empty user storages which have EBS volumes attached. 
#   EBS volumes leaves detached, but not terminated.
# 
############################################################################### 
EC2_CERT=~/.ec2/aws-cer.pem
EC2_PRIVATE_KEY=~/.ec2/aws-pk.pem

INSTANCE=`curl http://169.254.169.254/latest/meta-data/instance-id 2> /dev/null`
mkdir -p /var/storage/temp2

function fixup_storage_location()
{
    i=$1

    FULL_PATH=/var/storage/${i}
    
    if [ -d "${FULL_PATH}" ]; then
    if [ "$i" != "temp" ]; then
        
        if [ -d "${FULL_PATH}/${i}" ]; then
            SUBITEMS=$(ls "${FULL_PATH}/${i}")
            if [ -z "${SUBITEMS}" ]; then
                echo No data in the "${FULL_PATH}/${i}". Removed
                rm -Rf "${FULL_PATH}/${i}"
            else
                echo "${FULL_PATH}/${i}" found
                #mv -T "${FULL_PATH}/${i}/" ${FULL_PATH}/
                mv "${FULL_PATH}/${i}" /var/storage/temp2
                mv /var/storage/temp2/${i}/* ${FULL_PATH}/
                chown ${i}:${i} -R ${FULL_PATH}
            fi
        fi
    fi
    fi
}

for i in $( ls /var/storage ); do
    fixup_storage_location $i
done
