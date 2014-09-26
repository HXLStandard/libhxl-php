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

  public $source_col_number;

  public $column;

  public $fixedColumn;

  public function __construct($source_col_number, $column = null, $fixedColumn = null) {
    $this->source_col_number = $source_col_number;
    $this->column = $column;
    $this->fixedColumn = $fixedColumn;
  }

}