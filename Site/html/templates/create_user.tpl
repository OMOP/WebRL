{*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Template for Edit Account page.
 
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
{include file="validation_support.tpl"}
<div class="invisible_block">
    <form action="{php}echo PageRouter::build('create_user','submit');{/php}" method="post" class="form2" autocomplete="off">
    <div class="left no-border no-margin">
    <div class="left">
	{include file="round_corners.tpl"}
	<fieldset>
	<legend>Login</legend>
	    <div>
		<label for="idUserid">Login ID<strong>*</strong>:</label>
		<input type="text" id="idUserid" name="user_id" maxlength="50" class="required text" value="{$user_id}" />
	    </div>
	    <div>
		<label for="idFirstname">First Name<strong>*</strong>:</label>
		<input type="text" id="idFirstname" name="first_name" maxlength="50" class="required text" value="{$first_name}" />
	    </div>
	    <div>
		<label for="idLastname">Last Name<strong>*</strong>:</label>
		<input type="text" id="idLastname" name="last_name" maxlength="50" class="required text" value="{$last_name}" />
	    </div>
	    <div>
		<label for="idEmail">Email<strong>*</strong>:</label>
		<input type="text" id="idEmail" name="email" maxlength="128" class="required text" value="{$email}" />
	    </div>
	    <div>
		<label for="idPhone">Phone<strong>*</strong>:</label>
		<input type="text" id="idPhone" name="phone" maxlength="20" class="required text" value="{$phone}" />
	    </div>
{if $current_user->organization_id == 0}
	    <div>
		<label for="idOrganization">Organization:</label>
		<select id="idOrganization" name="organization_id" style="width:195px">
            <option value="">--None--</option>
            {foreach item=org from=$organizations}
                <option value="{$org->organization_id}"{if ($organization_id == $org->organization_id)}selected="selected"{/if}>{$org->organization_name}</option>
            {/foreach}
        </select>
	    </div>
{else}
        <input type="hidden" id="idOrganization" class="text_disable" readonly="readonly" maxlength="50" name="organization_id" value="{$current_user->organization_id}" />
{/if}
	    <div>
		<label for="idTitle">Title:</label>
		<input type="text" id="idTitle" name="title" maxlength="50" class="text" value="{$user_title}" />
	    </div>
	    <div style="margin:10px">
		Account information including password will be emailed to the user.
	    </div>
	</fieldset>
    </div>
    <div class="right" style="clear:both">
	<input onmouseover="this.style.backgroundPosition='bottom';" onmouseout="this.style.backgroundPosition='top';" 
	type="submit" name="button_submit" id="button_submit" class="button_90" value="Submit" 
	{if $limit_reached == true}onclick="alert('Limit of users reached.');return false;"{/if}/>
    </div>
    
    </div>  
    <div class="left_">
	{include file="round_corners.tpl"}
	<fieldset>
	<legend>Login</legend>
	    <div class="checkbox">
		<label for="idStatus">Active Status:</label>
		<input type="checkbox" id="idStatus" name="active_flag" class="checkbox" {if $active_flag == 'Y'}checked="checked"{/if} value="Y" />
	    </div>
        <div class="checkbox">
		<label for="idSvnAccess">Has SVN Access?:</label>
		<input type="checkbox" id="idSvnAccess" name="svn_access_flag" class="checkbox" {if $svn_access_flag == 'Y'}checked="checked"{/if} value="Y" />
	    </div>
        <div class="checkbox">
	    <label for="idAdminFlag">Is {if $current_user->organization_id == 0}Sys {else}Org {/if}Admin?:</label>
		<input type="checkbox" id="idAdminFlag" name="admin_flag" class="checkbox" {if $admin_flag == 'Y'}checked="checked"{/if} value="Y" />
	    </div>
	    <div>
		<label for="idLimit">Charge limit ($)<strong>*</strong>:</label>
		<input type="text" id="idLimit" name="money_limit" maxlength="10" class="text" value="{$default_charge_limit|string_format:"%d"}" />
	    </div>
	    <div>
		<label for="idStorageSize" class="twolines">Personal Storage<br/>Size (GB)<strong>*</strong>:</label>
		<input type="text" id="idStorageSize" name="user_volume_size" class="text" value="{$user_volume_size}" />
	    </div>
	    <div>
		<label for="idMaxinst">Max instances<strong>*</strong>:</label>
		<input type="text" id="idMaxinst" name="num_instances" maxlength="5" class="text" value="{$num_instances}" />
	    </div>
	    <div class="checkbox">
		<label for="idDatabasetypes">Dataset access<strong>*</strong>:<br/></label>
            <div class="block_left">
                {foreach item=dt from=$db_types}
                <p><input type="checkbox" id="idDatabasetypes_{$dt->dataset_type_id}" class="required checkbox" name="database_type[]"
                {if (empty($database_types) && ($dt->default_checked_flag == 1)) || (is_array($database_types) && in_array($dt->dataset_type_id, $database_types))}checked="checked"{/if}
                {if is_array($allowed_db_types) && !in_array($dt->dataset_type_id, $allowed_db_types)}disabled="disabled"{/if}
                value="{$dt->dataset_type_id}" /> {$dt->dataset_type_description}</p>
                {/foreach}
            </div>
	    </div>
	    <div class="checkbox">
		<label for="idSoftwaretypes">Image access<strong>*</strong>:<br/></label>
            <div class="block_left">
                {foreach item=st from=$soft_types}
                <p><input type="checkbox" id="idSoftwaretypes_{$st->software_type_id}" class="required checkbox" name="software_type[]"
                {if (empty($software_types) && ($st->default_checked_flag == 1)) || (is_array($software_types) && in_array($st->software_type_id, $software_types))}checked="checked"{/if}
                {if !in_array($st->software_type_id, $allowed_soft_types)}disabled="disabled"{/if}
                value="{$st->software_type_id}" /> {$st->software_type_description}</p>
                {/foreach}
            </div>
	    </div>
	</fieldset>
    </div>
    <div class="clear"></div>
    </form>
    <div class="clear"></div>
</div>