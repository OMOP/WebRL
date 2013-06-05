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
{if $current_user != null}
<div id="header">
    <div class="head_block_small" style="padding: 0 175px 0 0; ">
        <h1>{if $application_mode != 1}<a href="{php}echo PageRouter::build('edit_account');{/php}">{$current_user->first_name|escape:'html'|stripslashes}&nbsp;{$current_user->last_name|escape:'html'|stripslashes}</a>{else}{$current_user->first_name|escape:'html'|stripslashes}&nbsp;{$current_user->last_name|escape:'html'|stripslashes}{/if}</h1>
        <p><a href="{php}echo PageRouter::build('login','logout');{/php}" title="log out">log out</a></p>
    </div>
    <div class="head_block_big">
        <a href="{php}echo PageRouter::build('instances');{/php}" title="OBSERVATIONAL MEDICAL OUTCOMES PARTNERSHIP"><img src="/images/logo.gif" width="110" height="27" alt="OBSERVATIONAL MEDICAL OUTCOMES PARTNERSHIP" /></a>
        <h1>Research Lab</h1>
    </div>
    <div class="logo">
        <a href="http://omop.fnih.org" title="OBSERVATIONAL MEDICAL OUTCOMES PARTNERSHIP"><img src="/images/logo_OMOP.gif" width="416" height="33" alt="OBSERVATIONAL MEDICAL OUTCOMES PARTNERSHIP" /></a>
    </div>
</div>
{else}
<div id="header">
    <div class="head_block_big" style="padding: 0 225px 0 0; ">
        <a href="{php}echo PageRouter::build('login');{/php}" title="OBSERVATIONAL MEDICAL OUTCOMES PARTNERSHIP"><img src="/images/logo.gif" width="110" height="27" alt="OBSERVATIONAL MEDICAL OUTCOMES PARTNERSHIP" /></a>
        <h1>Research Lab</h1>
    </div>
    <div class="logo">
        <a href="http://omop.fnih.org" title="OBSERVATIONAL MEDICAL OUTCOMES PARTNERSHIP"><img src="/images/logo_OMOP.gif" width="416" height="33" alt="OBSERVATIONAL MEDICAL OUTCOMES PARTNERSHIP" /></a>
    </div>
</div>
{/if}