<?php

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/pager_functions.php";

// 0 - No debug; 1 - Show and run SQL query; 2 - Show SQL query only
$DEBUG_MODE = 0;

dbconn();
loggedinorreturn();

if ($CURUSER['class'] < UC_MODERATOR)
	stderr("Error", "Permission denied.");

function is_set_not_empty($param) {
  if(isset($_GET[$param]) && !empty($_GET[$param]))
    return TRUE;
  else
    return FALSE;
}

stdhead("Administrative User Search");


echo "<h1>Administrative User Search</h1>\n";
$where_is = '';
$join_is = '';
$q = '';
$comment_is = '';
$comments_exc = '';
$email_is = '';
if (isset($_GET['h']))
{
	echo "<table border='0' align='center'><tr><td class='embedded' bgcolor='#F5F4EA'><div align='left'>\n
	Fields left blank will be ignored;\n
	Wildcards * and ? may be used in Name, Email and Comments, as well as multiple values\n
	separated by spaces (e.g. 'wyz Max*' in Name will list both users named\n
	'wyz' and those whose names start by 'Max'. Similarly  '~' can be used for\n
	negation, e.g. '~alfiest' in comments will restrict the search to users\n
	that do not have 'alfiest' in their comments).<br /><br />\n
    The Ratio field accepts 'Inf' and '---' besides the usual numeric values.<br /><br />\n
	The subnet mask may be entered either in dotted decimal or CIDR notation\n
	(e.g. 255.255.255.0 is the same as /24).<br /><br />\n
    Uploaded and Downloaded should be entered in GB.<br /><br />\n
	For search parameters with multiple text fields the second will be\n
	ignored unless relevant for the type of search chosen. <br /><br />\n
	'Active only' restricts the search to users currently leeching or seeding,\n
	'Disabled IPs' to those whose IPs also show up in disabled accounts.<br /><br />\n
	The 'p' columns in the results show partial stats, that is, those\n
	of the torrents in progress. <br /><br />\n
	The History column lists the number of forum posts and torrent comments,\n
	respectively, as well as linking to the history page.\n
	</div></td></tr></table><br /><br />\n";
}
else
{
	echo "<p align='center'>(<a href='usersearch.php?h=1'>Instructions</a>)";
	echo "&nbsp;-&nbsp;(<a href='usersearch.php'>Reset</a>)</p>\n";
}

$highlight = " bgcolor='lightgrey'";

?>

<form method='get' action='usersearch.php'>
<table border="1" cellspacing="0" cellpadding="5">
<tr>

  <td valign="middle" class='rowhead'>Name:</td>
  <td<?php echo (isset($_GET['n'])&&!empty($_GET['n']))?$highlight:""?>><input name="n" type="text" value="<?php echo isset($_GET['n'])?htmlentities($_GET['n']):""?>" size='25' /></td>

  <td valign="middle" class='rowhead'>Ratio:</td>
  <td<?php echo (isset($_GET['r'])&&!empty($_GET['r']))?$highlight:""?>><select name="rt">
    <?php
	$options = array("equal","above","below","between");
	for ($i = 0; $i < count($options); $i++){
	    echo "<option value='$i' ".(((isset($_GET['rt'])?$_GET['rt']:"3")=="$i")?"selected='selected'":"").">".$options[$i]."</option>\n";
	}
	?>
    </select>
    <input name="r" type="text" value="<?php echo isset($_GET['r'])?$_GET['r']:''?>" size="5" maxlength="4" />
    <input name="r2" type="text" value="<?php echo isset($_GET['r2'])?$_GET['r2']:''?>" size="5" maxlength="4" /></td>

  <td valign="middle" class='rowhead'>Member status:</td>
  <td<?php echo (isset($_GET['st'])&&!empty($_GET['st']))?$highlight:""?>><select name="st">
    <?
	$options = array("(any)","confirmed","pending");
	for ($i = 0; $i < count($options); $i++){
	    echo "<option value='$i' ".(((isset($_GET['st'])?$_GET['st']:"0")=="$i")?"selected='selected'":"").">".$options[$i]."</option>\n";
	}
    ?>
    </select></td></tr>
