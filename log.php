<?php
  require_once "include/bittorrent.php";
  require_once "include/user_functions.php";
  dbconn(false);

  loggedinorreturn();

  // delete items older than a week
  $secs =30 * 24 * 60 * 60;
  stdhead("Site log");
  mysql_query("DELETE FROM sitelog WHERE " . time() . " - added > $secs") or sqlerr(__FILE__, __LINE__);
  $res = mysql_query("SELECT added, txt FROM sitelog ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);
  print("<h1>Site log</h1>\n");
  if (mysql_num_rows($res) == 0)
    print("<b>Log is empty</b>\n");
  else
  {
    print("<table border='1' cellspacing='0' cellpadding='5'>\n");
    print("<tr><td class='colhead' align='left'>Date</td><td class='colhead' align='left'>Time</td><td class='colhead' align='left'>Event</td></tr>\n");
    while ($arr = mysql_fetch_assoc($res))
    {
      $date = explode( ',', get_date( $arr['added'], 'LONG' ) );
      print("<tr><td>{$date[0]}</td><td>{$date[1]}</td><td align='left'>".htmlentities($arr['txt'], ENT_QUOTES)."</td></tr>\n");
    }
    print("</table>");
  }
  print("<p>Times are in GMT.</p>\n");
  stdfoot();
  
?>