<div class="password">
    <img class="top_left" src="images/top_left.gif" width="11" height="11" alt="" />
    <img class="top_right" src="images/top_right.gif" width="11" height="11" alt="" />
    <img class="bottom_left" src="images/bottom_left.gif" width="11" height="11" alt="" />
    <img class="bottom_right" src="images/bottom_right.gif" width="11" height="11" alt="" />    
    {if $mode == "view"}
    <p>Please enter new password and confirm your input again.</p>
    <form action="{php}echo PageRouter::build('reset','reset');{/php}" method="post" class="form6">
    <fieldset>
    <legend>Reset password</legend>
	    <div>
        <input type="hidden" id="idScreenWidth" name="width" value="" />
        <input type="hidden" id="idScreenHeight" name="height" value="" />
        <input type="hidden" id="user_id" name="user_id" value="{$user_id}" />
        <input type="hidden" id="unique_key" name="unique_key" value="{$unique_key}"/>
		<label for="idPassword">Password:</label>
		<input type="password" id="idPassword" name="password" class="required password text" value="" />
	    </div>
        <div>
		<label for="idPasswordConfirm">Password Again:</label>
		<input type="password" id="idPasswordConfirm" name="confirmpassword" class="required text" value="" />
        <span style="color:black;">Entered passwords should match</span>
	    </div>
        <div>
            <p>
            <input onmouseover="this.style.backgroundPosition='bottom';" onmouseout="this.style.backgroundPosition='top';" type="submit" name="reset" id="reset" class="button_105" value="Reset password" /></p>
        </div>
    </fieldset>
    </form>
    {elseif $mode=="reset"}
    {if $success == true}
    <p>You are succesfully reset your pasword. Please to to home page and <a href="{php}echo PageRouter::build('login');{/php}" title"login">login</a> to web-site.</p>
    {else}
    <p>
        <b>Your request for change password is expired or mailformed.</b><br />


    {/if}
    {/if}
<div class="clear"></div>
</div>
