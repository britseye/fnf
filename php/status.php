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
   bail("Operation blocked.\nYour security token is invalid ($token_msg)");

require_once "writepost.php";

$db = dbConnect();
if (!$db) bail(sql_error());

$status = $db->real_escape_string($_POST["status"]);
$contrib = $_POST["contribid"];
$logtype = $_POST["logtype"];
$dept = 0;//$_POST["dept"];

$havemedia = false;
$imgsize = 0;
$imgtype = 0;
$width = 0;
$height = 0;
$tmpname = "";
$filecount = count($_FILES);
$fname= "";
$ext='';
$nfn='';
if ($filecount)  // See what we got
{
   $imgsize = $_FILES["file"]["size"];
   if ($imgsize > 0 && $_FILES["file"]["error"] > 0) bail("Image transfer error");
   if ($imgsize > 0)
   {
      $tmpname = $_FILES['file']['tmp_name'];
      $fname = $_FILES['file']['name'];
      $a = explode('.', $fname);
      $ext = array_pop($a);
      $ext = strtolower($ext);
      if (strpos(".txt.c.cpp.c++.d.js.php.html.htm.xml.cs.py.java.diff.rb", $ext))
         $imgtype = 7;
      else if ($ext == "fnfx")
         $imgtype = 6;
      else if ($ext == "mp4" || $ext == "webm")
         $imgtype = 5;
      else if ($ext == "mp3" || $ext == "wav")
         $imgtype = 4;
      else if ($ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "gif")
      {
         $tmpname = $_FILES['file']['tmp_name'];
         $isd = getimagesize($tmpname);
         $imgtype = $isd[2];
         $width = $isd[0];
         $height = $isd[1];
      }
      else
         $imgtype = 8;
      $havemedia = true;
   }
}
$iid = 0;

if (strlen($status) == 0) bail("Post has no text");

if ($havemedia)
{
   if ($imgtype < 8)  // we are not bothered about preserving the file name
   {
      $instr = fopen($tmpname,"rb");
      $image = addslashes(fread($instr,filesize($tmpname)));
      if (strlen($image) < 16777215)  // will it fit in mediumblob
      {
         if (!$db->query("insert into ffimages (iid, owner, width, height, type, imgdata) values (NULL, NULL, $width, $height, $imgtype, '$image')"))
            bail($db->error);
         $iid = $db->insert_id;
      }
      else
      {
         $result = $db->query("select max(iid) from ffimages");
         if (!$result)
            bail($db->error);
         $row = $result->fetch_row();
         $nfn = "".time()."-".$row[0].'.'.$ext;
         if (move_uploaded_file($_FILES['file']['tmp_name'], "../ff_uploads/".$nfn));
         if (!$db->query("insert into ffimages (iid, type, imgdata, filename) values (null, $imgtype, null, '$nfn')"))
            bail($db->error);
         $iid = $db->insert_id;
      }
   }
   else if ($imgtype == 8)  // anything else
   {
      $result = $db->query("select max(iid) from ffimages");
      if (!$result)
         bail($db->error);
      $row = $result->fetch_row();
      $nfn = "(".time()."-".$row[0].')'.$fname;
      if (move_uploaded_file($_FILES['file']['tmp_name'], "../ff_uploads/".$nfn));
      if (!$db->query("insert into ffimages (iid, type, imgdata, filename) values (null, 8, null, '$nfn')"))
         bail($db->error);
      $iid = $db->insert_id;
   }
   else
      bail("File type is garbage.");
}

$query =
"insert into fffeed (id, logtype, owner, text, iid, width, height, flags, instance, mtype, ifn) values ".
"(NULL, $logtype, $contrib, '$status', $iid, $width, $height, 0, $dept, $imgtype, '$nfn')";
if (!$db->query($query))
   bail($db->error);
$id = $db->insert_id;
$s = "Aaaargh";
// Now read it back, and use writepost to format it
$pquery =
"select fffeed.*, ffmembers.fid, ffmembers.name from fffeed LEFT JOIN ffmembers ON fffeed.owner=ffmembers.id ".
"where fffeed.id=$id";

$result = $db->query($pquery);
if (!$result) bail($db->error);
$s = "";

if ($result->num_rows)
{
   $row = $result->fetch_row();
   $s = writePost($row, $contrib, $db, true);
   $s .= "\n";
}
else
   bail("Post not found");
$rv["success"] = true; $rv["sid"] = $id; $rv["logtype"] = $logtype; $rv["iid"] = $iid; $rv["width"] = $width; $rv["height"] = $height;
$rv["text"] = $s; $rv["mtype"] = $imgtype; $rv["ifn"] = $nfn;
echo json_encode($rv);
?>
