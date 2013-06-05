{*================================================================================

    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Template file for /running_methods page.
 
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
<div class="clear"></div>
<div class="list">
    {include file="round_corners.tpl"}    
    {include file="dashboard_menu.tpl"}
    <form action="{php}echo PageRouter::build('running_methods');{/php}" method="post" class="form4">
        <fieldset>
        <legend>User</legend>
        <div><label for="idUser">User:</label>
            <select id="idUser" name="user_id">
            	<option value="0">--None--</option>
                {foreach item=u from=$users}
                    <option value="{$u->user_id}" {if $filter_user_id == $u->user_id}selected="selected"{/if}>{$u->last_name}, {$u->first_name} ({$u->login_id})</option>
                {/foreach}
            </select>
&nbsp;
&nbsp;
&nbsp;
        <input onmouseover="this.style.backgroundPosition='bottom';" 
                    onmouseout="this.style.backgroundPosition='top';" 
                    id="button_filter" class="button_80" 
                    type="submit" value="Filter" 
                    />
        </div>
        </fieldset>
    </form>
    <table cellspacing="0" cellpadding="0" border="0">
	<thead>
	<tr>
        {if $application_mode == 1}
        <th>
            {assign var="column_name" value="Login ID"}
            {if $sort_mode == 3}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],3,'asc',$this->_tpl_vars['filter_user_id']){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],3,'desc',$this->_tpl_vars['filter_user_id']){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_running_methods(1,3,'asc'){/php}" title="">
            {$column_name}</a>
            {/if}
        </th>
        {/if}
	    <th>
            {assign var="column_name" value="Method"}
            {if $sort_mode == 1}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],1,'asc',$this->_tpl_vars['filter_user_id']){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],1,'desc',$this->_tpl_vars['filter_user_id']){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_running_methods(1,1,'asc'){/php}" title="{$column_name} ">
            {$column_name}</a>
            {/if}
        </th>
{*	    <th>
            {assign var="column_name" value="Connect"}
            {$column_name}
        </th>*}
	    <th>
            {assign var="column_name" value="Parameter"}
            {if $sort_mode == 2}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],2,'asc',$this->_tpl_vars['filter_user_id']){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],2,'desc',$this->_tpl_vars['filter_user_id']){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_running_methods(1,2,'asc'){/php}" title="{$column_name} ">
            {$column_name}</a>
            {/if}
        </th>
	    <th>
            {assign var="column_name" value="Start Date"}
            {if $sort_mode == 4}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],4,'asc',$this->_tpl_vars['filter_user_id']){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],4,'desc',$this->_tpl_vars['filter_user_id']){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_running_methods(1,4,'asc'){/php}" title="">
            {$column_name}</a>
            {/if}
        </th>
	    <th>
            {assign var="column_name" value="Complete Date"}
            {if $sort_mode == 7}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],7,'asc',$this->_tpl_vars['filter_user_id']){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],7,'desc',$this->_tpl_vars['filter_user_id']){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_running_methods(1,7,'asc'){/php}" title="">
            {$column_name}</a>
            {/if}
        </th>
	    <th>
            {assign var="column_name" value="Instance"}
            {if $sort_mode == 5}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],5,'asc',$this->_tpl_vars['filter_user_id']){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],5,'desc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_running_methods(1,5,'asc'){/php}" title="">
            {$column_name}</a>
            {/if}
        </th>
	    <th>
            {assign var="column_name" value="Status"}
            {if $sort_mode == 6}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],6,'asc',$this->_tpl_vars['filter_user_id']){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_running_methods($this->_tpl_vars['currentuserpage'],6,'desc',$this->_tpl_vars['filter_user_id']){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_running_methods(1,6,'asc'){/php}" title="">
            {$column_name}</a>
            {/if}
        </th>
	</tr>
	</thead>
	<tfoot>
	<tr>
	    <th colspan="9">
        {include file="pager.tpl"}
        </th>
	</tr>
	</tfoot>
    <tbody>
    {foreach item=ml from=$method_launches}
        {if $ml->public_dns == null}
        <tr id="launch_row_{$ml->method_launch_id}" class="booting">
        {else}
        <tr>
        {/if}
            {if $application_mode == 1}
            <td><a href="{php}echo PageRouter::build('edit_user',$this->_tpl_vars['ml']->instance->instance_request->user_id);{/php}" title="">{$ml->instance->instance_request->user->login_id}</a></td>
            {/if}
            <td>
            {$ml->method_name|escape:'html'|stripslashes}
            </td>
            <td>
            {$ml->method_parameter|escape:'html'|stripslashes|default:'All'}
            </td>
            <td>{$ml->start_date|date_format:$date_format}</td>
            <td>{$ml->complete_date|date_format:$date_format|default:'&nbsp;'}</td>
            <td>{$ml->instance->assigned_name|default:'&nbsp;'}</td>
            <td>{if $ml->status_flag == 'A'}Running{elseif $ml->status_flag == 'S'}Completed{elseif $ml->status_flag == 'N'}Starting{elseif $ml->status_flag == 'F'}Failed{elseif $ml->status_flag == 'P'}Paused{elseif $ml->status_flag == 'T'}Terminated{elseif $ml->status_flag == 'W'}Completed/Terminated{else}Unknown{/if}</td>
        </tr>
        {/foreach}
	</tbody>
    </table>
</div>