<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    24-11-2011
 
    Main include file that contains generic startup logic and function in the 
    application.
 
    © 2009=2011 Foundation for the National Institutes of Health (FNIH)
 
    Licensed under the Apache License, Version 2.0 (the "License"); you may not
    use this file except in compliance with the License. You may obtain a copy
    of the License at http://omop.fnih.org/publiclicense.
 
    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
    WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. Any
    redistributions of this work or any derivative work or modification based on
    this work should be accompanied by the following source attribution: "This
    work is based on work by the Observational Medical Outcomes Partnership
    (OMOP) and used under license from the FNIH at
    http://omop.fnih.org/publiclicense.
 
    Any scientific publication that is based on this work should include a
    reference to http://omop.fnih.org.
 
================================================================================*/
require_once "Amazon/EC2/Model/CreateVolumeRequest.php";
require_once "Amazon/EC2/Model/DescribeVolumesRequest.php";
require_once "Amazon/EC2/Model/AttachVolumeRequest.php";
require_once "Amazon/EC2/Model/DetachVolumeRequest.php";
require_once "Amazon/EC2/Model/DeleteVolumeRequest.php";
class Application_Model_DatasetUploading {
    
    
    protected $_service;
    
    const MAX_WAIT_TIME = 300;
    
    const METADATA_URL = 'http://169.254.169.254/latest/meta-data/instance-id';
    const MOUNT_POINT = '/datasets/';
    const FS_TYPE = 'ext3';
    
    private $_volumeCreated;
    private $_volumeAttached;
    private $_volumeMounted;
    private $_logger;
    
    
    public function getLogger() {
        if (! $this->_logger) {
            if (Zend_Registry::isRegistered('logger')) {
                $this->_logger = Zend_Registry::get('logger');
            } else {
                $this->_logger = new Zend_Log(new Zend_Log_Writer_Null());
            }
        
        }
        
        return $this->_logger;
    }
    
    private function createVolume($size) {
        $service = $this->getService();
        
        $request = new Amazon_EC2_Model_CreateVolumeRequest();
        
        $request->setSize($size);
        $request->setAvailabilityZone('us-east-1c');
        try {
            $this->getLogger()->info('Creating volume of size '.$size);
            $response = $service->createVolume($request);
            
            if ($response) {
                if ($response->isSetCreateVolumeResult()) {
                    $createVolumeResult = $response->getCreateVolumeResult();
                    if ($createVolumeResult->isSetVolume()) {
                        $volume = $createVolumeResult->getVolume();
                        if ($volume->isSetVolumeId()) {
                            $volumeId = $volume->getVolumeId();
                            $this->getLogger()->info('Launched volume: '.$volumeId);
                            if ($volume->isSetStatus()) {
                                $status = $volume->getStatus();
                                $this->getLogger()->info('Volume status: '.$status);
                                if ($status != 'available') {
                                    $this->waitForVolume($volumeId);
                                }
                                $this->_volumeCreated = true;
                                return $volumeId;
                            }
                        }
                    }
                }
            }
            $this->getLogger()->info('Volume was not created.');
            throw Exception('Error during volume creation.');
            
        } catch (Amazon_EC2_Exception $exc) {
            $this->getLogger()->info('AWS error during volume creation: '.$exc->getMessage());
            $e = new Exception('Error during volume creation');
            $e->amazon_exc = $exc;
            throw $e;
        }
        
    }
    
