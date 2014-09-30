<?php

/**
 * A row of data in a HXL file.
 *
 * This class implements the Iterator interface, so you can process
 * all of the values in a row using foreach.
 *
 * Started by David Megginson, August 2014
 */
class HXLRow implements Iterator {

  /**
   * The row data (an array of HXLValue objects).
   */
  public $data;

  /**
   * The logical (HXL) row number.
   */
  public $rowNumber;

  /**
   * The row number in the original source file.
   */
  public $sourceRowNumber;

  /**
   * Index used by Iterator methods.
   */
  private $iterator_index = -1;

  /**
   * Public constructor.
   *
   * @param $data An array of HXLValue objects representing the row's content
   * @param $rowNumber The logical (HXL) row number, or null if unspecified.
   * @param $sourceRowNumber The row number in the original source,
   * or null if unspecified.
   */
  public function __construct($data, $rowNumber = null, $sourceRowNumber = null) {
    $this->data = $data;
    $this->rowNumber = $rowNumber;
    $this->sourceRowNumber = $sourceRowNumber;
  }

  //
  // Methods to implement the Iterator interface
  //

  /**
   * {@inheritDoc}
   */
  public function current() {
    return $this->data[$this->iterator_index];
  }

  /**
   * {@inheritDoc}
   */
  public function key() {
    return $this->iterator_index;
  }

  /**
   * {@inheritDoc}
   */
  public function next() {
    $this->iterator_index++;
    if ($this->iterator_index >= count($this->data)) {
      $this->iterator_index = -1;
    }
  }

  /**
   * {@inheritDoc}
   */
  public function rewind() {
    $this->iterator_index = 0;
  }

  /**
   * {@inheritDoc}
   */
  public function valid() {
    return ($this->iterator_index > -1);
  }

}

