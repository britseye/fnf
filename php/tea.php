<?php
error_reporting(-1);
ini_set('display_errors', 'On');
header("Content-type: text/plain");

// use (16 chars of) 'password' to encrypt 'plaintext'
function encrypt($plaintext, $password) {
  $v = array(0, 0);
  $s = ""; $i = 0;

  //$plaintext = escape($plaintext);  // use escape() so only have single-byte chars to encode
  $k = buildkey($password);

  $len = strlen($plaintext);
  $pad = $len%8;
  $pad = $pad? 8-$pad: 0;
  $len += $pad;
  $t = "";
  for (; $pad > 0; $pad--) $plaintext .= "\0";
  for ($i = 0; $i < $len; $i += 8) {            // encode plaintext into s in 64-bit (8 char) blocks
    $v[0] = Str4ToLong(substr($plaintext, $i, 4));  // ... note this is 'electronic codebook' mode
    $v[1] = Str4ToLong(substr($plaintext, $i+4,4));
    code($v, $k);
    $c1=LongToStr4($v[0]);
    $c2=LongToStr4($v[1]);
    $s .= $c1.$c2;
  }

  return $s; //escCtrlCh($s);
}

// use (16 chars of) 'password' to decrypt 'ciphertext' with xTEA
function decrypt($ciphertext, $password) {
  $v = array(0, 0);
  $s = ""; $i = 0;
  $k = buildkey($password);

  //$ciphertext = unescCtrlCh($ciphertext);
  $len = strlen($ciphertext);
  for ($i = 0; $i < $len; $i += 8) {  // decode ciphertext into s in 64-bit (8 char) blocks
    $v[0] = Str4ToLong(substr($ciphertext, $i, 4));  // ... note this is 'ECB' mode
    $v[1] = Str4ToLong(substr($ciphertext, $i+4,4));
    decode($v, $k);
    $s .= LongToStr4($v[0]).LongToStr4($v[1]);
  }

  // strip trailing null chars resulting from filling 4-char blocks:
  //$s = preg_replace('/\0+$/', '', $s);

  return $s;//unescape($s);
}


function buildkey($pwd)
{
  $k = array();
  // build key directly from 1st 16 chars of password
  for ($i = 0; $i < 16; $i += 4) {
    $t = substr($pwd, $i, 4);
    array_push($k, Str4ToLong($t));
  }
  return $k;
}

function code(&$v, $k) {
  // Extended TEA: this is the 1997 revised version of Needham & Wheeler's algorithm
  // params: v[2] 64-bit value block; k[4] 128-bit key
  $y = $v[0]; $z = $v[1];
  $delta = 0x9E3779B9; $limit = $delta*32; $sum = 0;
  while ($sum != $limit) {
    $y += ($z<<4 ^ urshift($z, 5))+$z ^ $sum+$k[$sum & 3];
    $sum += $delta;
    $z += ($y<<4 ^ urshift($y, 5))+$y ^ $sum+$k[urshift($sum, 11) & 3];
  }
  $v[0] = $y; $v[1] = $z;
}

function decode(&$v, $k) {
  $y = $v[0]; $z = $v[1];
  $delta = 0x9E3779B9; $sum = $delta*32;

  while ($sum != 0) {
    $z -= ($y<<4 ^ urshift($y, 5))+$y ^ $sum+$k[urshift($sum,11) & 3];
    $sum -= $delta;
    $y -= ($z<<4 ^ urshift($z, 5))+$z ^ $sum+$k[$sum & 3];
  }
  $v[0] = $y; $v[1] = $z;
}

function urshift( $n , $s ) {
  return ( $n >> $s )   //Arithmetic right shift
      & ( PHP_INT_MAX >> ($s - 1 ));   //Deleting unnecessary bits
}

function uadd($a, $b)
{
   $x = $a+$b;
   return $x;
}

function Str4ToLong($s) {  // convert 4 chars of s to a 32 bit unsigned int
  $v = 0;
  $i = 0;
  $t = unpack('C*', $s);
  $v = $t[1];
  for ($i = 2; $i <= 4; $i++) {
    $v <<= 8;
    $v |= $t[$i];
  }
  return $v;
}

function LongToStr4($v) {  // convert a 32 bit unsigned int to 4 char string
  $i=0;
  $a = array(0,0,0,0);
  for ($i = 3; $i >= 0; $i--)
  {
    $a[$i] = $v & 0xff;
    $v = urshift($v, 8);
  }
  $b = array('C*');
  for ($i = 0; $i < 4; $i++)
    array_push($b, $a[$i]);
  $s = call_user_func_array("pack", $b);
  return $s;
}

function escCtrlCh($str) {  // escape control chars which might cause problems with encrypted texts
  //return str.replace(/[\0\t\n\v\f\r\xa0'"!]/g, function(c) { return '!' + c.charCodeAt(0) + '!'; });
  return $str;
}

function unescCtrlCh($str) {  // unescape potentially problematic nulls and control characters
  //return str.replace(/!\d\d?\d?!/g, function(c) { return String.fromCharCode(c.slice(1,-1)); });
  return $str;
}
/*
$ct = encrypt("the quick brown fox", "1234567890abcdef");
echo "ct: $ct\n";
echo "________________________________________________________________\n";
$rpt = decrypt($ct, "1234567890abcdef");
echo "pt: $rpt\n";
echo base64_encode($rpt)."\n";

echo "OK";
*/
?>