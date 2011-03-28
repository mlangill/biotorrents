<?php

require_once "include/bittorrent.php";
require_once ROOT_PATH."/cache/timezones.php";

dbconn();

ini_set('session.use_trans_sid', '0');

// Begin the session
session_start();
#if (isset($_SESSION['captcha_time']))
#(time() - $_SESSION['captcha_time'] < 10) ? exit('NO SPAM! Wait 10 seconds and then refresh page') : NULL;

$res = mysql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_row($res);
if ($arr[0] >= $maxusers)
	stderr("Sorry", "The current user account limit (" . number_format($maxusers) . ") has been reached. Inactive accounts are pruned all the time, please check back again later...");

// TIMEZONE STUFF
 		$offset = (string)$CONFIG_INFO['time_offset'];
 		
 		$time_select = "<select name='user_timezone'>";
 		
 		foreach( $lang as $off => $words )
 		{
 			if ( preg_match("/^time_(-?[\d\.]+)$/", $off, $match))
 			{
				$time_select .= $match[1] == $offset ? "<option value='{$match[1]}' selected='selected'>$words</option>\n" : "<option value='{$match[1]}'>$words</option>\n";
 			}
 		}
 		
 		$time_select .= "</select>";
// TIMEZONE END
 		
stdhead("Signup");

?>


<script type="text/javascript" src="captcha/captcha.js"></script>

<p>Note: You need cookies enabled to sign up or log in.</p>

<form method="post" action="takesignup.php">
<table border="1" cellspacing="0" cellpadding="10">
<tr>
    <td align="right" class="heading">Desired username:</td>
    <td align="left">
        <input type="text" size="40" name="wantusername" />
    </td>
</tr>
<tr>
    <td align="right" class="heading">Pick a password:</td>
    <td align="left">
        <input type="password" size="40" name="wantpassword" />
        <table width="250" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td class="embedded">
                    <font class="small">Please choose a password 6 digits or longer.</font>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr>
    <td align="right" class="heading">Enter password again:</td>
    <td align="left">
        <input type="password" size="40" name="passagain" />
    </td>
</tr>
<tr valign="top">
    <td align="right" class="heading">Email address:</td>
    <td align="left">
        <input type="text" size="40" name="email" />
        <table width="250" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td class="embedded">
                    <font class="small">The email address must be valid.
You will receive a confirmation email which you need to respond to. The email address won't be publicly shown anywhere.</font>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr><td align="right" class="heading">Timezone</td><td align="left"><?php echo $time_select; ?></td></tr>
  <tr>
    <td>&nbsp;</td>
    <td>
      <div id="captchaimage">
      <a href="signup.php" onclick="refreshimg(); return false;" title="Click to refresh image">
      <img class="cimage" src="captcha/GD_Security_image.php?<?php echo time(); ?>" alt="Captcha image" />
      </a>
      </div>
     </td>
  </tr>
  <tr>
      <td class="rowhead">PIN:</td>
      <td>
        <input type="text" maxlength="6" name="captcha" id="captcha" onblur="check(); return false;"/>
      </td>
  </tr>
<!--<tr><td align="right" class="heading"></td><td align="left"><input type="checkbox" name='rulesverify' value='yes' /> I have read the site rules page.<br />
<input type='checkbox' name='faqverify' value='yes' /> I agree to read the FAQ before asking questions.<br />
<input type='checkbox' name='ageverify' value='yes' /> I am at least 13 years old.</td></tr>-->

<tr><td colspan="2" align="center"><input type='submit' value="Sign up! (PRESS ONLY ONCE)" style='height: 25px' /></td></tr> 
</table>
</form>
<?php

stdfoot();

?>
