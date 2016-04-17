<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include "not4everyone.php";
include_once "constants.php";
include "tea.php";

if (mail("ssteale@gmail.com", "Subject", "Body text", "From: pwdreset@ffcentral.org"))
   echo "OK";
else
   echo "NO";
?>