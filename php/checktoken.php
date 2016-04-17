<?php
$ffid = -1;
$ffflags = 0;
$token_ok = false;
$token_msg = "";

function check($obj, $ip)
{
   global $ffmark;
   global $token_msg;
   $marker = substr(hash('sha256', ''.$obj->expires.$ffmark), 0, 10);
   if ($marker != $obj->marker)
   {
      $token_msg = "Token not marked by this server.";
      return false;
   }
   if (time() >= $obj->expires)
   {
      $token_msg = "Token has expired.";
      return false;
   }
   /*
   // This will mmost likely fail with a mobile phone
   // Internet connection, the IP address is likely to
   // be different as the phone connects to the provider server
   if ($obj->ip != $ip)
   {
      $token_msg = "Token IP does not match REMOTE_ADDR.";
      return false;
   }
   */
   return true;
}

$ip = $_SERVER["REMOTE_ADDR"];
$authData = $_SERVER['HTTP_FF_TOKEN'];
$ct = base64_decode($authData);
$dt = decrypt( $ct, $ffkey );
$dt = trim($dt, "\0");
$a = explode(".", $dt);
$hdr = $a[0];
$hdr = base64_decode($hdr);
$payload = $a[1];
$payload = base64_decode($payload);
$sig = hash('sha256', $hdr.$payload.$ffkey);
if ($sig == $a[2])
{
   $obj = json_decode($payload);
   if (check($obj, $ip))
   {
      $ffid = $obj->id;
      $ffflags = $obj->flags;
$token_msg .= "marking OK, ";
      $token_ok = true;
      $token_msg = "Good to go.";
   }
}
else $token_msg = "Token signature invalid.";
?>