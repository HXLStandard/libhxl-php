<?php

require_once(__DIR__ . '/HXL.php');

/**
 * Specification for parsing a HXL data table.
 *
 * Started 2014-09-26 by David Megginson
 */
class HXLTableSpec {

  public $colSpecs;

  public function __construct() {
    $this->colSpecs = array();
  }

  /**
   * Add a new column spec to the table spec.
   */
  public function add($colSpec) {
    array_push($this->colSpecs, $colSpec);
  }

  /**
   * Count the number of compact disaggregation columns.
   */
  public function getDisaggregationCount() {
    $n = 0;
    foreach ($this->colSpecs as $colSpec) {
      if ($colSpec->fixedColumn) {
        $n++;
      }
    }
    return $n;
  }

  public function getFixedPos($n) {
    $pos = 0;
    foreach ($this->colSpecs as $colSpec) {
      if ($colSpec->fixedColumn) {
        if ($n == 0) {
          return $pos;
        } else {
          $n--;
        }
      }
      $pos++;
    }
    return -1;
  }

}
