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
<style type="text/css">
#container form.form2 .classStorageSharing label {ldelim}
    float: none;
{rdelim}
#container form.form2 .classStorageSharing p {ldelim}
    clear: both;
    padding-left: 10px;
{rdelim}
</style>

{include file="validation_support.tpl"}
<div class="clear"></div>
<div class="invisible_block {if $sharing_allowed}invisible_block_3columns{/if}">
    {if $user->password_is_expired}
    <div class="errorbox">Your password expired</div>
    {/if}
    <form action="{php}echo PageRouter::build('edit_account','submit');{/php}" method="post" class="form2" autocomplete="off">

    <div class="left no-border no-margin">
    <div class="left">
	<img class="top_left" src="/images/top_left.gif" width="11" height="11" alt="" />
	<img class="top_right" src="/images/top_right.gif" width="11" height="11" alt="" />
	<img class="bottom_left" src="/images/bottom_left.gif" width="11" height="11" alt="" />
	<img class="bottom_right" src="/images/bottom_right.gif" width="11" height="11" alt="" />
	<fieldset>
	<legend>Edit Account</legend>
	    <input type="hidden" id="internal_id" name="internal_id" value="{$user->user_id}" />
	    <input type="hidden" id="organization_id" name="organization_id" value="{$user->organization_id}" />
	    <div>
		<label for="idEmail">Email<strong>*</strong>:</label>
		<input type="text" id="idEmail" class="required text" maxlength="128" name="email" value="{$user->email|escape:'html'|stripslashes}" />
	    </div>
	    <div>
		<label for="idFirstname">First Name<strong>*</strong>:</label>
		<input type="text" id="idFirstname" class="required text" maxlength="50" name="first_name" value="{$user->first_name|escape:'html'|stripslashes}" />
	    </div>
	    <div>
		<label for="idLastname">Last Name<strong>*</strong>:</label>
		<input type="text" id="idLastname" class="required text" maxlength="50" name="last_name" value="{$user->last_name|escape:'html'|stripslashes}" />
	    </div>
	    <div>
		<label for="idPhone">Phone<strong>*</strong>:</label>
		<input type="text" id="idPhone" class="required text" maxlength="20" name="phone" value="{$user->phone|escape:'html'|stripslashes}" />
	    </div>
	    <div>
		<label for="idOrganization">Organization:</label>
		<input type="text" id="idOrganization" class="text_disable" readonly="readonly" maxlength="50" name="organization" value="{$user->organization->organization_name|escape:'html'|stripslashes}" />
	    </div>
	    <div>
		<label for="idTitle">Title:</label>
		<input type="text" id="idTitle" class="text" maxlength="50" name="title" value="{$user->title|escape:'html'|stripslashes}" />
	    </div>
	    <div>
		<label for="idPassword">Password:</label>
		<input autocomplete="off" type="password" id="idPassword" class="text" name="password" value="" />
	    </div>
	    <div>
		<label for="idConfirmation">Password Again:</label>
		<input type="password" id="idConfirmation" class="text" name="confirmation" value="" />
        <span style="color:black;">Password will be reset only if it is entered</span>
	    </div>
	    {*<div>
		<label for="idCertificate">Certificate:</label>
		<input type="text" id="idCertificate" class="text" name="certificate" value="{if $user->certificate_public_key != ''}{$user->certificate_public_key}{/if}" />
	    </div>*}
	</fieldset>
    </div>
    <div class="right" style="clear: both;">
        <input onmouseover="this.style.backgroundPosition='bottom';" onmouseout="this.style.backgroundPosition='top';" type="submit" name="button_submit" id="button_submit" class="button_90" value="Submit" />
    </div>
    </div>
    
    {if $sharing_allowed}
    <div class="left_ no-border no-margin" style="width: 370px;">

    <div class="left_">
	<img class="top_left" src="/images/top_left.gif" width="11" height="11" alt="" />
	<img class="top_right" src="/images/top_right.gif" width="11" height="11" alt="" />
	<img class="bottom_left" src="/images/bottom_left.gif" width="11" height="11" alt="" />
	<img class="bottom_right" src="/images/bottom_right.gif" width="11" height="11" alt="" />
    <h2>Allow sharing my storage with</h2>
	<fieldset>
        <legend>Allow sharing my storage with</legend>
	    <div class="checkbox classStorageSharing">
            {foreach item=u from=$usersToShare}
            {if $u->user_id != $user->user_id}
            <p><label for="idStorageSharing_{$u->user_id}"><input type="checkbox" id="idStorageSharing_{$u->user_id}" class="checkbox" name="share_users[]"
            {if in_array($u->user_id, $allowed_users)}checked="checked"{/if}
            value="{$u->user_id}" /> {$u->login_id}</label></p>
            {/if}
            {/foreach}
	    </div>
	</fieldset>
    </div>

    <div class="left_">
	<img class="top_left" src="/images/top_left.gif" width="11" height="11" alt="" />
	<img class="top_right" src="/images/top_right.gif" width="11" height="11" alt="" />
	<img class="bottom_left" src="/images/bottom_left.gif" width="11" height="11" alt="" />
	<img class="bottom_right" src="/images/bottom_right.gif" width="11" height="11" alt="" />
    <h2>Users sharing their storage with me</h2>
	<fieldset>
        <legend>Users sharing their storage with me</legend>
        <div class="checkbox classStorageSharing">
        {if is_array($sharing_their_storage_users) && sizeof($sharing_their_storage_users) > 0}
            {foreach item=u from=$sharing_their_storage_users}
            {if $u.user_id != $user->user_id}
            <p><input type="checkbox" class="required checkbox"
                checked="checked" disabled="disabled"
                value="{$u.user_id}" /> {$u.login_id}</p>
            {/if}
            {/foreach}
        {else}
            <p><em>Other users haven't shared their storages yet.</em></p>
        {/if}
        </div>
	</fieldset>
    </div>

    </div>
    {/if}

    <div class="left_">
	<img class="top_left" src="/images/top_left.gif" width="11" height="11" alt="" />
	<img class="top_right" src="/images/top_right.gif" width="11" height="11" alt="" />
	<img class="bottom_left" src="/images/bottom_left.gif" width="11" height="11" alt="" />
	<img class="bottom_right" src="/images/bottom_right.gif" width="11" height="11" alt="" />
	<fieldset>
	<legend>Login</legend>
	    <div class="checkbox">
		<label for="idDatabasetypes">Dataset access:</label>
		<div class="block_left">
            {foreach item=dt from=$dataset_types}
			{if in_array($dt->dataset_type_id, $da_ids)}
			<p><input type="checkbox" class="required checkbox"
			checked="checked"
            name="database_type[]"
            value="{$dt->dataset_type_id}" disabled="disabled" />&nbsp;{$dt->dataset_type_description}</p>
            {/if}
            {/foreach}
		</div>
	    </div>
	    <div class="checkbox">
		<label for="idSoftwareTypes">Image access:</label>
		<div class="block_left">
            {foreach item=type from=$software_types}
                {assign var=id value=$type->software_type_id}
                {if in_array($type->software_type_id, $sa_ids)}
                <p><input type="checkbox" checked="checked" disabled="disabled" />&nbsp;{$type->software_type_description}</p>
                {/if}
            {/foreach}
		</div>
	    </div>
	    <div>
		<label for="idLimit">Charge limit ($):</label>
		<input type="text" id="idLimit" class="text_disable" readonly="readonly" value="{$user->user_money|number_format:0}" />
	    </div>
        <div>
        <label for="idRemaining">Remaining ($):</label>
        <input type="text" id="idRemaining" class="text_disable" readonly="readonly" value="{$user->remains_limit|number_format:0}" />
        </div>
	    <div>
		<label for="idMaxinst">Max instances:</label>
		<input type="text" id="idMaxinst" class="text_disable" readonly="readonly" value="{$user->num_instances}"/>
	    </div>
	    {*<div>
		<label for="idStatus">Active instances:</label>
		<input type="checkbox" id="idStatus" disabled="disabled" {if $user->running_instances_count > 0} checked="checked"{/if}/>
	    </div>*}
	</fieldset>
    </div>

    <div class="clear"></div>
</form>
</div>
