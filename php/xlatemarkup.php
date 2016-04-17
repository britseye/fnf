<?php
$out = '';
function doLinks($t)
{
   $pattern = "/{L[^}]+L}/";
   preg_match_all($pattern, $t, $out);
   $len = count($out[0]);
   if ($len) {
      $a = $out[0];
      for ($i = 0; $i < $len; $i++)
      {
         $l = strlen($a[$i])-4;
         $ts = substr($a[$i], 2, $l);
         $ts = trim($ts);
         $pos = strpos($ts, ' ');
         if (!$pos)
            $t = str_replace($a[$i], "");
         else
         {
            $url = substr($ts, 0, $pos);
            $rest = substr($ts, $pos+1);
            $t = str_replace($a[$i], '<a href="'.$url.'" target="blank">'.$rest.'</a>', $t);
         }
      }
   }
   return $t;
}

function doList($t, $ordered)
{
   $pattern = $ordered? "/{O[^}]+O}/": "/{U[^}]+U}/";
   preg_match_all($pattern, $t, $out);
   $len = count($out[0]);
   if ($len) {
      $a = $out[0];
      $s = $ordered? '<ol style="margin-top:3px;">': '<ul style="margin-top:3px;">';
      for ($i = 0; $i < $len; $i++)
      {
         $l = strlen($a[$i])-4;
         $ts = substr($a[$i], 2, $l);
         $ts = trim($ts);
         $b = explode("\n", $ts);
         for ($j = 0; $j < count($b); $j++)
         {
            $ts = trim($b[$j]);
            $s .= "<li>".$ts."</li>``";
         }
         $s .= $ordered? "</ol>": "</ul>";
         $t = str_replace($a[$i], $s, $t);
      }
   }
   return $t;
}

function doEmphasis($t, $tbold)
{
   $pattern = $tbold? "/{B[^}]+B}/": "/{I[^}]+I}/";
   preg_match_all($pattern, $t, $out);
   $len = count($out[0]);
   if ($len) {
      $a = $out[0];
      for ($i = 0; $i < $len; $i++)
      {
         $l = strlen($a[$i])-4;
         $ts = substr($a[$i], 2, $l);
         $ts = trim($ts);
         if ($tbold)
            $t = str_replace($a[$i], '<b>'.$ts.'</b>', $t);
         else
            $t = str_replace($a[$i], '<i>'.$ts.'</i>', $t);
      }
   }
   return $t;
}

function doImage($t)
{
   $pattern = "/{P[^}]+P}/";
   preg_match_all($pattern, $t, $out);
   $len = count($out[0]);
   if ($len) {
      $a = $out[0];
      for ($i = 0; $i < $len; $i++)
      {
         $l = strlen($a[$i])-4;
         $ts = substr($a[$i], 2, $l);
         $ts = trim($ts);
         $t = str_replace($a[$i], '<img src="'.$ts.'" style="max-width:100%">', $t);
      }
   }
   return $t;
}

function doFill($t)
{
   $pattern = "/{F[^}]+F}/";
   preg_match_all($pattern, $t, $out);
   $len = count($out[0]);
   if ($len) {
      $a = $out[0];
      for ($i = 0; $i < $len; $i++)
      {
         $l = strlen($a[$i])-4;
         $ts = substr($a[$i], 2, $l);
         $ts = trim($ts);
         if ((string)(int)$ts != $ts)
            $t = str_replace($a[$i], '', $t);
         else
         {
            $n = (int)$ts;
            $ts = "";
            for ($j=0; $j < $n; $j++)
               $ts .= "&nbsp;";
            $t = str_replace($a[$i], $ts, $t);
         }
      }
   }
   return $t;
}

