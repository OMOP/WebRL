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

for i in $( ls /var/storage ); do
    FULL_PATH=/var/storage/${i}
    
    if [ -d "${FULL_PATH}" ]; then
    if [ "$i" != "temp" ]; then
        SUBITEMS=$(ls ${FULL_PATH} | grep -v "lost+found")
        if [ -z "${SUBITEMS}" ]; then
            DEVICE=$(df | grep "${FULL_PATH}" | awk '{print $1 }')
            VOLUME_ID=''
            if [ ! -z "${DEVICE}" ]; then
                echo "Start cleaning folder ${FULL_PATH:0:40}"
                VOLUME_ID=$(ec2-describe-volumes -K ${EC2_PRIVATE_KEY} -C ${EC2_CERT} -F "attachment.instance-id=${INSTANCE}" -F "attachment.device=${DEVICE}" | grep ATTACHMENT | cut -f2)
                FULL_PATH="${FULL_PATH}                                "
                echo -n "Unmounting volume ${VOLUME_ID} which is mapped on device ${DEVICE} from the instance ${INSTANCE}..."
                umount ${DEVICE}
                echo "Done."
                
                echo "Detaching volume ${VOLUME_ID} from the instance ${INSTANCE} ..."
                ec2-detach-volume -K ${EC2_PRIVATE_KEY} -C ${EC2_CERT} ${VOLUME_ID} -i ${INSTANCE}
                echo "Done."
                
                echo -n "Schedule volume ${VOLUME_ID} for termination..."
                echo "${VOLUME_ID}" >>volumes_to_remove.txt
                echo "Done."
                
                echo "Cleanup of folder ${FULL_PATH:0:40} completed."
                echo ""
            fi
                      
        fi        
    fi
    fi
done

echo "Process completed."
echo "EBS volumes detached from the instance, but not terminated. This will be done later."

