<?php
require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn(false);
loggedinorreturn();

?>
<script type="text/javascript" src="captcha/captcha.js"></script>
<?php
// Standard Administrative PM Replies
$pm_std_reply[1] = "Read the bloody [url=".$BASEURL."/faq.php]FAQ[/url] and stop bothering me!";
$pm_std_reply[2] = "Die! Die! Die!";

// Standard Administrative PMs
$pm_template['1'] = array("Ratio warning","Hi,\n
You may have noticed, if you have visited the forum, that TB is disabling the accounts of all users with low share ratios.\n
I am sorry to say that your ratio is a little too low to be acceptable.\n
If you would like your account to remain open, you must ensure that your ratio increases dramatically in the next day or two, to get as close to 1.0 as possible.\n
I am sure that you will appreciate the importance of sharing your downloads.
You may PM any Moderator, if you believe that you are being treated unfairly.\n
Thank you for your cooperation.");
$pm_template['2'] = array("Avatar warning", "Hi,\n
You may not be aware that there are new guidelines on avatar sizes in the [url=".$BASEURL."/rules.php]rules[/url], in particular \"Resize
your images to a width of 150 px and a size of [b]no more than 150 KB[/b].\"\n
I'm sorry to say your avatar doesn't conform to them. Please change it as soon as possible.\n
We understand this may be an inconvenience to some users but feel it is in the community's best interest.\n
Thanks for the cooperation.");

// Standard Administrative MMs
$mm_template['1'] = $pm_template['1'];
$mm_template['2'] = array("Downtime warning","We'll be down for a few hours");
$mm_template['3'] = array("Change warning","The tracker has been updated. Read
the forums for details.");

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{						          ////////  MM  //
	if ($CURUSER['class'] < UC_MODERATOR)
		stderr("Error", "Permission denied");

  $n_pms = htmlentities($_POST['n_pms']);
  $pmees = htmlentities($_POST['pmees']);
  $auto = isset($_POST['auto']) ? $_POST['auto'] : FALSE;

  if ($auto)
  	$body=$mm_template[$auto][1];

  stdhead("Send message", false);
	?>



  <table class='main' width='750' border='0' cellspacing='0' cellpadding='0'>
	<tr><td class='embedded'><div align='center'>
	<h1>Mass Message to <?php echo $n_pms?> user<?php echo ($n_pms>1?"s":"")?>!</h1>
	<form method='post' action='takemessage.php'>
	<?php if ($_SERVER["HTTP_REFERER"]) { ?>
	<input type='hidden' name='returnto' value='<?php echo $_SERVER["HTTP_REFERER"]?>' />
	<?php } ?>
	<table border='1' cellspacing='0' cellpadding='5'>
	<tr>
  <td colspan="2"><b>Subject:&nbsp;&nbsp;</b>
  <input name="subject" type="text" size="76" /></td>
  </tr>
	<tr><td colspan="2"><div align="center">
	<textarea name='msg' cols='80' rows='15'><?php echo isset($body) ? htmlentities($body, ENT_QUOTES) : ''?></textarea>
	</div></td></tr>
	<tr><td colspan="2"><div align="center"><b>Comment:&nbsp;&nbsp;</b>
  <input name="comment" type="text" size="70" />
	</div></td></tr>
  <tr><td><div align="center"><b>From:&nbsp;&nbsp;</b>
	<?php echo $CURUSER['username']?>
	<input name="sender" type="radio" value="self" checked='checked' />
	&nbsp; System
	<input name="sender" type="radio" value="system" />
	</div></td>
  <td><div align="center"><b>Take snapshot:</b>&nbsp;<input name="snap" type="checkbox" value="1" />
  </div></td></tr>
	<tr><td colspan="2" align='center'><input type='submit' value="Send it!" class='btn' />
	</td></tr></table>
	<input type='hidden' name='pmees' value="<?php echo $pmees?>" />
	<input type='hidden' name='n_pms' value='<?php echo $n_pms?>' />
	</form><br /><br />
	<form method='post' action='sendmessage.php'>
	<table border='1' cellspacing='0' cellpadding='5'>
	<tr><td>
	<b>Templates:</b>
	<select name="auto">
	<?php
	for ($i = 1; $i <= count($mm_template); $i++)	{
		echo "<option value='$i' ".($auto == $i?"selected='selected'":"").
    		">".$mm_template[$i][0]."</option>\n";}
  ?>
	</select>
	<input type='submit' value="Use" class='btn' />
	</td></tr></table>
	<input type='hidden' name='pmees' value="<?php echo $pmees?>" />
	<input type='hidden' name='n_pms' value='<?php echo $n_pms?>' />
	</form></div></td></tr></table>
  <?php
} else {                                                        ////////  PM  //
	$receiver = 0+$_GET["receiver"];
	if (!is_valid_id($receiver))
	  die;

	$replyto = isset($_GET["replyto"]) ? (int)$_GET["replyto"] : 0;
	if ($replyto && !is_valid_id($replyto))
	  die;

	$auto = isset($_GET["auto"]) ? $_GET["auto"] : false;
	$std = isset($_GET["std"]) ? $_GET["std"] : false;

	if (($auto || $std ) && $CURUSER['class'] < UC_MODERATOR)
	  die("Permission denied.");

	$res = mysql_query("SELECT * FROM users WHERE id=$receiver") or die(mysql_error());
	$user = mysql_fetch_assoc($res);
	if (!$user)
	  die("No user with that ID.");

  if ($auto)
 		$body = $pm_std_reply[$auto];
  if ($std)
		$body = $pm_template[$std][1];

	if ($replyto)
	{
	  $res = mysql_query("SELECT * FROM messages WHERE id=$replyto") or sqlerr();
	  $msga = mysql_fetch_assoc($res);
	  if ($msga['receiver'] != $CURUSER['id'])
	    die;
	  $res = mysql_query("SELECT username FROM users WHERE id=" . $msga['sender']) or sqlerr();
	  $usra = mysql_fetch_assoc($res);
	  if(!isset($body)){
		$body='';
		}
	  $body .= "\n\n\n-------- $usra[username] wrote: --------\n{$msga['msg']}\n";
	  $subject = "Re: " . htmlspecialchars($msga['subject']);
	}
	stdhead("Send message", false);
	?>
	<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>
	<div align='center'>
	<h1>Message to <a href='userdetails.php?id=<?php echo $receiver?>'><?php echo $user["username"]?></a></h1>
	<form method='post' action='takemessage.php'>
	<?php if (isset($_GET["returnto"]) || isset($_SERVER["HTTP_REFERER"])) { ?>
	<input type='hidden' name='returnto' value='<?php echo isset($_GET["returnto"]) ? $_GET["returnto"] : $_SERVER["HTTP_REFERER"]?>' />
	<?php } ?>
	<table border='1' cellspacing='0' cellpadding='5'>
	<tr>
<td colspan="2"><b>Subject:&nbsp;&nbsp;</b>
  <input name="subject" type="text" size="76" value="<?php echo isset($subject) ? htmlentities($subject, ENT_QUOTES) : ''?>" /></td>
</tr>
	<tr><td<?php echo $replyto?" colspan=2":""?>><textarea name='msg' cols='80' rows='15'><?php echo isset($body) ? htmlspecialchars($body) : ''?></textarea></td></tr>
	<tr>
	<?php if ($replyto) { ?>
	<td align='center'><input type='checkbox' name='delete' value='yes' <?php echo $CURUSER['deletepms'] == 'yes'?"checked='checked'":""?> />Delete message you are replying to
	<input type='hidden' name='origmsg' value='<?php echo $replyto?>' /></td>
	<?php } ?>
	<td align='center'><input type='checkbox' name='save' value='yes' <?php echo $CURUSER['savepms'] == 'yes'?"checked='checked'":""?> />Save message to Sentbox</td></tr>
<tr><td align='center'>

<div id="captchaimage">
      <a href="" onclick="refreshimg(); return false;" title="Click to refresh image">
      <img class="cimage" src="captcha/GD_Security_image.php?<?php echo time(); ?>" alt="Captcha image" />
      </a>
      </div>

        Word Verification: <input type="text" maxlength="6" name="captcha" id="captcha" onblur="check(); return false;"/>

</td></tr>
	<tr><td<?php echo $replyto?" colspan='2'":""?> align='center'><input type='submit' value="Send it!" class='btn' /></td></tr>
	</table>
	<input type='hidden' name='receiver' value='<?php echo $receiver?>' />
	</form>
	<!--
  <?php
  if ($CURUSER['class'] >= UC_MODERATOR)
  {
  ?>
  	<br /><br />
  	<form method='get' action='sendmessage.php'>
	  <table border='1' cellspacing='0' cellpadding='5'>
	  <tr><td>
	  <b>PM Templates:</b>
	  <select name="std"><?php
	  for ($i = 1; $i <= count($pm_template); $i++)
	  {
	    echo "<option value='$i' ".($std == $i?"selected='selected'":"").
	      ">".$pm_template[$i][0]."</option>\n";
	  }?>
	  </select>
		<?php if (isset($_SERVER["HTTP_REFERER"])) { ?>
		<input type='hidden' name='returnto' value='<?php echo $_GET["returnto"]?$_GET["returnto"]:$_SERVER["HTTP_REFERER"]?>' />
    <?php } ?>
  	<input type='hidden' name='receiver' value='<?php echo $receiver?>' />
		<input type='hidden' name='replyto' value='<?php echo $replyto?>' />
	  <input type='submit' value="Use" class='btn' />
	  </td></tr></table></form>
	<?php
  }
	?>
	-->
 	</div></td></tr></table>
	<?php
}
stdfoot();
?>