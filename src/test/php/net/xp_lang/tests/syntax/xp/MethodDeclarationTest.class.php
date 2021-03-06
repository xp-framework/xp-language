<?php namespace net\xp_lang\tests\syntax\xp;

use xp\compiler\ast\MethodNode;
use xp\compiler\ast\OperatorNode;
use xp\compiler\ast\AnnotationNode;
use xp\compiler\types\TypeName;

/**
 * TestCase for method declarations
 */
class MethodDeclarationTest extends ParserTestCase {

  /**
   * Parse method source and return method declaration
   *
   * @param   string $decl The method declaration
   * @return  xp.compiler.ast.MethodNode
   */
  protected function parse($decl) {
    return $this->parseTree('class Test { '.$decl.' }')->declaration->body[0];
  }

  #[@test]
  public function toStringMethod() {
    $this->assertEquals(new MethodNode([
      'modifiers'  => MODIFIER_PUBLIC,
      'annotations'=> null,
      'name'       => 'toString',
      'returns'    => new TypeName('string'),
      'parameters' => null,
      'throws'     => null,
      'body'       => [],
      'extension'  => null
    ]), $this->parse(
      'public string toString() { }'
    ));
  }

  #[@test]
  public function equalsMethod() {
    $this->assertEquals(new MethodNode([
      'modifiers'  => MODIFIER_PUBLIC,
      'annotations'=> null,
      'name'       => 'equals',
      'returns'    => new TypeName('bool'),
      'parameters' => [[
        'name'  => 'cmp',
        'type'  => new TypeName('Object'),
        'check' => true
      ]],
      'throws'     => null,
      'body'       => [],
      'extension'  => null
    ]), $this->parse(
      'public bool equals(Object $cmp) { }'
    ));
  }

  #[@test]
  public function abstractMethod() {
    $this->assertEquals(new MethodNode([
      'modifiers'  => MODIFIER_PUBLIC | MODIFIER_ABSTRACT,
      'annotations'=> null,
      'name'       => 'setTrace',
      'returns'    => TypeName::$VOID,
      'parameters' => [[
        'name'  => 'cat',
        'type'  => new TypeName('util.log.LogCategory'),
        'check' => true
      ]],
      'throws'     => null,
      'body'       => null,
      'extension'  => null
    ]), $this->parse(
      'public abstract void setTrace(util.log.LogCategory $cat);'
    ));
  }

  #[@test]
  public function interfaceMethod() {
    $this->assertEquals(new MethodNode([
      'modifiers'  => MODIFIER_PUBLIC,
      'annotations'=> null,
      'name'       => 'compareTo',
      'returns'    => new TypeName('int'),
      'parameters' => [[
        'name'  => 'other',
        'type'  => new TypeName('Object'),
        'check' => true
      ]],
      'throws'     => null,
      'body'       => null,
      'extension'  => null
    ]), $this->parse( 
      'public int compareTo(Object $other);'
    ));
  }

  #[@test]
  public function staticMethod() {
    $this->assertEquals(new MethodNode([
      'modifiers'  => MODIFIER_PUBLIC | MODIFIER_STATIC,
      'annotations'=> null,
      'name'       => 'loadClass',
      'returns'    => new TypeName('Class', [new TypeName('T')]),
      'parameters' => [[
        'name'  => 'name',
        'type'  => new TypeName('string'),
        'check' => true
      ]],
      'throws'     => [new TypeName('ClassNotFoundException'), new TypeName('SecurityException')],
      'body'       => [],
      'extension'  => null
    ]), $this->parse(
      'public static Class<T> loadClass(string $name) throws ClassNotFoundException, SecurityException { }'
    ));
  }

  #[@test]
  public function printfMethod() {
    $this->assertEquals(new MethodNode([
      'modifiers'   => MODIFIER_PUBLIC | MODIFIER_STATIC,
      'annotations' => null,
      'name'        => 'printf',
      'returns'     => new TypeName('string'),
      'parameters'  => [[
        'name'      => 'format',
        'type'      => new TypeName('string'),
        'check'     => true
      ], [
        'name'      => 'args',
        'type'      => new TypeName('string'),
        'vararg'    => true,
        'check'     => true
      ]], 
      'throws'      => null,
      'body'        => [],
      'extension'   => null
    ]), $this->parse(
      'public static string printf(string $format, string... $args) { }'
    ));
  }

