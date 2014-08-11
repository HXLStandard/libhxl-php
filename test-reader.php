<?php

require_once(__DIR__ . '/HXLReader.php');

$reader = new HXLReader(STDIN);

while ($row = $reader->read()) {
  print_r($row);
}