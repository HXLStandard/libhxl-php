<?php

require_once(__DIR__ . '/HXL.php');

/**
 * Parse HXL data from a CSV file.
 *
 * <p>Usage:</p>
 *
 * <pre>
 * $hxl = new HXLReader(STDIN);
 * foreach ($hxl as $hxlRow) {
 *   printf("Row %d:\n", $row->rowNumber);
 *   foreach ($row as $value) {
 *     printf(" %s=%s\n", $value->header->tag, $value->content);
 *   }
 * }
 * </pre>
 *
 * <p>This is a one-time iterable class.  You can use it in a foreach
 * expression, but you can't rewind and go through it again.</p>
 *
 * @author David Megginson
 * @since August 2014
 */
class HXLReader implements Iterator {

  private $input;
  private $tableSpec;
  private $sourceRowNumber = -1;
  private $rowNumber = -1;
  private $lastHeaderRow = null;
  private $currentRow = null;

  private $rawData = null;
  private $disaggregationCount;
  private $disaggregationPosition;

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
      $this->tableSpec = $this->parseTableSpec($this->input);
      $this->disaggregationCount = $this->tableSpec->getDisaggregationCount();
      $this->disaggregationPosition = 0;
    }

    if ($this->disaggregationPosition >= $this->disaggregationCount || !$this->rawData) {
      // Read a row from the source CSV
      $this->rawData = $this->parseSourceRow();
      if ($this->rawData == null) {
        return null;
      }
      $this->disaggregationPosition = 0;
    }
    $this->rowNumber++;

    // Sort the raw data into a row of HXLValue objects
    $data = array();
    $columnNumber = -1;
    $seenFixed = false;
    foreach ($this->rawData as $sourceColumnNumber => $content) {
      $colSpec = @$this->tableSpec->colSpecs[$sourceColumnNumber];

      // If there's no HXL tag, we don't process the column
      if (!$colSpec->column->hxlTag) {
        continue;
      }

      if ($colSpec->fixedColumn) {
        if (!$seenFixed) {
          $columnNumber++;
          $fixedPosition = $this->tableSpec->getFixedPos($this->disaggregationPosition);
          array_push($data, new HXLValue(
            $this->tableSpec->colSpecs[$fixedPosition]->fixedColumn,
            $this->tableSpec->colSpecs[$fixedPosition]->fixedValue,
            $columnNumber,
            $sourceColumnNumber
          ));
          $columnNumber++;
          array_push($data, new HXLValue(
            $this->tableSpec->colSpecs[$fixedPosition]->column,
            $this->rawData[$fixedPosition],
            $columnNumber,
            $sourceColumnNumber
          ));
          $seenFixed = true;
        }
      } else {
        $columnNumber++;
        array_push($data, new HXLValue(
          $this->tableSpec->colSpecs[$sourceColumnNumber]->column,
          $this->rawData[$sourceColumnNumber],
          $columnNumber,
          $sourceColumnNumber
        ));
      }
    }

    $this->disaggregationPosition++;
    return new HXLRow($data, $this->rowNumber, $this->sourceRowNumber);
  }

  //
  // Methods to implement the Iterator interface
  //

  /**
   * {@inheritDoc}
   */
  public function current() {
    return $this->currentRow;
  }

  /**
   * {@inheritDoc}
   */
  public function key() {
    return $this->rowNumber;
  }

  /**
   * {@inheritDoc}
   */
  public function next() {
    $this->currentRow = $this->read();
  }

  /**
   * {@inheritDoc}
   */
  public function rewind() {
    if ($this->rowNumber > -1) {
      throw new Exception("Cannot rewind the HXL input stream.");
    } else {
      $this->next();
    }
  }

  /**
   * {@inheritDoc}
   */
  public function valid() {
    return ($this->currentRow != null);
  }

  //
  // Private utility methods
  //

  /**
   * Read a row from the source document.
   */
  private function parseSourceRow() {
    $this->sourceRowNumber++;
    return fgetcsv($this->input);
  }

  /**
   * Skip to and read the HXL header row in a source document.
   */
  private function parseTableSpec() {
    while ($rawData = $this->parseSourceRow()) {
      $tableSpec = $this->parseHashtagRow($rawData);
      if ($tableSpec != null) {
        return $tableSpec;
      } else {
        $this->lastHeaderRow = $rawData;
      }
    }
    throw new Exception("HXL hashtag row not found");
  }

  /**
   * Attempt to read a HXL table spec in a source document.
   *
   * The parser uses this function to go fishing: it tries to parse a
   * CSV row as HXL hashtags, and if it succeeds, then it assumes that
   * this is, in fact, the HXL hashtag row, and returns a {@link
   * HXLTableSpec} object.
   *
   * @param $rawDataRow A row of raw CSV data, which may or may not
   * contain HXL hashtags.
   * @return A {@link HXLTableSpec} object if the row is a valid HXL
   * hashtag row, or null otherwise.
   */
  private function parseHashtagRow($rawDataRow) {

    // Create the tablespec here, so that we can add non-HXL
    // colspecs (for empty cells) in advance of seeing a HXL hashtag
    $tableSpec = new HXLTableSpec();
    $seenHeader = false;

    // Iterate through the row, cell by cell
    foreach ($rawDataRow as $sourceColumnNumber => $rawString) {
      // Trim whitespace, then see if there's any text left; if so,
      // try parsing it as a HXL hashtag
      $rawString = trim($rawString);
      if ($rawString) {

        // try a parse (go fishing)
        $colSpec = self::parseHashtag($sourceColumnNumber, $rawString);
        if ($colSpec) {
          // the parse succeeded; flag that we've seen a tag, and add the col spec
          $seenHeader = true;

          // Is the column using compact-disaggregated notation?
          if ($colSpec->fixedColumn) {

            // Yes: compact disaggregated

            // Create human-readable headers from the tags
            $colSpec->fixedColumn->headerText = $this->prettyTag($colSpec->fixedColumn->hxlTag);
            $colSpec->column->headerText = $this->prettyTag($colSpec->column->hxlTag);

            // The fixed value is the header in the previous row
            $colSpec->fixedValue = $this->lastHeaderRow[$sourceColumnNumber];

          } else {
            // No: this is a simple tag
            $colSpec->column->headerText = $this->lastHeaderRow[$sourceColumnNumber];
          }

        } else {
          // The cell contained text, but it wasn't a hashtag; that means that it's not a HXL
          // tag row, so fail now
          return null;
        }

      } else {

        // Special case: the cell was empty, which is allowed in a HXL tag row. For now, add the
        // empty cell to the tablespec, until we have more information.
        $colSpec = new HXLColSpec($sourceColumnNumber);
        $colSpec->column = new HXLColumn();
      }

      // Add the col spec to the table spec
      $tableSpec->add($colSpec);
    }

    // Use the $seenHeader variable to determine whether we actually saw at least one hashtag
    if ($seenHeader) {
      // there was at least one hashtag; return the table spec
      return $tableSpec;
    } else {
      // it was an all-empty row; fail
      return null;
    }
  }

  /**
   * Attempt to parse a HXL hashtag.
   *
   * @param $sourceColumnNumber The current column number in the source CSV.
   * @param $rawString A raw string to attempt to parse.
   * @return null if not a properly-formatted hashtag.
   */
  private static function parseHashtag($sourceColumnNumber, $rawString) {
    static $tagRegexp = '(#[a-zA-z0-9_]+)(?:\/([a-zA-Z]{2}))?';
    $matches = array();
    if (preg_match("/^\s*$tagRegexp(?:\s*\+\s*$tagRegexp)?\s*\$/", $rawString, $matches)) {
      $col1 = new HXLColumn($matches[1], @$matches[2]);
      $col2 = null;
      if (@$matches[3]) {
        $col2 = new HXLColumn($matches[3], @$matches[4]);
        $colSpec = new HXLColSpec($sourceColumnNumber, $col2, $col1);
      } else {
        $colSpec = new HXLColSpec($sourceColumnNumber, $col1);
      }
      return $colSpec;
    } else {
      return false;
    }
  }

  /**
   * Attempt to create a human-readable header from a tag.
   *
   * This is a bit of a hack, to provide a header for a compact-disaggregated tag.
   * It should be replaced with a proper lookup in a HXL dictionary.
   *
   * @param $hxlTag The tag to pretty print.
   * @return An attempt at a human-readable version of the tag.
   */
  private static function prettyTag($hxlTag) {
    // TODO try looking up standard tags
    $hxlTag = preg_replace('/^#/', '', $hxlTag);
    $hxlTag = preg_replace('/_(date|deg|id|link|num)$/', '', $hxlTag);
    $hxlTag = preg_replace('/_/', ' ', $hxlTag);
    return ucfirst($hxlTag);
  }

}

// end
