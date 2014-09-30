<?php

/**
 * A column specification for parsing a HXL CSV file
 *
 * This class captures the way a column is encoded in the input CSV,
 * which might be different from the logical structure of the HXL data
 * (for example, if the source is using compact-disaggregated notation
 * to represent more than one logical HXL column in a single CSV
 * column). This data isn't normally exposed to a client.
 *
 * @author David Megginson
 * @since September 2014.
 */
class HXLColSpec {

  /**
   * Zero-based column number in the original CSV
   */
  public $sourceColumnNumber;

  /**
   * The logical variable column (actual data)
   */
  public $column;

  /**
   * The logical fixed-value column, if using compact disaggregated notation
   */
  public $fixedColumn;

  /**
   * The fixed value (from the header), if using compact-disaggregated notation
   */
  public $fixedValue;

  /**
   * Constructor
   *
   * @param $sourceColumnNumber
   * @param $column
   * @param $fixedColumn
   * @param $fixedValue
   */
  public function __construct($sourceColumnNumber, $column = null, $fixedColumn = null, $fixedValue = null) {
    $this->sourceColumnNumber = $sourceColumnNumber;
    $this->column = $column;
    $this->fixedColumn = $fixedColumn;
  }

}

// end
