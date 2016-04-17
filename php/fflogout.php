<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include "not4everyone.php";
include_once "constants.php";
include_once "tea.php";
include "bail.php";
include_once "checktoken.php";
header("Content-type: text/plain");
if (!$token_ok)
   die("Operation blocked.<br>Your security token is invalid ($token_msg)");

$uid = $_GET["uid"];
$db = dbConnect();
if (!$db) bail(sql_error());
if (!$db->query("update ffmembers set flags = flags & ~2 where id=$uid"))
   bail($db->error);
echo "OK";
?>
