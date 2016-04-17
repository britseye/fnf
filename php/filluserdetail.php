<?php
error_reporting(-1);
ini_set('display_errors', 'On');

include_once "not4everyone.php";
include_once "constants.php";
include_once "tea.php";
include "bail.php";
include_once "checktoken.php";
header("Content-type: text/plain");
if (!$token_ok)
   bail("Content blocked.\nYour security token is invalid\n($token_msg)");

function writeUD($ra, $cu)
{
$dob=$ra[1];
$phone=$ra[2];
$ad1=$ra[3];
$ad2=$ra[4];
$city=$ra[5];
$region=$ra[6];
$postcode=$ra[7];
$country=$ra[8];
$hru=$ra[9];
$email=$ra[11];
$name=$ra[12];

$t = "";
if ($ra[10] != -1)
   $t .=  '<img style="max-width:100%" src="../php/getimg.php/'.$ra[10].'">'."\n";
else
   $t .= "(No user picture)<p>\n";
$t .= '<input type="hidden" id="hidename" value="'.$name.'">'."\n";
$t .= '<input type="hidden" id="hidepicid" value="'.$ra[10].'">'."\n";
$t .= '<div class="detail">';

$t .= <<<EOD
<table style="width:100%;">
<tr style="vertical-align:top"><td>
Disposition:
</td><td>$hru</td></tr>
<tr><td>
Email:
</td><td>$email</td></tr>
<tr><td>
DOB:
</td><td>$dob</td></tr>
<tr><td>
Phone:
</td><td>$phone</td></tr>
<tr style="vertical-align:top"><td>
Addr 1:
</td><td>$ad1</td></tr>
<tr style="vertical-align:top"><td>
Addr 2:
</td><td>$ad2</td></tr>
<tr><td>
City:
</td><td>$city</td></tr>
<tr><td>
Region:
</td><td>$region</td></tr>
<tr><td>
Postcode:
</td><td>$postcode</td></tr>
<tr><td>
Country:
</td><td>$country</td></tr>
</table>
EOD;
$t .= "</div>\n";
return $t;
}

$uid = $_GET["uid"];

$db = dbConnect();
if (!$db) bail(sql_error());
$query =
"select ffudetail.*, ffmembers.email, ffmembers.name from ffudetail LEFT JOIN ffmembers ON ffudetail.uid=ffmembers.id where ffudetail.uid=$uid";
$result = $db->query($query);
if (!$result) bail($db->error);
$n = $result->num_rows;
$s="";
if (!$n)
{
   $result = $db->query("select email, name from ffmembers where id=$uid");
   if (!$result) bail($db->error);
   $r = $result->fetch_row();
   $a = array(0,'','','','','','','','','Fine',-1,$r[0],$r[1]);
   $s = writeUD($a, $uid);
}
else if ($n == 1)
{
   $row = $result->fetch_row();
   $s = writeUD($row, $uid);
}
else
   bail("Ambiguous, more than one user detail row for same user");

   $rv["success"] = true; $rv["text"] = $s;
echo json_encode($rv);
?>
