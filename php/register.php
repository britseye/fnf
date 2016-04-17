<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include_once "not4everyone.php";
include_once "constants.php";
include_once "tea.php";
include "bail.php";
/*include_once "checktoken.php";
header("Content-type: text/plain");
if (!$token_ok)
   bail("Operation blocked.\nYour security token is invalid ($token_msg)");
*/
header("Content-type: text/plain");

function getAnon()
{
   $f = fopen("anon.png", 'rb');
   if (!$f) bail("Failed to open anon.png.");
   $data = fread($f, 100000);
   fclose($f);
   return $data;
}

$db = dbConnect();
if (!$db) bail(sql_error());

$moniker = $db->real_escape_string($_GET["moniker"]);
$name = $db->real_escape_string($_GET["username"]);
$pass = $db->real_escape_string($_GET["pass"]);
$pass = md5("raT69arSe$pass");
$email = $db->real_escape_string($_GET["email"]);

$img = addslashes(getAnon());

$query = "insert into ffmembers (id, type, moniker, pass, name, imgtype, mugshot, flags, email) ".
"values (NULL, 1, '$moniker', '$pass', '$name', 3, '$img', 0, '$email')";
if (!$db->query($query))
   bail($db->error);
$uid = $db->insert_id;

$rv["success"] = true; $rv["uid"] = $uid; $rv["moniker"] = $moniker; $rv["name"] = $name;
echo json_encode($rv);
?>