<tr><td valign="middle" class='rowhead'>Email:</td>
  <td<?php echo (isset($_GET['em'])&&!empty($_GET['em']))?$highlight:""?>><input name="em" type="text" value="<?php echo isset($_GET['em'])?$_GET['em']:''?>" size="25" /></td>
  <td valign="middle" class='rowhead'>IP:</td>
  <td<?php echo (isset($_GET['ip'])&&!empty($_GET['ip']))?$highlight:""?>><input name="ip" type="text" value="<?php echo isset($_GET['ip'])?$_GET['ip']:''?>" maxlength="17" /></td>

  <td valign="middle" class='rowhead'>Account status:</td>
  <td<?php echo (isset($_GET['as'])&&!empty($_GET['as']))?$highlight:""?>><select name="as">
    <?php
    $options = array("(any)","enabled","disabled");
    for ($i = 0; $i < count($options); $i++){
      echo "<option value='$i' ".(((isset($_GET['as'])?$_GET['as']:"0")=="$i")?"selected='selected'":"").">".$options[$i]."</option>\n";
    }
    ?>
    </select></td></tr>
<tr>
  <td valign="middle" class='rowhead'>Comment:</td>
  <td<?php echo (isset($_GET['co'])&&!empty($_GET['co']))?$highlight:""?>><input name="co" type="text" value="<?php echo isset($_GET['co'])?$_GET['co']:""?>" size="25" /></td>
  <td valign="middle" class='rowhead'>Mask:</td>
  <td<?php echo (isset($_GET['ma'])&&!empty($_GET['ma']))?$highlight:""?>><input name="ma" type="text" value="<?php echo isset($_GET['ma'])?$_GET['ma']:""?>" maxlength="17" /></td>
  <td valign="middle" class='rowhead'>Class:</td>
  <td<?php echo (isset($_GET['c']) && !empty($_GET['c']))?$highlight:""?>><select name="c"><option value='1'>(any)</option>
  <?php
  $class = isset($_GET['c']) ? (int)$_GET['c'] : '';
  if (!is_valid_id($class))
  	$class = '';
  for ($i = 2;;++$i) {
		if ($c = get_user_class_name($i-2))
       	 print("<option value='" . $i . "'".((isset($class)?$class:0) == $i? " selected='selected'" : "") . ">$c</option>\n");
	  else
	   	break;
	}
	?>
    </select></td></tr>
<tr>

    <td valign="middle" class='rowhead'>Joined:</td>

  <td<?php echo (isset($_GET['d'])&&!empty($_GET['d']))?$highlight:""?>><select name="dt">
    <?php
	$options = array("on","before","after","between");
	for ($i = 0; $i < count($options); $i++){
	  echo "<option value='$i' ".(((isset($_GET['dt'])?$_GET['dt']:"0")=="$i")?"selected='selected'":"").">".$options[$i]."</option>\n";
	}
    ?>
    </select>

    <input name="d" type="text" value="<?php echo isset($_GET['d'])?$_GET['d']:''?>" size="12" maxlength="10" />

    <input name="d2" type="text" value="<?php echo isset($_GET['d2'])?$_GET['d2']:''?>" size="12" maxlength="10" /></td>


  <td valign="middle" class='rowhead'>Uploaded:</td>

  <td<?php echo (isset($_GET['ult'])&&!empty($_GET['ult']))?$highlight:""?>><select name="ult" id="ult">
    <?php
    $options = array("equal","above","below","between");
    for ($i = 0; $i < count($options); $i++){
  	  echo "<option value='$i' ".(((isset($_GET['ult'])?$_GET['ult']:"0")=="$i")?"selected='selected'":"").">".$options[$i]."</option>\n";
    }
    ?>
    </select>

    <input name="ul" type="text" id="ul" size="8" maxlength="7" value="<?php echo isset($_GET['ul'])?$_GET['ul']:''?>" />

    <input name="ul2" type="text" id="ul2" size="8" maxlength="7" value="<?php echo isset($_GET['ul2'])?$_GET['ul2']:''?>" /></td>
  <td valign="middle" class="rowhead">Donor:</td>

  <td<?php echo (isset($_GET['do'])&&!empty($_GET['do']))?$highlight:""?>><select name="do">
    <?php
    $options = array("(any)","Yes","No");
	for ($i = 0; $i < count($options); $i++){
	  echo "<option value='$i' ".(((isset($_GET['do'])?$_GET['do']:"0")=="$i")?"selected='selected'":"").">".$options[$i]."</option>\n";
    }
    ?>
	</select></td></tr>
<tr>

