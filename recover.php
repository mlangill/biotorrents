<?php

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
 	
ini_set('session.use_trans_sid', '0');

// Begin the session
session_start();

dbconn();

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
  
    if(empty($_POST['captcha']) || $_SESSION['captcha_id'] != strtoupper($_POST['captcha'])){
      header('Location: recover.php');
     exit();
  }
  $email = trim($_POST["email"]);
  if (!validemail($email))
    stderr("Error", "You must enter an email address");
  $res = mysql_query("SELECT * FROM users WHERE email=" . sqlesc($email) . " LIMIT 1") or sqlerr();
  $arr = mysql_fetch_assoc($res) or stderr("Error", "The email address was not found in the database.\n");

	$sec = mksecret();

  mysql_query("UPDATE users SET editsecret=" . sqlesc($sec) . " WHERE id=" . $arr["id"]) or sqlerr();
  if (!mysql_affected_rows())
	  stderr("Error", "Database error. Please contact an administrator about this.");

  $hash = md5($sec . $email . $arr["passhash"] . $sec);

  $body = <<<EOD
Someone, hopefully you, requested that the password for the account
associated with this email address ($email) be reset.

The request originated from {$_SERVER["REMOTE_ADDR"]}.

If you did not do this ignore this email. Please do not reply.


Should you wish to confirm this request, please follow this link:

$DEFAULTBASEURL/recover.php?id={$arr["id"]}&secret=$hash


After you do this, your password will be reset and emailed back
to you.

--
$SITENAME
EOD;

  @mail($arr["email"], "$SITENAME password reset confirmation", $body, "From: $SITEEMAIL", "-f$SITEEMAIL")
    or stderr("Error", "Unable to send mail. Please contact an administrator about this error.");
  stderr("Success", "A confirmation email has been mailed.\n" .
    " Please allow a few minutes for the mail to arrive.");
}
elseif($_GET)
{
//	if (!preg_match(':^/(\d{1,10})/([\w]{32})/(.+)$:', $_SERVER["PATH_INFO"], $matches))
//	  httperr();

//	$id = 0 + $matches[1];
//	$md5 = $matches[2];

	$id = 0 + $_GET["id"];
  $md5 = $_GET["secret"];

	if (!$id)
	  httperr();

	$res = mysql_query("SELECT username, email, passhash, editsecret FROM users WHERE id = $id");
	$arr = mysql_fetch_assoc($res) or httperr();

  $email = $arr["email"];

	$sec = hash_pad($arr["editsecret"]);
	if (preg_match('/^ *$/s', $sec))
	  httperr();
	if ($md5 != md5($sec . $email . $arr["passhash"] . $sec))
	  httperr();

	// generate new password;
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

  $newpassword = "";
  for ($i = 0; $i < 10; $i++)
    $newpassword .= $chars[mt_rand(0, strlen($chars) - 1)];

 	$sec = mksecret();

  $newpasshash = md5($sec . $newpassword . $sec);

	mysql_query("UPDATE users SET secret=" . sqlesc($sec) . ", editsecret='', passhash=" . sqlesc($newpasshash) . " WHERE id=$id AND editsecret=" . sqlesc($arr["editsecret"]));

	if (!mysql_affected_rows())
		stderr("Error", "Unable to update user data. Please contact an administrator about this error.");

  $body = <<<EOD
As per your request we have generated a new password for your account.

Here is the information we now have on file for this account:

    User name: {$arr["username"]}
    Password:  $newpassword

You may login at $DEFAULTBASEURL/login.php

--
$SITENAME
EOD;
  @mail($email, "$SITENAME account details", $body, "From: $SITEEMAIL", "-f$SITEEMAIL")
    or stderr("Error", "Unable to send mail. Please contact an administrator about this error.");
  stderr("Success", "The new account details have been mailed to <b>$email</b>.\n" .
    "Please allow a few minutes for the mail to arrive.");
}
else
{

#if (isset($_SESSION['captcha_time']))
#(time() - $_SESSION['captcha_time'] < 10) ? exit('NO SPAM! Wait 10 seconds and then refresh page') : NULL;
 	
 	stdhead();
	?>
	<script type="text/javascript" src="captcha/captcha.js"></script>
	
	<h1>Recover lost user name or password</h1>
	<p>Use the form below to have your password reset and your account details mailed back to you.<br />
  (You will have to reply to a confirmation email.)</p>
  
	<form method='post' action='recover.php'>
	<table border='1' cellspacing='0' cellpadding='10'>
	  <tr>
    <td>&nbsp;</td>
    <td>
      <div id="captchaimage">
      <a href="recover.php" onclick="refreshimg(); return false;" title="Click to refresh image">
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

	<tr><td class='rowhead'>Registered email</td>
	<td><input type='text' size='40' name='email' /></td></tr>
	<tr><td colspan='2' align='center'><input type='submit' value='Do it!' class='btn' /></td></tr>
	</table>
	</form>
	<?php
	stdfoot();
}

?>