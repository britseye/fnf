<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include_once "not4everyone.php";
include_once "constants.php";

function getAnon($anon)
{
   $f = fopen("$anon", 'rb');
   if (!$f) { error_log("$anon: open failed"); exit(); }
   $data = fread($f, 100000);
   fclose($f);
   return $data;
}

$db = dbConnect();
if (!$db) die(sql_error());

$id = substr($_SERVER["PATH_INFO"], 1);

$itype = 0;
$ts = "";
$img = "";
$result = $db->query("SELECT imgtype, mugshot, UNIX_TIMESTAMP(mts) from ffmembers where id=$id");

if (!$result || $result->num_rows != 1)
{
   $itype = "png";
   $img = getAnon($anon);
   $ts = '1325365200';     // 2012-01-01 00:00:00
}
else
{
   $row = $result->fetch_row();
   $itype = $row[0];
//die("Rows ".$result->num_rows." ".$row[0]);
   if ($itype == 0)
   {
      $img = getAnon($anon);
      $itype = "png";
      $ts = '1325365200';
   }
   else
   {
      $itype = ($itype == 1)? "gif": (($itype == 2)? "jpeg": "png");
      $img = $row[1];
      $ts = $row[2];
   }
}

header("Cache-Control: must-revalidate");
header("Last-Modified: ".gmdate("D, d M Y H:i:s", $ts)." GMT");
header("Expires: -1");

header("Content-type: image/$itype");
echo $img;
?>
