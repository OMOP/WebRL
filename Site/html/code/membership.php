<?php
/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Performs authorization management for web-site.
 
    ©2009 Foundation for the National Institutes of Health (FNIH)
 
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

class Membership
{
    /*
    Check that user can login to web-site.
    */
	static function is_user_valid($login, $password)
	{
		$password = md5($password);
		$user = new User();
		$result = $user->load("login_id = ? AND password_hash = ? and active_flag='Y'", array($login, $password));
		if ($result == false)
			return false;
		return true;
	}
    /*
    Check that user is administrator of web-site.
    */
	static function is_administrator($login)
	{
		$user = new User();
		$result = $user->load("login_id = ?", array($login, $password));
		if ($result == false)
			return false;
		return $user->admin_flag == 'Y';
	}
    /*
    Get currently logged in user object.
    */
	static function get_current_user()
	{
        if (!array_key_exists("current_user", $_SESSION))
            return null;
		$user_id = $_SESSION["current_user"];
		if(isset($user_id))
		{
			$current_user = new User();
			$result = $current_user->load("login_id=?", array($user_id));
			if ($result)
				return $current_user;
		}
		return null;
	}
    static function is_logged_in()
    {
        $user = Membership::get_current_user();
		return $user != null;
    }
    /*
    Sets currently logged in user.

    @login  Login of user that assumed to be logged-in.
    */
	static function set_current_user($login)
	{
		$_SESSION["current_user"] = $login;
        $user = new User();
        $user->load('login_id = ?', array($login));
        $_SESSION['OMOP_Auth'] = array();
        $_SESSION['OMOP_Auth']['has_svn_access'] = $user->svn_access_flag;
	}
    /*
    Sets current application mode.

    @appmode  Aplication mode in which application should run.s
    */
	static function set_app_mode($appmode)
	{
		$_SESSION["app_mode"] = $appmode;
	}
    /*
    Gets current application mode.
    */
	static function get_app_mode()
	{
        if (!array_key_exists("app_mode", $_SESSION))
            return null;
		$appmode = $_SESSION["app_mode"];
        return $appmode;
	}
    /*
    Sets currently logged in user.

    @login          Login of user that assumed to be logged-in.
    @success        Flag that indicates success of login operation.
    @screen_width   Width of screen.
    @screen_height  Height of screen.
    */
	static function update_last_login_time($login, $success, $screen_width, $screen_height)
	{
		require_once('browser_detection.php');
        $user = new User();
        if (!$user->load("login_id = ?",array($login)))
            $user == null;
        $userconnect_log = new UserConnectLog();
        $userconnect_log->user_id = $user == null ? null : $user->user_id;
        $userconnect_log->instance_id = null;
        $userconnect_log->remote_ip = $_SERVER['REMOTE_ADDR'];
        $userconnect_log->status_flag = $success ? 'Y' : 'N';
        $userconnect_log->connect_date = gmdate('c');

        $browser_info = browser_detection('full');
        
        if ($browser_info[0] == 'moz' )
        {
            $a_temp = $browser_info[10];// use the second to last item in array, the moz array
            $full .= ($a_temp[0] != 'mozilla') ? 'Mozilla ' . ucfirst($a_temp[0]) . ' ' : ucfirst($a_temp[0]) . ' ';
            $full .= $a_temp[1] . ' ';
            //$full .= 'ProductSub: ';
            //$full .= ( $a_temp[4] != '' ) ? $a_temp[4] . ' ' : 'Not Available ';
            //$full .= ($a_temp[0] != 'galeon') ? 'Engine: Gecko RV: ' . $a_temp[3] : '';
        }
        elseif ($browser_info[0] == 'ns' )
        {
            $full .= 'Browser: Netscape ';
            $full .= $browser_info[1];
        }
        elseif ( $browser_info[0] == 'webkit' )
        {
            $a_temp = $browser_info[11];// use the last item in array, the webkit array
            $full .= 'User Agent: ';
            $full .= ucwords($a_temp[0]) . ' ' . $a_temp[1];
            $full .= ' Engine: AppleWebKit v: ';
            $full .= ( $browser_info[1] ) ? $browser_info[1] : 'Not Available';
        }
        else
        {
            $full .= ($browser_info[0] == 'ie') ? strtoupper($browser_info[7]) : ucwords($browser_info[7]);
            $full .= ' ' . $browser_info[1];
        }


        $userconnect_log->browser_type = $full;
        $userconnect_log->user_agent = isSet($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

            switch ($browser_info[5])
			{
				case 'win':
					$os .= 'Windows ';
					break;
				case 'nt':
					$os .= 'Windows NT ';
					break;
				case 'lin':
					$os .= 'Linux ';
					break;
				case 'mac':
					$os .= 'Mac ';
					break;
				case 'iphone':
					$os .= 'Mac ';
					break;
				case 'unix':
					$os .= 'Unix Version: ';
					break;
				default:
					$os .= $browser_info[5];
			}

			if ( $browser_info[5] == 'nt' )
			{
				if ($browser_info[6] == 5)
				{
					$os .= '5.0';// (Windows 2000)';
				}
				elseif ($browser_info[6] == 5.1)
				{
					$os .= '5.1';// (Windows XP)';
				}
				elseif ($browser_info[6] == 5.2)
				{
					$os .= '5.2';// (Windows XP x64 Edition or Windows Server 2003)';
				}
				elseif ($browser_info[6] == 6.0)
				{
					$os .= '6.0';// (Windows Vista)';
				}
				elseif ($browser_info[6] == 6.1)
            {
               $os .= '6.1';// (Windows 7)';
            }
            elseif ($browser_info[6] == 'ce')
            {
               $os .= 'CE';
            }
			}
			elseif ( $browser_info[5] == 'iphone' )
			{
				$os .=  'OS X (iPhone)';
			}
			elseif ( ( $browser_info[5] == 'mac' ) &&  ( $browser_info[6] >= 10 ) )
			{
				$os .=  'OS X';
			}
			elseif ( $browser_info[5] == 'lin' )
			{
				$os .= ( $browser_info[6] != '' ) ? 'Distro: ' . ucfirst ($browser_info[6] ) : 'Smart Move!!!';
			}
			elseif ( $browser_info[5] && $browser_info[6] == '' )
			{
				$os .=  ' (version unknown)';
			}
			elseif ( $browser_info[5] )
			{
				$os .=  strtoupper( $browser_info[5] );
			}

        $userconnect_log->os = $os;
        $userconnect_log->screen_x = $screen_width;
        $userconnect_log->screen_y = $screen_height;
        $userconnect_log->user_name = $login;
        $userconnect_log->save();
	}
    /*
    Get number of attemts login within given period of time.

    @login          Login of user that assumed to be logged-in.
    @atempts_count  Count of invalid attempts allowed
    @seconds        Count of seconds within which should not be invalid login attempts
    */
	static function check_login_attempts_count($login, $attempts_count, $seconds)
	{
        $userconnect_log = new UserConnectLog();
		$extras = array();
		$extras['limit'] = $attempts_count;
		$extras['offset'] = 0;
        
        $attempts = $userconnect_log->find("user_name = ? AND connect_date > DATE_SUB(?, INTERVAL ? SECOND) ORDER BY user_connect_id DESC", array($login, gmdate('c'), $seconds), false, $extras);

        $counter = 0;
        if (count($attempts) > 0 && $attempts != null)
        {
            foreach($attempts as $l)
            {
                if ($l->status_flag == 'Y')
                {
                    return true;
                }
                $counter++;
            }
        }
        if ($counter == $attempts_count)
        {
            return false;
        }

        return true;
	}
    /*
    Log-out currently logged in user.
    */
	static function clear_current_user()
	{
		session_unset(); 
		session_destroy(); 
	}
}


?>