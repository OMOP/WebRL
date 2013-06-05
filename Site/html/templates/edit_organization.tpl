{*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    18 August 2010
 
    Template for Edit Organization page.
 
    �2009-2010 Foundation for the National Institutes of Health (FNIH)
 
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
<div class="invisible_block">
    <form action="{php}echo PageRouter::build('edit_organization','submit');{/php}" method="post" class="form2" autocomplete="off">
    <div class="left">
	<img class="top_left" src="/images/top_left.gif" width="11" height="11" alt="" />
	<img class="top_right" src="/images/top_right.gif" width="11" height="11" alt="" />
	<img class="bottom_left" src="/images/bottom_left.gif" width="11" height="11" alt="" />
	<img class="bottom_right" src="/images/bottom_right.gif" width="11" height="11" alt="" />
	<fieldset>
	<legend>Login</legend>
        <input type="hidden" id="organization_id" name="organization_id" value="{$org->organization_id}" />
	    <div>
		<label for="idOrganizationName">Organization Name<strong>*</strong>:</label>
		<input type="text" id="idOrganizationName" name="organization_name" maxlength="50" class="required text" value="{$org->organization_name}" />
	    </div>
	    <div>
		<label for="idAddress1">Address Line 1:</label>
		<input type="text" id="idAddress1" name="organization_address_line1" maxlength="50" class="text" value="{$org->organization_address_line1}" />
	    </div>
	    <div>
		<label for="idAddress2">Address Line 2:</label>
		<input type="text" id="idAddress2" name="organization_address_line2" maxlength="50" class="text" value="{$org->organization_address_line2}" />
	    </div>
	    <div>
		<label for="idCity">City<strong>*</strong>:</label>
		<input type="text" id="idCity" name="organization_city" maxlength="50" class="required text" value="{$org->organization_city}" />
	    </div>
	    <div>
		<label for="idState">State<strong>*</strong>:</label>
		<input type="text" id="idState" name="organization_state" maxlength="128" class="required text" value="{$org->organization_state}" />
	    </div>
	    <div>
		<label for="idZip">ZIP Code<strong>*</strong>:</label>
		<input type="text" id="idZip" name="organization_zip" maxlength="12" class="required text" value="{$org->organization_zip}" />
	    </div>
	</fieldset>
    </div>
    <div class="left_">
	<img class="top_left" src="/images/top_left.gif" width="11" height="11" alt="" />
	<img class="top_right" src="/images/top_right.gif" width="11" height="11" alt="" />
	<img class="bottom_left" src="/images/bottom_left.gif" width="11" height="11" alt="" />
	<img class="bottom_right" src="/images/bottom_right.gif" width="11" height="11" alt="" />
	<fieldset>
	<legend>Login</legend>
	    <div class="checkbox">
		<label for="idStatus">Active Status:</label>
		<input type="checkbox" id="idStatus" name="active_flag" class="checkbox" {if $org->active_flag == 'Y'}checked="checked" {/if}value="Y" />
	    </div>
	    <div class="checkbox">
		<label for="idStorageSharingStatus">Storage Sharing:</label>
		<input type="checkbox" id="idStorageSharingStatus" name="storage_sharing_flag" class="checkbox"  {if $org->storage_sharing_flag == 'Y'}checked="checked" {/if}value="Y" />
	    </div>
	    <div>
		<label for="idAdminFactor">Admin Factor (%)<strong>*</strong>:</label>
		<input type="text" id="idAdminFactor" name="organization_admin_factor" maxlength="10" class="text" value="{$org->organization_admin_factor|string_format:"%d"}" />
	    </div>
	    <div>
	    <div>
		<label for="idLimit">Budget ($)<strong>*</strong>:</label>
		<input type="text" id="idLimit" name="organization_budget" maxlength="10" class="text" value="{$org->organization_budget|string_format:"%d"}" />
	    </div>
	    <div>
		<label for="idInstancesLimit">Max Instances<strong>*</strong>:</label>
		<input type="text" id="idInstancesLimit" name="organization_instances_limit" maxlength="5" class="text" value="{$org->organization_instances_limit}" />
	    </div>
	    <div>
		<label for="idUserLimit">Max Users<strong>*</strong>:</label>
		<input type="text" id="idUserLimit" name="organization_users_limit" maxlength="5" class="text" value="{$org->organization_users_limit}" />
	    </div>

	    <div>
		<label for="idSVNFolder">SVN top folder:</label>
		<input type="text" id="idSVNFolder" name="organization_svn_folder" maxlength="100" class="text" value="{$org->organization_svn_folder}" />
	    </div>
	    
	    <div class="checkbox">
		<label for="idDatabasetypes">Dataset access<strong>*</strong>:<br/></label>
            <div class="block_left">
                {foreach item=dt from=$db_types}
				<p><input type="checkbox" class="required checkbox"
	            {if in_array($dt->dataset_type_id, $da_ids)}checked="checked"{/if}
	            name="database_type[]" value="{$dt->dataset_type_id}" /> {$dt->dataset_type_description}</p>
	            {/foreach}
            </div>
	    </div>
	    <div class="checkbox">
		<label for="idSoftwaretypes">Image access<strong>*</strong>:<br/></label>
            <div class="block_left">
                {foreach item=st from=$soft_types}
				<p><input type="checkbox" class="required checkbox"
	            {if in_array($st->software_type_id, $sa_ids)}checked="checked"{/if}
	            name="software_type[]" value="{$st->software_type_id}" /> {$st->software_type_description}</p>
	            {/foreach}
            </div>
	    </div>
	</fieldset>
    </div>
    <div class="right" style="margin-right:37px">
	<input onmouseover="this.style.backgroundPosition='bottom';" onmouseout="this.style.backgroundPosition='top';" type="submit" name="button_submit" id="button_submit" class="button_90" value="Submit" />
    </div>
    <div class="clear"></div>
    </form>
    <div class="clear"></div>
</div>