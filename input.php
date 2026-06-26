<?php
function tcp_bounded_utf8($value, $maxCharacters = 100) {
  if (!is_string($value) || !is_int($maxCharacters) || $maxCharacters < 1) {
    return '';
  }

  $character = '(?:[\x00-\x7F]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE-\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})';
  $pattern = '/\A(' . $character . '{0,' . $maxCharacters . '})/s';
  if (preg_match($pattern, $value, $matches) !== 1) {
    return '';
  }

  $prefix = $matches[1];
  if (strlen($prefix) === strlen($value)) {
    return $prefix;
  }

  return preg_match_all('/' . $character . '/s', $prefix) === $maxCharacters
    ? $prefix
    : '';
}
