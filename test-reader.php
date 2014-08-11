<?php

require_once(__DIR__ . '/HXLReader.php');

$reader = new HXLReader(STDIN);

print_r($reader->read());