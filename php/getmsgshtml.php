<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include_once "not4everyone.php";
include_once "constants.php";
include_once "tea.php";
include_once "bail.php";
/*
include_once "checktoken.php";
header("Content-type: text/plain");
if (!$token_ok)
   bail("Content blocked.<br>Your security token is invalid ($token_msg)");
*/
header("Content-type: text/plain");
include "writemessage.php";

$db = dbConnect();
if (!$db) bail(sql_error());

$cu = $_GET["cu"];
$other = $_GET["other"];
$cuname = $_GET["cuname"];
// Check the purported users against the token
//if ($cu != $ffid && $other != $ffid)
//   bail("You are not one of the parties to this conversation.");

$result = $db->query("select name from ffmembers where id=$other");
if (!$result) bail($db->error);
$count = $result->num_rows;
if (!$count) bail("Other user not found.");
$row = $result->fetch_row();
$oname = $row[0];

$result = $db->query("select id from ffmessages order by id desc limit 1");
if (!$result) bail($db->error);
$row = $result->fetch_row();
$mostrecent = $row[0];

$count = 0;
$sql =
"select * from ffmessages where ((fromid=$cu and toid=$other) or (fromid=$other and toid=$cu)) order by id desc";
$result = $db->query($sql);
if (!$result) bail($db->error);

$count = $result->num_rows;
$from = "";
$s = '';
$latest = '';
for ($i = 0; $i < $count; $i++)
{
   $row = $result->fetch_row();

   $mid = $row[0];
   if ($latest == '') $latest = $mid;
   $fid = $row[1];
   $tid = $row[2];
   $text = $row[4];
   $ts = $row[7];
   $mtype = $row[8];
   $iid = $row[9];
   $ifn = $row[10];
   $from = "";
   if ($fid == $cu)
      $from = $cuname;
   else
      $from = $oname;

   $s .= writeMsg($db, $cu, $mid, $fid, $tid, $from, $text, $ts, $mtype, $iid, $ifn);
   if ($i > 5) break;
}

$sql = "update ffmessages set readflag=1 where fromid=$other and toid=$cu";
$db->query($sql);

$rv["success"] = true; $rv["html"] = $s; $rv["mostrecent"] = $mostrecent; $rv["latest"] = $latest;
echo json_encode($rv);
?>