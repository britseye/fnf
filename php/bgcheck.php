<?php
error_reporting(-1);
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
   bail("Operation blocked.\nYour security token is invalid ($token_msg)");
*/
header("Content-type: text/plain");

$uid = (int)$_GET["uid"];
$lastfeed = (int)$_GET["lastfeed"];
$lastmsg = (int)$_GET["lastmsg"];
$lastmember = (int)$_GET["lastmember"];

$db = dbConnect();
if (!$db) bail(sql_error());
$result = null;
$row= null;

if ($lastfeed >= 0 )  // feed loaded
{
   $result = $db->query("select id from fffeed where id>$lastfeed order by id desc");
   if (!$result) bail($db->error);
   if ($result->num_rows)
   {
      $rv["newfeeds"] = $result->num_rows;
      $row = $result->fetch_row();
      $rv["latestfeed"] = $row[0];
   }
   else
   {
      $rv["newfeeds"] = 0;
      $rv["latestfeed"] = $lastfeed;
   }
}
if ($lastmsg >= 0)
{
   $result = $db->query("select id from ffmessages where id>$lastmsg order by id desc");
   if (!$result) bail($db->error);
   if ($result->num_rows)
   {
      $rv["newmsgs"] = $result->num_rows;
      $row = $result->fetch_row();
      $rv["latestmsg"] = $row[0];
   }
   else
   {
      $rv["newmsgs"] = 0;
      $rv["latestmsg"] = $lastmsg;
   }
}

if ($lastmember >= 0)
{
   $result = $db->query("select id from ffmembers where id>$lastmember order by id desc");
   if (!$result) bail($db->error);
   if ($result->num_rows)
   {
      $rv["newmembers"] = $result->num_rows;
      $row = $result->fetch_row();
      $rv["latestmember"] = $row[0];
   }
   else
   {
      $rv["newmembers"] = 0;
      $rv["latestmember"] = $lastmember;
   }
}
$rv["success"] = true;
echo json_encode($rv);
?>