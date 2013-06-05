{*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    29 November 2009
 
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
<h2>
{if $page=='user_view'}
Users list
{else}
<a href="/public/user" title="Users list">Users list</a>
{/if}
{if $current_user->organization_id == 0} |
{if $page=='organizations'}
Organizations list
{else}
<a href="{php}echo PageRouter::build('organizations'){/php}" title="Organizations list">Organizations list</a>
{/if}
{/if}
</h2>
