<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    18-Jul-2011
 
    Controller for the tools downloading
 
    (c)2009-2011 Foundation for the National Institutes of Health (FNIH)
 
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
require_once('OMOP/WebRL/Configuration/WebRLConfiguration.php');
require_once('Archive/Tar.php');

class DownloadController extends Zend_Controller_Action
{
    private $_folder;
    private $_user;
    
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('redirector');
        $this->_folder = $this->_genTempDir('/var/tmp', true);
        $this->_user = Membership::get_current_user();
        
        // disable the view and the layout
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }
    
    public function postDispatch()
    {
        if ($this->_folder) {
            exec('echo "y" | rm -RI ' . $this->_folder);
        }
    }
    
    public function indexAction()
    {
        $this->_redirector->gotoSimple('error', 'error');
    }
    
    public function toolsInstallAction()
    {
        $filename = 'ClientTools.exe';
        
        $fileTools = $this->_getTempLocation($filename);

        $cmd  = "/usr/local/nsis/nsis-2.46/bin/makensis -Doutputfile={$fileTools} -Dlogin={$this->_user->login_id} ";
        $cmd .= '/var/www/installer/installer.nsi';
        exec($cmd);

        //Sending file
        $this->_sendFile($filename);

        $this->_logDownloading(2);
    }
    
    public function toolsAction()
    {
        if ($this->_user->svn_access_flag != 'Y') {
            throw new Exception('User could not download full tools package.');
        }
        
        $fileTools = $this->_zipTools(false);
        
        //Sending file
        $this->_sendFile($fileTools); 

        $this->_logDownloading(1);
    }
    
    public function toolsSmallAction()
    {
        $fileTools = $this->_zipTools(true);
        
        //Sending file
        $this->_sendFile($fileTools);

        $this->_logDownloading(1);
    }
    
    public function certificatesAction()
    {
        global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);
        
        $certificatesPath = $configuration->folder_keys() . DIRECTORY_SEPARATOR . $this->_user->login_id;
        $files = glob($certificatesPath . DIRECTORY_SEPARATOR . '*');
        $zip = new ZipArchive();
        
        $filename = 'certificates.zip';
        $this->_copyToTemp($configuration->files_folder(), $filename);

        $tempFile = $this->_getTempLocation($filename);
        $zip->open($tempFile, ZIPARCHIVE::CREATE);
        foreach ($files as $file) {
            $pathinfo = pathinfo($file);
            $zip->addFile($file, $pathinfo['basename']);
        }
        $zip->deleteName('placeholder');
        $zip->close();

        $this->_sendFile($filename);

        $this->_logDownloading(1);
    }
    
    public function toolsMacosAction()
    {
        global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);
        
        $fileTools = 'omopprotocol-0.73.mac.src.zip';

        $this->_copyToTemp($configuration->files_folder(), $fileTools);

        $zip = new ZipArchive;
        if (!$zip->open($this->_getTempLocation($fileTools))) {
            throw new Exception("Corrupted ZIP archive $fileTools");
        }
        $filename  = $configuration->folder_keys();
        $filename .= $this->_user->login_id . DIRECTORY_SEPARATOR . $this->_user->login_id;
        $localname = 'CustomProtocol.app/Contents/Resources/' . $this->_user->login_id;
        $zip->addFile($filename, $localname);
        $zip->close();

        //Sending file
        $this->_sendFile($fileTools);

        $this->_logDownloading(3);
    }
    
    public function toolsFedoraAction()
    {
        global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);
        
        $fileTools = 'omopprotocol.tar';
        $filesFolder = $configuration->files_folder();
        $this->_copyToTemp($filesFolder, $fileTools);

        $tempFileTools = $this->_getTempLocation($fileTools);
        if (!file_exists($tempFileTools)) {
            throw new Exception('Could not open source archive.');
        }
        $username = $this->_user->login_id;
        $tempPemKey = $this->_folder . '/omopprotocol-0.73/' . $username . '.pem';
        $tempPpkKey = $this->_folder . '/omopprotocol-0.73/' . $username . '.ppk';
        exec('mkdir -p ' . $this->_folder . '/omopprotocol-0.73');
        $src = $configuration->folder_keys() . $username . DIRECTORY_SEPARATOR . $username;
        exec('cp ' . $src . ' '.$tempPemKey);
        $src = $configuration->folder_keys() . $username . DIRECTORY_SEPARATOR . $username . '.ppk';
        exec('cp ' . $src . ' ' . $tempPpkKey);
        exec('chmod 644 ' . $tempPemKey);
        exec('chmod 644 ' . $tempPpkKey);
        $cmd  = 'pushd ' . $this->_folder . '; ';
        $cmd .= 'tar -rf ' . $this->_getTempLocation($fileTools) . ' omopprotocol-0.73/' . $username . '.pem; ';
        $cmd .= 'popd';
        exec($cmd);
        $cmd  = 'pushd ' . $this->_folder . '; ';
        $cmd .= 'tar -rf ' . $this->_getTempLocation($fileTools) . ' omopprotocol-0.73/' . $username . '.ppk; ';
        $cmd .= 'popd';
        exec($cmd);
        $cmd  = "fakeroot rpmbuild -vv ";
        $cmd .= "--define '_sourcedir " . $this->_folder . "' ";
        $cmd .= "--define '_topdir " . $this->_folder . "' ";
        $cmd .= "--define '_rpmdir ".$this->_folder."' ";
        $cmd .= "-ba ".$filesFolder."omopprotocol.spec";
        exec($cmd);

        //Sending file
        $this->_sendFile('i386/omopprotocol-0.73-1.i386.rpm');
        
        $this->_logDownloading(4);
    }
    
    public function toolsUbuntuAction()
    {
        global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);
        
        $fileTools = 'omopprotocol-0.82.deb.src.tar';
        $username = $this->_user->login_id;

        $this->_copyToTemp($configuration->files_folder(), $fileTools);

        $cmd  = 'tar -xf ' . $this->_getTempLocation($fileTools) . ' ';
        $cmd .= '--mode 0755 --same-permissions --no-same-owner -C ' . $this->_folder;
        exec($cmd);
        $src  = $configuration->folder_keys() . $username . DIRECTORY_SEPARATOR . $username;
        $dest = $this->_folder . '/omopprotocol/etc/omopprotocol/' . $username . '.pem';
        exec('cp ' . $src . ' ' . $dest);
        $src  = $configuration->folder_keys() . $username . DIRECTORY_SEPARATOR . $username . '.ppk';
        $dest = $this->_folder . '/omopprotocol/etc/omopprotocol/' . $username . '.ppk';
        exec('cp ' . $src . ' ' . $dest);
        exec('chmod 644 ' . $this->_folder . '/omopprotocol/etc/omopprotocol/' . $username . '.pem');
        exec('chmod 644 ' . $this->_folder . '/omopprotocol/etc/omopprotocol/' . $username . '.ppk');
        exec('fakeroot dpkg-deb -b ' . $this->_folder . '/omopprotocol');

        //Sending file
        $this->_sendFile('omopprotocol.deb');
        
        $this->_logDownloading(4);
    }
    
    private function _zipTools($removeSvnFolder = false) {
        global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);
        
        $fileTools = $configuration->file_tools();

        $this->_copyToTemp($configuration->files_folder(), $fileTools);

        $zip = new ZipArchive;
        if (!$zip->open($this->_getTempLocation($fileTools))) {
            throw new Exception("Corrupted ZIP archive $fileTools");
        }
        $file = $this->_user->login_id.'.ppk';
        $zip->addFile($file, $configuration->folder_keys() . $this->_user->login_id . DIRECTORY_SEPARATOR . $file);

        $files = array('PuttyLauncher.exe.config',
            'PuttySynchronizer.exe.config',
            'WinSCPLauncher.exe.config',
            'X11Launcher.exe.config'
        );
        foreach ($files as $filename) {
            $fileContent = $this->_getConfigFileContent($configuration, $fileName, $user->login_id);
            $zip->addFromString($fileName, $fileContent);
        }

        if ($removeSvnFolder) {
            $this->_deleteFolderFromZip($zip, 'SVN/');
        }
        
        $zip->close();
        
        return $fileTools;
    }

    private function _getConfigFileContent($configuration, $template, $username)
    {
    	$template = file_get_contents($configuration->files_folder().$template);
        $template = str_replace("root@{0}", "{1}@{0}", $template);
        $template = str_replace("%username%", $username, $template);
        return $template;
    }
    
    private function _deleteFolderFromZip($zip, $prefix)
    {
       	$pl = strlen($prefix);
    	for ($i = $zip->numFiles - 1; $i >= 0; $i--) {
			$stat = $zip->statIndex($i);
			if (!$stat) {
				continue;
            }
			$name = $stat['name'];
			if (strlen($name) > $pl && substr($name, 0, $pl) == $prefix) {
				$result = $zip->deleteName($name);
			}
		}
    }
    
    /**
     * Generates unique name for temporary folder and optionally creates it
     * @todo this function may represent some system layer, it might be useful not only for results uploading
     * @todo make it static
     * @param string $path path to the folder for the new folder to be created within
     * @param boolean $create whether the new folder should be physically created or only its name should be generated
     * @param string $suffix text to add to the generated name
     * @return string generated folder name or null if it wasn't generated
     */
    private function _genTempDir($path, $create = false, $suffix = '')
    {
        $result = true;
        $limit = 10;
        do {
            if ($limit <= 0) {
                break;
            }
            $file = $path . DIRECTORY_SEPARATOR . mt_rand().$suffix;
            --$limit;
        } while(file_exists($file));

        if ($limit>0) {
            if ($create) {
                $oldumask = umask(0);
                $result = mkdir($file, 01700, true);
                umask($oldumask);
            }
        } else {
            $result = false;
        }

        return $result ? $file : null;
    }
    
    private function _getTempLocation($filename)
    {
    	return $this->_folder . DIRECTORY_SEPARATOR . $filename;
    }
    
    private function _copyToTemp($folder, $file)
    {
    	copy($folder . DIRECTORY_SEPARATOR . $file, 
             $this->_getTempLocation($file));
    }
    
    /**
     * Send file to user
     * @param string $filename file to send
     */
    private function _sendFile($filename)
    {
        ob_end_clean();
        $tempFile = $this->_getTempLocation($filename);
        header('Contend-Description: File Transfer');
        header("Cache-Control: max-age=3600, must-revalidate"); // HTTP/1.1
        header("Pragma: max-age=3600, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Oct 1998 05:00:00 GMT"); // Date in the past
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-type: application/zip');
        header('Content-Length: '.filesize($tempFile));
        readfile($tempFile);
    }
    
    /**
     * Record the act of downloading.
     * @todo rework with Zend models
     */
    private function _logDownloading($type)
    {
        $udh = new Application_Model_UserDownloadHistory();
        $udh->setUserId($this->_user->user_id)
            ->setType($type)
            ->setDate(gmdate('c'))
            ->save();
        
        // Mark that certificate was downloaded.
        $this->_user->certificate_downloaded = 'Y';
        $this->_user->Save();
    }
}
