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
   bail("Operation blocked.\n($token_msg)");

$db = dbConnect();
if (!$db) bail(sql_error());

$mid = $_GET["mid"];
if (!$db->query("delete from ffmessages where id=$mid"))
   bail($db->error);
   $rv["success"]=true;
echo json_encode($rv);
?>

