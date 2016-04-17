<?php
$dbhost = 'localhost';
$dbuser = 'whatever';
$dbpwd = 'whatever';
$dbdefault = 'whatever';

$ffkey="whatever";
$ffmark="whatever";

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
