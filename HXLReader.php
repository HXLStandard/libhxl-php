<?php

/**
 * Read HXL data from a CSV file.
 *
 * Started by David Megginson, August 2014.
 */
class HXLReader {

  private $input;

  private $headers;

  private $source_row_number = -1;

  private $row_number = -1;

  /**
   * Public constructor.
   *
   * @param The input stream.
   */
  function __construct($input) {
    $this->input = $input;
  }

  /**
   * Read a row of HXL data.
   *
   * @return A data structure describing the row, or null if input is finished.
   * @exception If a row of HXL hashtags isn't found.
   */
  function read() {
    if ($this->headers == null) {
      $this->headers = $this->_read_headers($this->input);
    }

    $this->row_number++;
    $raw_data = $this->_read_source_row();

    if ($raw_data == null) {
      return null;
    }

    $row = array();

    $col_number = -1;
    foreach ($raw_data as $i => $content) {
      if (@$this->headers[$i]) {
        $col_number++;
        array_push($row, array(
          'hxl_tag' => $this->headers[$i],
          'content' => $raw_data[$i],
          'col_number' => $col_number,
          'source_col_number' => $i,
        ));
      }
    }

    return array(
      'data' => $row,
      'row_number' => $this->row_number,
      'source_row_number' => $this->source_row_number,
    );

  }

  private function _read_source_row() {
    $this->source_row_number++;
    return fgetcsv($this->input);
  }

  private function _read_headers() {
    while ($raw_data = $this->_read_source_row()) {
      $headers = self::_try_header_row($raw_data);
      if ($headers != null) {
        return $headers;
      }
    }
    throw new Exception("HXL hashtag row not found");
  }

  private static function _try_header_row($raw_data) {
    $seen_header = false;
    $headers = array();

    foreach ($raw_data as $header) {
      $header = trim($header);
      if ($header) {
        if (self::_is_hashtag($header)) {
          $seen_header = true;
        } else {
          return null;
        }
      }
      array_push($headers, $header);
    }

    if ($seen_header) {
      return $headers;
    } else {
      return null;
    }
  }

  private static function _is_hashtag($s) {
    return preg_match('/^#[a-zA-z0-9_]+/', $s);
  }

}