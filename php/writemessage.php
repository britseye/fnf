<?php
require_once "xlatemarkup.php";
require_once "fnfx.php";

function writeMsg($db, $cu, $mid, $fid, $tid, $from, $markup, $ts, $mtype, $iid, $ifn)
{
   $text = xlateMarkup($markup);

   $s = '<div class="msgitem" id="m'.$mid.'" style="margin-bottom:1em;" data-ifn="'.$ifn.'">'."\n";
   // Poster name and details
   $sclass = ($fid == $cu)? " from": "";
   $s .= '<span class="clicktext" id="sns'.$mid.'" style="cursor:pointer;" onclick="exposeDelDiv('.$mid.', '.$mtype.', '.$iid.');">'.$from.'</span>'."\n";
   // Date/time
   $s .= '      <span class="datetime">('.$ts.')</span>'."\n";
   // The post text
   $s .= '      <div class="posttxt" style="margin:10px 0 10px 0;">'.$text.'</div>'."\n";
   $s .= '<div id="deldiv'.$mid.'" style="display:none; padding:5px; border:solid 3px red; margin-bottom:0.8em"></div>'."\n";

   if ($iid > 0)   // Yup, media of some sort
   {
      $s .= '<div style="margin-bottom: 1.2em;">';
      if ($mtype && $mtype < 4)
      {
         $s .= '<img src="../php/getimg.php/'.$iid.'" style="max-width:100%;"><br>';
      }
      else if ($mtype == 4)
      {
         if ($ifn != '')
            $s .= '<audio style="max-width:100%;" src="../ff_uploads/'.$ifn.'" preload="metadata" controls><br>'."\n";
         else
            $s .= '<audio src="../php/getaudio.php/'.$iid.'" style="max-width:100%;" preload="metadata" controls><br>'."\n";
      }
      else if ($mtype == 5)
      {
         if ($ifn != '')
            $s .= '<video style="max-width:100%;" src="../ff_uploads/'.$ifn.'" preload="metadata" controls><br>'."\n";
         else
            $s .= '<video src="../php/getvideo.php/'.$iid.'" style="max-width:100%;" preload="metadata" controls><br>'."\n";
      }
      else if ($mtype == 6)
      {
         $rv = formatFnFx($db, 'msg', $mid, $iid);
         $t = strpos($rv, ':');
         $xtype = substr($rv, 0, $t);
         $xtext = substr($rv, $t+1);
         if ($xtype == "linklist")
            $s .= $xtext;
         else if ($xtype == "html")
            $s .= $xtext;
         else if ($xtype == "canvas")
            $s .= $xtext;
      }
      else if ($mtype == 7)
      {
         $result = $db->query("select imgdata from ffimages where iid=$iid");
         if (!$result) bail($db->error);
         $row = $result->fetch_row();
         $text = $row[0];
         $s .= '<textarea rows="15" spellcheck="false" style="width:97%; margin-bottom:1em;">'.$text.'</textarea>';
      }
      else if ($mtype == 8)
      {
         $s .= '<a class="clicktext" style="margin-bottom: 0.5em;" href="../ff_uploads/'.$ifn.'">Download</a><p>'."\n";
         $t = strpos($ifn, ")");
         $t = substr($ifn, $t+1);  // Strip off the uniquifing stuff
         $s .= 'File name: '.$t."<br>\n";
         $s .= '(Be cautious if the sender has not mentioned this name.)<br>';
         $s .= '<div style="border-bottom:solid 1px #bbbbbb; height: 0.5em;"></div>'."\n";
      }
   }
   $s .= "</div>\n";
   return $s;
}
?>