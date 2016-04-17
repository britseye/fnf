<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include_once "not4everyone.php";
include_once "constants.php";
header("Content-type: text/plain");

$db = dbConnect();
if (!$db) die(sql_error());

$db->query("SET @mid = 0");
$db->query("SET @ts = ''");
if (!$db->query("CALL insertmsg(1, 2, 'Thebody', 0, 0, @mid, @ts)"))
   die($db->error);
$result = $db->query("SELECT @mid");
$row = $result->fetch_row();
echo $row[0]."\n";
$result = $db->query("SELECT @ts");
$row = $result->fetch_row();
echo $row[0]."\n";
?>