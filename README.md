# Humanitarian Exchange Language (HXL) PHP Library

Started by David Megginson, August 2014


## Description

PHP5 library for parsing HXL-tagged data.  For more information about HXL, see http://docs.hdx.rwlabs.org/hxl


## Usage

```php
require_once('HXL/HXL.php');

$input = fopen('MyFile.csv', 'r');
$hxl = new HXLReader($input);

foreach ($hxl as $row) {
  printf("Row %d:\n", $row->rowNumber);
  foreach ($row as $value) {
    printf(" %s=%s\n", $value->header->tag, $value->content);
  }
}

fclose($input);
```

## Requirements

* PHP5
* PHPUnit to run unit tests.
