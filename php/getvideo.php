<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include_once "not4everyone.php";
include_once "constants.php";

$iid = substr($_SERVER["PATH_INFO"], 1);

$db = dbConnect();
if (!$db) die(sql_error());

$query = "SELECT type, UNIX_TIMESTAMP(ts), imgdata from ffimages where iid=$iid";
$result = $db->query($query);
if (!$result) die ($db->error);
if (!$result || $result->num_rows != 1)
{
   header("HTTP/1.0 404 Not Found");
   exit;
}
$row = $result->fetch_row();
$itype = $row[0];
if ($itype != 5)
{
   header("HTTP/1.0 404 Not Found");
   exit;
}
$mtime = $row[1];

$fp = fopen($_SERVER["SCRIPT_FILENAME"], "r");
$etag = md5(serialize(fstat($fp)));
fclose($fp);

header("Cache-Control: must-revalidate");
header("Last-Modified: ".gmdate("D, d M Y H:i:s", $mtime)." GMT");
header('Etag: '.$etag);
header("Expires: -1");

if ((@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $mtime) && (
    trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag)) {
    header("HTTP/1.1 304 Not Modified");
    exit;
}

header("Content-type: video/mp4");
echo $row[2];
?>