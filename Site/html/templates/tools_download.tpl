{*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Contains class Application that act as main entry point for the application 
	specific logic. This class handle all logic that are the same for all pages
	across all aplication.
 
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
<div class="clear"></div>
<div class="list">
    <img class="top_left" src="/images/top_left.gif" width="11" height="11" alt="" />
    <img class="top_right" src="/images/top_right.gif" width="11" height="11" alt="" />
    <img class="bottom_left" src="/images/bottom_left.gif" width="11" height="11" alt="" />
    <img class="bottom_right" src="/images/bottom_right.gif" width="11" height="11" alt="" />
    <h2>Tools download</h2>
	{if ($certificate_not_downloaded)}
	<div style="padding: 10px; margin: 10px; " class="warning">
    After you change your password you also should download your certificate again.
    </div>
	{/if}
	<div class="bigpoint">
<p>Find packages for your operating system:</p>
<p>
   <a href="#windows">Windows</a> |
   <a href="#fedora">Fedora&nbsp;Linux</a> |
   <a href="#osx">Mac&nbsp;OS&nbsp;X</a> |
   <a href="#ubuntu">Ubuntu&nbsp;Linux</a> |
   <a href="#other">Other platforms</a></p>
</div>
    
    <div id="windows" class="h2">
<h3>Windows
  <a title="Link to this section" href="#windows" class="sectionlink">&para;</a>
</h3>
<div style="background-image: url(&quot;/images/windows.png&quot;);" class="package">

    <h3 style="padding: 0px 10px; margin: 14px 0px;">Default</h3>
    <p style="padding: 0 10px;">
	The default package installs all tools that will enable one click access from the Research Lab Webpage to the instances using:
    </p>
    <ul>
    	<li>PuTTY - terminal emulator</li>
        <li>XMing - XWindows emulator</li>
        <li>WinSCP - file transfer utility</li>
        {if $current_user->svn_access_flag == 'Y'}
        <li>RapidSVN - source control utility used by OMOP</li>
        {/if}
    </ul>
    <p style="padding: 0 10px;">
    Download the <a href="{php}echo PageRouter::build('download', 'tools_install'){/php}">installer</a> and follow the instructions.
	The certificate (private key) is part of the installation package and all tools will be automatically configured.
	</p>
	<h3 style="padding: 0px 10px; margin: 14px 0px;">Package for non-Administrators</h3>
    <p style="padding: 0 10px;">
	If you don’t have administrative rights on your computer, you can still use the tools.
	Use this download if that applies to your, or if you prefer a different configuration from the default package.
	This package contains the same tools as the default package. Follow the following steps:
	</p>
	<ul>
		{if $current_user->svn_access_flag == 'Y'}
		<li>Download the <a href="{php}echo PageRouter::build('download', 'tools'){/php}">zip package</a></li>
		{else}
		<li>Download the <a href="{php}echo PageRouter::build('download', 'tools_small'){/php}">zip package</a></li>
		{/if}
		<li>Unzip</li>
		<li>Run Install.bat from the extracted archive</li>
		<li>Run XMing setup from the XMing directory using the default configuration</li>
		<li>PuTTY and WinSCP do not need a separate installation</li>
	</ul>
    <h3 style="padding: 0 10px;margin: 14px 0px">Certificate only</h3>
	<p style="padding: 0 10px;">
		If you don’t require the installation of the tools, or you want to use your own access utilities, you can download the zipped certificate file <a href="{php}echo PageRouter::build('download', 'certificates'){/php}">here</a>.
		In PuTTY, select Connection > SSH > Auth and point to the ppk file with your login name.
		In WinSCP create a new Login and point to the ppk file with your login name in the “Private key file” field.
	</p>
    </div>
</div>

<div id="osx" class="h2">
<h3>Mac OS X
  <a title="Link to this section" href="#osx" class="sectionlink">&para;</a>
</h3>
<div style="background-image: url(&quot;/images/mac.png&quot;);" class="package">
<h3 style="padding: 0 10px; margin: 14px 0px">Default</h3>
<p style="padding: 0 10px;">
   The default package contains the integration of SSH with the browser configuration for telnet to use the certificate when connecting to the instance.
   Download the archive with the <a href="{php}echo PageRouter::build('download', 'tools_macos'){/php}">aplication</a>.
</p>
<h3 style="padding: 0 10px;margin: 14px 0px">Certificate only</h3>
<p style="padding: 0 10px;">
Download the zipped <a href="{php}echo PageRouter::build('download', 'certificates'){/php}">certificate</a>.
</p>
</div>
</div>


<div id="fedora" class="h2">
<h3>Fedora Linux
  <a title="Link to this section" href="#fedora" class="sectionlink">&para;</a>
</h3>
<div style="background-image: url(&quot;/images/fedora.png&quot;);" class="package">
<h3 style="padding: 0px 10px; margin: 14px 0px;">Default</h3>
	<p style="padding: 0 10px;">
	The default package contains the configuration for telnet to use the certificate when connecting to the instance.
	Download and run the <a href="{php}echo PageRouter::build('download', 'tools_fedora'){/php}">RPM</a> package.
    </p>
<h3 style="padding: 0 10px;margin: 14px 0px">Certificate only</h3>
<p style="padding: 0 10px;">
Download the zipped <a href="{php}echo PageRouter::build('download', 'certificates'){/php}">certificate</a>.
</p>
</div>
</div>



<div id="ubuntu" class="h2">
<h3>Ubuntu Linux
  <a title="Link to this section" href="#ubuntu" class="sectionlink">&para;</a>
</h3>
<div style="background-image: url(&quot;/images/ubuntu.png&quot;);" class="package">
<h3 style="padding: 0px 10px; margin: 14px 0px;">Default</h3>
<p style="padding: 0 10px;">
    The default package contains the integration of SSH with the browser configuration for telnet to use the certificate when connecting to the instance.
	Download the <a href="{php}echo PageRouter::build('download', 'tools_ubuntu'){/php}">DEB</a> package.
</p>
<h3 style="padding: 0 10px;margin: 14px 0px">Certificate only</h3>
<p style="padding: 0 10px;">
Download the zipped <a href="{php}echo PageRouter::build('download', 'certificates'){/php}">certificate</a>.
</p>

</div>
</div>


<div id="other" class="h2">
<h3>All Other Platforms
  <a title="Link to this section" href="#other" class="sectionlink">&para;</a>
</h3>
<div class="package">

<h3 style="padding: 0 10px;margin: 14px 0px">Certificate only</h3>
<p style="padding: 0 10px;">
Download the zipped <a href="{php}echo PageRouter::build('download', 'certificates'){/php}">certificate</a> and use the SSH and SCP clients available on your computer.
</p>

</div>
</div>

</div>
