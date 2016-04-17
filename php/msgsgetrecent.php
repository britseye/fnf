<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include_once "not4everyone.php";
include_once "constants.php";
include_once "tea.php";
include "bail.php";
include_once "checktoken.php";
header("Content-type: text/plain");
if (!$token_ok)
   bail("Content blocked.<br>Your security token is invalid ($token_msg)");
include "writemessage.php";

$db = dbConnect();
if (!$db) bail(sql_error());

$latest = $_GET["midlatest"];
$previous = $_GET["midprevious"];
$uid = $_GET["uid"];
$oid = $_GET["oid"];
$cuname = $_GET["cuname"];
$oname = $_GET["oname"];
// Check the purported users against the token
//if ($uid != $ffid && $oid != $ffid)
//   bail("You are not one of the parties to this conversation.");

$count = 0;
$sql =
"select * from ffmessages where ((fromid=$uid and toid=$oid) or (fromid=$oid and toid=$uid)) and id>$previous order by id desc";
$result = $db->query($sql);
if (!$result) bail($db->error."\n".$sql);
$count = $result->num_rows;
$from = "";
$latest = 0;
$s = "";
for ($i = 0; $i < $count; $i++)
{
   $row = $result->fetch_row();

   $mid = $row[0];
   if ($i== 0) $latest = $mid;
   $fid = $row[1];
   $tid = $row[2];
   $text = $row[4];
   $ts = $row[7];
   $mtype = $row[8];
   $iid = $row[9];
   $ifn = $row[10];
   $from = "";
   if ($fid == $uid)
      $from = $cuname;
   else
      $from = $oname;

   $s .= writeMsg($db, $uid, $mid, $fid, $tid, $from, $text, $ts, $mtype, $iid, $ifn);
}
$sql = "update ffmessages set readflag=1 where fromid=$oid and toid=$uid";
$db->query($sql);

$rv["success"] = true; $rv["html"] = $s; $rv["count"] = "$count, $latest, $previous"; $rv["latest"] = $latest;
echo json_encode($rv);
?>