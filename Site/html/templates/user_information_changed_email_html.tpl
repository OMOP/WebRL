{*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Template for password recovery email in plain text.
 
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
<pre style="font-size: medium;">
Dear {$user->first_name} {$user->last_name}, 
 
We have received request to reset your password.
Login ID is: {$user->login_id}
New Password: {$new_password}

Best Regards, 
OMOP Support Team</pre>