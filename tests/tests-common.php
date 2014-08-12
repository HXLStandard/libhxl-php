<?php

require_once(__DIR__ . '/../lib/HXLReader.php');

function open_data($filename) {
  return fopen(__DIR__ . '/data/' . $filename, 'r');
}