function doShim($t)
{
   $pattern = "/{S[^}]+S}/";
   preg_match_all($pattern, $t, $out);
   $len = count($out[0]);
   if ($len) {
      $a = $out[0];
      for ($i = 0; $i < $len; $i++)
      {
         $l = strlen($a[$i])-4;
         $ts = substr($a[$i], 2, $l);
         $ts = trim($ts);
         if ((string)(int)$ts != $ts)
            $t = str_replace($a[$i], '', $t);
         else
         {
            if ($ts{0} == '-')
               $t = str_replace($a[$i], '<div style="height:0; width:0; margin:'.$ts.'px 0 0 0;"></div>', $t);
            else if ($ts == '0')
               $t = str_replace($a[$i], '', $t);
            else
               $t = str_replace($a[$i], '<div style="height:'.$ts.'px; margin;0 0 0 0; width:0;"></div>', $t);
         }
      }
   }
   return $t;
}

function doTitle($t)
{
   $pattern = "/{H[^}]+H}/";
   preg_match_all($pattern, $t, $out);
   $len = count($out[0]);
   if ($len) {
      $a = $out[0];
      $last = $a[$len-1];
      $l = strlen($last)-4;
      $ts = substr($last, 2,$l);
      $ts = trim($ts);
      for ($i = 0; $i < $len; $i++)
         $t = str_replace($a[$i], "", $t);
      $t = '<h3>'.$ts.'</h3>'.$t;
   }
   return $t;
}

function doTable($t)
{
   $pattern = "/{T[^}]+T}/";
   preg_match_all($pattern, $t, $out);
   $len = count($out[0]);
   if ($len) {
      $a = $out[0];
      for ($i = 0; $i < $len; $i++)
      {
         $l = strlen($a[$i])-4;
         $ts = substr($a[$i], 2, $l);
         $ts = trim($ts);
         $b = explode("\n", $ts);
         $lines = count($b);
         $ts = trim($b[0]);
         $c = explode('|', $ts);
         $cols = count($c);
         $tw = $cols;
         for ($n = 0; $n < $cols; $n++)
         {
            $m = strlen($c[$n]);
            $m *= 0.8;
            $c[$n] = ''.$m.'em;';
            $tw += $m;
         }
         $ts = $b[1];
         $ts = trim($ts);
         $d = explode('|', $ts);
         $s = '<table style="width='.$tw.'em; border:solid 1px #bbbbbb; border-collapse: collapse;">'."\n<thead>\n".
               '<tr style="vertical-align:top;">';
         $s .= '<tr>';
         for ($j = 0; $j < $cols; $j++)
         {
            $s .= '<th style="width:'.$c[$j].'em;" align="left">'.$d[$j];
         }
         $s .= '</tr>';
         $s .= '</thead><tbody>';
         for ($j = 2; $j < $lines; $j++)
         {
            $ts = trim($b[$j]);
            $d = explode('|', $ts);
            $s .= '<tr style="vertical-align:top">';
            for ($k = 0; $k < $cols; $k++)
               $s .= '<td>'.$d[$k].'</td>';
            $s .= '</tr>';
         }
         $s .= '</tbody></table>';
         $t = str_replace($a[$i], $s, $t);
      }
   }
   return $t;
}

function xlateMarkup($text)
{
   $text = str_replace('&', '&amp;', $text);
   $text = str_replace('<', '&lt;', $text);
   $text = str_replace('>', '&gt;', $text);
   $text = str_replace("'", '&apos;', $text);
   $text = str_replace('"', '&quot;', $text);
   $text = str_replace("\r", '', $text);
   $text = doTitle($text);
   $text = doEmphasis($text, true);
   $text = doEmphasis($text, false);
   $text = doLinks($text);
   $text = doList($text, true);
   $text = doList($text, false);
   $text = doImage($text);
   $text = doShim($text);
   $text = doFill($text);
   $text = doTable($text);
   $text = str_replace("\n\n", '<p>', $text);
   $text = str_replace("\n", '<br>', $text);
   $text = str_replace("``", "\n", $text);
   return $text;
}
?>