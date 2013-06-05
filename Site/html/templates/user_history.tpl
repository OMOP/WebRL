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
    <h2>User history</h2>
    <form action="{php}echo PageRouter::build('user_history',$this->_tpl_vars['filter_user_id'].'_'.$this->_tpl_vars['filter_organization_id']);{/php}" method="post" class="form4">
        <fieldset>
        <legend>Status/User</legend>
        <div><label for="idStatus">Status:</label>
            <p><input type="radio" name="status" value="R" class="radio" {if $filter_status == "R"}checked="checked" {/if}/>Running</p>
            <p><input type="radio" name="status" value="P" class="radio" {if $filter_status == "P"}checked="checked" {/if}/>Paused</p>
            <p><input type="radio" name="status" value="S" class="radio" {if $filter_status == "S"}checked="checked" {/if}/>Terminated</p>
            <p><input type="radio" name="status" value="" id="idStatus" class="radio" {if $filter_status == ""}checked="checked" {/if}/>All</p>
        </div>
{if $current_user->organization_id == 0}
        <div>
            <label for="idOrganization">Organization:</label>
            <select id="idOrganization" name="organization_id" style="max-width:150px">
            	<option value="0">--All--</value>
                {foreach item=o from=$organizations}
                    <option value="{$o->organization_id}" {if $filter_organization_id == $o->organization_id}selected="selected"{/if}>{$o->organization_name}</option>
                {/foreach}
            </select>
        </div>
{else}
        <input type="hidden" id="idOrganization" class="text_disable" readonly="readonly" maxlength="50" name="organization_id" value="{$current_user->organizationid}" />
{/if}
        <div><label for="idUser">User:</label>
            <select id="idUser" name="user_id" style="max-width:250px">
            	<option value="0">--All--</value>
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
            <th>
                {assign var="column_name" value="Start Date"}
                {if $sort_mode == 4}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],4,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],4,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], 1, 4,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            <th>
                {assign var="column_name" value="Terminate Date"}
                {if $sort_mode == 8}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],8,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],8,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], 1, 8,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            <th style="width:50px">
                {assign var="column_name" value="User ID"}
                {if $sort_mode == 3}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],3,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],3,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], 1, 3,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            <th style="width:250px">
                {assign var="column_name" value="Instance Name"}
                {if $sort_mode == 1}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],1,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],1,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], 1, 1,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            <th>
                {assign var="column_name" value="Instance Type"}
                {if $sort_mode == 5}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],5,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],5,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], 1, 5,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            <th>
                {assign var="column_name" value="Status"}
                {if $sort_mode == 9}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],9,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],9,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], 1, 9,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            <th>
                {assign var="column_name" value="Dataset"}
                {if $sort_mode == 6}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],6,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],6,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], 1, 6,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            <th>
                {assign var="column_name" value="Image"}
                {if $sort_mode == 7}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],7,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],7,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], 1, 7,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            <th>
                {assign var="column_name" value="Charge ($)"}
                {if $sort_mode == 10}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],10,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], $this->_tpl_vars['currentuserpage'],10,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_history($this->_tpl_vars['filter_organization_id'], $this->_tpl_vars['filter_user_id'], $this->_tpl_vars['filter_status'], 1, 10,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
        </tr>
        </thead>
        <tfoot>
            <tr>
                <th colspan="7">{include file="pager.tpl"}</th>
            </tr>
            <tr>
                <td colspan="5">
                
                </td>
                <th colspan="4">
                
                </th>
            </tr>
        </tfoot>
        <tbody>
        {foreach item=i from=$instances}
        <tr>
            <td>{$i->start_date|date_format:$date_format}</td>
            <td>{if isset($i->terminate_date)}{$i->terminate_date|date_format:$date_format}{else}&nbsp;{/if}</td>
            <td>
            	<a href="{php}echo PageRouter::build('edit_user',$this->_tpl_vars['u']->instance_request->user_id);{/php}">
            		{$i->instance_request->user->login_id|escape:'html'|stripslashes}
            	</a>
            </td>
            <td style="text-align: left;">
            	<a class="tooltip_handle" href="#" onclick="return false;">
            		{$i->assigned_name|escape:'html'|stripslashes|truncate:80:"...":true}
            		<span class="tooltip">
{$i->assigned_name|escape:'html'|stripslashes}<br />
<nobr>{$i->public_dns|escape:'html'|stripslashes|default:'&nbsp;'}</nobr>
            		</span>
        		</a>
			</td>            
            <td>
            	<a class="tooltip_handle" href="#" onclick="return false;">
            		{$i->instance_request->instance_size->instance_size_name|escape:'html'|stripslashes|truncate:25:"...":true}
            		<span class="tooltip">
            		{$i->instance_request->instance_size->instance_size_name|escape:'html'|stripslashes}
            		</span>
        		</a>
        	</td>
            <td>{if $i->status_flag == 'A'}Running{elseif $i->status_flag == 'S'}Terminated{elseif $i->status_flag == 'F'}Failed{elseif $i->status_flag == 'P'}Paused{else}Unknown{/if}</td>
            <td>
            	<a class="tooltip_handle" href="#" onclick="return false;">
            		{$i->instance_request->dataset_types_description|escape:'html'|stripslashes|truncate:12:"...":true}
            		<span class="tooltip">
            		{$i->instance_request->dataset_types_description|escape:'html'|stripslashes}
            		</span>
        		</a>
        	</td>
            <td>{$i->instance_request->software_type->software_type_description|default:'&nbsp;'}</td>
            <td style="text-align: right;">{$i->run_charge|string_format:"%d"}</td>
        </tr>
        {/foreach}
        </tbody>
    </table>
</div>
