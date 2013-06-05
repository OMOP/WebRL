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
#   Create EBS of given size which attached to current instance as given
#   device and mounted in the specified folder as part of the filesystem.
# 
###############################################################################

script=`basename $0`
USAGE="Usage: $script -d <mount dir> -s <volume size in GB> -b <block device to which attach>"

for opt in "$@"
do
    case "$opt" in
        -d) MOUNT_DIR=$2; export MOUNT_DIR
            shift 2;;
        -s) SIZE=$2; export SIZE
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
AVAIL_ZONE=`curl http://169.254.169.254/latest/meta-data/placement/availability-zone` 

MAX_TRIES=5
CREATE_VOLUME_OPTS="-s $SIZE -z $AVAIL_ZONE -K $EC2_PRIVATE_KEY -C $EC2_CERT"


echo -n "Waiting for create volume.."
CTR=0
NEW_VOLUME_ID=
while [ -z "$NEW_VOLUME_ID" ]; do
    NEW_VOLUME_ID=`ec2-create-volume $CREATE_VOLUME_OPTS | awk '{print $2}'`
    echo -n " ."
    sleep 1
    if [ $CTR -eq $MAX_TRIES ]
    then
        echo "WARNING: Failed to create new EBS volume after $MAX_TRIES attempts";
        exit 1;
    fi
done
echo "Done!"
echo "Volume ID: ${NEW_VOLUME_ID}"


echo -n "Waiting for attach volume.."
CTR=0
ATTACHMENT_RESULT=
while [ -z "$ATTACHMENT_RESULT" ]; do
    ATTACHMENT_RESULT=`ec2-attach-volume -d ${BLOCK_DEVICE} -i ${INSTANCE} ${NEW_VOLUME_ID} -K ${EC2_PRIVATE_KEY} -C ${EC2_CERT} | awk '{print $5}'`
    echo -n " ."
    sleep 1
    if [ $CTR -eq $MAX_TRIES ]
    then
        echo "WARNING: Failed to create new EBS volume after $MAX_TRIES attempts";
        exit 1;
    fi
done
echo "Done!"
echo "Volume now in state ${ATTACHMENT_RESULT}"

echo -n "Waiting for device to settle..."
while [ ! -b $BLOCK_DEVICE ];do echo -n " .";sleep 1;done
echo "Done!"

echo "Creating ext3 filesystem on ${BLOCK_DEVICE}..."
mke2fs -j $BLOCK_DEVICE

echo -n "Mounting ${BLOCK_DEVICE} on ${MOUNT_DIR}..."
mkdir -p ${MOUNT_DIR}
mount ${BLOCK_DEVICE} ${MOUNT_DIR} && echo "ok" || ( echo "failed"; exit 1 )
