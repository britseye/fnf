<?php
/* Copyright: Copyright 2016
 * License:   $(LINK www.boost.org/LICENSE_1_0.txt, Boost License 1.0).
 * Author:   Steve Teale
 */
error_reporting(-1);
ini_set('display_errors', 'On');

include_once "not4everyone.php";
include_once "constants.php";
include_once "tea.php";
include "bail.php";
/*
include_once "checktoken.php";
header("Content-type: text/plain");
if (!$token_ok)
   die("Content blocked.<br>Your security token is invalid ($token_msg)");
   */
header("Content-type: text/plain");
include_once "writepost.php";
include_once "writecomment.php";

$db = dbConnect();
if (!$db) die(sql_error());
$result = $db->query("SET time_zone = '+00:00'");
if (!$result) die($db->error);
//$strings = getStrings($db, 0);
$user = $_GET["uid"];
$latest = $_GET["latest"];

$pquery =
"select fffeed.*, ffmembers.fid, ffmembers.name from fffeed LEFT JOIN ffmembers ON fffeed.owner=ffmembers.id ".
"where fffeed.instance=0 and fffeed.id>$latest order by fffeed.id desc";


$cquery =
"select ffcomments.*, ffmembers.fid, ffmembers.name from ffcomments LEFT JOIN ffmembers ON ffcomments.owner=ffmembers.id where ffcomments.sid=";
$result = $db->query($pquery);

$s = "";
if (!$result) die($db->error);
$newlatest = '';
if ($result->num_rows)
{
	while ($row = $result->fetch_row())
	{
	   if ($newlatest == '') $newlatest = $row[0];
	   $s = writePost($row, $user, $db, false);
	   $s .= "\n";
	   $cresult = $db->query($cquery.$row[0]);
	   if (!$cresult) bail($db->error);
	   while ($crow = $cresult->fetch_row())
	   {
	      $s .= writeComment($crow, $user);
      }
      $s .= "\n";
      // This is where comments will get added
	   $s .= '<div class="sentinel"></div>'."\n";
	   $s .= '<span class="clicktext" style="cursor:pointer;" onclick="addComment('.$row[0].');">Add your comment</span><br>';
	   $s .= '<div style="border-top:solid 1px #bbbbbb; height:1em; margin-top:1em;"></div>'; // line under post
	   $s .= "\n</div>"; // end of feeditem
	}
}
if ($newlatest == '')
   $newlatest = $latest;

$rv["success"] = true; $rv["html"] = $s; $rv["latest"] = $newlatest;
echo json_encode($rv);
?>
