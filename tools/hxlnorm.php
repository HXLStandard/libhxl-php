<?php
/**
 * Normalise a HXL dataset.
 *
 * Usage:
 *
 * php hxlnorm.php < INFILE.csv > OUTFILE.csv
 *
 * Started by David Megginson, 2014-09-26
 */

require_once(__DIR__ . '/../HXL/HXL.php');

/**
 * Write the header and tag rows.
 */
function write_headers(HXLRow $row) {

  // header row
  $row_out = array();
  foreach ($row as $value) {
    if ($value->column->hxlTag) {
      array_push($row_out, $value->column->headerText);
    }
  }
  fputcsv(STDOUT, $row_out);

  // tag row
  $row_out = array();
  foreach ($row as $value) {
    if ($value->column->hxlTag) {
      array_push($row_out, $value->column->getDisplayTag());
    }
  }
  fputcsv(STDOUT, $row_out);
}

/**
 * Write a row of data.
 */
function write_data(HXLRow $row) {
  $row_out = array();
  foreach ($row as $value) {
    if ($value->column->hxlTag) {
      array_push($row_out, $value->content);
    }
  }
  fputcsv(STDOUT, $row_out);
}

//
// Main loop
//
$hxl = new HXLReader(STDIN);
$done_headers = false;

foreach ($hxl as $row) {
  if (!$done_headers) {
    // We haven't written the header rows yet
    write_headers($row);
    $done_headers = true;
  }
  write_data($row);
}

exit(0);

// end
