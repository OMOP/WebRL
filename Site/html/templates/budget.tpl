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

<div class="list" style="clear: both;">
<script language="javascript" type="text/javascript">
    var page_mode = '{$mode_type}';
</script>
<style type="text/css">
    /* Hacks below are the fixes of standard table */
    #container div.list
    {ldelim}
        border: 0;
        margin-top: 30px;
        width: auto;
    {rdelim}

    #container div.list table {ldelim}
        border-right: 20px solid #FFFFFF;
        width: auto;
    {rdelim}

    #container div.list table thead th.month-group {ldelim}
        border-left: 8px solid white;
    {rdelim}
</style>

<form id="budget-form" action="{php}echo PageRouter::build('budget','show');{/php}" method="post" autocomplete="off">

  <span>From: <input type="text" name="date_start" id="date-start" value="{$date_start}" /></span>
  <span>To: <input type="text" name="date_end" id="date-end" value="{$date_end}" /></span>

  <span><label for="report-type-table"><input type="radio" name="report_type" id="report-type-table" value="table" {if $mode_type == 'table' || !$mode_type}checked="checked" {/if}/> Table</label></span>
  <span><label for="report-type-graph"><input type="radio" name="report_type" id="report-type-graph" value="graph" {if $mode_type == 'graph'}checked="checked" {/if}/> Graph</label></span>

  <input type="submit" value="Filter" class="button_80" id="button_filter"
    onmouseout="this.style.backgroundPosition='top';"
    onmouseover="this.style.backgroundPosition='bottom';"
    style="background-position: center top;">
</form>

{if ($mode == 'show' && $mode_type == "table")}
<table id="budget-table" cellpadding="0" cellspacing="0">
<thead>
  <tr>
    <th>&nbsp;</th>
    <th>&nbsp;</th>
    {foreach item=m from=$tpl_months}
      <th colspan="2" class="month-group">{$m}</th>
    {/foreach}
    <th colspan="2" class="month-group" style="text-align: center">Budget</th>
    <th colspan="2" class="month-group" style="text-align: center">Current</th>
  </tr>
  <tr>
    <th>Organization</th>
    <th>Users/Instances</th>
    {foreach item=m from=$months}
      <th class="month-group">Charged ($)</th>
      <th>Num Instances</th>
    {/foreach}

    <th class="month-group">Limit ($)</th>
    <th>Num Instances</th>

    <th class="month-group">Remaining ($)</th>
    <th>Active Instances</th>
  </tr>
</thead>

{foreach item=o key=k from=$organizations}
{if $k != 'totals'}
<tbody class="org-data">
  <tr>
    <td title="{$o.name}" class="org-name left">
		{if count($o.users) > 0}
        <img class="expand" src="/images/table-plus.png" />
		{/if}
        {*if $k != $PREFIX_ORGANIZATION|cat:"0"}
            <a href="{php}echo PageRouter::build('edit_organization',substr($this->_tpl_vars['k'], strlen($this->_tpl_vars['PREFIX_ORGANIZATION'])));{/php}">{$o.name|escape:'html'|stripslashes|default:'&nbsp;'}</a>
        {else*}
            {$o.name|escape:'html'|stripslashes|default:'&nbsp;'}
        {*/if*}
    </td>
    <td></td>
    {foreach item=m from=$months}
      <td>{$o.totals.$m.charge|number_format:0} </td>
      <td>{$o.totals.$m.instances|number_format:0} </td>
    {/foreach}
    <td>{$o.totals.budget.limit|number_format:0} </td>
    <td>{$o.totals.budget.instances|number_format:0} </td>
    <td{if $o.totals.current.remaining < 0} class="negative"{/if}>{$o.totals.current.remaining|number_format:0} </td>
    <td>{$o.totals.current.instances|number_format:0} </td>
  </tr>
</tbody>
<tbody class="user-data">
  {foreach item=user key=ku from=$o.users}
  {if $ku != 'name'}
    <tr>
	  <td></td>
	  {if $k == 'oidsystem'}
      <td class="left"><a href="/?page=edit_system_instance&action={$ku}">{$user.name}</a></td>
	  {else}
      <td class="left"><a href="/?page=edit_user&action={$ku}">{$user.name}</a></td>
	  {/if}
      {foreach item=mu from=$months}
        <td>{$user.$mu.charge|number_format:0}</td>
        <td>{$user.$mu.instances|number_format:0}</td>
      {/foreach}
      <td>{$user.budget.limit|number_format:0}</td>
      <td>{$user.budget.instances|number_format:0}</td>
      <td{if $user.current.remaining < 0} class="negative"{/if}>{$user.current.remaining|number_format:0}</td>
      <td>{$user.current.instances|number_format:0}</td>
    </tr>
  {/if}
  {/foreach}
</tbody>
{/if}
{/foreach}

<tfoot>
  <tr>
    <td class="left">Total:</td>
    <td>&nbsp;</td>
    {foreach item=m from=$months}
      <td>{$organizations.totals.$m.charge|number_format:0}</td>
      <td>{$organizations.totals.$m.instances|number_format:0}</td>
    {/foreach}
    <td>{$organizations.totals.budget.limit|number_format:0}</td>
    <td>{$organizations.totals.budget.instances|number_format:0}</td>
    <td{if $organizations.totals.current.remaining < 0} class="negative"{/if}>{$organizations.totals.current.remaining|number_format:0}</td>
    <td>{$organizations.totals.current.instances|number_format:0}</td>
  </tr>
</tfoot>

</table>
{/if}

{if ($mode == 'show' && $mode_type == "graph")}
<div id="budget-graph">
    <div id="budget-graph-image" style="width:1100px; height:300px;"></div>
    <script language="javascript" type="text/javascript">
    {$graph_options}
    {$graph_data}
    </script>
</div>
{/if}

</div>