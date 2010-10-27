<?php
require_once "include/bittorrent.php";
require_once "include/user_functions.php";

  if ($_SERVER["REQUEST_METHOD"] != "POST")
    stderr("Error", "Method");

  dbconn();

  loggedinorreturn();
  
  function ratios($up, $down) {
  if ($down > 0)
    {
      $ratio = number_format($up / $down, 3);
      return "<font color='" . get_ratio_color($ratio) . "'>$ratio</font>";
    }
    else
    {
      if ($up > 0)
        return "Inf.";
      else
        return "---";
    }
    return;
  }

	
mkglobal("captcha");
session_start();
  if(empty($captcha) || $_SESSION['captcha_id'] != strtoupper($captcha)){
      stderr("Error","Wrong word verification. Go back and try again!");
      exit();
}


  $n_pms = isset($_POST["n_pms"]) ? $_POST["n_pms"] : false;
  if ($n_pms)
  {  			                                                      //////  MM  ///
    if ($CURUSER['class'] < UC_MODERATOR)
	  stderr("Error", "Permission denied");

    $msg = trim($_POST["msg"]);
		if (!$msg)
	  	stderr("Error","Please enter something!");
    
    $subject = trim($_POST['subject']);
    
    $sender_id = ($_POST['sender'] == 'system' ? 0 : $CURUSER['id']);

    //$from_is = explode(':', $_POST['pmees']);
    foreach( explode(':', $_POST['pmees']) as $k => $v ) {
        if( ctype_digit($v) )
        $from_is[] = sqlesc($v);
    }
    $from_is = "FROM users u WHERE u.id IN (" . join(',', $from_is) .")";

    $query = "INSERT INTO messages (sender, receiver, added, msg, subject, location, poster) ".
             "SELECT $sender_id, u.id, " . time() . ", " . sqlesc($msg) .
             ", ". sqlesc($subject).", 1, $sender_id " . $from_is;

    mysql_query($query) or sqlerr(__FILE__, __LINE__);
    $n = mysql_affected_rows();

    $comment = $_POST['comment'];
    $snapshot = $_POST['snap'];

    // add a custom text or stats snapshot to comments in profile
    if ($comment || $snapshot)
    {
	    $res = mysql_query("SELECT u.id, u.uploaded, u.downloaded, u.modcomment ".$from_is) or sqlerr(__FILE__, __LINE__);
	    if (mysql_num_rows($res) > 0)
	    {
	      $l = 0;
	      while ($user = mysql_fetch_assoc($res))
	      {
	        unset($new);
	        $new = '';
	        $old = $user['modcomment'];
	        if ($comment)
	          $new .= $comment;
	        if ($snapshot)
	        {
              
              $new .= ($new?"\n":"") .
	            "MMed, " . gmdate("Y-m-d") . ", " .
	            "UL: " . mksize($user['uploaded']) . ", " .
	            "DL: " . mksize($user['downloaded']) . ", " .
	            "r: " . ratios($user['uploaded'],$user['downloaded']) . " - " .
	            ($_POST['sender'] == "system"?"System":$CURUSER['username']);
	        }
	      	$new .= $old?("\n".$old):$old;
		      mysql_query("UPDATE users SET modcomment = " . sqlesc($new) . " WHERE id = " . $user['id'])
		        or sqlerr(__FILE__, __LINE__);
	  	    if (mysql_affected_rows())
	    	    $l++;
	      }
	    }
    }
  }
  else
  {               																							//////  PM  ///
		$receiver = isset($_POST["receiver"]) ? $_POST["receiver"] : false;
		$origmsg = isset($_POST["origmsg"]) ? $_POST["origmsg"] : false;
		$save = isset($_POST["save"]) ? $_POST["save"] : false;
		$returnto = isset($_POST["returnto"]) ? $_POST["returnto"] : '';

	  if (!is_valid_id($receiver) || ($origmsg && !is_valid_id($origmsg)))
	  	stderr("Error","Invalid ID");

	  $msg = trim($_POST["msg"]);
	  if (!$msg)
	    stderr("Error","Please enter something!");

	  $save = ($save == 'yes') ? "yes" : "no";

	  $res = mysql_query("SELECT acceptpms, email, notifs, UNIX_TIMESTAMP(last_access) as la FROM users WHERE id=$receiver") or sqlerr(__FILE__, __LINE__);
	  $user = mysql_fetch_assoc($res);
	  if (!$user)
	    stderr("Error", "No user with ID.");

	  //Make sure recipient wants this message
		if ($CURUSER['class'] < UC_MODERATOR)
		{
    	if ($user["acceptpms"] == "yes")
	    {
	      $res2 = mysql_query("SELECT * FROM blocks WHERE userid=$receiver AND blockid=" . $CURUSER["id"]) or sqlerr(__FILE__, __LINE__);
	      if (mysql_num_rows($res2) == 1)
	        stderr("Refused", "This user has blocked PMs from you.");
	    }
	    elseif ($user["acceptpms"] == "friends")
	    {
	      $res2 = mysql_query("SELECT * FROM friends WHERE userid=$receiver AND friendid=" . $CURUSER["id"]) or sqlerr(__FILE__, __LINE__);
	      if (mysql_num_rows($res2) != 1)
	        stderr("Refused", "This user only accepts PMs from users in his friends list.");
	    }
	    elseif ($user["acceptpms"] == "no")
	      stderr("Refused", "This user does not accept PMs.");
	  }

	  $subject = trim($_POST['subject']);
    
    mysql_query("INSERT INTO messages (poster, sender, receiver, added, msg, subject, saved, location) VALUES(" . $CURUSER["id"] . ", " . $CURUSER["id"] . ", $receiver, " . time() . ", " . sqlesc($msg) . ", " . sqlesc($subject) . ", " . sqlesc($save) . ", 1)") or sqlerr(__FILE__, __LINE__);

	  if (strpos($user['notifs'], '[pm]') !== false)
	  {
	    if (time() - $user["la"] >= 300)
	    {
	    $username = $CURUSER["username"];
$body = <<<EOD
You have received a PM from $username!

You can use the URL below to view the message (you may have to login).

$DEFAULTBASEURL/messages.php

--
$SITENAME
EOD;
	    @mail($user["email"], "You have received a PM from " . $username . "!",
	    	$body, "From: $SITEEMAIL", "-f$SITEEMAIL");
	    }
	  }
	  $delete = isset($_POST["delete"]) ? $_POST["delete"] : '';

	  if ($origmsg)
	  {
      if ($delete == "yes")
      {
	      // Make sure receiver of $origmsg is current user
	      $res = mysql_query("SELECT * FROM messages WHERE id=$origmsg") or sqlerr(__FILE__, __LINE__);
	      if (mysql_num_rows($res) == 1)
	      {
	        $arr = mysql_fetch_assoc($res);
	        if ($arr["receiver"] != $CURUSER["id"])
	          stderr("w00t","This shouldn't happen.");
	        if ($arr["saved"] == "no")
            mysql_query("DELETE FROM messages WHERE id=$origmsg") or sqlerr(__FILE__, __LINE__);
          elseif ($arr["saved"] == "yes")
            mysql_query("UPDATE messages SET location = '0' WHERE id=$origmsg") or sqlerr(__FILE__, __LINE__);
	      }
      }
   	  if (!$returnto)
   	  	$returnto = "messages.php";
	  }

    if ($returnto)
    {
      header("Location: $returnto");
      die;
    }

	 
	} 
	stdhead();
	$l = (isset($l)?$l:'');
	  stdmsg("Succeeded", (($n_pms > 1) ? "$n messages out of $n_pms were" : "Message was").
	    " successfully sent!" . ($l ? " $l profile comment" . (($l>1) ? "s were" : " was") . " updated!" : ""));
	stdfoot();
	exit;
?>