{*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Template for password recovery email.
 
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
Dear {$user->first_name} {$user->last_name}, 
 
We have created your OMOP RL User Account.
Your Login ID is: {$user->login_id}
Your new Password: {$new_password}
You may login to the Research Lab at the following URL: {$site} 

Your configuration is:
{if $user->admin_flag == 'Y'}
Admin access
{/if}
{if $user->has_svn_access == 'Y'}
SVN Access
{/if}
Charge limit (cash): {$user->user_money}
Maximum number of running instances at one time: {$user->num_instances}
Dataset access: {foreach item=type name=dataset_types from=$user->dataset_types}{$type->dataset_type_description}{if !$smarty.foreach.dataset_types.last},{/if}{/foreach}
Images access: {foreach item=type name=software_types from=$software_types}{$type->software_type_description}{if !$smarty.foreach.software_types.last},{/if}{/foreach}

Best Regards, 
OMOP Support Team