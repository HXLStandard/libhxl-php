<?php

/**
 * A column definition in a HXL file.
 *
 * Started by David Megginson, August 2014.
 */
class HXLColumn {

  /**
   * The column's HXL tag.
   */
  public $tag;

  /**
   * The column's ISO 639 language code (null if unspecified).
   */
  public $lang;

  /**
   * The source text of the original header.
   */
  public $source_text;

  /**
   * Public constructor.
   *
   * @param $tag The HXL hashtag (including the initial '#')
   * @param $lang The ISO 639 language code (defaults to null, for unspecified)
   * @param $source_text The text of the original header (defaults to null, for unspecified)
   */
  public function __construct($tag, $lang = null, $source_text = null) {
    $this->tag = $tag;
    $this->lang = $lang;
    $this->source_text = $source_text;
  }

  /**
   * Get the full tagspec, including language (if specified).
   */
  public function getTagSpec() {
    if ($this->tag) {
      if ($this->lang) {
        return sprintf("%s/%s", $this->tag, $this->lang);
      } else {
        return $this->tag;
      }
    } else {
      return null;
    }
  }

}
