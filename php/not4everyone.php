<?php
$dbhost = 'localhost';
$dbuser = 'softcent_admin';
$dbpwd = 'friday@730';
$dbdefault = 'softcent_britseye';

$ffkey="e3b0c44298fc1c14";
$ffmark="9afbf4";

$MYSQL_ERROR = '';

function dbConnect()
{
	global $dbhost, $dbuser, $dbpwd, $dbdefault;
	global $MYSQL_ERROR;

	$db = new mysqli($dbhost, $dbuser, $dbpwd, $dbdefault);

   if ($db->connect_errno > 0) {
      $MYSQL_ERROR = 'Unable to connect to database: '.$db->connect_error;
		return null;
	}

	return $db;
}

function sql_error()
{
	return $MYSQL_ERROR;
}
?>