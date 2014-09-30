<?php

/**
 * A column definition in a HXL file.
 *
 * The definition of a logical column in the HXL data, including the
 * data type, language code, and the human-readable text of the
 * original header (if provided).  This is the left side of a
 * NAME=VALUE model.
 *
 * @author David Megginson
 * @since August 2014
 */
class HXLColumn {

  /**
   * The column's HXL tag.
   */
  public $hxlTag;

  /**
   * The column's ISO 639 language code (null if unspecified).
   */
  public $languageCode;

  /**
   * The source text of the original header.
   */
  public $headerText;

  /**
   * Public constructor.
   *
   * @param $hxlTag The HXL hashtag (including the initial '#')
   * @param $languageCode The ISO 639 language code (defaults to null, for unspecified)
   * @param $headerText The text of the original header (defaults to null, for unspecified)
   */
  public function __construct($hxlTag = null, $languageCode = null, $headerText = null) {
    $this->hxlTag = $hxlTag;
    $this->languageCode = $languageCode;
    $this->headerText = $headerText;
  }

  /**
   * Get the full tagspec, including language (if specified).
   *
   * This is the tag as it would appear at the top of a HXL
   * spreadsheet column, e.g. #country/fr
   *
   * @return The full logical tag specification for the column,
   * including language (if provided).
   */
  public function getDisplayTag() {
    if ($this->hxlTag) {
      if ($this->languageCode) {
        return sprintf("%s/%s", $this->hxlTag, $this->languageCode);
      } else {
        return $this->hxlTag;
      }
    } else {
      return null;
    }
  }

}

// end
