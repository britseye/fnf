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
   bail("Operation blocked.<br>Your security token is invalid ($token_msg)");
include "writemessage.php";

$db = dbConnect();
if (!$db) bail(sql_error());

$toid = $_POST["msgformto"];
$fromid = $_POST["msgformfrom"];
$fromname = $_POST["msgformname"];
$origbody = $_POST["newmsg"];
$mbody = $db->real_escape_string($origbody);

$havemedia = false;
$imgsize = 0;
$imgtype = 0;
$width = 0;
$height = 0;
$tmpname = "";
$filecount = count($_FILES);
$fname = "";
$ext='';
$nfn='';
if ($filecount)  // See what we got
{
   $imgsize = $_FILES["msgfile"]["size"];
   if ($imgsize > 0 && $_FILES["msgfile"]["error"] > 0) bail("Image transfer error");
   if ($imgsize > 0)
   {
      $tmpname = $_FILES['msgfile']['tmp_name'];
      $fname = $_FILES['msgfile']['name'];
      $a = explode('.', $fname);
      if (count($a) != 2)
         bail("Uploaded file name has multiple periods\nshould be just filename.ext.");
      $ext = $a[1];
      $fname = $a[0];
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
         $tmpname = $_FILES['msgfile']['tmp_name'];
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

if (strlen($mbody) == 0) bail("Post has no text");

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
         if (move_uploaded_file($_FILES['msgfile']['tmp_name'], "../ff_uploads/".$nfn));
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
      if (move_uploaded_file($_FILES['msgfile']['tmp_name'], "../ff_uploads/".$nfn));
      if (!$db->query("insert into ffimages (iid, type, imgdata, filename) values (null, 8, null, '$nfn')"))
         bail($db->error);
      $iid = $db->insert_id;
   }
   else
      bail("File type is garbage.");
}

$db->query("SET @mid = 0");
$db->query("SET @ts = ''");
if (!$db->query("CALL insertmsg($fromid, $toid, '$mbody', $imgtype, $iid, @mid, @ts)"))
   bail($db->error);
$result = $db->query("SELECT @mid");
$row = $result->fetch_row();
$mid = $row[0];
$result = $db->query("SELECT @ts");
$row = $result->fetch_row();
$ts = $row[0];

//function writeMsg($db, $cu, $mid, $fid, $tid, $from, $text, $ts, $mtype, $iid, $ifn)
$html = writeMsg($db, $fromid, $mid, $fromid, $toid, $fromname, $origbody, $ts, $imgtype, $iid, $nfn);

$rv["success"] = true; $rv["mid"] = $mid;
$rv["html"] = $html;
echo json_encode($rv);
?>

