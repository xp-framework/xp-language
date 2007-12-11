<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'xml.XSLCallback',
    'xml.DomXSLProcessor',
    'xml.Node',
    'util.Date'
  );

  /**
   * TestCase for XSL callbacks
   *
   * @see      xp://xml.XSLCallback
   * @purpose  Unittest
   */
  class XslCallbackTest extends TestCase {

    /**
     * Runs a transformation
     *
     * @param   string xml
     * @param   string callback
     * @param   string[] arguments
     * @return  string
     */
    protected function runTransformation($xml, $callback, $arguments) {
      sscanf($callback, '%[^:]::%s', $name, $method);
      $p= new DomXSLProcessor();
      $p->registerInstance('this', $this);
      $p->setXMLBuf($xml);
      $p->setXSLBuf(sprintf('
        <xsl:stylesheet 
         version="1.0" 
         xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
         xmlns:php="http://php.net/xsl"
        >
          <xsl:output method="text"/>
          
          <xsl:template match="/">
            <xsl:value-of select="php:function(\'XSLCallback::invoke\', \'%s\', \'%s\'%s)"/>
          </xsl:template>
        </xsl:stylesheet>
        ',
        $name,
        $method,
        $arguments ? ', '.implode(', ', $arguments) : ''
      ));
      $p->run();
      return $p->output();
    }
    
    /**
     * Simple XSL callback method
     *
     * @param   string name default 'World'
     * @return  string
     */
    #[@xslmethod]
    public function sayHello($name= 'World') {
      return 'Hello '.$name;
    }
    
    /**
     * Test simple XSL callback method
     *
     */
    #[@test]
    public function callSayHello() {
      $this->assertEquals('Hello Test', $this->runTransformation(
        '<document/>', 
        'this::sayHello',
        array("'Test'")
      ));
    }

    /**
     * Test simple XSL callback method and omitting sayHello()'s optional
     * name argument
     *
     */
    #[@test]
    public function callSayHelloOmittingOptionalParameter() {
      $this->assertEquals('Hello World', $this->runTransformation(
        '<document/>', 
        'this::sayHello',
        array()
      ));
    }

    /**
     * Test calling function of not-registered callback
     *
     */
    #[@test, @expect('lang.IllegalArgumentException')]
    public function callOnNotRegisteredCallback() {
      $this->runTransformation('<irrelevant/>', 'not-registered::irrelevant', array());
    }

    /**
     * Test calling a method without xslmethod annotation
     *
     */
    #[@test, @expect('lang.IllegalArgumentException')]
    public function callNonXslMethod() {
      $this->runTransformation('<irrelevant/>', 'this::setUp', array());
    }

    /**
     * Test calling a non-existant method
     *
     */
    #[@test, @expect('lang.IllegalArgumentException')]
    public function callNonExistantMethod() {
      $this->runTransformation('<irrelevant/>', 'this::nonExistantMethod', array());
    }

    /**
     * Test xp.date::format
     *
     * @see      xp://xml.xslt.XSLDateCallback
     */
    #[@test]
    public function dateFormatCallback() {
      $date= Date::now();
      $this->assertEquals($date->toString('Y-m-d H:i:s'), $this->runTransformation(
        Node::fromObject($date)->getSource(),
        'xp.date::format',
        array('string(/date/value)', "'Y-m-d H:i:s'")
      ));
    }

    /**
     * Test xp.string::urlencode
     *
     * @see      xp://xml.xslt.XSLStringCallback
     */
    #[@test]
    public function stringUrlencodeCallback() {
      $this->assertEquals('a+%26+b%3F', $this->runTransformation(
        '<url>a &amp; b?</url>',
        'xp.string::urlencode',
        array('string(/)')
      ));
    }

    /**
     * Test xp.string::urldecode
     *
     * @see      xp://xml.xslt.XSLStringCallback
     */
    #[@test]
    public function stringUrldecodeCallback() {
      $this->assertEquals('a & b?', $this->runTransformation(
        '<url>a+%26+b%3F</url>',
        'xp.string::urldecode',
        array('string(/)')
      ));
    }

    /**
     * Test xp.string::replace
     *
     * @see      xp://xml.xslt.XSLStringCallback
     */
    #[@test]
    public function stringReplaceCallback() {
      $this->assertEquals('Hello World!', $this->runTransformation(
        '<string>Hello Test!</string>',
        'xp.string::replace',
        array('string(/)', "'Test'", "'World'")
      ));
    }

    /**
     * Test xp.string::nl2br
     *
     * @see      xp://xml.xslt.XSLStringCallback
     */
    #[@test]
    public function stringNl2BrCallback() {
      $this->assertEquals("Line 1<br />\nLine 2", $this->runTransformation(
        "<string>Line 1\nLine 2</string>",
        'xp.string::nl2br',
        array('string(/)')
      ));
    }
  }
?>