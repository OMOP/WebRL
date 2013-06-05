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
    <img class="top_left" src="/images/top_left.gif" width="11" height="11" alt="" />
    <img class="top_right" src="/images/top_right.gif" width="11" height="11" alt="" />
    <img class="bottom_left" src="/images/bottom_left.gif" width="11" height="11" alt="" />
    <img class="bottom_right" src="/images/bottom_right.gif" width="11" height="11" alt="" />
    <h2>Charges for organization {$organization_name}</h2>
    <table cellspacing="0" cellpadding="0" border="0">
        <thead>
        <tr>
            <th>
                {assign var="column_name" value="User ID"}
                {$column_name}
            </th>
            <th>
                {assign var="column_name" value="Budget ($)"}
                {$column_name}
            </th>
            <th>
                {assign var="column_name" value="Charge ($)"}
                {$column_name}
            </th>            
            <th>
                {assign var="column_name" value="Remaining ($)"}
                {$column_name}
            </th>
        </tr>
        </thead>
        <tbody>
        {foreach item=u from=$charges}
        <tr>
            <td><a href="{php}echo PageRouter::build('user_history',$this->_tpl_vars['u']['user_id'].'_'.$this->_tpl_vars['u']['organization_id']);{/php}" title="">{$u.login_id}</a></td>
            <td class="right">
            {$u.budget|string_format:"%d"}
            </td>
            <td class="right">
            {$u.charged|string_format:"%d"}
            </td>
            <td class="right">
            {$u.remains|string_format:"%d"}
            </td>
        </tr>
        {/foreach}
        </tbody>
    </table>
</div>