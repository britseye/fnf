<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include_once "not4everyone.php";
include_once "constants.php";
include_once "tea.php";
include_once "bail.php";
/*
include_once "checktoken.php";
header("Content-type: text/plain");
if (!$token_ok)
   bail("Content blocked.<br>Your security token is invalid\n($token_msg)");
*/
header("Content-type: text/plain");

function writeUser($row, $cu)
{

   $uid = $row[0]; $logtype = $row[1]; $fid = $row[2]; $name = $row[5]; $hru = $row[14];
   $msgs = ($uid == $cu)? "threads": "conversation";
   $s = '<div id="user'.$uid.'" class="uldiv" style="margin-bottom:1em;" data-uid="'.$uid.'" data-name="'.$name.'">'."\n";
   $s .= '<table style="width:100%;"><tr style="vertical-align:top"><td style="width:15mm;">'."\n";
   $s .= '   <img class="ulthumb showsud" data-owner="'.$uid.'" style="cursor:pointer;" src="';
   //if ($logtype == 3)
   //   $s .= 'https://plus.google.com/s2/photos/profile/'.$fid.'?sz=50">';
   //else if ($logtype == 2)
   //   $s .= 'https://graph.facebook.com/'.$fid.'/picture">';
   //else
      $s .= '../php/getthumb.php/'.$uid.'">'."\n";
   $s .= "</td><td>\n";
   $s .= '<span class="halfclick showsud" data-owner="'.$uid.'" style="cursor:pointer;">'.$name."</span><br>\n";
   $s .= '<div style="margin-top:0.3em">'."\n";
   $s .= '<span class="clicktext" style="margin-top:2em; cursor:pointer;" onclick="msgThunk('.$uid.');">Messages</span></div>'."\n";
   $s .= "</td></tr></table>\n";
   $s .= $hru;
   $s .= "</div>\n";
   return $s;
}

$cu = $_GET["cu"];
$limit = (isset($_GET["count"]))? $_GET["count"]: "0";
$limit = (int)$limit;

$db = dbConnect();
if (!$db) bail(sql_error());

// Newest first
$query =
"select ffmembers.*, ffudetail.hru from ffmembers LEFT JOIN ffudetail ON ffmembers.id = ffudetail.uid order by ffmembers.id desc";
if ($limit) $query .= " limit $limit";
$result = $db->query($query);
if (!$result) bail($db->error);

$latest = '';
$html = '';
if ($result->num_rows)
{
	while ($row = $result->fetch_row())
	{
	   $html .= writeUser($row, $cu);
	   if ($latest == '')
	      $latest = $row[0];
   }
}
$rv["success"] = true; $rv["html"] = $html; $rv["latest"]= $latest;
echo json_encode($rv);
?>
