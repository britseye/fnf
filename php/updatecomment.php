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
   bail("Operation blocked.\nYour security token is invalid ($token_msg)");

header("Content-type: text/plain");
$db = dbConnect();
if (!$db) bail(sql_error());

$id = $_GET["id"];
$bcid = $_GET["bcid"];
$text = $db->real_escape_string($_GET["blurb"]);
$ip = $_SERVER["REMOTE_ADDR"];

$result = $db->query("update ffcomments set text='$text' where id=$id");
if (!$result) bail($db->error);
$rv["success"]= true;
echo json_encode($rv);
?>

