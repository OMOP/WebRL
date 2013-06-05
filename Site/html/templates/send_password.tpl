<div class="password">
    <img class="top_left" src="images/top_left.gif" width="11" height="11" alt="" />
    <img class="top_right" src="images/top_right.gif" width="11" height="11" alt="" />
    <img class="bottom_left" src="images/bottom_left.gif" width="11" height="11" alt="" />
    <img class="bottom_right" src="images/bottom_right.gif" width="11" height="11" alt="" />
    {if $mode == "view"}
    <p>Please enter your email. We will send you your login and password.</p>
    <form action="{php}echo PageRouter::build('send_password','recovery');{/php}" method="post" class="form6">
    <fieldset>
    <legend>Send password</legend>
        <div>
            <label for="idemail">Email:</label>
            <input type="text" id="idemail" name="email" class="text" value="" />
        </div>
        <div>
            <p>
            <input onmouseover="this.style.backgroundPosition='bottom';" onmouseout="this.style.backgroundPosition='top';" type="button" name="button_cancel" id="button_cancel" class="button_90" value="Cancel" 
   
    onclick="window.location='{php}echo PageRouter::build('login');{/php}'" 
    />
            <input onmouseover="this.style.backgroundPosition='bottom';" onmouseout="this.style.backgroundPosition='top';" type="submit" name="password" id="password" class="button_105" value="Send password" /></p>
        </div>
    </fieldset>
    </form>
    {elseif $mode=="recovery"}
    {if $success == true}
    <p>We have emailed your login ID and the new password.  If you do not receive this email, please check trash mail folder.
    <a href="{php}echo PageRouter::build('login');{/php}" title"login">Login</a></p>
    {else}
    <p>
        <b>Based on the information you entered, we were unable to locate your user account.</b><br />
        Please make sure you entered your email properly, or click {$support_mail_link} to <b>contact Administrator</b>.

    {/if}
    {/if}
<div class="clear"></div>
</div>
