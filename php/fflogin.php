<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include "not4everyone.php";
include_once "constants.php";
include "tea.php";
include "bail.php";

function makeToken($id, $name, $flags, $ip)
{
   global $ffkey;
   global $ffmark;
   $hdr = '{"typ":"JWT","alg":"HS256"}';
   $then = time()+30*24*60*60;  // 30 day token
   //$then = time()+30;  // 30s token
   $marker = substr(hash('sha256', ''.$then.$ffmark), 0, 10);
   $payload = '{"iss":"fnfhub.org","id":'.$id.',"name":"'.$name.'","flags":'.$flags.
      ',"expires":'.$then.',"marker": "'.$marker.'","ip":"'.$ip.'"}';
   $sig = hash('sha256', $hdr.$payload.$ffkey);
   $s = base64_encode($hdr).".".base64_encode($payload).".".$sig;
   return $s;
}

$ip = $_SERVER["REMOTE_ADDR"];
$s = $_SERVER['HTTP_FF_LOGIN'];
$s=base64_decode($s);
$dt = decrypt($s, $ffkey);
$dt = trim($dt, "\0");
$a = explode(" ", $dt);

$db = dbConnect();
if (!$db) bail(sql_error());
$bevid = $a[0];
$rawpass = $a[1];

$bevid = $db->real_escape_string($bevid);  // e.g. o'reilly

if ($bevid == "")
   bail("User ID was blank");
$isemail = 0;
$pos = strpos($bevid, "@");
if ($pos !== false)
   $isemail = 1;

$ip = $_SERVER["REMOTE_ADDR"];
$pass = md5("raT69arSe$rawpass");
$name = "";

$val = $isemail? "true": "false";

$result = "";
$otp = "";
$result = 0;
if ($isemail)
   $result = $db->query("select id, moniker, pass, name, flags, email from ffmembers where email='$bevid'");
else
   $result = $db->query("select id, moniker, pass, name, flags, email from ffmembers where moniker='$bevid'");
if (!$result) bail("SQL error - ".$db->error);

if ($result->num_rows == 1)
{
   $row = $result->fetch_row();
   $id = $isemail? $row[7]: $row[1];
   if ($id == $bevid && $row[2] == $pass)      // authenticated
   {
      $bcid = $row[0];
      $name = $row[3];
      $s = makeToken($row[0], $row[3], $row[4], $ip);
      $s = encrypt($s, $ffkey);
      $s = base64_encode($s);
      $flags = $row[4] | 2;  // online flag
      if ($isemail)
         $db->query("update ffmembers set flags=$flags where email='$bevid'");
      else
         $db->query("update ffmembers set flags=$flags where moniker='$bevid'");
      $rv["success"] = true; $rv["token"] = $s;
      die(json_encode($rv));
   }
   bail("User ID/Email and password do not match");
}
if ($isemail)
  bail("Email address '$bevid' not recognized");
else
  bail("User ID '$bevid' not recognized");
?>
