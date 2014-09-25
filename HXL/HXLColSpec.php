<?php

/**
 * A column specification in a HXL file.
 *
 * Might be using compact disaggregated notation to represent more
 * than one logical HXL column.
 *
 * Started by David Megginson, September 2014.
 */
class HXLColSpec {

  public $column;

  public $fixedColumn;

  public function __construct($column, $fixedColumn = null) {
    $this->column = $column;
    $this->fixedColumn = $fixedColumn;
  }

}