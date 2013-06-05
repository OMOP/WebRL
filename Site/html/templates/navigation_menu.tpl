{*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Template for navigation menu in the application. Menu builded dynamically based 
    on the currently logged-in user rights.
 
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
 
================================================================================*}
{if !$current_user->password_is_expired}
<ul class="navigation">
{if $application_mode == 1}
    {if $page=="instances"}<li class="active"><a {else}<li><a {/if}href="{php}echo PageRouter::build('instances');{/php}" title="">Instances</a></li>
    <li>
        <a href="/public/user" title="Users Management">Users Management</a>
        {*<ul>
            <li>
                <a title="User List" href="/public/user/list">User List</a>
            </li>
            <li>
                <a title="Organization List" href="/index.php?page=organizations">Organization List</a>
            </li>
        </ul>*}
    </li>
    <li>
        <a href="/public/run.results/default">Result Storage</a>
        {*<ul>
            <li>
                <a title="Upload Results" href="/public/run.results">Upload Results</a>
            </li>
            <li>
                <a title="Oracle Results" href="/public/run.results/oracle">Oracle Results</a>
            </li>
            <li>
                <a title="S3 Results" href="/public/s3results/list">S3 Results</a>
            </li>
            <li>
                <a title="Logs" href="/public/run.results/logs">Result Upload Logs</a>
            </li>
        </ul>*}
    </li>
    <li>
        <a title="OSIM2" href="/public/osim2">OSIM2</a>
        {*<ul>
            <li>
                <a title="Summary Sets" href="/public/osim2/list-summary">Summary Sets</a>
            </li>
            <li>
                <a title="Load Summary Set" href="/public/osim2/load-summary">Load Summary</a>
            </li>
            <li>
                <a title="Load Summary Set FTP" href="/public/osim2/load-summary-ftp">Load Summary FTP</a>
            </li>
            <li>
                <a title="Generate Summary Set" href="/public/osim2/generate-summary">Generate Summary</a>
            </li>
            <li>
                <a title="Generate OSIM2" href="/public/osim2/generate-dataset">Generate OSIM2</a>
            </li>
            <li>
                <a title="List OSIM2" href="/public/osim2/list-datasets">List OSIM2</a>
            </li>
        </ul>*}
    </li>
  {if $current_user->organization_id == 0}
    <li><a href="/public/budget" title="">Budget</a></li>
    <li>
        <a title="Security Log" href="/public/security-log">Security Log</a>
        {*<ul>
            <li>
                <a title="Security Log" href="/public/security-log/list">Security Log</a>
            </li>
            <li>
                <a title="Instance Connect Log" href="/public/connect-log/instance">Connect Log</a>
            </li>
            <li>
                <a title="Web Connect Log" href="/public/connect-log/web">Web Connect Log</a>
            </li>
            <li>
                <a title="Error Log" href="/public/error/log">Error Log</a>
            </li>
            <li>
                <a title="Audit Trail" href="/public/audit-trail/list">Audit Trail</a>
            </li>
            <li>
                <a title="Amazon Log" href="/public/amazon-log">Amazon Log</a>
            </li>
        </ul>*}
    </li>
    <li><a href="/public/configuration" title="">Configuration</a></li>
  {/if}
{else}
    {if $page=="instances"}<li class="active"><a {else}<li><a {/if}href="/public/instance/list" title="">Running Instances</a></li>
	{if $current_user->admin_flag == 'Y'}
	    <li {if $page=="running_methods"}class="active"{/if}><a href="/public/running-method/list" title="">Running Methods</a></li>
		<li {if $page=='launch_method'}class="active">{/if}><a href="/public/instance-launch/method" title="">Method Launch</a></li>
    {/if}
    {if $page=='launch'}<li class="active"><a {else}<li><a {/if}href="/public/instance-launch/instance" title="">Launch</a></li>
    {if $current_user->svn_access_flag == 'Y'}
    <li><a href="http://{php}echo $_SERVER['SERVER_NAME'];{/php}/websvn" title="">Subversion</a></li>
    {/if}
    {if $current_user->result_access_flag == 'Y'}
    <li><a href="/public/run.results/default" title="">Load Results</a></li>
    {/if}
    {if $page=="tools_download"}<li class="active" style="float:right"><a {else}<li style="float:right"><a {/if}href="{php}echo PageRouter::build('tools_download');{/php}" title="">Client Install</a></li>
{/if}
</ul>
{/if}