    private function waitForVolume($volumeId) {
        
        $service = $this->getService();
        
        $request = new Amazon_EC2_Model_DescribeVolumesRequest();
        $request->setVolumeId($volumeId);
        $this->getLogger()->info('Waiting for volume: '.$volumeId);
        $start = time();
        try {
            while (time() - $start < self::MAX_WAIT_TIME) {
                $this->getLogger()->info('Fetching volume information...');

                $response = $service->describeVolumes($request);

                if ($response->isSetDescribeVolumesResult()) {
                    $describeVolumesResult = $response->getDescribeVolumesResult();
                    $volumeList = $describeVolumesResult->getVolume();
                    foreach ($volumeList as $volume) {                
                        if ($volume->isSetStatus()) {
                            $status = $volume->getStatus();
                            $this->getLogger()->info('Volume status: '.$status);
                            if ($status == 'available') {
                                return;
                            }
                        }
                    }
                }
            }
            
            $this->getLogger()->info('Volume is not available for '. self.MAX_WAIT_TIME. 's. Aborting execution.');
            throw new Exception('Volume is not available. Maximum wait time exceeded, please check volume '.$volumeId .'.');
            
        } catch (Amazon_EC2_Exception $exc) {
            $this->getLogger()->info('AWS error while waiting for volume: '.$exc->getMessage());
            $e = new Exception('Error while waiting for volume');
            $e->amazon_exc = $exc;
            throw $e;
        }            
        
    }

    
    private function waitForAttachment($volumeId) {
        
        $service = $this->getService();
        
        $request = new Amazon_EC2_Model_DescribeVolumesRequest();
        $request->setVolumeId($volumeId);
        $this->getLogger()->info('Waiting for attachment of volume: '.$volumeId);
        $start = time();
        try {
            while (time() - $start < self::MAX_WAIT_TIME) {
                $this->getLogger()->info('Fetching volume information...');

                $response = $service->describeVolumes($request);

                if ($response->isSetDescribeVolumesResult()) {
                    $describeVolumesResult = $response->getDescribeVolumesResult();
                    $volumeList = $describeVolumesResult->getVolume();
                    foreach ($volumeList as $volume) {                
                        if ($volume->isSetStatus()) {
                            $status = $volume->getStatus();
                            $this->getLogger()->info('Volume status: '.$status);
                            if ($status == 'in-use') {
                                return;
                            }
                        }
                    }
                }
            }
            
            $this->getLogger()->info('Unable to attach volume for '. self.MAX_WAIT_TIME. 's. Aborting execution.');
            throw new Exception('Error while waiting for attachment. Maximum wait time exceeded, please check volume '.$volumeId .'.');
            
        } catch (Amazon_EC2_Exception $exc) {
            $this->getLogger()->info('AWS error while waiting for attachment: '.$exc->getMessage());
            $e = new Exception('Error while waiting for attachment');
            $e->amazon_exc = $exc;
            throw $e;
        }            
        
    }
    
    private function getInstanceId() {
        $this->getLogger()->info('Retrieving instance id');
        $c = curl_init(self::METADATA_URL);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $id = curl_exec($c);
        $this->getLogger()->info('Instance id is '.$id);
        return $id;
        
    }
    
    private function detachVolume($volumeId) {
        
        $request = new Amazon_EC2_Model_DetachVolumeRequest();
        $request->setVolumeId($volumeId);
        $service = $this->getService();
        try {
            $this->getLogger()->info('Trying to detach volume '.$volumeId);
            $response = $service->detachVolume($request);
            if ($response->isSetDetachVolumeResult()) {
                $detachVolumeResult = $response->getDetachVolumeResult();
                if ($detachVolumeResult->isSetAttachment()) {
                    $attachment = $detachVolumeResult->getAttachment();
                    if ($attachment->isSetStatus()) {
                        $status = $attachment->getStatus();
                        $this->getLogger()->info('Attachment status: '. $status);
                        if ($status != 'detached') {
                            $this->waitForVolume($volumeId);
                        }
                        $this->_volumeAttached = false;
                        return;
                    }
                }
            }
            
            $this->getLogger()->info('Failed to detach volume: '.$volumeId);
            throw new Exception('Unable to detach volume: '. $volumeId);
        } catch (Amazon_EC2_Exception $exc) {
                $this->getLogger()->info('AWS error while detaching volume: '.$exc->getMessage());
                $e = new Exception('Error while attaching volume');
                $e->amazon_exc = $exc;
                throw $e;
        }
            
    }
    
    private function createSnapshot($volumeId) {
        
        $service = $this->getService();
        
        $request = new Amazon_EC2_Model_CreateSnapshotRequest();
        $request->setVolumeId($volumeId);
        try {
            $this->getLogger()->info('Creating snapshot for volume: '.$volumeId);
            $response = $service->createSnapshot($request);
            
            if ($response->isSetCreateSnapshotResult()) {
                $createSnapshotResult = $response->getCreateSnapshotResult();
                if ($createSnapshotResult->isSetSnapshot()) {
                    $snapshot = $createSnapshotResult->getSnapshot();
                    if ($snapshot->isSetSnapshotId()) {
                        $snapshotId = $snapshot->getSnapshotId();
                        $this->getLogger()->info('Snapshot id: '. $snapshotId);
                        $this->waitForSnapshot($snapshotId);
                        return $snapshotId;
                    }
                }
            }
            
            $this->getLogger()->info('Failed to create snapshot for volume: '.$volumeId);
            throw new Exception('Unable to create snapshot for volume '. $volumeId);
            
        } catch (Amazon_EC2_Exception $exc) {
                $this->getLogger()->info('AWS error while creating snapshot: '.$exc->getMessage());
                $e = new Exception('Error while attaching volume');
                $e->amazon_exc = $exc;
                throw $e;
        }
        
    }

    
    private function waitForSnapshot($snapshotId) {
        
        $service = $this->getService();
        
        $request = new Amazon_EC2_Model_DescribeSnapshotsRequest();
        $request->setSnapshotId($snapshotId);
        $this->getLogger()->info('Waiting for snapshot: '.$snapshotId);
        $start = time();
        try {
            while (time() - $start < self::MAX_WAIT_TIME) {
                $this->getLogger()->info('Fetching snapshot information...');

                $response = $service->describeSnapshots($request);

                if ($response->isSetDescribeSnapshotsResult()) {
                    $describeSnapshotsResult = $response->getDescribeSnapshotsResult();
                    $volumeList = $describeVolumesResult->getVolume();
                    foreach ($volumeList as $volume) {                
                        if ($volume->isSetStatus()) {
                            $status = $volume->getStatus();
                            $this->getLogger()->info('Volume status: '.$status);
                            if ($status == 'attached') {
                                return;
                            }
                        }
                    }
                }
            }
            
            $this->getLogger()->info('Unable to attach volume for '. self.MAX_WAIT_TIME. 's. Aborting execution.');
            throw new Exception('Error while waiting for attachment. Maximum wait time exceeded, please check volume '.$volumeId .'.');
            
        } catch (Amazon_EC2_Exception $exc) {
            $this->getLogger()->info('AWS error while waiting for attachment: '.$exc->getMessage());
            $e = new Exception('Error while waiting for attachment');
            $e->amazon_exc = $exc;
            throw $e;
        }            
        
    }    
    
