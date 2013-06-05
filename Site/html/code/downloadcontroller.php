<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Page controller for /download page. Handles all user interaction within /launch page.
 
    (c)2009 Foundation for the National Institutes of Health (FNIH)
 
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

class DownloadController extends PageController
{
    protected function processCore($page, $action, $parameters)
    {
    	global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);
        $user = Membership::get_current_user();
        $app_mode = Membership::get_app_mode();
        
        switch($action)
        {
        	case 'tools':
        	case 'tools_small':
        		if ($user->svn_access_flag != 'Y' && $action == 'tools')
        		{
        			throw new Exception('User could not download full tools package.');
        		}
        		$file_tools = $configuration->file_tools();
                
                $this->model->copy_to_temp_folder($configuration->files_folder(), $file_tools);
                
				$zip = new ZipArchive;
        		if (!$zip->open($this->model->get_temp_location($file_tools)))
                {
                	throw new Exception("Corrupted ZIP archive $file_tools");
                }
                $zip->addFile($configuration->folder_keys().$user->login_id.'/'.$user->login_id.'.ppk', $user->login_id.'.ppk');

                $zip->addFromString('PuttyLauncher.exe.config', $this->get_content('PuttyLauncher.exe.config',$user->login_id));
                $zip->addFromString('PuttySynchronizer.exe.config', $this->get_content('PuttySynchronizer.exe.config',$user->login_id));
                $zip->addFromString('WinSCPLauncher.exe.config', $this->get_content('WinSCPLauncher.exe.config',$user->login_id));
                $zip->addFromString('X11Launcher.exe.config', $this->get_content('X11Launcher.exe.config',$user->login_id));

				if ($action == 'tools_small')
				{
					self::delete_folder_from_zip($zip,'SVN/');
				}
                $zip->close();

                //Sending file
                $this->send_file_to_user($file_tools);        		
                //Deleting file and folder
                $this->model->delete_from_temp_folder($file_tools);
                $this->model->delete_temp_folder();        			

                self::record_download($user, 1);
                
                die();        		
        		break;
        	case 'tools_macos':
        		$file_tools = 'omopprotocol-0.73.mac.src.zip';
                
                $this->model->copy_to_temp_folder($configuration->files_folder(), $file_tools);
                
				$zip = new ZipArchive;
        		if (!$zip->open($this->model->get_temp_location($file_tools)))
                {
                	throw new Exception("Corrupted ZIP archive $file_tools");
                }
                $zip->addFile($configuration->folder_keys().$user->login_id.'/'.$user->login_id, 'CustomProtocol.app/Contents/Resources/'.$user->login_id);
                $zip->close();

                //Sending file
                $this->send_file_to_user($file_tools);        		
                //Deleting file and folder
                $this->model->delete_from_temp_folder($file_tools);
                $this->model->delete_temp_folder();        			

                self::record_download($user, 3);
                die();
        		break;
        	case 'tools_fedora':
        		$file_tools = 'omopprotocol.tar';
        		$files_folder = $configuration->files_folder();
                $this->model->copy_to_temp_folder($files_folder, $file_tools);
        		
                $temp_file_tools = $this->model->get_temp_location($file_tools);
                if (!file_exists($temp_file_tools))
                {
                	throw new Exception('Could not open source archive');
                }
                $temp_folder = $this->model->get_temp_folder();
                $temp_pem_key = $temp_folder.'/omopprotocol-0.73/'.$user->login_id.'.pem';
                $temp_ppk_key = $temp_folder.'/omopprotocol-0.73/'.$user->login_id.'.ppk';
                exec('mkdir -p '.$temp_folder.'/omopprotocol-0.73');
				exec('cp '.$configuration->folder_keys().$user->login_id.'/'.$user->login_id.' '.$temp_pem_key);
                exec('cp '.$configuration->folder_keys().$user->login_id.'/'.$user->login_id.'.ppk '.$temp_ppk_key);
                exec('chmod 644 '.$temp_pem_key);
                exec('chmod 644 '.$temp_ppk_key);
                exec('pushd '.$temp_folder.'; tar -rf '.$this->model->get_temp_location($file_tools).' omopprotocol-0.73/'.$user->login_id.'.pem; popd');
				exec('pushd '.$temp_folder.'; tar -rf '.$this->model->get_temp_location($file_tools).' omopprotocol-0.73/'.$user->login_id.'.ppk; popd');
				exec("fakeroot rpmbuild -vv --define '_sourcedir ".$temp_folder."' --define '_topdir ".$temp_folder."' --define '_rpmdir ".$temp_folder."' -ba ".$files_folder."omopprotocol.spec");
				
                //Sending file
                $this->send_file_to_user('i386/omopprotocol-0.73-1.i386.rpm');
                //Deleting file and folder
                $this->model->delete_from_temp_folder($file_tools);
                $this->model->delete_from_temp_folder('omopprotocol-0.73/'.$user->login_id.'.pem');
                $this->model->delete_temp_folder();        			

                self::record_download($user, 4);
                die();
        		break;
        	case 'tools_ubuntu':
        		$file_tools = 'omopprotocol-0.81.deb.src.tar';
                
                $this->model->copy_to_temp_folder($configuration->files_folder(), $file_tools);
                
				$temp_folder = $this->model->get_temp_folder();
				exec('tar -xf '.$this->model->get_temp_location($file_tools).' --mode 0755 --same-permissions --no-same-owner -C '.$temp_folder);
				exec('cp '.$configuration->folder_keys().$user->login_id.'/'.$user->login_id.' '.$temp_folder.'/omopprotocol/etc/omopprotocol/'.$user->login_id.'.pem');
				exec('cp '.$configuration->folder_keys().$user->login_id.'/'.$user->login_id.'.ppk '.$temp_folder.'/omopprotocol/etc/omopprotocol/'.$user->login_id.'.ppk');
				exec('chmod 644 '.$temp_folder.'/omopprotocol/etc/omopprotocol/'.$user->login_id.'.pem');
				exec('chmod 644 '.$temp_folder.'/omopprotocol/etc/omopprotocol/'.$user->login_id.'.ppk');
				exec('fakeroot dpkg-deb -b '.$temp_folder.'/omopprotocol');
				
                //Sending file
                $this->send_file_to_user('omopprotocol.deb');        		
                //Deleting file and folder
                $this->model->delete_from_temp_folder('omopprotocol.deb');
                $this->model->delete_from_temp_folder('omopprotocol/etc/omopprotocol/'.$user->login_id.'.pem');
                $this->model->delete_from_temp_folder('omopprotocol/etc/omopprotocol/'.$user->login_id.'.ppk');
                $this->model->delete_temp_folder();        			

                self::record_download($user, 4);
                die();
        		break;
        	case 'tools_install':
        		
        		$file_tools = $this->model->get_temp_location('ClientTools.exe');

        		$command_line = "/usr/local/nsis/nsis-2.46/bin/makensis -Doutputfile={$file_tools} -Dlogin={$user->login_id} /var/www/installer/installer.nsi";
        		exec($command_line);
        		
        		//Sending file
                $this->send_file_to_user('ClientTools.exe');        		
                //Deleting file and folder
                $this->model->delete_from_temp_folder('ClientTools.exe');
                $this->model->delete_temp_folder();        			

                self::record_download($user, 2);
        		die();
        		break;
        		
        	case 'certificates':
        		$certificates_path = $configuration->folder_keys().'/'.$user->login_id;
        		$files = glob($certificates_path.'/*');
        		$zip = new ZipArchive();
                $this->model->copy_to_temp_folder($configuration->files_folder(), 'certificates.zip');
        		
                $temp_file = $this->model->get_temp_location('certificates.zip');
                $zip->open($temp_file, ZIPARCHIVE::CREATE);
        		foreach ($files as $file)
        		{
        			$pathinfo = pathinfo($file);
        			$zip->addFile($file, $pathinfo['basename']);
        		}
        		$zip->deleteName('placeholder');
        		$zip->close();
        	
        		$this->send_file_to_user('certificates.zip');
        		
        		$this->model->delete_from_temp_folder('certificates.zip');
       			$this->model->delete_temp_folder();        			
        		
       			self::record_download($user, 1);
        		
        		die();
        }           
        return true;
    }
    function delete_folder_from_zip($zip, $prefix)
    {
       	$pl = strlen($prefix);
    	for ($i = $zip->numFiles - 1; $i >= 0; $i--) 
    	{
			$stat = $zip->statIndex($i);
			if (!$stat)
				continue;
			$name = $stat['name'];
			if (strlen($name) > $pl && substr($name, 0, $pl) == $prefix)
			{
				$result = $zip->deleteName($name);
			}
		}
    }
    /*
     * Record act of downloading.
     * */
    function record_download($user, $type)
    {
    	$udh = new UserDownloadHistory();
        $udh->user_id = $user->user_id;
        $udh->download_type = $type;
        $udh->download_date = gmdate('c');
        $udh->Save();
        // Mark that certificate was downloaded. 
        $user->certificate_downloaded = 'Y';
        $user->Save();
    }
    function get_content($template, $username)
    {
    	global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);

    	$template = file_get_contents($configuration->files_folder().$template);
        $template = str_replace("root@{0}", "{1}@{0}", $template);
        $template = str_replace("%username%", $username, $template);
        return $template;
    }
    function send_file_to_user($file_name)
    {
        $temp_file = $this->model->get_temp_location($file_name);
        header('Contend-Description: File Transfer');
        header("Cache-Control: max-age=3600, must-revalidate"); // HTTP/1.1
        header("Pragma: max-age=3600, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Oct 1998 05:00:00 GMT"); // Date in the past
        header('Content-Disposition: attachment; filename='.$file_name);
        header('Content-type: application/zip');
        header('Content-Length: '.filesize($temp_file));
        readfile($temp_file);
    }
    
    function send_file($file_name)
    {
      	global $configurationManager; 
    	$configuration = new WebRLConfiguration($configurationManager);

    	$this->model->copy_to_temp_folder($configuration->files_folder(), $file_name);
        //Sending file
		$this->send_file_to_user($file_name);        		
        //Deleting file and folder
        $this->model->delete_from_temp_folder($file_name);
       	$this->model->delete_temp_folder();        			
    }
}

?>
