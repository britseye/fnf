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
/*
include_once "checktoken.php";
header("Content-type: text/plain");
if (!$token_ok)
   die("Content blocked.<br>Your security token is invalid ($token_msg)");
   */
header("Content-type: text/plain");
include_once "writepost.php";
include_once "writecomment.php";

function getStrings($db, $instance)
{
   $a = array();
   //$a["banner"] = "";
   //$a["site"] = "";
   //$a["title"] = "";
   //$a["lcend"] = "";
   $result = $db->query("select * from ffinstances where id=$instance");
   if (!$result) die($db->error);
   while ($row = $result->fetch_row())
   {
      $a[$row[1]] = $row[2];
   }
   return $a;
}

$db = dbConnect();
if (!$db) die(sql_error());
$result = $db->query("SET time_zone = '+00:00'");
if (!$result) die($db->error);
$strings = getStrings($db, 0);
$user = $_GET["uid"];
$rt = (isset($_GET["rt"]))? $_GET["rt"]: "latest";
if (!($rt == "latest" || $rt == "oldest"))
{
$lb = (isset($_GET["lb"]))? $_GET["lb"]: 0;
if ($lb == 0)
   die("No lower bound supplied for newsfeed range");
$ub = (isset($_GET["ub"]))? $_GET["ub"]: 0;
if ($ub == 0)
   die("No upper bound supplied for newsfeed range");
   $rt = "range";
}


$pquery = "";
if ($rt == "latest")
   $pquery =
   "select fffeed.*, ffmembers.fid, ffmembers.name from fffeed LEFT JOIN ffmembers ON fffeed.owner=ffmembers.id where fffeed.instance=0 ".
   "order by fffeed.id desc limit 30";
else if ($rt == "oldest")
   $pquery =
   "select fffeed.*, ffmembers.fid, ffmembers.name from fffeed LEFT JOIN ffmembers ON fffeed.owner=ffmembers.id where fffeed.instance=0 ".
   "order by fffeed.id asc limit 30";
else
   $pquery =
   "select fffeed.*, ffmembers.fid, ffmembers.name from fffeed LEFT JOIN ffmembers ON fffeed.owner=ffmembers.id where fffeed.instance=0 ".
   "and unix_timestamp(fffeed.ts) > $lb and unix_timestamp(fffeed.ts) <=$ub order by fffeed.id asc";


$cquery =
"select ffcomments.*, ffmembers.fid, ffmembers.name from ffcomments LEFT JOIN ffmembers ON ffcomments.owner=ffmembers.id where ffcomments.sid=";
$result = $db->query($pquery);

if (!$result) die($db->error);
$feedlast = '';
if ($result->num_rows)
{
	while ($row = $result->fetch_row())
	{
	   if ($feedlast == '') $feedlast = $row[0];
	   $s = writePost($row, $user, $db, false);
	   echo "$s\n";
	   $cresult = $db->query($cquery.$row[0]);
	   if (!$cresult) die($db->error);
	   $s = "";
	   while ($crow = $cresult->fetch_row())
	   {
	      $s .= writeComment($crow, $user);
      }
      echo "$s\n";
      // This is where comments will get added
	   echo '<div class="sentinel"></div>'."\n";
	   echo '<span class="clicktext" style="cursor:pointer;" onclick="addComment('.$row[0].');">Add your comment</span><br>';
	   echo '<div style="border-top:solid 1px #bbbbbb; height:1em; margin-top:1em;"></div>'; // line under post
	   echo "\n</div>"; // end of feeditem
	}
}
echo '<p id="feedclose" class="byebye" data-feedlast="'.$feedlast.'">Thats all folks!</p>';
?>
