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
   bail("Content blocked.\nYour security token is invalid ($token_msg)");

$db = dbConnect();
if (!$db) bail(sql_error());

$uid = $_GET["uid"];

// See if the database record for this user exists
$result = $db->query("select * from ffudetail WHERE uid=$uid");
if(!$result) bail($db->error);
$n = $result->num_rows;
$row = '';
$s = "";

$query =
"select ffudetail.*, ffmembers.email, ffmembers.name from ffudetail LEFT JOIN ffmembers ON ffudetail.uid=ffmembers.id where ffudetail.uid=$uid";
$result = $db->query($query);
if (!$result) bail($db->error);
$n = $result->num_rows;
if (!$n)  // Simulate a blank entry
{
   $result = $db->query("select email, name from ffmembers where id=$uid");
   if (!$result) bail($db->error);
   $r = $result->fetch_row();
   $row = array(0,'','','','','','','','','Fine','-1',$r[0],$r[1]);
}
else if ($n == 1)
{
   $row = $result->fetch_row();
}
else
   bail("Ambiguous query result, more than one row for user $uid");
$s = $row[1];
$rv["success"]=true; $rv["dob"] = $row[1]; $rv["phone"] = $row[2]; $rv["address1"]=$row[3]; $rv["address2"] = $row[4];
$rv["city"] = $row[5]; $rv["region"] = $row[6]; $rv["postcode"] = $row[7]; $rv["country"] = $row[8]; $rv["hru"] = $row[9];
$rv["email"] = $row[11]; $rv["name"] = $row[12];

echo json_encode($rv);
?>
