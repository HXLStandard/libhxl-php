<?php

require_once(__DIR__ . '/HXL.php');

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
  private $tableSpec;
  private $source_row_number = -1;
  private $row_number = -1;
  private $last_header_row = null;
  private $current_row = null;

  private $raw_data = null;
  private $disaggregation_count;

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
    if ($this->tableSpec == null) {
      $this->tableSpec = $this->_read_tableSpec($this->input);
      $this->disaggregation_count = $this->tableSpec->getDisaggregationCount();
      $this->disaggregation_pos = 0;
    }

    if ($this->disaggregation_pos >= $this->disaggregation_count || !$this->raw_data) {
      // Read a row from the source CSV
      $this->raw_data = $this->_read_source_row();
      if ($this->raw_data == null) {
        return null;
      }
      $this->disaggregation_pos = 0;
    }
    $this->row_number++;

    // Sort the raw data into a row of HXLValue objects
    $data = array();
    $col_number = -1;
    $seen_fixed = false;
    foreach ($this->raw_data as $i => $content) {
      $colSpec = @$this->tableSpec->colSpecs[$i];

      // If there's no HXL tag, we don't process the column
      if (!$colSpec->column->tag) {
        continue;
      }

      if ($colSpec->fixedColumn) {
        if (!$seen_fixed) {
          $col_number++;
          $fixed_pos = $this->tableSpec->getFixedPos($this->disaggregation_pos);
          array_push($data, new HXLValue(
            $this->tableSpec->colSpecs[$fixed_pos]->fixedColumn,
            $this->tableSpec->colSpecs[$fixed_pos]->column->source_text,
            $col_number,
            $i
          ));
          $col_number++;
          array_push($data, new HXLValue(
            $this->tableSpec->colSpecs[$fixed_pos]->column,
            $this->raw_data[$fixed_pos],
            $col_number,
            $i
          ));
          $seen_fixed = true;
        }
      } else {
        $col_number++;
        array_push($data, new HXLValue(
          $this->tableSpec->colSpecs[$i]->column,
          $this->raw_data[$i],
          $col_number,
          $i
        ));
      }
    }

    $this->disaggregation_pos++;
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
  private function _read_tableSpec() {
    while ($raw_data = $this->_read_source_row()) {
      $tableSpec = $this->_try_tableSpec($raw_data);
      if ($tableSpec != null) {
        return $tableSpec;
      } else {
        $this->last_header_row = $raw_data;
      }
    }
    throw new Exception("HXL hashtag row not found");
  }

  /**
   * Attempt to read a HXL table spec in a source document.
   */
  private function _try_tableSpec($raw_data) {
    $seen_header = false;
    $tableSpec = new HXLTableSpec();

    // It's a tag row
    $source_col_number = -1;
    foreach ($raw_data as $i => $s) {
      $source_col_number++;
      $s = trim($s);
      if ($s) {
        $colSpec = self::_parse_hashtag($source_col_number, $s);
        if ($colSpec) {
          $seen_header = true;
        } else {
          return null;
        }
      } else {
        $colSpec = new HXLColSpec($source_col_number);
        $colSpec->column = new HXLColumn();
      }
      $colSpec->column->source_text = $this->last_header_row[$i];
      $tableSpec->add($colSpec);
    }

    if ($seen_header) {
      return $tableSpec;
    } else {
      return null;
    }
  }

  /**
   * Attempt to parse a HXL hashtag.
   *
   * @return null if not a properly-formatted hashtag.
   */
  private static function _parse_hashtag($source_col_number, $s) {
    static $tag_regexp = '(#[a-zA-z0-9_]+)(?:\/([a-zA-Z]{2}))?';
    $matches = array();
    if (preg_match("/^\s*$tag_regexp(?:\s*\+\s*$tag_regexp)?\s*\$/", $s, $matches)) {
      $col1 = new HXLColumn($matches[1], @$matches[2]);
      $col2 = null;
      if (@$matches[3]) {
        $col2 = new HXLColumn($matches[3], @$matches[4]);
        $colSpec = new HXLColSpec($source_col_number, $col2, $col1);
      } else {
        $colSpec = new HXLColSpec($source_col_number, $col1);
      }
      return $colSpec;
    } else {
      return false;
    }
  }

}

// end
