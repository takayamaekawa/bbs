<?php

namespace Root\Composer\Lib\Helpers;

class Random
{
  /**
   * セキュアなランダムトークンを生成
   */
  public static function generateSecureToken(int $length = 32): string
  {
    return bin2hex(random_bytes($length / 2));
  }
}

function onemakeRandStr($length)
{
  $str = array_merge(range('A', 'Z'));
  $r_str = null;
  for ($i = 0; $i < $length; $i++) {
    $r_str .= $str[rand(0, count($str) - 1)];
  }
  return $r_str;
}

function makeRandStr($length)
{
  $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
  $r_str = '';
  for ($i = 0; $i < $length; $i++) {
    $r_str .= $str[rand(0, count($str) - 1)];
  }
  return $r_str;
}
