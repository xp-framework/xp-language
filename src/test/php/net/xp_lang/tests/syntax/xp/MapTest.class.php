<?php namespace net\xp_lang\tests\syntax\xp;

use xp\compiler\ast\MapNode;
use xp\compiler\ast\IntegerNode;
use xp\compiler\ast\StringNode;
use xp\compiler\types\TypeName;

/**
 * TestCase
 *
 */
class MapTest extends ParserTestCase {

  /**
   * Test an empty untyped map
   *
   */
  #[@test]
  public function emptyUntypedMap() {
    $this->assertEquals(
      [new MapNode([
        'elements'      => [],
        'type'          => null,
      ])], 
      $this->parse('[:];')
    );
  }

  /**
   * Test an empty typed map
   *
   */
  #[@test]
  public function emptyTypedMap() {
    $this->assertEquals([new MapNode([
      'elements'      => [],
      'type'          => new TypeName('[:string]'),
    ])], $this->parse('
      new [:string] {:};
    '));
  }

  /**
   * Test "[:int[]]"
   *
   */
  #[@test]
  public function intArrayMap() {
    $this->assertEquals([new MapNode([
      'elements'      => [],
      'type'          => new TypeName('[:int[]]'),
    ])], $this->parse('
      new [:int[]] {:};
    '));
  }

  /**
   * Test "[:var[]]"
   *
   */
  #[@test]
  public function varArrayMap() {
    $this->assertEquals([new MapNode([
      'elements'      => [],
      'type'          => new TypeName('[:var[]]'),
    ])], $this->parse('
      new [:var[]] {:};
    '));
  }

  /**
   * Test "[:[:int]]"
   *
   */
  #[@test]
  public function intMapMap() {
    $this->assertEquals([new MapNode([
      'elements'      => [],
      'type'          => new TypeName('[:[:int]]'),
    ])], $this->parse('
      new [:[:int]] {:};
    '));
  }

  /**
   * Test "[:util.Vector<net.xp_lang.tests.StringBuffer>]"
   *
   */
  #[@test]
  public function stringToGeneric() {
    $this->assertEquals([new MapNode([
      'elements'      => [],
      'type'          => new TypeName('[:util.Vector<net.xp_lang.tests.StringBuffer>]'),
    ])], $this->parse('
      new [:util.Vector<net.xp_lang.tests.StringBuffer>] {:};
    '));
  }

  /**
   * Test a non-empty untyped map
   *
   */
  #[@test]
  public function untypedMap() {
    $this->assertEquals(
      [new MapNode([
        'elements'      => [
          [
            new StringNode('one'),
            new IntegerNode('1'),
          ],
          [
            new StringNode('two'),
            new IntegerNode('2'),
          ],
          [
            new StringNode('three'),
            new IntegerNode('3'),
          ],
        ],
        'type'          => null,
      ])], 
      $this->parse('[ one : 1, two : 2,  three : 3 ];')
    );
  }

  /**
   * Test a non-empty typed map
   *
   */
  #[@test]
  public function typedMap() {
    $this->assertEquals(
      [new MapNode([
        'elements'      => [
          [
            new StringNode('one'),
            new IntegerNode('1'),
          ],
          [
            new StringNode('two'),
            new IntegerNode('2'),
          ],
          [
            new StringNode('three'),
            new IntegerNode('3'),
          ],
        ],
      'type'          => new TypeName('[:int]'),
      ])], 
      $this->parse('new [:int] { one : 1, two : 2,  three : 3 };')
    );
  }

  /**
   * Test a non-empty untyped map
   *
   */
  #[@test]
  public function untypedMapWithDanglingComma() {
    $this->assertEquals(
      [new MapNode([
        'elements'      => [
          [
            new StringNode('one'),
            new IntegerNode('1'),
          ],
          [
            new StringNode('two'),
            new IntegerNode('2'),
          ],
          [
            new StringNode('three'),
            new IntegerNode('3'),
          ],
        ],
        'type'          => null,
      ])], 
      $this->parse('[ one : 1, two : 2,  three : 3, ];')
    );
  }

  /**
   * Test map keys can be quoted
   *
   */
  #[@test]
  public function optionalQuoting() {
    $this->assertEquals(
      [new MapNode([
        'elements'      => [
          [
            new StringNode('content-type'),
            new IntegerNode('text/html'),
          ],
          [
            new StringNode('server'),
            new IntegerNode('Apache'),
          ],
        ],
        'type'          => null,
      ])], 
      $this->parse("[ 'content-type' : 'text/html', server : 'Apache' ];")
    );
  }
}
