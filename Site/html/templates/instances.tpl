{*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Contains class Application that act as main entry point for the application 
	specific logic. This class handle all logic that are the same for all pages
	across all aplication.
 
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
    <table cellspacing="0" cellpadding="0" border="0">
	<thead>
	<tr>
	    <th>
            {assign var="column_name" value="Name"}
            {if $sort_mode == 1}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],1,'asc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],1,'desc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_instances(1,1,'asc'){/php}" title="{$column_name} ">
            {$column_name}</a>
            {/if}
        </th>
        {if $application_mode != 1}
	    <th>
            {assign var="column_name" value="Connect"}
            {$column_name}
        </th>
        {/if}
	    <th>
            {assign var="column_name" value="Host"}
            {if $sort_mode == 11}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],11,'asc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],11,'desc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_instances(1,11,'asc'){/php}" title="{$column_name} ">
            {$column_name}</a>
            {/if}
        </th>
        {if $application_mode == 1}
        <th>
            {assign var="column_name" value="ID"}
            {if $sort_mode == 2}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],2,'asc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],2,'desc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_instances(1,2,'asc'){/php}" title="">
            {$column_name}</a>
            {/if}
        </th>
        <th>
            {assign var="column_name" value="Login ID"}
            {if $sort_mode == 3}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],3,'asc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],3,'desc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_instances(1,3,'asc'){/php}" title="">
            {$column_name}</a>
            {/if}
        </th>
        {/if}
	    <th>
            {assign var="column_name" value="Start Date"}
            {if $sort_mode == 4}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],4,'asc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],4,'desc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_instances(1,4,'asc'){/php}" title="">
            {$column_name}</a>
            {/if}
        </th>
	    <th>
            {assign var="column_name" value="Instance Type"}
            {if $sort_mode == 5}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],5,'asc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],5,'desc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_instances(1,5,'asc'){/php}" title="">
            {$column_name}</a>
            {/if}
        </th>
	    <th>
            {assign var="column_name" value="Dataset"}
            {if $sort_mode == 6}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],6,'asc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],6,'desc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_instances(1,6,'asc'){/php}" title="">
            {$column_name}</a>
            {/if}
        </th>
	    <th>
            {assign var="column_name" value="Image"}
            {if $sort_mode == 7}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],7,'asc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],7,'desc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_instances(1,7,'asc'){/php}" title="">
            {$column_name}</a>
            {/if}
        </th>
	    <th>
            {assign var="column_name" value="Actions"}
            {if $sort_mode == 9}
            {if $sort_order == 'desc'}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],9,'asc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
            {else}
            <a href="{php}echo PageRouter::build_instances($this->_tpl_vars['currentuserpage'],9,'desc'){/php}" title="">
            {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
            {/if}
            {else}
            <a href="{php}echo PageRouter::build_instances(1,9,'desc'){/php}" title="">
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
    {foreach item=i from=$instances}
        {if $i->public_dns == null}
        <tr id="instance_row_{$i->instance_id}" class="booting">
        {else}
        <tr>
        {/if}
            <td>
            <a class="tooltip_handle" href="#" onclick="return false;">
    	        {$i->assigned_name|truncate:20|escape:'html'|stripslashes}
	            <span class="tooltip">
        			{$i->assigned_name|escape:'html'|stripslashes}<br/>
<nobr>
{$i->public_dns|escape:'html'|stripslashes|default:'&nbsp;'}
</nobr>
        		</span>
			</a>            
            </td>
        {if $application_mode != 1}
            <td>
			{if preg_match('/Windows/i', $smarty.server.HTTP_USER_AGENT)}
				{assign var="ssh_title" value="PuTTY"}
				{assign var="x_title" value="XWindows"}
			{else}
				{assign var="ssh_title" value="SSH"}
				{assign var="x_title" value="SSH (with X11 forwarding)"}
			{/if}
            {if $i->status_flag == 'A'}
            <a href="omop://{$i->instance_request->user->internal_id}@{$lab_host}/{$i->assigned_name}" title="{$ssh_title} connect to {$i->assigned_name}"><img src="/images/putty.jpg" alt="Connect" /></a>
			{if !preg_match('/Mac/i', $smarty.server.HTTP_USER_AGENT)}
			<a href="omopf://{$i->instance_request->user->internal_id}@{$lab_host}/{$i->assigned_name}" title="WinSCP connect to {$i->assigned_name}"><img src="/images/WinSCP.png" alt="Transfer files" /></a>
			{/if}
			<a href="omopx://{$i->instance_request->user->internal_id}@{$lab_host}/{$i->assigned_name}" title="{$x_title} connect to {$i->assigned_name}"><img src="/images/xming.jpg" alt="X-Window session" /></a>
            {else}
            <span style="min-width: 54px; float: right;">&nbsp;</span>
            {/if}
            </td>
            {/if}
            {if $i->public_dns == null}
            	<td class="booting">Instance is booting</td>
            {elseif $i->status_flag=='X'}
            	<td class="booting">Instance is preparing</td>
            {else}<td>{$i->public_dns}</td>
            {/if}
            
            {if $application_mode == 1}
            <td>{$i->amazon_instance_id}</td>
            <td><a href="{php}echo PageRouter::build('edit_user',$this->_tpl_vars['i']->instance_request->user_id);{/php}" title="">
            {$i->instance_request->user->login_id}</a>
            </td>
            {/if}
            <td>{$i->start_date|date_format:$date_format}</td>
            <td>{$i->instance_request->instance_size->instance_size_name|default:'&nbsp;'}</td>
            <td>
            <a class="tooltip_handle" href="#" onclick="return false;">
            {$i->instance_request->dataset_types_description|default:'&nbsp;'|truncate:10}
            <span class="tooltip">{$i->instance_request->dataset_types_description|default:'&nbsp;'}</span>
            </a></td>
            <td>{$i->instance_request->software_type->software_type_description|default:'&nbsp;'}</td>
            <td class="center">
            <a href="{php}echo PageRouter::build('instances','terminate_'.$this->_tpl_vars['i']->instance_id);{/php}" 
            onclick="javascript:return confirm('Termination requested for instance {$i->assigned_name}.\r\n\r\nTerminating the instance will result in the loss of all data stored on the instance drive.\r\nPress CANCEL if you want to download or backup data prior to terminating the instance.')" title="Terminate Instance"><img src="/images/trash.gif" alt="Terminate" /></a>
            </td>
        </tr>
        {/foreach}
	</tbody>
    </table>
</div>