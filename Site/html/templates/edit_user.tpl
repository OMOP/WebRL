{*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Template for Edit Account page.
 
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
{include file="validation_support.tpl"}
<style type="text/css">
#container form.form2 .classStorageSharing label {ldelim}
    float: none;
{rdelim}
#container form.form2 .classStorageSharing p {ldelim}
    clear: both;
    padding-left: 16px;
{rdelim}
</style>

<div class="invisible_block {if $sharing_allowed}invisible_block_3columns{/if}">
	<form action="{php}echo PageRouter::build('edit_user','submit');{/php}" method="post" class="form2" id="edit_user_form" autocomplete="off">
    
    <div class="left no-border no-margin">
    <div class="left">
	{include file="round_corners.tpl"}
	<fieldset>
	<legend>Login</legend>
        <input type="hidden" id="internal_id" name="internal_id" value="{$user->user_id}" />
        <input type="hidden" id="command" name="command" value="save" />
        
        <input type="hidden" id="old_usage" name="old_usage" value="" />
        <input type="hidden" id="new_usage" name="new_usage" value="" />
        
	    <div>
		<label for="idUserid">Login ID<strong>*</strong>:</label>
		<input type="text" id="idUserid" name="user_id" class="required text" maxlength="50" value="{$user->login_id|escape:'html'|stripslashes}" />
	    </div>
	    <div>
		<label for="idCreatedate">Create Date:</label>
		<input type="text" id="idCreatedate" readonly="readonly" class="text_disable" value="{*$user->created_date*}{$user->created_date|date_format:$date_format}" />
	    </div>
	    <div>
		<label for="idFirstname">First Name<strong>*</strong>:</label>
		<input type="text" id="idFirstname" name="first_name" maxlength="50" class="required text" value="{$user->first_name|escape:'html'|stripslashes}" />
	    </div>
	    <div>
		<label for="idLastname">Last Name<strong>*</strong>:</label>
		<input type="text" id="idLastname" name="last_name" maxlength="50" class="required text" value="{$user->last_name|escape:'html'|stripslashes}" />
	    </div>
	    <div>
		<label for="idEmail">Email<strong>*</strong>:</label>
		<input type="text" id="idEmail" name="email" maxlength="128" class="required text" value="{$user->email|escape:'html'|stripslashes}" />
	    </div>
	    <div>
		<label for="idPhone">Phone<strong>*</strong>:</label>
		<input type="text" id="idPhone" name="phone" maxlength="20" class="required text" value="{$user->phone|escape:'html'|stripslashes}" />
	    </div>
	    <div>
		<label for="idOrganization">Organization:</label>
{if $current_user->organization_id == 0}
		<select id="idOrganization" name="organization_id" style="width:195px"{* onchange="calculateEstimate()"*}>
            <option value="">--None--</option>
            {foreach item=org from=$organizations}
                <option value="{$org->organization_id}"{if ($user->organization_id == $org->organization_id)}selected="selected"{/if}>{$org->organization_name}</option>
            {/foreach}
        </select>
{else}
        <input type="text" id="idOrganization" class="text_disable" readonly="readonly" maxlength="50" name="organization" value="{$user->organization->organization_name|escape:'html'|stripslashes}" />
{/if}
	    </div>
	    <div>
		<label for="idTitle">Title:</label>
		<input type="text" id="idTitle" name="title" maxlength="50" class="text" value="{$user->title|escape:'html'|stripslashes}" />
	    </div>
	    <div class="checkbox">
		<label for="idPasswordReset">Reset Password:</label>
		<input type="checkbox" id="idPasswordReset" name="password_reset" {if $user->password_expiration_period == 'Y'}checked="checked"{/if} value="Y" />
        <span style="color:black; margin-top:4px;"> Reset password will be emailed to the user.</span>
	    </div>
        <br/>
        {*<div>
		<label for="idCertificate">Certificate:</label>
		<input type="text" id="idCertificate" class="text" name="certificate" value="{if $user->certificate_public_key != ''}{$user->certificate_public_key}{/if}" />
	    </div>*}
	</fieldset>
    </div>
    <div style="clear: both;" class="right">
        <input 
        	type="submit" name="button_submit" id="button_submit" class="button_90" value="Submit" />
    </div>
    </div>    

    {if $sharing_allowed}
    <div class="left_">
	{include file="round_corners.tpl"}
	<h2>Shares storage with</h2>
	<fieldset>
	<legend>Storage Sharing</legend>	    
	    <div class="checkbox classStorageSharing">
	    
		    {if is_array($allowed_users) && sizeof($allowed_users)>0}
                {foreach item=u from=$org_users}
                {if $u->user_id != $user->user_id && in_array($u->user_id, $allowed_users)}
                <p><input type="checkbox" id="idStorageSharing_{$u->user_id}" class="required checkbox" name="share_users[]"
                checked="checked" value="{$u->user_id}" disabled="disabled" /> {$u->login_id}</p>
                {/if}
                {/foreach}
            {else}
            <p><em>The user didn't share the storage</em></p>
            {/if}
            </div>	    
	</fieldset>
    </div>
    {/if}

    <div class="left_">
	{include file="round_corners.tpl"}
	<fieldset>
	<legend>Login</legend>
	    <div class="checkbox">
		<label for="idStatus">Active Status:</label>
		<input type="checkbox" id="idStatus" name="active_flag" {if $user->active_flag == 'Y'}checked="checked"{/if} {if ($current_user->organization_id != 0) && ($user->organization->organization_admin_id == $user->user_id) } disabled="disabled" {/if} value="Y" />
	    </div>
        <div class="checkbox">
		<label for="idSvnAccess">Has SVN Access?:</label>
		<input type="checkbox" id="idSvnAccess" name="svn_access_flag" {if $user->svn_access_flag == 'Y'}checked="checked"{/if} value="Y" />
	    </div>
        <div class="checkbox">
		<label for="idAdminFlag">Is {if $user->organization_id == 0}Sys {else}Org {/if}Admin?:</label>
		<input type="checkbox" id="idAdminFlag" name="admin_flag" {if $user->admin_flag == 'Y'}checked="checked"{/if} {if ($current_user->organization_id != 0) && ($user->organization->organization_admin_id == $user->user_id) } disabled="disabled"{/if} value="Y" />
	    </div>
	    <div>
		<label for="idLimit">Charge limit ($)<strong>*</strong>:</label>
		<input type="text" id="idLimit" name="money_limit" maxlength="10" class="required text" value="{$user->user_money|string_format:"%d"}" />
	    </div>
	    <div>
		<label for="idRemaining">Remaining ($):</label>
		<input type="text" id="idRemaining" readonly="readonly" class="text_disable" value="{$user->remains_limit|number_format:0}" />
	    </div>
	    
	    
        <div>
        <label for="idStorageInstance" class="twolines">Storage Host:</label>
        <div class="block_left">
            <input type="text" id="idStorageInstance" readonly="readonly" name="storage_host" class="text_disable" value="ec2-72-44-59-15.compute-1.amazonaws.com" />
        </div>
        </div>
        <div>
        <label for="idStorageInstance" class="twolines">Mapping folder on Storage Host:</label>
        <div class="block_left">
            <input type="text" id="idStorageInstance" readonly="readonly" name="mapping_folder" class="text_disable" value="/var/storage/{$user->login_id|escape:'html'|stripslashes}" />
        </div>
        </div>
	    
	    <div>
		<label for="idCurrentStorageSize" class="twolines">Personal Storage Usage (GB):</label>
		<div class="block_left">
			<input type="text" id="idCurrentStorageSize" readonly="readonly" name="old_usage" class="text_disable" value="{$space_usage|number_format:0}" />
		</div>
	    </div>
		<div>
		<label for="idStorageSize" class="twolines">Personal Storage<br/>Size (GB)<strong>*</strong>:</label>
		<div>
			<input type="text" id="idStorageSize" class="text" name="user_volume_size" value="{$user->user_volume_size}" />
		</div>
	    </div>
	    
	    <div>
		<label for="idMaxinst">Max instances<strong>*</strong>:</label>
		<input type="text" id="idMaxinst" name="num_instances" maxlength="5" class="required text" value="{$user->num_instances}" />
	    </div>
	    <div class="checkbox">
		<label for="idDatabasetypes">Dataset access<strong>*</strong>:<br/><br/><br/></label>
		<div class="block_left">
		    {foreach item=dt from=$db_types}
			<p><input type="checkbox" class="required checkbox"
            {if in_array($dt->dataset_type_id, $da_ids)}checked="checked"{/if}
            {if !in_array($dt->dataset_type_id, $allowed_db_types)}disabled="disabled"{/if}
            name="database_type[]" value="{$dt->dataset_type_id}" /> {$dt->dataset_type_description}</p>
            {/foreach}
		</div>
	    </div>
	    <div class="checkbox">
		<label for="idSoftwaretypes">Image access<strong>*</strong>:<br/><br/><br/></label>
		<div class="block_left">
		    {foreach item=st from=$soft_types}
			<p><input type="checkbox" class="required checkbox"
            {if in_array($st->software_type_id, $sa_ids)}checked="checked"{/if}
            {if !in_array($st->software_type_id, $allowed_soft_types)}disabled="disabled"{/if}
            name="software_type[]" value="{$st->software_type_id}" /> {$st->software_type_description}</p>
            {/foreach}
		</div>
	    </div>
	</fieldset>
    </div>
    
	</form>
    <div class="clear"></div>
    
    
    
</div>
