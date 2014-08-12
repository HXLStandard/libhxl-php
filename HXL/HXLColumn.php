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
   * Public constructor.
   */
  public function __construct($tag, $lang = null) {
    $this->tag = $tag;
    $this->lang = $lang;
  }

}
