<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include_once "not4everyone.php";
include_once "constants.php";
include_once "tea.php";
include "bail.php";
/*
include_once "checktoken.php";
header("Content-type: text/plain");
if (!$token_ok)
   bail("Content blocked.\nYour security token is invalid ($token_msg)");
*/
header("Content-type: text/plain");
function writeThreadItem($index, $oid, $oname, $text, $ts, $direction, $read, $online)
{
   $text = str_replace("\n\n", '<p class="nps">', $text);
   $text = str_replace("\n", '<br>', $text);
   $text = substr($text, 0, 30)." ...";
   $s =
'<div id="thread'.$index.'" class="threaditem" data-oid="'.$oid.'" data-oname="'.$oname.'" data-online="'.$online.'" style="cursor:pointer;">'."\n";
   // Poster name and details
   if ($online)
      $s .= '<span class="greentext sender" onclick="threadClick('.$oid.", '".$oname."'".');">'.$oname.'</span>';
   else
      $s .= '<span class="clicktext sender" onclick="threadClick('.$oid.", '".$oname."'".');">'.$oname.'</span>';
   if ($direction == '< ' && !$read) $s .= ' <img src="greendot.jpg">';
   $s .= "\n";
   // Date/time
   $s .= '      <span class="datetime">('.$ts.')</span><br>'."\n";
   // The post text
   $s .= '<div style="margin-top:0.5em;">'."\n";
   $s .= $direction.'<span class="posttxt" style="margin-top: 0.5em">'.$text.'</span>'."\n";
   $s.= "</div>\n";
   $s .= '<div style="border-top:solid 1px #bbbbdd; margin-top:5px; height:5px;"></div>'."\n";
   $s .= "</div>\n";
   return $s;
}

$db = dbConnect();
if (!$db) bail(sql_error());

$cu = $_GET["cu"];

$sa = array();
$sql =
"select a.oid, a.lastmid, a.direction, b.mbody, b.readflag, b.ts, c.name, c.flags from ffthreads as a, ffmessages as b, ffmembers as c ".
"where a.uid=$cu and b.id=a.lastmid and c.id=a.oid order by a.oid desc";

$result =$db->query($sql);
if (!$result) bail($db->error);
$count = $result->num_rows;
$s = "";
$latest = 0;
for ($i = 0; $i < $count; $i++)
{
   $row = $result->fetch_row();
   $oid = $row[0];
   $lastmid = $row[1];
   if ($lastmid > $latest) $latest = $lastmid;
   $direction = $row[2]? '< ': '> ';
   $text = $row[3];
   $read = $row[4];
   $ts = $row[5];
   $oname = $row[6];
   $online = $row[7] & 2;
   $s .= writeThreadItem($i, $oid, $oname, $text, $ts, $direction, $read, $online);
}
$rv["success"] = true; $rv["text"] = $s; $rv["latest"] = $latest;
echo json_encode($rv);
?>
