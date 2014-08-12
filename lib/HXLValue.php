<?php

require_once(__DIR__ . '/HXLColumn.php');

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
  public $source_col_number;

  /**
   * Public constructor.
   *
   * @param $column The column definition.
   * @param $content The cell content (e.g. a number or string).
   * @param $col_number The logical (HXL) column number (zero-based, null if unspecified).
   * @param $source_col_number The column number in the original soruce (zero-based, null if unspecified).
   */
  public function __construct(HXLColumn $column, $content, $col_number = null, $source_col_number = null) {
    $this->column = $column;
    $this->content = $content;
    $this->col_number = $col_number;
    $this->source_col_number = $source_col_number;
  }
}
