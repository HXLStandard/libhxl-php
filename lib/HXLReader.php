<?php

class HXLHeader {
  public $tag;
  public $lang;

  public function __construct($tag, $lang = null) {
    $this->tag = $tag;
    $this->lang = $lang;
  }

}

class HXLRow {
  public $data;
  public $row_number;
  public $source_row_number;

  public function __construct($data, $row_number = null, $source_row_number = null) {
    $this->data = $data;
    $this->row_number = $row_number;
    $this->source_row_number = $source_row_number;
  }
}

class HXLValue {
  public $header;
  public $content;
  public $col_number;
  public $source_col_number;

  public function __construct($header, $content, $col_number = null, $source_col_number = null) {
    $this->header = $header;
    $this->content = $content;
    $this->col_number = $col_number;
    $this->source_col_number = $source_col_number;
  }
}

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

    foreach ($raw_data as $s) {
      $s = trim($s);
      if ($s) {
        $header = self::_parse_hashtag($s);
        if ($header) {
          $seen_header = true;
        } else {
          return null;
        }
      } else {
        $header = null;
      }
      array_push($headers, $header);
    }

    if ($seen_header) {
      return $headers;
    } else {
      return null;
    }
  }

  private static function _parse_hashtag($s) {
    $matches = array();
    if (preg_match('/^(#[a-zA-z0-9_]+)(?:\/([a-zA-Z]{2}))?/', $s, $matches)) {
      return new HXLHeader($matches[1], @$matches[2]);
    } else {
      return false;
    }
  }

}
