<?php
error_reporting(-1);
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

$id = $_GET["id"];

$db = dbConnect();
if (!$db) bail(sql_error());

$result = $db->query("select text from fffeed where id=$id");
if (!$result) bail($db->error);
$row =  $result->fetch_row();
if (!$row) bail('Not found');

$rv["success"] = true; $rv["text"] = $row[0];
echo json_encode($rv);
?>

