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
include "writecomment.php";
$db = dbConnect();
if (!$db) bail(sql_error());

$text = $db->real_escape_string($_GET["text"]);
$sid = $_GET["sid"];
$logtype = $_GET["logtype"];
$id = $_GET["id"];

if (!$db->query("insert into ffcomments (id, sid, logtype, owner, text) values (NULL, $sid, $logtype, '$id', '$text')"))
   bail($db->error);
$cid = $db->insert_id;
// now read it back
$cquery =
"select ffcomments.*, ffmembers.fid, ffmembers.name from ffcomments LEFT JOIN ffmembers ON ffcomments.owner=ffmembers.id where ffcomments.id=$cid";
$result = $db->query($cquery);
if (!$result)
   bail("NO+".$db->error);
if ($result->num_rows != 1)
   bail("Comment not found");
$row = $result->fetch_row();
$s = writeComment($row, $id);
$rv["success"] = true; $rv["cid"] = $cid; $rv["text"] = $s;
echo json_encode($rv);
?>