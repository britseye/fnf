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
   bail("Operation blocked.\n($token_msg)");

function nb($s)
{
   return (trim($s) != '');
}

function imageToThumb($srcFile, $size) {
    list($wo, $ho, $type) = getimagesize($srcFile);

    // Temporarily increase the memory limit to allow for larger images
    ini_set('memory_limit', '32M');
    switch ($type)
    {
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($srcFile);
            break;
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($srcFile);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($srcFile);
            break;
        default:
            throw new Exception('Unrecognized image type ' . $type);
    }

    // create a new blank image of the thumbmail size
    $newImage = imagecreatetruecolor($size, $size);

    // portrait
    if ($wo > $ho)
    {
        $x = $wo/2-$ho/2;
        imagecopyresampled($newImage, $image, 0, 0, $x, 0, $size, $size, $ho, $ho);
    }
    else
    {
        $y = $ho/2-$wo/2;
        imagecopyresampled($newImage, $image, 0, 0, 0, $y, $size, $size, $wo, $wo);
    }
    imagedestroy($image);
    unlink($srcFile);

    imagejpeg($newImage, $srcFile);
    imagedestroy($newImage);

    if ( is_file($srcFile) ) {
        $f = fopen($srcFile, 'rb');
        $data = fread($f, 100000);
        fclose($f);
        unlink($srcFile);
        return $data;
    }
    throw new Exception('Image conversion failed.');
}

$db = dbConnect();
if (!$db) die(sql_error());

$phone = $db->real_escape_string($_POST["exphone"]);
$email = $db->real_escape_string($_POST["exemail"]);
$dob = $db->real_escape_string($_POST["exdob"]);
$address1 = $db->real_escape_string($_POST["exaddress1"]);
$address2 = $db->real_escape_string($_POST["exaddress2"]);
$city = $db->real_escape_string($_POST["excity"]);
$region = $db->real_escape_string($_POST["exregion"]);
$postcode = $db->real_escape_string($_POST["expostcode"]);
$country = $db->real_escape_string($_POST["excountry"]);
$hru = $db->real_escape_string($_POST["exhru"]);
$uid = $_POST["exid"];
$hidp = $_POST["exppic"];
$hidm = $_POST["exmpic"];
$currentpic = $_POST["excurrentpic"];

$imgsize = 0;
$expected = 0;
if ($hidp == "Y")
   $expected++;
if ($hidm == "Y")
   $expected++;
$filecount = count($_FILES);

// What to do in this case, browsers apparently vary.
if ($filecount != $expected)
   die("NO+Expected $expected files ($hidp, $hidm), got $filecount");

$iid = -1;
$width = 0;
$height = 0;
if ($hidp == "Y")
{
   $imgsize = $_FILES["expic"]["size"];
   if ($imgsize == 0) bail("Unexpected zero image size");
   if ($imgsize > 0 && $_FILES["expic"]["error"] > 0) bail("Image transfer error");
   $tmpname = $_FILES['expic']['tmp_name'];
   $isd = getimagesize($tmpname);
   $imgtype = $isd[2];
   $width = $isd[0];
   $height = $isd[1];
   if ($imgtype < 4 && $imgsize < 2000000)
   {
      $instr = fopen($tmpname,"rb");
      $image = addslashes(fread($instr,filesize($tmpname)));
      if (strlen($image) < 16777000)
      {
         if (!$db->query("insert into ffimages (iid, owner, width, height, type, imgdata) values (NULL, NULL, $width, $height, $imgtype, '$image')"))
            bail($db->error);
         $iid = $db->insert_id;
         // Not vital, but ...
         if ($currentpic != -1)
            $db->query("delete from ffimages where iid=$currentpic");
      }
      else
      {
         bail("Picture image is too big");
      }
   }
   else
   {
      bail("NPicture image type is not supported, or image too big");
   }
}

$imgsize = 0;
if ($hidm == "Y")
{
   $imgsize = $_FILES["exmug"]["size"];
   if ($imgsize == 0) bail("Unexpected zero image size");
   if ($imgsize > 0 && $_FILES["exmug"]["error"] > 0) bail("Image transfer error");
   if ($imgsize > 200000)
      bail("Image too big $imgsize");
   $tmpname = $_FILES["exmug"]["tmp_name"];
   list($width, $height, $imgtype) = getimagesize($tmpname);
   if ($imgtype < 4)
   {
      try {
         $img = imageToThumb($tmpname, 50);
      }
      catch (Exception $e) {
         bail("Image conversion failed");
      }
      $img = addslashes($img);
      if (!$db->query("update ffmembers set imgtype=$imgtype, mugshot='$img', mts=null where id=$uid"))
         bail($db->error);
   }
   else bail("Unsupported image type");
}

// See if the database record for this user exists
$result = $db->query("select uid from ffudetail WHERE uid=$uid");
if(!$result) bail($db->error);
$n = $result->num_rows;

if (!$n)  // So create a blank entry
{
   $n = 1;
   $db->query("insert into ffudetail values($uid, '','','','','','','','','Fine', -1)");
}
if ($n == 1)
{
   $r = $result->fetch_row();
   $udq =
"update ffudetail set dob='$dob', phone='$phone', address1='$address1', address2='$address2', ".
"city='$city', region='$region', postcode='$postcode', country='$country', hru='$hru'";
   if ($iid != -1)
      $udq .= ", imageid=$iid";
   $udq .= " where uid=$uid";
   if (!$db->query($udq))
      bail($db->error);
   if (trim($email) != '')  // If we have an email adress we'll keep  it
   {
      if (!$db->query("update ffmembers set email='$email' where id=$uid"))
         bail($db->error);
   }
}
else
   bail("Ambiguous query result, more than one row for user $uid");

$rv["success"] = true; $rv["uid"] = $uid;
echo json_encode($rv);
?>
