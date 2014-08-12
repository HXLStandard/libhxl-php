<?php

require_once(__DIR__ . '/../HXL/HXL.php');

function open_data($filename) {
  return fopen(__DIR__ . '/data/' . $filename, 'r');
}