<?php namespace net\xp_lang\tests\syntax\php;

use xp\compiler\ast\MethodCallNode;
use xp\compiler\ast\VariableNode;
use xp\compiler\ast\InstanceCreationNode;
use xp\compiler\ast\MemberAccessNode;
use xp\compiler\ast\ArrayAccessNode;
use xp\compiler\ast\IntegerNode;
use xp\compiler\ast\StaticMethodCallNode;
use xp\compiler\ast\StringNode;
use xp\compiler\ast\InvocationNode;
use xp\compiler\ast\BracedExpressionNode;
use xp\compiler\types\TypeName;

class ChainingTest extends ParserTestCase {

  #[@test]
  public function methodCall() {
    $this->assertEquals(
      array(new MethodCallNode(new VariableNode('m'), 'invoke', array(new VariableNode('args')))),
      $this->parse('$m->invoke($args);')
    );
  }

  #[@test]
  public function chainedMethodCalls() {
    $this->assertEquals(
      array(new MethodCallNode(
        new MethodCallNode(new VariableNode('l'), 'withAppender', null),
        'debug',
        null
      )),
      $this->parse('$l->withAppender()->debug();')
    );
  }

  #[@test]
  public function chainedAfterNew() {
    $this->assertEquals(
      array(new MethodCallNode(
        new BracedExpressionNode(new InstanceCreationNode(array(
          'type'           => new TypeName('Date'),
          'parameters'     => null,
        ))),
        'toString',
        null
      )), 
      $this->parse('(new Date())->toString();')
    );
  }

  #[@test]
  public function arrayOffsetOnMethod() {
    $this->assertEquals(
      array(new MemberAccessNode(
        new ArrayAccessNode(
          new MethodCallNode(new VariableNode('l'), 'elements', null),
          new IntegerNode('0')
        ),
        'name'
      )),
      $this->parse('$l->elements()[0]->name;')
    );
  }

  #[@test]
  public function chainedAfterStaticMethod() {
    $this->assertEquals(
      array(new MethodCallNode(
        new StaticMethodCallNode(new TypeName('Logger'), 'getInstance', array()),
        'configure', 
        array(new StringNode('etc'))
      )), 
      $this->parse('Logger::getInstance()->configure("etc");')
    );
  }

  #[@test]
  public function chainedAfterFunction() {
    $this->assertEquals(
      array(new MethodCallNode(
        new InvocationNode('create', array(new VariableNode('a'))),
        'equals',
        array(new VariableNode('b'))
      )), 
      $this->parse('create($a)->equals($b);')
    );
  }

  #[@test]
  public function chainedAfterBraced() {
    $this->assertEquals(
      array(new MethodCallNode(
        new BracedExpressionNode(new VariableNode('a')),
        'equals', 
        array(new VariableNode('b'))
      )), 
      $this->parse('($a)->equals($b);')
    );
  }
}
