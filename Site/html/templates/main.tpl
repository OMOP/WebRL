{*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Main template for the web-site. Contains HTML layout that same for the whole 
    web-site.
 
    �2009 Foundation for the National Institutes of Health (FNIH)
 
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
 
================================================================================*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>OMOP: {$title}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=7" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link href="/main.css" rel="stylesheet" type="text/css" />
    <link href="/css/jquery/smoothness/jquery-ui-1.7.3.custom.css" rel="stylesheet" type="text/css" />
    <link rel="icon" type="image/png" href="/images/favicon.png" />
    <script src="/js/jquery-1.3.2.min.js" type="text/javascript"></script>
    <script src="/js/jquery.form.js" type="text/javascript"></script>
    <script src="/js/jquery.timers.js" type="text/javascript"></script>
    <script src="/js/jquery.validate.js" type="text/javascript"></script>
    <script src="/js/jquery.maskedinput.js" type="text/javascript"></script>
    <script src="/js/jquery-ui-1.7.3.custom.min.js" type="text/javascript"></script>
    <script src="/js/jquery.flot.min.js" type="text/javascript"></script>
    <script src="/js/additional-methods.js" type="text/javascript"></script>
    <script src="/js/md5.js" type="text/javascript"></script>
    <script src="/js/application.js" type="text/javascript"></script>
    <script type="text/javascript">
        {if ($current_user != null)}
        current_user_id = {$current_user->user_id};
        {/if}
        $(document).ready(function(){ldelim}
            adjust_height();
            if (typeof setup_{$page} == 'function')
                setup_{$page}();
        {rdelim});
        if (jQuery.browser.msie)
            if(jQuery.browser.version < 7)
                document.write('<link href="/ie6.css" rel="stylesheet" type="text/css" />');
            else
                document.write('<link href="/ie.css" rel="stylesheet" type="text/css" />');
    </script>
</head>

<body>
<div id="all">
    {include file="header.tpl"}
   
        <div id="container">
        {if !($page == 'login' || $page == 'loginadmin')}
            {if $title != ''}
            <h1>{$title}</h1>
            {/if}
        {/if}
	    {if $current_user 
        && !($page == 'login' || $page == 'loginadmin' || $page == 'send_password' || $page == 'reset')}
	        {include file=navigation_menu.tpl}
	    {/if}
            {$content}
        </div>
<div id="footer">
    <p>Questions or comments regarding {$product_name} may be directed to 
    {$support_mail_link} 
    or call {$support_phone}</p>
</div>
   
</div>
</body>
</html>