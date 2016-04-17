<?php
function check4SafeJS($target)
{
   $pattern1 = '~function\s+([$_a-zA-Z]*?)\s*\(~';
   $pattern2 = '~([$_.0-9a-zA-Z]*?)\s*\(\s*.*?\s*\)~';
   $pattern3 = '~\b(window|Window|document|location|frames|history|localStorage|sessionStorage|onpopstate)\b~';
   $a = array();
   preg_match_all($pattern3, $target, $a);
   //print_r($a);
   if (count($a[0]))
      return false;

   preg_match_all($pattern1, $target, $a);
   //print_r($a);
   $asml = $a[1];

   preg_match_all($pattern2, $target, $a);
   if (!count($a[0]))
      return true;
   //print_r($a);

   $asm = $a[1];
   $clean = true;

   for ($i = 0; $i < count($asm); $i++)
   {
      $ok = false;
      if ($asm[$i] == "if" || $asm[$i] == "for" || $asm[$i] == "while")
         continue;
      if (substr($asm[$i],0,4) =='ctx.')
         continue;
      if (substr($asm[$i],0,3) =='a1.')
         continue;
      if (substr($asm[$i],0,3) =='a2.')
         continue;
      if (substr($asm[$i],0,5) =='Math.')
         continue;
      if ($asm[$i] == "alert") // handy for debugging
         continue;
      for ($j = 0; $j < count($asml); $j++)
      {
         if ($asm[$i] == $asml[$j])
         {
            $ok = true;
            break;
         }
      }
      if ($ok) continue;
      return false;
   }
   return true;
}

function formatFnFx($db, $context, $id, $iid)
{
   $result = $db->query("select imgdata from ffimages where iid=$iid");
   if (!$result) bail($db->error);
   $row = $result->fetch_row();
   $text = $row[0];
   $text = str_replace("\r\n", "\n", $text);
   $lines = explode("\n", $text);
   $xtype = $lines[0];
   if ($xtype == "linklist")
   {
      $s = "<ul>\n";
      for ($i = 1; $i < count($lines); $i++)
      {
         $line = $lines[$i];
         if (!strlen($line)) break;
         $n = strpos($line, " ");
         $url = substr($line, 0, $n);
         $caption = substr($line, $n+1);
         $s .= '<li style="margin-bottom:1em;"><a href="'.$url.'" class="clicktext" target="blank">'.$caption."</a></li>\n";
      }
      $s .= "</ul>\n";
      return $xtype.':'.$s;
   }
   else if ($xtype == 'html')
   {
      if (strpos($text, '<script') > 0)
         $therest = 'You can not use script blocks in the HTML';
      else
         $therest = substr($text, strlen($xtype)+1);
      return $xtype.':'.$therest;
   }
   else if ($xtype == 'canvas')
   {
      $a = explode(',', $lines[1]);
      $w = trim($a[0]);
      $h = trim($a[1]);
      $border = trim($a[2]);
      $params = $lines[2];
      $s = '<canvas style="border:'.$border.'px solid #bbbbbb;" id="fnfx_'.$context.'canvas'.$id.'" width="'.$w.'" height="'.$h.'"></canvas>'."\n";
      $s .= '<script>'."\n";
      $s .= "function draw(pa) {\n";
      $s .= 'var cw = '.$w.', ch = '.$h.";\n";
      $s .= "var a1 = [], a2= [];\n";
      $s .= 'var canvas = document.getElementById("fnfx_'.$context.'canvas'.$id.'");'."\n";
      $s .= 'var ctx = canvas.getContext("2d");'."\n";
      $ts = "";
      for ($i = 3; $i < count($lines); $i++)
         $ts .= $lines[$i]."\n";
      if (!check4SafeJS($ts))
         return $xtype.':'.'Drawing script contains unlikely functions/objects';
      else
         $s .= $ts;
      $s .= "} // end of draw function\n";
      $s .= "try {\n";
      $s .= 'draw(['.$params.']);}'."\n";
      $s .= 'catch (err) { alert(err.message); }';
      $s .= "</script>\n";
      return $xtype.':'.$s;
   }
   return "";
}
?>