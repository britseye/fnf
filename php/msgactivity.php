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

$action = $_GET["action"];
$uid = $_GET["uid"];
$oid = $_GET["oid"];
$lastchange = $_GET["lastc"];
$lastmsg = $_GET["lastm"];

$db = dbConnect();
if (!$db) bail(sql_error());
$result = null;
$row= null;
$ip = $_SERVER['REMOTE_ADDR'];

if ($action == 'check')
{
   $result = $db->query("select * from ffactivity where uid=$oid");
   if (!$result) bail($db->error);
   if ($result->num_rows > 1)
      bail("Multiple activity rows for $oid");
   else if ($result->num_rows == 1)
   {
      $rv["success"] = true;
      $rv["online"] = true;
      $row =  $result->fetch_row();
      $rv["olastchange"] = $row[1];
      $rv["olastmsg"] = $row[2];
      $rv["ip"] = $row[3];
      $rv["ts"] = $row[4];
   }
   else
   {
      $rv["success"] = true;
      $rv["online"] = false;
   }
   if (!$db->query("update ffactivity set lastchange=$lastchange where uid=$uid"))
      $rv["updatefailed"] = true;
   else
      $rv["updatefailed"] = false;
}
else if ($action == 'add')
{
   $result = $db->query("insert into ffactivity values($uid, 0, $lastmsg, '$ip', null)");
   if (!$result) bail($db->error);
   $rv["success"] = true;
}
else
{
   $db->query("delete from ffactivity where uid=$uid");
   $rv["success"] = true;
}
echo json_encode($rv);
?>

