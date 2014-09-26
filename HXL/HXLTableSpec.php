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
    $colSpecs = array();
  }

  public function add($colSpec) {
    array_push($this->colSpecs, $colSpec);
  }

}