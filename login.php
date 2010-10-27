<?php

require_once "include/bittorrent.php" ;

ini_set('session.use_trans_sid', '0');

// Begin the session
session_start();
#if (isset($_SESSION['captcha_time']))
#(time() - $_SESSION['captcha_time'] < 10) ? exit('NO SPAM! Wait 10 seconds and then refresh page') : NULL;

stdhead("Login");

unset($returnto);
if (!empty($_GET["returnto"])) {
	$returnto = $_GET["returnto"];
	if (!isset($_GET["nowarn"])) {
		print("<h1>Not logged in!</h1>\n");
		print("<p><b>Error:</b> The page you tried to view can only be used when you're logged in.</p>\n");
	}
}

?>
<!--<script type="text/javascript" src="captcha/captcha.js"></script> -->

<form method="post" action="takelogin.php">
<p>Note: You need cookies enabled to log in.</p>
<table border="0" cellpadding="5">
  <tr>
    <td class="rowhead">Username:</td>
    <td align="left"><input type="text" size="40" name="username" /></td>
  </tr>
  <tr>
    <td class="rowhead">Password:</td>
    <td align="left"><input type="password" size="40" name="password" /></td>
  </tr>
<!--<tr><td class='rowhead'>Duration:</td><td align='left'><input type='checkbox' name='logout' value='yes' checked='checked' />Log me out after 15 minutes inactivity</td></tr>-->
  <!--<tr>
    <td>&nbsp;</td>
    <td>
      <div id="captchaimage">
      <a href="login.php" onclick="refreshimg(); return false;" title="Click to refresh image">
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
  </tr>-->
  <tr>
    <td colspan="2" align="center">
      <input type="submit" value="Log in!" class='btn' />
    </td>
  </tr>
</table>
<?php

if (isset($returnto))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlentities($returnto) . "\" />\n");

?>
</form>
<p>Don't have an account? <a href="signup.php">Sign up</a> right now!</p>
<?php

stdfoot();

?>