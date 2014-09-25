<?php

require_once(__DIR__ . '/tests-common.php');

class HXLReaderTest extends PHPUnit_Framework_TestCase {

  public function testConstructor() {
    $input = open_data('simple.csv');
    $this->assertNotNull($input);

    $reader = new HXLReader($input);
    $this->assertNotNull($reader);

    return $reader;
  }

  /**
   * @depends testConstructor
   */
  public function testRead(HXLReader $reader) {
    $row = $reader->read();
    $this->assertNotNull($row);

    $this->assertEquals(0, $row->row_number);
    $this->assertEquals(3, $row->source_row_number);

    return $row->data;
  }

  /**
   * @depends testRead
   */
  public function testData($data) {
    $this->assertEquals(6, count($data));
    return $data[0];
  }

  /**
   * @depends testData
   */
  public function testSourceText(HXLValue $value) {
    $this->assertEquals('Sector/Cluster', $value->column->source_text);
  }


  /**
   * @depends testRead
   */
  public function testTag($data) {
    $this->assertEquals('#sector', $data[0]->column->tag);
  }

  /**
   * @depends testRead
   */
  public function testComplexTag($data) {
    $this->assertEquals('#sex', $data[4]->column->tag);
    $this->assertEquals('#targeted_num', $data[5]->column->tag);
  }

  /**
   * @depends testData
   */
  public function testContent(HXLValue $value) {
    $this->assertEquals('WASH', $value->content);
  }

  /**
   * @depends testData
   */
  public function testColumn(HXLValue $value) {
    $this->assertEquals(0, $value->col_number);
    $this->assertEquals(1, $value->source_col_number);
  }

}