<?php
$rv=array();
function bail($msg)
{
   global $rv;
   $rv["success"] = false;
   $rv["errmsg"] = $msg;
   $s = json_encode($rv);
   die($s);
}
?>