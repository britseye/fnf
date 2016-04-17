<?php
require_once "xlatemarkup.php";
require_once "fnfx.php";

function writePost($row, $uid, $db, $newpost)
{
   $logtype = 1;//$row[1];
   $sid = $row[0]; $owner = $row[2]; $markup = $row[3]; $iid= $row[4]; $width = $row[5]; $height = $row[6];
   $flags = $row[7]; $dep = $row[8]; $ts = $row[9]; $mtype = $row[10]; $ifn = $row[11]; $fid = $row[12]; $poster = $row[13];

   $text = xlateMarkup($markup);

   $ts = str_replace(' ', '<br>', $ts);
   $class = ($owner == $uid)? "clicktext poster": "noclicktext";

   $s = '<div id="post'.$sid.'" class="feeditem">'."\n";  // Start of the post + comments
   $s .= '<div id="postpart'.$sid.'" style="margin-bottom: 1em; width:100%;" data-owner="'.$owner.'" data-postid="'.$sid.'">'."\n";
   $s .= '<img id="pthumb'.$sid.'" class="thumb fithumb" src="';
   // Thumbnail source
   /*
   if ($logtype == 3)
      $s .= 'https://plus.google.com/s2/photos/profile/'.$fid.'?sz=50">';
   else if ($logtype == 2)
      $s .= 'https://graph.facebook.com/'.$fid.'/picture">';
   else
   */
      $s .= '../php/getthumb.php/'.$owner.'" data-owner="'.$owner.'">';
   // Poster name and details
   $s .= '<div class="posthdr">';
   $s .= '<span id="ospan'.$sid.'" data-owner="'.$owner.'"  data-postid="'.$sid.'" class="'.$class.'">'.$poster.'</span>'."\n";
   // Date/time
   $s .= '<div class="datetime" style="display:inline-flex; height:3em; vertical-align:top;">'.$ts;
   $s .= "</div>\n";
   $s .= "</div>\n";  // end of post header
   $s .= '<div id="markup'.$sid.'" style="display:none;">'.$markup."</div>\n";
   // The post text
   $s .= '<div class="posttxt" style="width:97%;" id="posttxt'.$sid.'">'.$text."</div>\n";   // end of posttext
   $s .= "</div>\n";    // End of postpart

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
            $s .= '<audio src=",,/php/getaudio.php/'.$iid.'" style="max-width:100%;" preload="metadata" controls><br>'."\n";
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
         $rv = formatFnFx($db, 'feed', $sid, $iid);
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
      $s .= "</div>\n";  // End of attachment
   }
   if ($newpost)
   {
      // This is where comments will get added
	   $s .= '<div class="sentinel"></div>'."\n";
	   $s .= '<span class="clicktext" style="cursor:pointer;" onclick="addComment('.$row[0].');">Add your comment</span><br>';
	   $s .= '<div style="border-top:solid 1px #bbbbbb; height:1em; margin-top:1em;"></div>'; // line under post
	   $s .= "\n</div>"; // end of feeditem
   }
   return "$s";
}
?>