<td valign="middle" class='rowhead'>Last seen:</td>

  <td <?php echo (isset($_GET['ls'])&&!empty($_GET['ls']))?$highlight:""?>><select name="lst">
  <?php
  $options = array("on","before","after","between");
  for ($i = 0; $i < count($options); $i++){
    echo "<option value='$i' ".(((isset($_GET['lst'])?$_GET['lst']:"0")=="$i")?"selected='selected'":"").">".$options[$i]."</option>\n";
  }
  ?>
  </select>

  <input name="ls" type="text" value="<?php echo isset($_GET['ls'])?$_GET['ls']:''?>" size="12" maxlength="10" />

  <input name="ls2" type="text" value="<?php echo isset($_GET['ls2'])?$_GET['ls2']:''?>" size="12" maxlength="10" /></td>
	  <td valign="middle" class='rowhead'>Downloaded:</td>

  <td<?php echo (isset($_GET['dl'])&&!empty($_GET['dl']))?$highlight:""?>><select name="dlt" id="dlt">
  <?php
	$options = array("equal","above","below","between");
	for ($i = 0; $i < count($options); $i++){
	  echo "<option value='$i' ".(((isset($_GET['dlt'])?$_GET['dlt']:"0")=="$i")?"selected='selected'":"").">".$options[$i]."</option>\n";
	}
	?>
    </select>

    <input name="dl" type="text" id="dl" size="8" maxlength="7" value="<?php echo isset($_GET['dl'])?$_GET['dl']:''?>" />

    <input name="dl2" type="text" id="dl2" size="8" maxlength="7" value="<?php echo isset($_GET['dl2'])?$_GET['dl2']:''?>" /></td>

	<td valign="middle" class='rowhead'>Warned:</td>

	<td<?php echo (isset($_GET['w'])&&!empty($_GET['w']))?$highlight:""?>><select name="w">
  <?php
  $options = array("(any)","Yes","No");
	for ($i = 0; $i < count($options); $i++){
		echo "<option value='$i' ".(((isset($_GET['w'])?$_GET['w']:"0")=="$i")?"selected='selected'":"").">".$options[$i]."</option>\n";
  }
  ?>
	</select></td></tr>

<tr><td class="rowhead"></td><td></td>
  <td valign="middle" class='rowhead'>Active only:</td>
	<td<?php echo (isset($_GET['ac'])&&!empty($_GET['ac']))?$highlight:""?>><input name="ac" type="checkbox" value="1" <?php echo (isset($_GET['ac']))?"checked='checked'":"" ?> /></td>
  <td valign="middle" class='rowhead'>Disabled IP: </td>
  <td<?php echo (isset($_GET['dip'])&&!empty($_GET['dip']))?$highlight:""?>><input name="dip" type="checkbox" value="1" <?php echo (isset($_GET['dip']))?"checked='checked'":"" ?> /></td>
  </tr>
<tr><td colspan="6" align='center'><input name="submit" type='submit' class='btn' /></td></tr>
</table>
<br /><br />
</form>

<?php

// Validates date in the form [yy]yy-mm-dd;
// Returns date if valid, 0 otherwise.
function mkdate($date){
  if (strpos($date,'-'))
  	$a = explode('-', $date);
  elseif (strpos($date,'/'))
  	$a = explode('/', $date);
  else
  	return 0;
  for ($i=0;$i<3;$i++)
  	if (!is_numeric($a[$i]))
    	return 0;
    if (checkdate($a[1], $a[2], $a[0]))
    	return  date ("Y-m-d", mktime (0,0,0,$a[1],$a[2],$a[0]));
    else
			return 0;
}

// ratio as a string
function ratios($up,$down, $color = True)
{
	if ($down > 0)
	{
		$r = number_format($up / $down, 2);
    if ($color)
			$r = "<font color='".get_ratio_color($r)."'>$r</font>";
	}
	else
		if ($up > 0)
	  	$r = "Inf.";
	  else
	  	$r = "---";
	return $r;
}

// checks for the usual wildcards *, ? plus mySQL ones
function haswildcard($text){
	if (strpos($text,'*') === False && strpos($text,'?') === False
			&& strpos($text,'%') === False && strpos($text,'_') === False)
  	return False;
  else
  	return True;
}

///////////////////////////////////////////////////////////////////////////////

