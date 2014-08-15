<?php

/**
 * Read HXL data from a CSV file.
 *
 * This is a one-time iterable class.  You can use it in a foreach
 * expression, but you can't rewind and go through it again.
 *
 * Started by David Megginson, August 2014.
 */
class HXLReader implements Iterator {

  private $input;
  private $headers;
  private $source_row_number = -1;
  private $row_number = -1;
  private $last_header_row = null;
  private $current_row = null;

  /**
   * Public constructor.
   *
   * @param The input stream.
   */
  function __construct($input) {
    $this->input = $input;
  }

  //
  // Public methods
  //

  /**
   * Read a row of HXL data.
   *
   * @return A data structure describing the row, or null if input is finished.
   * @exception If a row of HXL hashtags hasn't been (and can't be) found.
   */
  function read() {

    // Look for the headers first, if we don't already have them.
    if ($this->headers == null) {
      $this->headers = $this->_read_headers($this->input);
    }

    // Read a row from the source CSV
    $this->row_number++;
    $raw_data = $this->_read_source_row();
    if ($raw_data == null) {
      return null;
    }

    // Sort the raw data into a row of HXLValue objects
    $data = array();
    $col_number = -1;
    foreach ($raw_data as $i => $content) {
      if (@$this->headers[$i]) {
        $col_number++;
        array_push($data, new HXLValue(
          $this->headers[$i],
          $raw_data[$i],
          $col_number,
          $i
        ));
      }
    }

    return new HXLRow($data, $this->row_number, $this->source_row_number);
  }

  //
  // Methods to implement the Iterator interface
  //

  /**
   * {@inheritDoc}
   */
  public function current() {
    return $this->current_row;
  }

  /**
   * {@inheritDoc}
   */
  public function key() {
    return $this->row_number;
  }

  /**
   * {@inheritDoc}
   */
  public function next() {
    $this->current_row = $this->read();
  }

  /**
   * {@inheritDoc}
   */
  public function rewind() {
    if ($this->row_number > -1) {
      throw new Exception("Cannot rewind the HXL input stream.");
    } else {
      $this->next();
    }
  }

  /**
   * {@inheritDoc}
   */
  public function valid() {
    return ($this->current_row != null);
  }

  //
  // Private utility methods
  //

  /**
   * Read a row from the source document.
   */
  private function _read_source_row() {
    $this->source_row_number++;
    return fgetcsv($this->input);
  }

  /**
   * Skip to and read the HXL header row in a source document.
   */
  private function _read_headers() {
    while ($raw_data = $this->_read_source_row()) {
      $headers = $this->_try_header_row($raw_data);
      if ($headers != null) {
        return $headers;
      } else {
        $this->last_header_row = $raw_data;
      }
    }
    throw new Exception("HXL hashtag row not found");
  }

  /**
   * Attempt to read a HXL header row in a source document.
   */
  private function _try_header_row($raw_data) {
    $seen_header = false;
    $columns = array();

    foreach ($raw_data as $i => $s) {
      $s = trim($s);
      if ($s) {
        $column = self::_parse_hashtag($s);
        if ($column) {
          $seen_header = true;
          $column->source_text = $this->last_header_row[$i];
        } else {
          return null;
        }
      } else {
        $column = null;
      }
      array_push($columns, $column);
    }

    if ($seen_header) {
      return $columns;
    } else {
      return null;
    }
  }

  /**
   * Attempt to parse a HXL hashtag.
   *
   * @return null if not a properly-formatted hashtag.
   */
  private static function _parse_hashtag($s) {
    $matches = array();
    if (preg_match('/^(#[a-zA-z0-9_]+)(?:\/([a-zA-Z]{2}))?/', $s, $matches)) {
      return new HXLColumn($matches[1], @$matches[2]);
    } else {
      return false;
    }
  }

}

// end
