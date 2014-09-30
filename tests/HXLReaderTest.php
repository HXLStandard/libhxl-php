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
    $row = $reader->read();
    $this->assertNotNull($row);

    $this->assertEquals(1, $row->rowNumber);
    $this->assertEquals(3, $row->sourceRowNumber);

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
    $this->assertEquals('Sector/Cluster', $value->column->headerText);
  }


  /**
   * @depends testRead
   */
  public function testTag($data) {
    $this->assertEquals('#sector', $data[0]->column->hxlTag);
  }

  /**
   * @depends testRead
   */
  public function testComplexTag($data) {
    $this->assertEquals('#sex', $data[4]->column->hxlTag);
    $this->assertEquals('#targeted_num', $data[5]->column->hxlTag);
  }

  /**
   * @depends testRead
   */
  public function testComplexValue($data) {
    $this->assertEquals('Females', $data[4]->content);
    $this->assertEquals('100', $data[5]->content);
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
    $this->assertEquals(1, $value->sourceColumnNumber);
  }

}