if (count($_GET) > 0 && !isset($_GET['h']))
{
	// name
  $names = isset($_GET['h']) ? explode(' ',trim($_GET['n'])) : array(0=>'');
  if ($names[0] !== "")
  {
		foreach($names as $name)
		{
	  	if (substr($name,0,1) == '~')
	  	{
      	if ($name == '~') continue;
   	    $names_exc[] = substr($name,1);
      }
	    else
	    	$names_inc[] = $name;
	  }

    if (is_array($names_inc))
    {
	  	$where_is .= !empty($where_is)?" AND (":"(";
	    foreach($names_inc as $name)
	    {
      	if (!haswildcard($name))
	        $name_is .= (isset($name_is)?" OR ":"")."u.username = ".sqlesc($name);
	      else
	      {
	        $name = str_replace(array('?','*'), array('_','%'), $name);
	        $name_is .= (isset($name_is)?" OR ":"")."u.username LIKE ".sqlesc($name);
	      }
	    }
      $where_is .= $name_is.")";
      unset($name_is);
	  }

    if (is_array($names_exc))
    {
	  	$where_is .= !empty($where_is)?" AND NOT (":" NOT (";
	    foreach($names_exc as $name)
	    {
	    	if (!haswildcard($name))
	      	$name_is .= (isset($name_is)?" OR ":"")."u.username = ".sqlesc($name);
	      else
	      {
	      	$name = str_replace(array('?','*'), array('_','%'), $name);
	        $name_is .= (isset($name_is)?" OR ":"")."u.username LIKE ".sqlesc($name);
	      }
	    }
      $where_is .= $name_is.")";
	  }
	  $q .= ($q ? "&amp;" : "") . "n=".urlencode(trim($_GET['n']));
  }

  // email
  if(is_set_not_empty('em')) {
  $emaila = explode(' ', trim($_GET['em']));
  if ($emaila[0] !== "")
  {
  	$where_is .= !empty($where_is)?" AND (":"(";
    foreach($emaila as $email)
    {
	  	if (strpos($email,'*') === False && strpos($email,'?') === False
	    		&& strpos($email,'%') === False)
	    {
      	if (validemail($email) !== 1)
      	{
	        stdmsg("Error", "Bad email.");
	        stdfoot();
	      	die();
	      }
	      $email_is .= (!empty($email_is)?" OR ":"")."u.email =".sqlesc($email);
      }
      else
      {
	    	$sql_email = str_replace(array('?','*'), array('_','%'), $email);
	      $email_is .= (!empty($email_is)?" OR ":"")."u.email LIKE ".sqlesc($sql_email);
	    }
    }
		$where_is .= $email_is.")";
    $q .= ($q ? "&amp;" : "") . "em=".urlencode(trim($_GET['em']));
  }
}
  //class
  // NB: the c parameter is passed as two units above the real one
  $class = is_set_not_empty('c') ? $_GET['c'] - 2 : -2;
	if (is_valid_id($class + 1))
	{
  	$where_is .= (!empty($where_is)?" AND ":"")."u.class=$class";
    $q .= ($q ? "&amp;" : "") . "c=".($class+2);
  }

  // IP
  
  if (is_set_not_empty('ip'))
  {
  	$ip = trim($_GET['ip']);
  	$regex = "/^(((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))(\.\b|$)){4}$/";
    if (!preg_match($regex, $ip))
    {
    	stdmsg("Error", "Bad IP.");
    	stdfoot();
    	die();
    }

    $mask = trim($_GET['ma']);
    if ($mask == "" || $mask == "255.255.255.255")
    	$where_is .= (!empty($where_is)?" AND ":"")."u.ip = '$ip'";
    else
    {
    	if (substr($mask,0,1) == "/")
    	{
      	$n = substr($mask, 1, strlen($mask) - 1);
        if (!is_numeric($n) or $n < 0 or $n > 32)
        {
        	stdmsg("Error", "Bad subnet mask.");
        	stdfoot();
          die();
        }
        else
	      	$mask = long2ip(pow(2,32) - pow(2,32-$n));
      }
      elseif (!preg_match($regex, $mask))
      {
				stdmsg("Error", "Bad subnet mask.");
				stdfoot();
	      die();
      }
      $where_is .= (!empty($where_is)?" AND ":"")."INET_ATON(u.ip) & INET_ATON('$mask') = INET_ATON('$ip') & INET_ATON('$mask')";
      $q .= ($q ? "&amp;" : "") . "ma=$mask";
    }
    $q .= ($q ? "&amp;" : "") . "ip=$ip";
  }

  // ratio
  
  if (is_set_not_empty('r'))
  {
  	$ratio = trim($_GET['r']);
  	if ($ratio == '---')
  	{
    	$ratio2 = "";
      $where_is .= !empty($where_is)?" AND ":"";
      $where_is .= " u.uploaded = 0 and u.downloaded = 0";
    }
    elseif (strtolower(substr($ratio,0,3)) == 'inf')
    {
    	$ratio2 = "";
      $where_is .= !empty($where_is)?" AND ":"";
      $where_is .= " u.uploaded > 0 and u.downloaded = 0";
    }
    else
    {
    	if (!is_numeric($ratio) || $ratio < 0)
    	{
      	stdmsg("Error", "Bad ratio.");
      	stdfoot();
        die();
      }
      $where_is .= !empty($where_is)?" AND ":"";
      $where_is .= " (u.uploaded/u.downloaded)";
      $ratiotype = $_GET['rt'];
      $q .= ($q ? "&amp;" : "") . "rt=$ratiotype";
      if ($ratiotype == "3")
      {
      	$ratio2 = trim($_GET['r2']);
        if(!$ratio2)
        {
        	stdmsg("Error", "Two ratios needed for this type of search.");
        	stdfoot();
          die();
        }
        if (!is_numeric($ratio2) or $ratio2 < $ratio)
        {
        	stdmsg("Error", "Bad second ratio.");
        	stdfoot();
        	die();
        }
        $where_is .= " BETWEEN $ratio and $ratio2";
        $q .= ($q ? "&amp;" : "") . "r2=$ratio2";
      }
      elseif ($ratiotype == "2")
      	$where_is .= " < $ratio";
      elseif ($ratiotype == "1")
      	$where_is .= " > $ratio";
      else
      	$where_is .= " BETWEEN ($ratio - 0.004) and ($ratio + 0.004)";
    }
    $q .= ($q ? "&amp;" : "") . "r=$ratio";
  }

  // comment
  if(is_set_not_empty('co')) {
  $comments = explode(' ',trim($_GET['co']));
  if ($comments[0] !== "")
  {
		foreach($comments as $comment)
		{
	    if (substr($comment,0,1) == '~')
	    {
      	if ($comment == '~') continue;
   	    $comments_exc[] = substr($comment,1);
      }
      else
	    	$comments_inc[] = $comment;
	  }

    if (is_array($comments_inc))
    {
	  	$where_is .= !empty($where_is)?" AND (":"(";
	    foreach($comments_inc as $comment)
	    {
	    	if (!haswildcard($comment))
		    	$comment_is .= (!empty($comment_is)?" OR ":"")."u.modcomment LIKE ".sqlesc("%".$comment."%");
        else
        {
	      	$comment = str_replace(array('?','*'), array('_','%'), $comment);
	        $comment_is .= (!empty($comment_is)?" OR ":"")."u.modcomment LIKE ".sqlesc($comment);
        }
      }
      $where_is .= $comment_is.")";
      unset($comment_is);
    }

    if (is_array($comments_exc))
    {
	  	$where_is .= !empty($where_is)?" AND NOT (":" NOT (";
	    foreach($comments_exc as $comment)
	    {
	    	if (!haswildcard($comment))
		    	$comment_is .= (isset($comment_is)?" OR ":"")."u.modcomment LIKE ".sqlesc("%".$comment."%");
        else
        {
	      	$comment = str_replace(array('?','*'), array('_','%'), $comment);
	        $comment_is .= (isset($comment_is)?" OR ":"")."u.modcomment LIKE ".sqlesc($comment);
	      }
      }
      $where_is .= $comment_is.")";
	  }
    $q .= ($q ? "&amp;" : "") . "co=".urlencode(trim($_GET['co']));
  }
}
  $unit = 1073741824;		// 1GB

  // uploaded
  
  if (is_set_not_empty('ul'))
  {
  	$ul = trim($_GET['ul']);
  	if (!is_numeric($ul) || $ul < 0)
  	{
    	stdmsg("Error", "Bad uploaded amount.");
    	stdfoot();
      die();
    }
    $where_is .= !empty($where_is)?" AND ":"";
    $where_is .= " u.uploaded ";
    $ultype = $_GET['ult'];
    $q .= ($q ? "&amp;" : "") . "ult=$ultype";
    if ($ultype == "3")
    {
	    $ul2 = trim($_GET['ul2']);
    	if(!$ul2)
    	{
      	stdmsg("Error", "Two uploaded amounts needed for this type of search.");
      	stdfoot();
        die();
      }
      if (!is_numeric($ul2) or $ul2 < $ul)
      {
      	stdmsg("Error", "Bad second uploaded amount.");
      	stdfoot();
        die();
      }
      $where_is .= " BETWEEN ".$ul*$unit." and ".$ul2*$unit;
      $q .= ($q ? "&amp;" : "") . "ul2=$ul2";
    }
    elseif ($ultype == "2")
    	$where_is .= " < ".$ul*$unit;
    elseif ($ultype == "1")
    	$where_is .= " >". $ul*$unit;
    else
    	$where_is .= " BETWEEN ".($ul - 0.004)*$unit." and ".($ul + 0.004)*$unit;
    $q .= ($q ? "&amp;" : "") . "ul=$ul";
  }

  // downloaded
  
  if (is_set_not_empty('dl'))
  {
  	$dl = trim($_GET['dl']);
  	if (!is_numeric($dl) || $dl < 0)
  	{
    	stdmsg("Error", "Bad downloaded amount.");
    	stdfoot();
      die();
    }
    $where_is .= !empty($where_is)?" AND ":"";
    $where_is .= " u.downloaded ";
    $dltype = $_GET['dlt'];
    $q .= ($q ? "&amp;" : "") . "dlt=$dltype";
    if ($dltype == "3")
    {
    	$dl2 = trim($_GET['dl2']);
      if(!$dl2)
      {
      	stdmsg("Error", "Two downloaded amounts needed for this type of search.");
      	stdfoot();
        die();
      }
      if (!is_numeric($dl2) or $dl2 < $dl)
      {
      	stdmsg("Error", "Bad second downloaded amount.");
      	stdfoot();
        die();
      }
      $where_is .= " BETWEEN ".$dl*$unit." and ".$dl2*$unit;
      $q .= ($q ? "&amp;" : "") . "dl2=$dl2";
    }
    elseif ($dltype == "2")
    	$where_is .= " < ".$dl*$unit;
    elseif ($dltype == "1")
     	$where_is .= " > ".$dl*$unit;
    else
     	$where_is .= " BETWEEN ".($dl - 0.004)*$unit." and ".($dl + 0.004)*$unit;
    $q .= ($q ? "&amp;" : "") . "dl=$dl";
  }

  // date joined
  
  if (is_set_not_empty('d'))
  {
  	$date = trim($_GET['d']);
  	if (!$date = mkdate($date))
  	{
    	stdmsg("Error", "Invalid date.");
    	stdfoot();
      die();
    }
    $q .= ($q ? "&amp;" : "") . "d=$date";
    $datetype = $_GET['dt'];
		$q .= ($q ? "&amp;" : "") . "dt=$datetype";
    if ($datetype == "0")
    // For mySQL 4.1.1 or above use instead
    // $where_is .= (isset($where_is)?" AND ":"")."DATE(added) = DATE('$date')";
    $where_is .= (!empty($where_is)?" AND ":"").
    		"(UNIX_TIMESTAMP(added) - UNIX_TIMESTAMP('$date')) BETWEEN 0 and 86400";
    else
    {
      $where_is .= (!empty($where_is)?" AND ":"")."u.added ";
      if ($datetype == "3")
      {
        $date2 = mkdate(trim($_GET['d2']));
        if ($date2)
        {
          if (!$date = mkdate($date))
          {
            stdmsg("Error", "Invalid date.");
            stdfoot();
            die();
          }
          $q .= ($q ? "&amp;" : "") . "d2=$date2";
          $where_is .= " BETWEEN '$date' and '$date2'";
        }
        else
        {
          stdmsg("Error", "Two dates needed for this type of search.");
          stdfoot();
          die();
        }
      }
      elseif ($datetype == "1")
        $where_is .= "< '$date'";
      elseif ($datetype == "2")
        $where_is .= "> '$date'";
    }
  }

	// date last seen
  
  if (is_set_not_empty('ls'))
  {
  	$last = trim($_GET['ls']);
  	if (!$last = mkdate($last))
  	{
    	stdmsg("Error", "Invalid date.");
    	stdfoot();
      die();
    }
    $q .= ($q ? "&amp;" : "") . "ls=$last";
    $lasttype = $_GET['lst'];
    $q .= ($q ? "&amp;" : "") . "lst=$lasttype";
    if ($lasttype == "0")
    // For mySQL 4.1.1 or above use instead
    // $where_is .= (isset($where_is)?" AND ":"")."DATE(added) = DATE('$date')";
    	$where_is .= (!empty($where_is)?" AND ":"").
      		"(UNIX_TIMESTAMP(last_access) - UNIX_TIMESTAMP('$last')) BETWEEN 0 and 86400";
    else
    {
    	$where_is .= (!empty($where_is)?" AND ":"")."u.last_access ";
      if ($lasttype == "3")
      {
      	$last2 = mkdate(trim($_GET['ls2']));
        if ($last2)
        {
        	$where_is .= " BETWEEN '$last' and '$last2'";
	        $q .= ($q ? "&amp;" : "") . "ls2=$last2";
        }
        else
        {
        	stdmsg("Error", "The second date is not valid.");
        	stdfoot();
        	die();
        }
      }
      elseif ($lasttype == "1")
    		$where_is .= "< '$last'";
      elseif ($lasttype == "2")
      	$where_is .= "> '$last'";
    }
  }

  // status
  
  if (is_set_not_empty('st'))
  {
  	$status = $_GET['st'];
  	$where_is .= ((!empty($where_is))?" AND ":"");
    if ($status == "1")
    	$where_is .= "u.status = 'confirmed'";
    else
    	$where_is .= "u.status = 'pending'";
    $q .= ($q ? "&amp;" : "") . "st=$status";
  }

  // account status
  
  if (is_set_not_empty('as'))
  {
  	$accountstatus = $_GET['as'];
  	$where_is .= (!empty($where_is))?" AND ":"";
    if ($accountstatus == "1")
    	$where_is .= " u.enabled = 'yes'";
    else
    	$where_is .= " u.enabled = 'no'";
    $q .= ($q ? "&amp;" : "") . "as=$accountstatus";
  }

  //donor
	
  if (is_set_not_empty('do'))
  {
		$donor = $_GET['do'];
		$where_is .= (!empty($where_is))?" AND ":"";
    if ($donor == 1)
    	$where_is .= " u.donor = 'yes'";
    else
    	$where_is .= " u.donor = 'no'";
    $q .= ($q ? "&amp;" : "") . "do=$donor";
  }

  //warned
	
  if (is_set_not_empty('w'))
  {
		$warned = $_GET['w'];
		$where_is .= (!empty($where_is))?" AND ":"";
    if ($warned == 1)
    	$where_is .= " u.warned = 'yes'";
    else
    	$where_is .= " u.warned = 'no'";
    $q .= ($q ? "&amp;" : "") . "w=$warned";
  }

  // disabled IP
  $disabled = isset($_GET['dip']) ? (int)$_GET['dip'] : '';
  if (!empty($disabled))
  {
  	$distinct = "DISTINCT ";
    $join_is .= " LEFT JOIN users AS u2 ON u.ip = u2.ip";
		$where_is .= ((!empty($where_is))?" AND ":"")."u2.enabled = 'no'";
    $q .= ($q ? "&amp;" : "") . "dip=$disabled";
  }

  // active
  $active = isset($_GET['ac']) ? $_GET['ac'] : '';
  if ($active == "1")
  {
  	$distinct = "DISTINCT ";
    $join_is .= " LEFT JOIN peers AS p ON u.id = p.userid";
    $q .= ($q ? "&amp;" : "") . "ac=$active";
  }


  $from_is = isset($join_is) ? "users AS u".$join_is:"users AS u";
  $distinct = isset($distinct)?$distinct:"";
	$where_is = !empty($where_is) ? $where_is : "";
  $queryc = "SELECT COUNT(".$distinct."u.id) FROM ".$from_is.
  		(($where_is == "")?"":" WHERE $where_is ");

  $querypm = "FROM ".$from_is.(($where_is == "")?" ":" WHERE $where_is ");

  $select_is = "u.id, u.username, u.email, u.status, u.added, u.last_access, u.ip,
  	u.class, u.uploaded, u.downloaded, u.donor, u.modcomment, u.enabled, u.warned";

  $query = "SELECT ".$distinct." ".$select_is." ".$querypm;

//    <temporary>    /////////////////////////////////////////////////////
  if ($DEBUG_MODE > 0)
  {
  	stdmsg("Count Query",$queryc);
    echo "<br /><br />";
    stdmsg("Search Query",$query);
    echo "<br /><br />";
    stdmsg("URL Parameters 'Actually' Used",$q);
    if ($DEBUG_MODE == 2)
    	stdfoot();
    	exit();
  }
//    </temporary>   /////////////////////////////////////////////////////

  $res = mysql_query($queryc) or sqlerr();
  $arr = mysql_fetch_row($res);
  $count = $arr[0];

  $q = isset($q)?($q."&amp;"):"";

  $perpage = 30;

  $pager = pager($perpage, $count, "usersearch.php?".$q);

  $query .= $pager['limit'];

  $res = mysql_query($query) or sqlerr();

  if (mysql_num_rows($res) == 0)
  	stdmsg("Warning","No user was found.");
  else
  {
  	if ($count > $perpage)
  		echo $pager['pagertop'];
    echo "<table border='1' cellspacing='0' cellpadding='5'>\n";
    echo "<tr class='rowhead'><td align='left'>Name</td>
    		<td align='left'>Ratio</td>
        <td align='left'>IP</td>
        <td align='left'>Email</td>".
        "<td align='left'>Joined:</td>".
        "<td align='left'>Last seen:</td>".
        "<td align='left'>Status</td>".
        "<td align='left'>Enabled</td>".
        "<td>pR</td>".
        "<td>pUL (MB)</td>".
        "<td>pDL (MB)</td>".
        "<td>History</td></tr>";
        $ids = '';
    while ($user = mysql_fetch_array($res))
    {
    	if ($user['added'] == '0000-00-00 00:00:00')
      	$user['added'] = '---';
      if ($user['last_access'] == '0000-00-00 00:00:00')
      	$user['last_access'] = '---';

      if ($user['ip'])
      {
	    	$nip = ip2long($user['ip']);
        $auxres = mysql_query("SELECT COUNT(*) FROM bans WHERE $nip >= first AND $nip <= last") or sqlerr(__FILE__, __LINE__);
        $array = mysql_fetch_row($auxres);
    	  if ($array[0] == 0)
      		$ipstr = $user['ip'];
	  	  else
	      	$ipstr = "<a href='testip.php?ip=".$user['ip']."'><font color='#FF0000'><b>".$user['ip']."</b></font></a>";
			}
			else
      	$ipstr = "---";

      $auxres = mysql_query("SELECT SUM(uploaded) AS pul, SUM(downloaded) AS pdl FROM peers WHERE userid = ".$user['id']) or sqlerr(__FILE__, __LINE__);
      $array = mysql_fetch_array($auxres);

      $pul = $array['pul'];
      $pdl = $array['pdl'];
      if ($pdl > 0)
      	$partial = ratios($pul,$pdl)." (".mksizemb($pul)."/".mksizemb($pdl).")";
      else
      	if ($pul > 0)
      		$partial = "Inf. ".mksizemb($pul)."/".mksizemb($pdl).")";
      	else
      		$partial = "---";

//    $auxres = mysql_query("SELECT COUNT(id) FROM posts WHERE userid = ".$user['id']) or sqlerr(__FILE__, __LINE__);

      $auxres = mysql_query(
      "SELECT COUNT(DISTINCT p.id)
      FROM posts AS p LEFT JOIN topics as t ON p.topicid = t.id
      LEFT JOIN forums AS f ON t.forumid = f.id
      WHERE p.userid = " . $user['id'] . " AND f.minclassread <= " . $CURUSER['class']) or sqlerr(__FILE__, __LINE__);

      $n = mysql_fetch_row($auxres);
      $n_posts = $n[0];

      $auxres = mysql_query("SELECT COUNT(id) FROM comments WHERE user = ".$user['id']) or sqlerr(__FILE__, __LINE__);
			// Use LEFT JOIN to exclude orphan comments
      // $auxres = mysql_query("SELECT COUNT(c.id) FROM comments AS c LEFT JOIN torrents as t ON c.torrent = t.id WHERE c.user = '".$user['id']."'") or sqlerr(__FILE__, __LINE__);
      $n = mysql_fetch_row($auxres);
      $n_comments = $n[0];
      $ids .= $user['id'].':';
    	echo "<tr><td><b><a href='userdetails.php?id=" . $user['id'] . "'>" .
      		$user['username']."</a></b>" .
      		($user["donor"] == "yes" ? "<img src='pic/star.gif' alt=\"Donor\" />" : "") .
					($user["warned"] == "yes" ? "<img src=\"pic/warned.gif\" alt=\"Warned\" />" : "") . "</td>
          <td>" . ratios($user['uploaded'], $user['downloaded']) . "</td>
          <td>" . $ipstr . "</td><td>" . $user['email'] . "</td>
          <td><div align='center'>" . $user['added'] . "</div></td>
          <td><div align='center'>" . $user['last_access'] . "</div></td>
          <td><div align='center'>" . $user['status'] . "</div></td>
          <td><div align='center'>" . $user['enabled']."</div></td>
          <td><div align='center'>" . ratios($pul,$pdl) . "</div></td>
          <td><div align='right'>" . number_format($pul / 1048576) . "</div></td>
          <td><div align='right'>" . number_format($pdl / 1048576) . "</div></td>
          <td><div align='center'>".($n_posts?"<a href='userhistory.php?action=viewposts&amp;id=".$user['id']."'>$n_posts</a>":$n_posts).
          "|".($n_comments?"<a href='userhistory.php?action=viewcomments&amp;id=".$user['id']."'>$n_comments</a>":$n_comments).
          "</div></td></tr>\n";
          
    }
    echo "</table>";
    if ($count > $perpage)
    	echo $pager['pagerbottom'];

	?>
    <br /><br />
    <form method='post' action='sendmessage.php'>
      <table border="1" cellpadding="5" cellspacing="0">
        <tr>
          <td>
            <div align="center">
              <!--<input name="pmees" type="hidden" value="<?php echo $querypm?>" size=10>-->
              <input name="pmees" type="hidden" value="<?php echo htmlentities(rtrim($ids, ':'))?>" />
              <input name="PM" type="submit" value="PM" class='btn' />
              <input name="n_pms" type="hidden" value="<?php echo htmlentities($count)?>" size='10' />
            </div></td>
        </tr>
      </table>
    </form>
    <?php

  }
}
if(isset($pagemenu))
print("<p>$pagemenu<br />$browsemenu</p>");
stdfoot();
die;

?>