<?php

namespace Root\Composer\Lib\Helpers;

function escape($text)
{
  $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  return $text;
}
