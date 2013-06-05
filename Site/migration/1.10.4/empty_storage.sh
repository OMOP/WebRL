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
#   List empty user storages and devices which these storages used.
# 
############################################################################### 
EC2_CERT=~/.ec2/aws-cer.pem
EC2_PRIVATE_KEY=~/.ec2/aws-pk.pem

INSTANCE=`curl http://169.254.169.254/latest/meta-data/instance-id 2> /dev/null`

for i in $( ls /var/storage ); do
    FULL_PATH=/var/storage/${i}
    
    if [ -d "${FULL_PATH}" ]; then
    if [ "$i" != "temp" ]; then
        SUBITEMS=$(ls ${FULL_PATH} | grep -v "lost+found")
        if [ -z "${SUBITEMS}" ]; then
            DEVICE=$(df | grep "${FULL_PATH}" | awk '{print $1 }')
            VOLUME_ID=''
            if [ ! -z "${DEVICE}" ]; then
                VOLUME_ID=$(ec2-describe-volumes -K ${EC2_PRIVATE_KEY} -C ${EC2_CERT} -F "attachment.instance-id=${INSTANCE}" -F "attachment.device=${DEVICE}" | grep ATTACHMENT | cut -f2)
            fi
                      
            FULL_PATH="${FULL_PATH}                                "
            echo "${FULL_PATH:0:40}" ${DEVICE} ${VOLUME_ID}
        fi        
    fi
    fi
done

