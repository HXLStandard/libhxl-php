<?php

/**
 * A single HXL value at the intersection of a row and column.
 *
 * Started by David Megginson, August 2014.
 */
class HXLValue {

  /**
   * The column definition (HXLColumn).
   */
  public $column;

  /**
   * The column content.
   */
  public $content;

  /**
   * The logical (HXL) column number (zero-based, null if unspecified).
   */
  public $col_number;

  /**
   * The column number in the original source (zero-based, null if unspecified).
   */
  public $sourceColumnNumber;

  /**
   * Public constructor.
   *
   * @param $column The column definition.
   * @param $content The cell content (e.g. a number or string).
   * @param $col_number The logical (HXL) column number (zero-based, null if unspecified).
   * @param $sourceColumnNumber The column number in the original soruce (zero-based, null if unspecified).
   */
  public function __construct(HXLColumn $column, $content, $col_number = null, $sourceColumnNumber = null) {
    $this->column = $column;
    $this->content = $content;
    $this->col_number = $col_number;
    $this->sourceColumnNumber = $sourceColumnNumber;
  }
}