    private function attachVolume($volumeId) {
        
        $instanceId = $this->getInstanceId();
        $deviceName = $this->getFreeDeviceName();
        
        $request = new Amazon_EC2_Model_AttachVolumeRequest();
        if ($instanceId && $deviceName) {
            $request->setVolumeId($volumeId)
                    ->setDevice($deviceName)
                    ->setInstanceId($instanceId);
        
            $service = $this->getService();
            try {
                $this->getLogger()->info('Trying to attach volume '.$volumeId. ' to instance '.$instanceId.', device '.$deviceName);
                $response = $service->attachVolume($request);
                if ($response->isSetAttachVolumeResult()) {                
                    $attachVolumeResult = $response->getAttachVolumeResult();
                    if ($attachVolumeResult->isSetAttachment()) {
                        $attachment = $attachVolumeResult->getAttachment();
                        $status = $attachment->getStatus();
                        $this->getLogger()->info('Attachment status: '. $status);
                        if ($status != 'attached') {
                            $this->waitForAttachment($volumeId);
                        }
                        $this->_volumeAttached = true;
                        return $deviceName;
                    }

                }

                $this->getLogger()->info('Unable to attach volume');
                throw new Exception('Unable to attach volume: '.$volumeId);

            } catch (Amazon_EC2_Exception $exc) {
                $this->getLogger()->info('AWS error while attaching volume: '.$exc->getMessage());
                $e = new Exception('Error while attaching volume');
                $e->amazon_exc = $exc;
                throw $e;
            }     
        }
    }
    
    private function deleteVolume($volumeId) {
        
        $request = new Amazon_EC2_Model_DeleteVolumeRequest();
        
        $request->setVolumeId($volumeId);
        $service = $this->getService();
        try {
            $this->getLogger()->info('Deleting volume '. $volumeId);
            $response = $service->deleteVolume($request);
            if ($response->isSetResponseMetadata()) { 
                $responseMetadata = $response->getResponseMetadata();
                if ($responseMetadata->isSetRequestId()) 
                {
                    $this->getLogger()->info('Deleted volume '.$volumeId);
                }
                $this->_volumeCreates = false;
            }            
            
            
        } catch (Amazon_EC2_Excpetion $exc) {
            $this->getLogger()->info('AWS error while deleting volume: '.$exc->getMessage());
            $e = new Exception('Error while deleting volume');
            $e->amazon_exc = $exc;
            throw $e;
        }
        
    }
    
    private function mkFS($deviceName) {
        $this->getLogger()->info('Creating filesystem '.self::FS_TYPE.' on '.$deviceName);
        exec('/sbin/mkfs -t '.self::FS_TYPE.' '.$deviceName.' 2>&1', $output, $result);
        if ($result != 0) {
            $this->getLooger()->info('Failed to create filesystem on device '.$device.'. Output: '.implode("\n", $output));
            throw new Exception('Can\'t create filesystem on device '.$device);
        }
    }
    
    private function mountDevice($deviceName) {
        
        $mountPoint = self::MOUNT_POINT.$deviceName;
        if (! is_dir($mountPoint)) {
            $result = mkdir($mountPoint, 0700, true);
            if ($result !== True) {
                $this->getLogger()->info('Failed to create mountpoint '.$mountPoint.' for '.$deviceName);
                throw new Exception('Can\'t create mountpoint '.$mountPoint.' for '.$deviceName);
            }
        }
        
        $this->mkFS($deviceName);

        $this->getLogger()->info('Mounting '.$deviceName.' to '.$mountPoint);        
        exec('sudo mount '.$deviceName.' '.$mountPoint.' 2>&1', $output, $result);
        if ($result != 0) {
            $this->getLooger()->info('Failed to mount device '.$deviceName.'. Output: '.implode("\n", $output));
            throw new Exception('Can\'t mount device '.$deviceName);
        }
        $this->_volumeMounted = true;
        return $mountPoint;
        
    }
    
