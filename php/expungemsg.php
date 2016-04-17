<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include_once "not4everyone.php";
include_once "constants.php";
include_once "tea.php";
include_once "checktoken.php";
header("Content-type: text/plain");
if (!$token_ok)
   die("Operation blocked.<br>Your security token is invalid ($token_msg)");

$mid = $_GET["mid"];
$mtype = $_GET["mtype"];
$iid = $_GET["iid"];
$ifn = $_GET["ifn"];

$ifn = '../ff_uploads/'.$ifn;
if ($mtype == 5)
   unlink($ifn);

$db = dbConnect();
if (!$db) die(sql_error());
if ($iid > 0)
   $db->query("delete from ffimages where iid=$iid");
$db->query("delete from ffmessages where id=$mid");
?>
