<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include_once "not4everyone.php";
include_once "constants.php";
include_once "tea.php";
include_once "checktoken.php";
header("Content-type: text/plain");
if (!$token_ok)
   exit();

$db = dbConnect();
if (!$db) exit();

$sid = $_GET["sid"];

$result = $db->query("select iid, ifn from fffeed where id=$sid");
if (!$result)
   exit();
$row = $result->fetch_row();
$iid = $row[0];
$ifn = $row[1];
$db->query("delete from fffeed where id=$sid");
$db->query("delete from ffcomments where sid=$sid");
$db->query("delete from ffimages where iid=$iid");
if (strlen($ifn))
   unlink('../ff_uploads/'.$ifn);
// There's nothing useful to return
?>