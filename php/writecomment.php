<?php
require_once "xlatemarkup.php";

function writeComment($row, $uid)
{
   $logtype = $row[2];
   $cid = $row[0]; $sid = $row[1];  $owner = $row[3]; $markup = $row[4];
          $ts = $row[5]; $fbid = $row[6]; $name = $row[7];
   $text= xlateMarkup($markup);
   $class = ($owner == $uid)? "clicktext commenter": "noclicktext";

   $s = '      <div id="commentdiv'.$cid.'" class="comment" data-owner="'.$owner.'" data-postid="'.$cid.'">'."\n";
   // Small thumbnail
   $s .= '            <img id="cthumb'.$cid.'" class="smallthumb fithumb" src="';
   /*
   if ($logtype == 3)
      $s .= 'https://plus.google.com/s2/photos/profile/'.$fid.'?sz=50">';
   else if ($logtype == 2)
      $s .= 'https://graph.facebook.com/'.$fid.'/picture">';
   else
   */
      $s .= '../php/getthumb.php/'.$owner.'" data-owner="'.$owner.'">';
   $s .= "\n";
      // Comment owner and commenter name
   $s .= '   <span id="ocspan'.$cid.'" data-owner="'.$owner.'" data-postid="'.$cid.'" class="'.$class.'">'.$name.' </span>'."\n";
   // Park the markup somewhere so it can be edited withoout a call to the server
   $s .= '<div id="cmarkup'.$cid.'" style="display:none;">'.$markup."</div>\n";
   $s .= '   <div id="comspan'.$cid.'" class="comtxt" data-markup="'.$markup.'">'.$text."</div>\n";
   $s .= "</div>\n";
   $s .= '<div style="height:1px; clear:both;"></div>'."\n";
   return $s;
}
?>