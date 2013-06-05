{*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Contains class Application that act as main entry point for the application 
	specific logic. This class handle all logic that are the same for all pages
	across all aplication.
 
    ©2009 Foundation for the National Institutes of Health (FNIH)
 
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
    {include file="users_menu.tpl"}    
    <table cellspacing="0" cellpadding="0" border="0">
        <thead>
        <tr>
            <th>
                {assign var="column_name" value="Login ID"}
                {if $sort_mode == 1}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],1,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],1,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_view(1,1,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            {if $current_user->organization_id == 0}
            <th>
                {assign var="column_name" value="Organization"}
                {if $sort_mode == 11}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],11,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],11,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_view(1,11,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            {/if}            
            <th>
                {assign var="column_name" value="User Name"}
                {if $sort_mode == 2}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],2,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],2,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_view(1,2,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            <th>
                {assign var="column_name" value="Email"}
                {if $sort_mode == 3}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],3,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],3,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_view(1,3,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            <th>
                {assign var="column_name" value="Active"}
                {if $sort_mode == 4}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],4,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],4,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_view(1,4,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            <th>
            {assign var="column_name" value="Active Instances"}
                {if $sort_mode == 5}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],5,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],5,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_view(1,5,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            <th>
                {assign var="column_name" value="Charged ($)"}
                {if $sort_mode == 6}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],6,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],6,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_view(1,6,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
            <th>
                {assign var="column_name" value="Remaining ($)"}
                {if $sort_mode == 7}
                {if $sort_order == 'desc'}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],7,'asc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_top.gif" width="7" height="4" alt="" /></a>
                {else}
                <a href="{php}echo PageRouter::build_user_view($this->_tpl_vars['currentuserpage'],7,'desc'){/php}" title="">
                {$column_name}&nbsp;<img src="/images/sort_bottom.gif" width="7" height="4" alt="" /></a>
                {/if}
                {else}
                <a href="{php}echo PageRouter::build_user_view(1,7,'asc'){/php}" title="">
                {$column_name}</a>
                {/if}
            </th>
        </tr>
        </thead>
        <tfoot> 
        <tr>
            <th colspan="8">{include file="pager.tpl"}</th>
        </tr>
        <tr>
	    <th colspan="8">
	    <input onmouseover="this.style.backgroundPosition='bottom';" onmouseout="this.style.backgroundPosition='top';" 
	    id="button_new_user" class="button_105" 
	    type="button" value="Add User" onclick="window.location='{php}echo PageRouter::build('create_user');{/php}'" />
	    </th>
	</tr>
        </tfoot>
        <tbody>
        {foreach item=u from=$users}
        <tr>
            <td>
            	<a href="{php}echo PageRouter::build('edit_user',$this->_tpl_vars['u']->user_id);{/php}" title="">
            		{$u->login_id|escape:'html'|stripslashes}
            	</a>
            </td>
            {if $current_user->organization_id == 0}
            <td><a href="{php}echo PageRouter::build('edit_organization',$this->_tpl_vars['u']->organization_id);{/php}" 
            title="Edit Org information">
            {$u->organization->organization_name|escape:'html'|stripslashes}</a></td>
            {/if}
            <td>{$u->last_name|escape:'html'|stripslashes}, {$u->first_name|escape:'html'|stripslashes}</td>
            <td><a href="mailto:{$u->email}">{$u->email|escape:'html'|stripslashes|default:'&nbsp;'}</a></td>
            <td>{if $u->active_flag == 'Y'}Yes{else}No{/if}</td>
            <td class="right"><a href="{php}echo PageRouter::build('user_history',$this->_tpl_vars['u']->user_id.'_'.$this->_tpl_vars['current_user']->organization_id);{/php}" title="">{$u->running_instances_count}</a></td>	
            <td class="right">{if $u->total_charged != null}{$u->total_charged|number_format:0}{else}&nbsp;{/if}</td>	
            <td class="right">{$u->remains_limit|number_format:0}</td>
            {*<td class="center">{if $u->active_flag == 'Y'}<a href="{php}echo PageRouter::build('edit_user','deactivate_'.$this->_tpl_vars['u']->user_id);{/php}">Deactivate</a>{else}<a href="{php}echo PageRouter::build('edit_user','activate_'.$this->_tpl_vars['u']->user_id);{/php}">Activate</a>{/if}</td>*}
        </tr>
        {/foreach}
        </tbody>
    </table>
</div>