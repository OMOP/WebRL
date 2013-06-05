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
#   Moves user storages for which of them was allocated less storage then 
#   specified to the target volume.
# 
############################################################################### 
script=`basename $0`
USAGE="Usage: $script -s <alocated size in GB per user> -l <old limit on used storage> -b <block device to which move>"

for opt in "$@"
do
    case "$opt" in
        -s) NEW_SIZE=$2; export NEW_SIZE
            shift 2;;
        -l) ORIGINAL_LIMIT=$2; export ORIGINAL_LIMIT
            shift 2;;
        -b) BLOCK_DEVICE=$2; export BLOCK_DEVICE
            shift 2;;
        --) shift; break;;
    esac
done

if [ $# -ne 0 ]; then
    echo $USAGE; exit 1
fi

EC2_CERT=~/.ec2/aws-cer.pem
EC2_PRIVATE_KEY=~/.ec2/aws-pk.pem

INSTANCE=`curl http://169.254.169.254/latest/meta-data/instance-id 2> /dev/null`
TARGET_FOLDER=/var/storage/${BLOCK_DEVICE}

echo "Creating template for virtual storage of ${NEW_SIZE}G size"

BLOCKS_COUNT=$((${NEW_SIZE} * 2 * 1000 * 1000 ))  # Calculate size of 512-blocks which will be on the devices.
dd if=/dev/zero of=${TARGET_FOLDER}/template.ext3 count=${BLOCKS_COUNT}
echo "Formatting virtual storage."
mkfs -t ext3 -q ${TARGET_FOLDER}/template.ext3 -F
echo "Creation virtual storage template completed."

for USER_NAME in $( ls /var/storage ); do
    FULL_PATH=/var/storage/${USER_NAME}
    
    if [ -d "${FULL_PATH}" ]; then
    if [[ "${USER_NAME}" != "temp" && "${USER_NAME}" != "dev" ]] ; then
            DEVICE=$(df | grep "${FULL_PATH}" | awk '{print $1 }')
            USED_SIZE=$(df | grep "${FULL_PATH}" | awk '{print $2 }')
            
            if [ ! -z ${USED_SIZE} ] && (( ${USED_SIZE} > ${ORIGINAL_LIMIT} * 1024 * 1024 )) ; then
                echo "Skip copying of ${FULL_PATH}"
                continue
            fi
            
            echo "Start migration data from ${FULL_PATH}"
            
            # If this is fodler mapped to the root partition, then
            # just mount virtual storage and copy data from 
            # original location to virtual storage.
            echo -n "Creating virtual storage for user ${USER_NAME} "
            cp ${TARGET_FOLDER}/template.ext3 ${TARGET_FOLDER}/${USER_NAME}.ext3
            echo "Done."
            
            echo -n "Copying data to virtual storage ${USER_NAME} "
            mount -o loop,rw,usrquota,grpquota ${TARGET_FOLDER}/${USER_NAME}.ext3 /var/storage/temp
            cp -R ${FULL_PATH}/ /var/storage/temp/
            umount /var/storage/temp
            echo "Done."
            
            VOLUME_ID=''
            if [ ! -z "${DEVICE}" ]; then
                VOLUME_ID=$(ec2-describe-volumes -K ${EC2_PRIVATE_KEY} -C ${EC2_CERT} -F "attachment.instance-id=${INSTANCE}" -F "attachment.device=${DEVICE}" | grep ATTACHMENT | cut -f2)
                echo -n "Unmounting volume ${VOLUME_ID} which is mapped on device ${DEVICE} from the instance ${INSTANCE}..."
                umount ${DEVICE}
                echo "Done."
                
                echo "Detaching volume ${VOLUME_ID} from the instance ${INSTANCE} ..."
                ec2-detach-volume -K ${EC2_PRIVATE_KEY} -C ${EC2_CERT} ${VOLUME_ID} -i ${INSTANCE}
                echo "Done."
                
                echo -n "Schedule volume ${VOLUME_ID} for termination..."
                echo "${VOLUME_ID}" >>volumes_to_remove.txt
                echo "Done."
            fi
            mount -o loop,rw,usrquota,grpquota ${TARGET_FOLDER}/${USER_NAME}.ext3 ${FULL_PATH}
            
            echo "Migration data for user ${USER_NAME} completed."
            echo ''
    fi
    fi
done

echo "Cleanup"
rm -f ${TARGET_FOLDER}/template.ext3

echo ""
echo "Migration of data Completed."
