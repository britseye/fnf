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

// The worst that can happen is we accumulate some unwanted records
header("Content-type: text/plain");
$db = dbConnect();
if (!$db) exit();

$id = $_GET["id"];
$cid = $_GET["cid"];
$ip = $_SERVER["REMOTE_ADDR"];
$db->query("delete from ffcomments where id='$cid'");
// There's nothing useful to return
?>