  #[@test]
  public function addAllMethod() {
    $this->assertEquals(new MethodNode([
      'modifiers'  => MODIFIER_PUBLIC,
      'annotations'=> null,
      'name'       => 'addAll',
      'returns'    => TypeName::$VOID,
      'parameters' => [[
        'name'   => 'elements',
        'type'   => new TypeName('T[]'),  // XXX FIXME this is probably not a good representation
        'check'  => true      
      ]], 
      'throws'     => null,
      'body'       => [],
      'extension'  => null
    ]), $this->parse(
      'public void addAll(T[] $elements) { }'
    ));
  }

  #[@test]
  public function plusOperator() {
    $this->assertEquals(new OperatorNode([
      'modifiers'  => MODIFIER_PUBLIC | MODIFIER_STATIC,
      'annotations'=> null,
      'symbol'     => '+',
      'returns'    => new TypeName('self'),
      'parameters' => [[
        'name'  => 'a',
        'type'  => new TypeName('self'),
        'check' => true
      ], [
        'name'  => 'b',
        'type'  => new TypeName('self'),
        'check' => true
      ]],
      'throws'     => null,
      'body'       => []
    ]), $this->parse(
      'public static self operator + (self $a, self $b) { }'
    ));
  }

  #[@test, @expect(class= 'lang.FormatException', withMessage= '/Method "run" requires a return type/')]
  public function missingReturnType() {
    $this->parse('public run() { }');
  }

  #[@test]
  public function mapMethodWithAnnotations() {
    $this->assertEquals(new MethodNode([
      'modifiers'  => 0,
      'annotations'=> [
        new AnnotationNode([
          'type'        => 'test',
          'parameters'  => []
        ])
      ],
      'name'       => 'map',
      'returns'    => new TypeName('[:string]'),
      'parameters' => [], 
      'throws'     => null,
      'body'       => [],
      'extension'  => null
    ]), $this->parse(
      '[@test] [:string] map() { }'
    ));
  }

  #[@test]
  public function extensionMethod() {
    $this->assertEquals(new MethodNode([
      'modifiers'  => MODIFIER_PUBLIC | MODIFIER_STATIC,
      'annotations'=> null,
      'name'       => 'endsWith',
      'returns'    => new TypeName('bool'),
      'parameters' => [
        [
          'name'   => 'self',
          'type'   => new TypeName('string'),
          'check'  => true
        ],
        [
          'name'   => 'end',
          'type'   => new TypeName('string'),
          'check'  => true
        ]
      ],
      'throws'     => null,
      'body'       => [],
      'extension'  => new TypeName('string')
    ]), $this->parse(
      'public static bool endsWith(this string $self, string $end) { }'
    ));
  }

  #[@test, @values([
  #  ['bool equals($cmp) { }', [['var', false]]],
  #  ['bool equals(Generic $cmp) { }', [['Generic', true]]],
  #  ['bool equals(Generic? $cmp) { }', [['Generic', false]]],
  #  ['string pos(string $subject, $test) { }', [['string', true], ['var', false]]],
  #  ['int compare($a, $b) { }', [['var', false], ['var', false]]]
  #])]
  public function parameters($src, $result) {
    $cmp= [];
    foreach ($this->parse($src)->parameters as $param) {
      $cmp[]= [$param['type']->compoundName(), $param['check']];
    }
    $this->assertEquals($result, $cmp);
  }

  #[@test]
  public function addingMethod() {
    $this->assertEquals(new MethodNode([
      'modifiers'  => MODIFIER_PUBLIC,
      'annotations'=> null,
      'name'       => 'adding',
      'returns'    => new TypeName('->int', null),
      'parameters' => null,
      'throws'     => null,
      'body'       => [],
      'extension'  => null
    ]), $this->parse(
      'public {? -> int} adding() { }'
    ));
  }

  #[@test]
  public function countingMethod() {
    $this->assertEquals(new MethodNode([
      'modifiers'  => MODIFIER_PUBLIC,
      'annotations'=> null,
      'name'       => 'counting',
      'returns'    => new TypeName('->int', [new TypeName('Collection')]),
      'parameters' => null,
      'throws'     => null,
      'body'       => [],
      'extension'  => null
    ]), $this->parse(
      'public {Collection -> int} counting() { }'
    ));
  }

  #[@test]
  public function groupingMethod() {
    $this->assertEquals(new MethodNode([
      'modifiers'  => MODIFIER_PUBLIC,
      'annotations'=> null,
      'name'       => 'grouping',
      'returns'    => new TypeName('->int', [new TypeName('Map'), new TypeName('string')]),
      'parameters' => null,
      'throws'     => null,
      'body'       => [],
      'extension'  => null
    ]), $this->parse(
      'public {(Map, string) -> int} grouping() { }'
    ));
  }
}
