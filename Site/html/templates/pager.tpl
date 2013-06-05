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
{assign var=prevpage value=`$currentuserpage-1`}
{assign var=nextpage value=`$currentuserpage+1`}
{if count($pagerdata) > 1}

{if $currentuserpage != 1}
<a href="{php}echo PageRouter::build($this->_tpl_vars['page'],$this->_tpl_vars['prefix'].'1'.$this->_tpl_vars['postfix']);{/php}" title="First">First</a>
<a href="{php}echo PageRouter::build($this->_tpl_vars['page'],$this->_tpl_vars['prefix'].$this->_tpl_vars['prevpage'].''.$this->_tpl_vars['postfix']);{/php}" title="Prev">&lt;&lt;</a>
{/if}
{if $currentuserpage - 5 > 1}
&nbsp;&nbsp;&nbsp;...
{/if}
{foreach name=pager item=i from=$pagerdata}
{if $smarty.foreach.pager.iteration != $currentuserpage}
{if $smarty.foreach.pager.iteration >= $currentuserpage - 5 && $smarty.foreach.pager.iteration <= $currentuserpage + 5}
<a href="{php}echo PageRouter::build($this->_tpl_vars['page'],$this->_tpl_vars['prefix'].$this->_foreach['pager']['iteration'].$this->_tpl_vars['postfix']);{/php}" title="">{$smarty.foreach.pager.iteration}</a>
{/if}
{else}
<span>[{$smarty.foreach.pager.iteration}]</span>
{/if}
{/foreach}
{if $currentuserpage + 5 < count($pagerdata) }
&nbsp;&nbsp;&nbsp;...
{/if}
{if $currentuserpage != count($pagerdata)}
<a href="{php}echo PageRouter::build($this->_tpl_vars['page'],$this->_tpl_vars['prefix'].$this->_tpl_vars['nextpage'].$this->_tpl_vars['postfix']);{/php}" title="Next">&gt;&gt;</a>
<a href="{php}echo PageRouter::build($this->_tpl_vars['page'],$this->_tpl_vars['prefix'].count($this->_tpl_vars['pagerdata']).$this->_tpl_vars['postfix']);{/php}" title="Last">Last</a>
{/if}

{/if}
