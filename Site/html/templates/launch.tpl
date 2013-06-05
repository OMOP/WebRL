{*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Template for Launch Instance page
 
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
<script type="text/javascript">
        const_software_types = {ldelim}
        {foreach item=type name=software_types from=$software_types}
            '{$type->software_type_id}' : 
            {ldelim} 
                id : '{$type->software_type_id}', 
                description: '{$type->software_type_description}', 
                platform: '{$type->software_type_platform}', 
                os_family: '{$type->os_family}', 
                ebs_flag: '{$type->ebs_flag}', 
                gpu_required_flag: '{$type->gpu_required_flag}',
                cluster_flag: '{$type->cluster_flag}'
            {rdelim}{if !$smarty.foreach.software_types.last},{/if}
        {/foreach}
        {rdelim};
        const_dataset_types = {ldelim}
        {foreach item=type name=dataset_types from=$dataset_types}
            '{$type->dataset_type_id}' : 
            {ldelim} 
                id : '{$type->dataset_type_id}', 
                default_flag: '{$type->default_checked_flag}', 
                description: '{$type->dataset_type_description}',
                size: {$type->dataset_type_ebs_size}
            {rdelim}{if !$smarty.foreach.dataset_types.last},{/if}
        {/foreach}
        {rdelim};
        const_temporary_ebs_entries = {ldelim}
        {foreach item=temp_ebs name=temporary_ebs_entries from=$temporary_ebs_entries}
            '{$temp_ebs->snapshot_entry_id}' : 
            {ldelim} 
                id : '{$temp_ebs->snapshot_entry_id}', 
                default_flag: '{$temp_ebs->default_checked_flag}', 
                description: '{$temp_ebs->snapshot_entry_description}',
                size: {$temp_ebs->snapshot_entry_ebs_size}
            {rdelim}{if !$smarty.foreach.temporary_ebs_entries.last},{/if}
        {/foreach}
        {rdelim};
        
        const_instance_sizes = {ldelim}
        {foreach item=type name=instance_sizes from=$instance_types}
            '{$type->instance_size_id}' : 
            {ldelim} 
                id : '{$type->instance_size_id}', 
                name : '{$type->instance_size_name}',
                aws_instance_size_name : '{$type->aws_instance_size_name}',
                description: '{$type->instance_size_description}',
                platform: '{$type->platform}',
                price: '{$type->instance_price}',
                gpu_flag: '{$type->gpu_flag}',
                ebs_required_flag: '{$type->ebs_required_flag}',
                os_family: '{$type->os_family}', 
                cluster_flag: '{$type->cluster_flag}'

            {rdelim}{if !$smarty.foreach.instance_sizes.last},{/if}
        {/foreach}
        {rdelim};
        
        {if $current_user->organization != null}
		admin_factor = {$current_user->organization->organization_admin_factor} 
		{else}
		admin_factor = 0;
		{/if}
        
        user_money = {$user->user_money};    
		{if isset($selected_instance_types)}
		selected_instance_types = {$selected_instance_types};
		{/if}
    var assigned_instance_names = [{foreach item=i from=$instance_names}'{$i}', {/foreach}];
</script>
<style>
/* 
This is a workaround for make same margin for Cost and Budget content across all browsers.
Chrome has different calculation of internal height then FF.
*/
#container form.form2  fieldset
{ldelim} 
	margin: 20px 0;    
{rdelim}
</style>
<div class="clear"></div> 
<div id="message" style="display:none"></div>
<form action="{php}echo PageRouter::build('launch','submit');{/php}" method="post" class="form2">
<div class="invisible_block">
    <div class="left">
	<img class="top_left" src="/images/top_left.gif" width="11" height="11" alt="" />
	<img class="top_right" src="/images/top_right.gif" width="11" height="11" alt="" />
	<img class="bottom_left" src="/images/bottom_left.gif" width="11" height="11" alt="" />
	<img class="bottom_right" src="/images/bottom_right.gif" width="11" height="11" alt="" />
	<h2>Configure Instances</h2>
	<fieldset>
	<legend>Configure Instances</legend>
	    <div style="margin: 0px;">
		<label for="idTool">Image:</label>
		<select id="idTool" name="tool" class="text">
            {foreach item=type from=$software_types}
                <option value="{$type->software_type_id}"{if ($selected_software_type == $type->software_type_id) } selected="selected"{/if}>{$type->software_type_description}</option>            
            {/foreach}            
        </select>
	    </div>
        {if (count($dataset_types) != 0)}
        <div class="checkbox">
		<label for="idDatabase">Dataset:</label>
		<div class="block_left" id="idDatabase">
		    {foreach item=dt from=$dataset_types}
			<p>			
			<input type="checkbox" class="checkbox" {if (!isset($selected_dataset_types) && $dt->default_checked_flag) || in_array($dt->dataset_type_id, $selected_dataset_types)}checked="checked"{/if}
				id="dataset_type_{$dt->dataset_type_id}" 
            	name="database_type[]" value="{$dt->dataset_type_id}" /> {$dt->dataset_type_description} </p>
            	
            {if $dt->encrypted_flag}
            <div id="dataset_type_label_{$dt->dataset_type_id}_password"
            	style="color:black;left:11px;position:absolute;width:150px;{if (!isset($selected_dataset_types) && $dt->default_checked_flag) || in_array($dt->dataset_type_id, $selected_dataset_types)}{else}display:none{/if}">
            	Password for dataset:<br />
            </div>
            <input autocomplete="off" type="password" class="{if (!isset($selected_dataset_types) && $dt->default_checked_flag) || in_array($dt->dataset_type_id, $selected_dataset_types)}required {else}{/if}text" 
            	name="dataset_type_{$dt->dataset_type_id}_password" 
            	id="dataset_type_{$dt->dataset_type_id}_password" 
            	style="{if (!isset($selected_dataset_types) && $dt->default_checked_flag) || in_array($dt->dataset_type_id, $selected_dataset_types)}{else}display:none;{/if}margin: 6px 0pt 4px 0px;">{/if}
            {/foreach}
		</div>
	    </div>
	    {/if}
	    
	    <div>
		<label for="idInstancetype">Instance Type:</label>
        <select id="idInstancetype" name="instance_type" onchange="calculateEstimate()">
            {foreach item=type from=$instance_types}
                <option value="{$type->instance_size_id}"{if $selected_instance_types == $type->instance_size_id } selected="selected"{/if}>{$type->instance_size_name}</option>
            {/foreach}
        </select>
        </div>
	    <div>
		<label for="idInstanceTypeDescription">Description:</label>
		<textarea id="idInstanceTypeDescription" rows="4" cols="50" class="text_disable" readonly="readonly" >{$instance_types[0]->instance_size_description}</textarea>
	    </div>
	    <div class="checkbox">
	        <label for="idCheckoutMethodCode">Load Method Code:</label>
	        <p><input type="checkbox" id="idCheckoutMethodCode" name="checkout_method_code" class="checkbox"{if isset($selected_checkout_method_code) && $selected_checkout_method_code == 'Y'} checked="checked"{/if} value="Y" /></p>
        </div>
        {if $other_storage_available}
	    <div class="checkbox">
	        <label for="idOtherUsersStorage">Other Users Storage:</label>
	        <p><input type="checkbox" id="idOtherUsersStorage" name="attach_shared_storage" class="checkbox"{if isset($selected_attach_shared_storage) && $selected_attach_shared_storage == 'Y'} checked="checked"{/if} value="Y" /></p>
        </div>
        {/if}
	    <div class="checkbox">
	        <label for="idCreateUserEBS">My Personal Storage:</label>
	        <p><input type="checkbox" id="idCreateUserEBS" name="create_user_ebs" class="checkbox" {if !isset($selected_create_user_ebs) || $selected_create_user_ebs == 'Y'}checked="checked"{/if} value="Y" onchange="calculateEstimate()" /></p>
        </div>
	    <div>
		<label for="idTempEBS">Temporary Storage:</label>
        <select id="idTempEBS" name="temporary_ebs" onchange="calculateEstimate()">
            <option value="">--None--</option>
            {foreach item=ebs_entry from=$temporary_ebs_entries}
                <option value="{$ebs_entry->snapshot_entry_id}"{if (!isset($selected_temporary_ebs) && ($ebs_entry->default_checked_flag == 1)) || ($selected_temporary_ebs == $ebs_entry->snapshot_entry_id)} selected="selected"{/if}>{$ebs_entry->snapshot_entry_ebs_size} GB</option>
            {/foreach}
        </select>
        </div>
        <div>
        <label for="idNumber">Number of instances<strong>*</strong>:</label>
        <input type="text" id="idNumber" name="number_of_instances" class="required text" value="{if isset($number_of_instances)}{$number_of_instances}{else}1{/if}" maxlength="2" onchange="calculateEstimate()"/>
        </div>
        <div>
        </div>
	</fieldset>
    </div>
    <div class="left_">
    <img class="top_left" src="/images/top_left.gif" width="11" height="11" alt="" />
    <img class="top_right" src="/images/top_right.gif" width="11" height="11" alt="" />
    <img class="bottom_left" src="/images/bottom_left.gif" width="11" height="11" alt="" />
    <img class="bottom_right" src="/images/bottom_right.gif" width="11" height="11" alt="" />
    <h2>Cost and Budget <img id="calculating" style="display:none" src="/images/indicator.gif" /></h2>
    <fieldset>
    <legend>Cost and Budget</legend>
        <div style="margin:0px;">
	        <label for="idLimit" class="twolines">Estimated Instance<br/>$/h:</label>
	        <input type="text" id="idLimit" class="text_disable" readonly="readonly" value="" name="estimate"/>
        </div>
        <div>
	        <label for="idStorageCharge" class="twolines">Estimated Storage<br/>$/h:</label>
	        <input type="text" id="idStorageCharge" class="text_disable" readonly="readonly" value="" name="storage_estimage"/>
        </div>
        <div>
	        <label for="idTotalCharge" class="twolines">Estimated Total<br/>$/h:</label>
	        <input type="text" id="idTotalCharge" class="text_disable" readonly="readonly" value="" name="total_estimage"/>
        </div>
        <div>
	        <label for="idRemaining">Remaining Budget $:</label>
	        <input type="text" id="idRemaining" class="text_static" readonly="readonly" value="{$user->remains_limit|number_format:0}" name="remaining" />
        </div>
        <div>
        <label for="idMaxInstancesAllowed">Instances Limit:</label>
        <input type="text" id="idMaxInstancesAllowed" class="text_static" readonly="readonly" value="{$user->num_instances}" name="num_instances" />
        </div>
        <div>
        <label for="idActiveInstances">Running Instances:</label>
        <input type="text" id="idActiveInstances" class="text_static" readonly="readonly" value="{$user->running_instances_count}" name="active_instances" />
        </div>
	</fieldset>
    </div>
    <div class="right">
        <input onmouseover="this.style.backgroundPosition='bottom';" onmouseout="this.style.backgroundPosition='top';" type="submit" name="button_submit" id="button_submit" class="button_90" value="Launch" onclick="javascript: return hide_launch_button();" {if $user->certificate_public_key == ''} onclick="alert('Certificate not set for you account. You are not able launch instances.');return false;"{/if}/>
    </div>
</div>
</form>
<div class="clear"></div>