    private function umountDevice($deviceName) {
        
        $mountPoint = self::MOUNT_POINT.$deviceName;
        
        $this->getLogger()->info('Umounting '.$deviceName.' to '.$mountPoint);        
        exec('sudo umount '.$deviceName.' 2>&1', $output, $result);
        if ($result != 0) {
            $this->getLooger()->info('Failed to umount device '.$device.'. Output: '.implode("\n", $output));
            throw new Exception('Can\'t umount device '.$device);
        }
        $result = rmdir($mountPoint);
        if ($result !== True) {
            $this->getLooger()->info('Failed to delete mountpoint '.$mountPoint);
        }
        
        $this->_volumeMounted = false;
        
        
    }    
    
    private function openDataset($src) {
        $zip = new ZipArchive();
        $this->getLogger()->info('Openning ZIP: '.$src);
        $retval = $zip->open($src);
        if ($retval !== True) {
            $this->getLogger()->info('Failed to open ZIP: '. $src);
            throw new Exception('Unable to open ZIP');
        }
        
        return $zip;
        
    }
    
    private function removeZip($src) {
        $this->getLogger()->info('Removing ZIP '.$src);
        $retval = unlink($src);
        if ($retval !== True ) {
            $this->getLogger()->info('Failed to remove ZIP: '. $src);
            throw new Exception('Unable to remove ZIP');            
        }
    }
    
    private function copyDataset($zip, $dst) {
        
        if (! $zip instanceof ZipArchive) {
            $this->getLogger()->info('Wrong parameter type passed to copyDataset function');
            throw new Exception('Unable to extract ZIP.');
        }
        $this->getLogger()->info('Extracting dataset to mounted device: '. $dst);
        $retval = $zip->extractTo($dst);
        if ($retval !== True) {
            $this->getLogger()->info('Failed to extract dataset to mountpoint');
            throw new Exception('Unable to extract ZIP');
        }
        
    }
    
    private function getFreeDeviceName() {
        
        $devices = glob('/dev/sd*');
        $this->getLogger()->info('Searching for free device name...');
        $i = 'a';
        while (in_array('/dev/sd'.$i, $devices)) {
            $this->getLogger()->info('Device /dev/sd'.$i.' exists');        
            $i = chr(ord($i) + 1);
        }
        $this->getLogger()->info('Using device name /dev/sd'.$i);        
        
        return '/dev/sd'.$i;
        
    }

    private function getService()
    {
        if (! $this->_service) {
            require_once('OMOP/WebRL/AmazonFactory.php');
            global $configurationManager; 
        
            $this->_service = AmazonFactory::getEC2Client($configurationManager);
        }
        
        return $this->_service;
    }    
    
    public function makeDatasetFromZip($src) {
        try {
            $zip = $this->openDataset($src);
            $size = 4;
            $volumeId = $this->createVolume($size);
            $device = $this->attachVolume($volumeId);
            $mountPoint = $this->mountDevice($device);
            $this->copyDataset($zip, $mountPoint);
            $this->umountDevice($device);
            $this->detachVolume($volumeId);
            $snapshotId = $this->createSnapshot($volumeId);
            $this->deleteVolume($volumeId);
            
            return $snapshotId;
        } catch (Exception $exc) {
            if ($this->_volumeMounted)
                $this->umountDevice($device);
            if ($this->_volumeAttached)
                $this->detachVolume ($volumeId);
            if ($this->_volumeCreated)
                $this->deleteVolume ($volumeId);
        }

        $zip->close();
        $this->deleteZip();
        
        
    }
    
    public function makeDatasetFromUserStorage($user, $storagePath) {
        $mountPoint = $this->mountUserStorage($user);
        $size = $this->getStorageSize($mountPoint, $storagePath);
        if ($size == 0) {
            throw new Exception('Specified path is empty or does not exist');
        }
        $volumeId = $this->createVolume($size);
        $device = $this->attachVolume($volumeId);
        $devMountPoint = $this->mountDevice($device);
        $this->copyDatasetFromStorage($mountPoint, $storagePath, $devMountPoint);
        $this->umountDevice($device);
        $this->detachVolume($volumeId);
        $snapshotId = $this->createSnapshot($volumeId);
        $this->deleteVolume($volumeId);
        $this->umountUserStorage($user);
        return $snapshotId;
    }
      
}