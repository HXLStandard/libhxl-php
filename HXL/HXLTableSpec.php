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
   * Test whether this table uses compact disaggregation.
   */
  public function hasCompactDisaggregation() {
    foreach ($this->colSpecs as $colSpec) {
      if ($colSpec->fixedColumn) {
        return true;
      }
    }
    return false;
  }

}