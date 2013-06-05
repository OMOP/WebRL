{*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    17 August 2010
 
    Contains class Application that act as main entry point for the application 
	specific logic. This class handle all logic that are the same for all pages
	across all aplication.
 
    (c)2009-2010 Foundation for the National Institutes of Health (FNIH)
 
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
    {include file="round_corners.tpl"}    
    {include file="users_menu.tpl"}      
    <table cellspacing="0" cellpadding="0" border="0">
        <thead>
        <tr>
            <th>
                {assign var="column_name" value="Organization"}
                {$column_name}
            </th>
            <th>
                {assign var="column_name" value="Admin"}
                {$column_name}
            </th>
            <th>
                {assign var="column_name" value="Active Users"}
                {$column_name}
            </th>
            <th>
                {assign var="column_name" value="Max Users"}
                {$column_name}
            </th>
            <th>
                {assign var="column_name" value="Running Instances"}
                {$column_name}
            </th>
            <th>
                {assign var="column_name" value="Instances Limit"}
                {$column_name}
            </th>
            <th>
                {assign var="column_name" value="Charged ($)"}
                {$column_name}
            </th>
            <th>
                {assign var="column_name" value="Remaining ($)"}
                {$column_name}
            </th>
        </tr>
        </thead>
        <tfoot> 
        <tr>
            <th colspan="8">{include file="pager.tpl"}</th>
        </tr>
        <tr>
	    <th colspan="8">
	    <input onmouseover="this.style.backgroundPosition='bottom';" onmouseout="this.style.backgroundPosition='top';" 
	    id="button_new_user" class="button_105" 
	    type="button" value="Add Org." onclick="window.location='{php}echo PageRouter::build('create_organization');{/php}'" />
	    </th>
	</tr>
        </tfoot>
        <tbody>
        {foreach item=o from=$organizations}
        <tr>
            <td>
            <a href="{php}echo PageRouter::build('edit_organization',$this->_tpl_vars['o']->organization_id);{/php}" 
            title="Edit Org information">
            {$o->organization_name|escape:'html'|stripslashes|default:'&nbsp;'}
            </a>
            </td>
            <td>
            {assign var="organization_admins" value=`$o->organization_admins`}
            {if count($organization_admins)}
	            {foreach item=admin name=organization_admins from=$organization_admins}
		            	 {assign var="admin_id" value=`$admin->organization_admin_id`}
				         <a href="{php}echo PageRouter::build('edit_user',$this->_tpl_vars['admin']->user_id);{/php}" 
				         title="Edit Admin account">
				         {$admin->login_id|escape:'html'|stripslashes|default:'&nbsp;'}</a>
		            {if !$smarty.foreach.organization_admins.last},{/if}
		        {/foreach}
            {else}
            &nbsp;
            {/if}
            
            </td>
            <td class="right">
            {$o->active_users_count|string_format:"%d"}
            </td>
            <td class="right">
            {$o->organization_users_limit|string_format:"%d"}
            </td>
            <td class="right">
            {$o->running_instances_count|string_format:"%d"}
            </td>
            <td class="right">
            {$o->organization_instances_limit|string_format:"%d"}
            </td>
            <td class="right">
            <a href="{php}echo PageRouter::build('organization_charges',$this->_tpl_vars['o']->organization_id);{/php}" 
            title="View per user charges">
            {$o->total_charged|number_format:0}
            </a>
            </td>
            <td class="right">
            {$o->remains_limit|number_format:0}
            </td>
            <td class="center">
                {if $o->active_users_count == 0}
                <a href="{php}echo PageRouter::build('organizations','delete_'.$this->_tpl_vars['o']->organization_id);{/php}"
                onclick="javascript:return confirm('Deletion requested for organization {$o->organization_name}.\r\n\r\nPress CANCEL if you do not want to delete this organization.')"/><img src="/images/trash.gif" alt="Terminate" /></a>
                {/if}
            </td>
        </tr>
        {/foreach}
        </tbody>
    </table>
</div>
