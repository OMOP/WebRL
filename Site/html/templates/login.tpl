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
<div class="login">
    <img class="top_left" src="/images/top_left_1.gif" width="11" height="11" alt="" />
    <img class="top_right" src="/images/top_right_1.gif" width="11" height="11" alt="" />
    <img class="bottom_left" src="/images/bottom_left.gif" width="11" height="11" alt="" />
    <img class="bottom_right" src="/images/bottom_right.gif" width="11" height="11" alt="" />
    <h1>{$title}</h1>
    <form action="{php}echo PageRouter::build($this->_tpl_vars['page'],'submit');{/php}" method="post" class="form1">
    <fieldset>
    <legend>Login</legend>
        <input type="hidden" id="idScreenWidth" name="width" value="" />
        <input type="hidden" id="idScreenHeight" name="height" value="" />
        <div>
            <label for="idLogin">Login:</label>
            <input type="text" id="idLogin" name="login" class="text" value="{$login_id}" maxlength="128" />
        </div>
        <div>
            <label for="idPassword">Password:</label>
            <input autocomplete="off" type="password" id="idPassword" name="password" class="text" value="" maxlength="32" />
        </div>
        {if $error_message != ''}
        <div>
            <div>{$error_message}</div>
        </div>
        {/if}
        <div>
            <input disabled="disabled" onmouseover="this.style.backgroundPosition='bottom';" onmouseout="this.style.backgroundPosition='top';" type="submit" name="button_login" id="button_login" class="button_login" value="Login" />
        </div>
        <div>
            <p><a href="{php}echo PageRouter::build('send_password');{/php}" title="Forgot password?">Forgot password?</a></p>
        </div>
    </fieldset>
    </form>
</div>