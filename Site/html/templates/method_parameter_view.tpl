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
    <h2>Method Parameters List</h2>
    <table cellspacing="0" cellpadding="0" border="0">
        <thead>
        <tr>
            <th>
                {assign var="column_name" value="Name"}
                {$column_name}
            </th>
            <th>
                {assign var="column_name" value="SVN Path"}
                {$column_name}
            </th>
            {*<th>
                {assign var="column_name" value="Status Flag"}
                {$column_name}
            </th>*}
        </tr>
        </thead>
        {*<tfoot> 
        <tr>
            <th colspan="3">{include file="pager.tpl"}</th>
        </tr>
        <tr>
	    <th colspan="3">
	    <input onmouseover="this.style.backgroundPosition='bottom';" onmouseout="this.style.backgroundPosition='top';" 
            id="button_new_user" class="button_105" 
            type="button" value="Add Tool" onclick="window.location='{php}echo PageRouter::build('create_software_type');{/php}'" />
            </th>
        </tr>
        </tfoot>*}
        <tbody>
        {foreach item=mp from=$method_parameters}
        <tr>
            <td>
            {*<a href="{php}echo PageRouter::build('edit_dataset_type',$this->_tpl_vars['dt']->dataset_type_id);{/php}" title="">*}
            {$mp->method_parameter_name|escape:'html'|stripslashes}
            {*</a>*}
            </td>
            <td>{$mp->svn_path|escape:'html'|stripslashes|default:'&nbsp;'}</td>
            {*<td>{if $mn->default_checked_flag == '1'}Default{else}&nbsp;{/if}</td>
            <td>{$dt->dataset_type_ebs|escape:'html'|stripslashes|default:'&nbsp;'}</td>*}
            {*<td class="center">{if $u->active_flag == 'Y'}<a href="{php}echo PageRouter::build('edit_user','deactivate_'.$this->_tpl_vars['u']->user_id);{/php}">Deactivate</a>{else}<a href="{php}echo PageRouter::build('edit_user','activate_'.$this->_tpl_vars['u']->user_id);{/php}">Activate</a>{/if}</td>*}
        </tr>
        {/foreach}
        </tbody>
    </table>
</div>