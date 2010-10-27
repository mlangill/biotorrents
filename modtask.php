<?php
require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn(false);

loggedinorreturn();

function puke($text = "w00t")
{
stderr("w00t", $text);
}

if ($CURUSER['class'] < UC_MODERATOR) die();

// Correct call to script
if ((isset($_POST['action'])) && ($_POST['action'] == "edituser"))
    {
    // Set user id
    if (isset($_POST['userid'])) $userid = $_POST['userid'];
    else die();

    // and verify...
    if (!is_valid_id($userid)) stderr("Error", "Bad user ID.");

    // Fetch current user data...
    $res = mysql_query("SELECT * FROM users WHERE id=".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    $user = mysql_fetch_assoc($res) or sqlerr(__FILE__, __LINE__);

    $updateset = array();

    if ((isset($_POST['modcomment'])) && ($modcomment = $_POST['modcomment'])) ;
    else $modcomment = "";

    // Set class

    if ((isset($_POST['class'])) && (($class = $_POST['class']) != $user['class']))
    {
    if (($CURUSER['class'] < UC_SYSOP) && ($user['class'] >= $CURUSER['class'])) die();

    // Notify user
    $what = ($class > $user['class'] ? "promoted" : "demoted");
    $msg = sqlesc("You have been $what to '" . get_user_class_name($class) . "' by ".$CURUSER['username']);
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES(0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);

    $updateset[] = "class = ".sqlesc($class);

    $modcomment = get_date( time(), 'DATE', 1 ) . " - $what to '" . get_user_class_name($class) . "' by $CURUSER[username].\n". $modcomment;
    }

    // Clear Warning - Code not called for setting warning
    if (isset($_POST['warned']) && (($warned = $_POST['warned']) != $user['warned']))
    {
    $updateset[] = "warned = " . sqlesc($warned);
    $updateset[] = "warneduntil = 0";
    if ($warned == 'no')
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Warning removed by " . $CURUSER['username'] . ".\n". $modcomment;
    $msg = sqlesc("Your warning has been removed by " . $CURUSER['username'] . ".");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    }

    // Set warning - Time based
    if (isset($_POST['warnlength']) && ($warnlength = 0 + $_POST['warnlength']))
    {
    unset($warnpm);
    if (isset($_POST['warnpm'])) $warnpm = $_POST['warnpm'];

    if ($warnlength == 255)
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Warned by " . $CURUSER['username'] . ".\nReason: $warnpm\n" . $modcomment;
    $msg = sqlesc("You have received a warning from ".$CURUSER['username'].($warnpm ? "\n\nReason: $warnpm" : ""));
    $updateset[] = "warneduntil = 0";
    }
    else
    {
    $warneduntil = (time() + $warnlength * 604800);
    $dur = $warnlength . " week" . ($warnlength > 1 ? "s" : "");
    $msg = sqlesc("You have received a $dur warning from ".$CURUSER['username'].($warnpm ? "\n\nReason: $warnpm" : ""));
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Warned for $dur by " . $CURUSER['username'] . ".\nReason: $warnpm\n" . $modcomment;
    $updateset[] = "warneduntil = ".$warneduntil;
    }
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    $updateset[] = "warned = 'yes'";
    }

    // Clear donor - Code not called for setting donor
    if (isset($_POST['donor']) && (($donor = $_POST['donor']) != $user['donor']))
    {
    $updateset[] = "donor = " . sqlesc($donor);
    $updateset[] = "warneduntil = 0";
    if ($donor == 'no')
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Donor status removed by ".$CURUSER['username'].".\n". $modcomment;
    $msg = sqlesc("Your donator status has expired.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    }

    // Set donor - Time based
    if ((isset($_POST['donorlength'])) && ($donorlength = 0 + $_POST['donorlength']))
    {
    if ($donorlength == 255)
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Donor status set by " . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("You have received donor status from ".$CURUSER['username']);
    $updateset[] = "donoruntil = 0";
    }
    else
    {
    $donoruntil = (time() + $donorlength * 604800);
    $dur = $donorlength . " week" . ($donorlength > 1 ? "s" : "");
    $msg = sqlesc("You have received donator status for $dur from " . $CURUSER['username']);
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Donator status set for $dur by " . $CURUSER['username']."\n".$modcomment;
    $updateset[] = "donoruntil = ".$donoruntil;
    }
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    $updateset[] = "donor = 'yes'";
    }

    // Enable / Disable
    if ((isset($_POST['enabled'])) && (($enabled = $_POST['enabled']) != $user['enabled']))
    {
    if ($enabled == 'yes')
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Enabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    else
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Disabled by " . $CURUSER['username'] . ".\n" . $modcomment;

    $updateset[] = "enabled = " . sqlesc($enabled);
    }
    /* If your running the forum post enable/disable, uncomment this section
    // Forum Post Enable / Disable
    if ((isset($_POST['forumpost'])) && (($forumpost = $_POST['forumpost']) != $user['forumpost']))
    {
    if ($forumpost == 'yes')
    {
    $modcomment = gmdate("Y-m-d")." - Posting enabled by ".$CURUSER['username'].".\n" . $modcomment;
    $msg = sqlesc("Your Posting rights have been given back by ".$CURUSER['username'].". You can post to forum again.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    else
    {
    $modcomment = gmdate("Y-m-d")." - Posting disabled by ".$CURUSER['username'].".\n" . $modcomment;
    $msg = sqlesc("Your Posting rights have been removed by ".$CURUSER['username'].", Please PM ".$CURUSER['username']." for the reason why.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    $updateset[] = "forumpost = " . sqlesc($forumpost);
    } */

    // Change Custom Title
    if ((isset($_POST['title'])) && (($title = $_POST['title']) != ($curtitle = $user['title'])))
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Custom Title changed to '".$title."' from '".$curtitle."' by " . $CURUSER['username'] . ".\n" . $modcomment;

    $updateset[] = "title = " . sqlesc($title);
    }

    // The following code will place the old passkey in the mod comment and create
    // a new passkey. This is good practice as it allows usersearch to find old
    // passkeys by searching the mod comments of members.

    // Reset Passkey
    if ((isset($_POST['resetpasskey'])) && ($_POST['resetpasskey']))
    {
    $newpasskey = md5($user['username'].time().$user['passhash']);
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Passkey ".sqlesc($user['passkey'])." Reset to ".sqlesc($newpasskey)." by " . $CURUSER['username'] . ".\n" . $modcomment;

    $updateset[] = "passkey=".sqlesc($newpasskey);
    }

    /* This code is for use with the safe mod comment modification. If you have installed
    the safe mod comment mod, then uncomment this section...

    // Add Comment to ModComment
    if ((isset($_POST['addcomment'])) && ($addcomment = trim($_POST['addcomment'])))
    {
    $modcomment = gmdate("Y-m-d") . " - ".$addcomment." - " . $CURUSER['username'] . ".\n" . $modcomment;
    } */

    /* Uncomment the following code if you have the upload mod installed...

    // Set Upload Enable / Disable
    if ((isset($_POST['uploadpos'])) && (($uploadpos = $_POST['uploadpos']) != $user['uploadpos']))
    {
    if ($uploadpos == 'yes')
    {
    $modcomment = gmdate("Y-m-d") . " - Upload enabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("You have been given upload rights by " . $CURUSER['username'] . ". You can now upload torrents.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    elseif ($uploadpos == 'no')
    {
    $modcomment = gmdate("Y-m-d") . " - Upload disabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("Your upload rights have been removed by " . $CURUSER['username'] . ". Please PM ".$CURUSER['username']." for the reason why.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    else
    die(); // Error

    $updateset[] = "uploadpos = " . sqlesc($uploadpos);
    } */

    /* Uncomment the following code if you have the download mod installed...

    // Set Download Enable / Disable
    if ((isset($_POST['downloadpos'])) && (($downloadpos = $_POST['downloadpos']) != $user['downloadpos']))
    {
    if ($downloadpos == 'yes')
    {
    $modcomment = gmdate("Y-m-d") . " - Download enabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("Your download rights have been given back by " . $CURUSER['username'] . ". You can download torrents again.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    elseif ($downloadpos == 'no')
    {
    $modcomment = gmdate("Y-m-d") . " - Download disabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("Your download rights have been removed by " . $CURUSER['username'] . ", Please PM ".$CURUSER['username']." for the reason why.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    else
    die(); // Error

    $updateset[] = "downloadpos = " . sqlesc($downloadpos);
    } */

    // Avatar Changed
    if ((isset($_POST['avatar'])) && (($avatar = $_POST['avatar']) != ($curavatar = $user['avatar'])))
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Avatar changed from ".htmlspecialchars($curavatar)." to ".htmlspecialchars($avatar)." by " . $CURUSER['username'] . ".\n" . $modcomment;

    $updateset[] = "avatar = ".sqlesc($avatar);
    }

    /* Uncomment if you have the First Line Support mod installed...

    // Support
    if ((isset($_POST['support'])) && (($support = $_POST['support']) != $user['support']))
    {
    if ($support == 'yes')
    {
    $modcomment = gmdate("Y-m-d") . " - Promoted to FLS by " . $CURUSER['username'] . ".\n" . $modcomment;
    }
    elseif ($support == 'no')
    {
    $modcomment = gmdate("Y-m-d") . " - Demoted from FLS by " . $CURUSER['username'] . ".\n" . $modcomment;
    }
    else
    die();

    $supportfor = $_POST['supportfor'];

    $updateset[] = "support = " . sqlesc($support);
    $updateset[] = "supportfor = ".sqlesc($supportfor);
    } */

    // Add ModComment to the update set...
    $updateset[] = "modcomment = " . sqlesc($modcomment);

    mysql_query("UPDATE users SET " . implode(", ", $updateset) . " WHERE id=".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);

    $returnto = $_POST["returnto"];
    header("Location: $BASEURL/$returnto");

    die();
    }

puke();

?>