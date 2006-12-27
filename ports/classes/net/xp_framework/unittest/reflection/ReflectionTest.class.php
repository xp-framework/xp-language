<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'net.xp_framework.unittest.reflection.TestClass'
  );

  /**
   * Test the XP reflection API
   *
   * @see      xp://lang.XPClass
   * @purpose  Testcase
   */
  class ReflectionTest extends TestCase {
    public
      $class  = NULL;
  
    /**
     * Setup method
     *
     */
    public function setUp() {
      $this->class= XPClass::forName('net.xp_framework.unittest.reflection.TestClass');
    }
 
    /**
     * Tests the getName() method
     *
     * @see     xp://lang.XPClass#getName
     */
    #[@test]
    public function name() {
      $this->assertEquals(
        'net.xp_framework.unittest.reflection.TestClass', 
        $this->class->getName()
      );
    }

    /**
     * Tests instanciation
     *
     * @see     xp://lang.XPClass#newInstance
     */
    #[@test]
    public function instanciation() {
      $instance= $this->class->newInstance(1);
      $this->assertObject($instance);
      $this->assertClass($instance, 'net.xp_framework.unittest.reflection.TestClass');
      $this->assertTrue($this->class->isInstance($instance));
    }
    
    /**
     * Tests subclass
     *
     * @see     xp://lang.XPClass#isSubclassOf
     */
    #[@test]
    public function subClass() {
      $this->assertTrue($this->class->isSubclassOf('lang.Object'));
      $this->assertFalse($this->class->isSubclassOf('util.Date'));
      $this->assertFalse($this->class->isSubclassOf('net.xp_framework.unittest.reflection.TestClass'));
    }
   
    /**
     * Tests the parent class
     *
     * @see     xp://lang.XPClass#getParentClass
     */
    #[@test]
    public function parentClass() {
      $parent= $this->class->getParentClass();
      $this->assertClass($parent, 'lang.XPClass');
      $this->assertEquals('lang.Object', $parent->getName());
      $this->assertNull($parent->getParentClass());
    }

    /**
     * Tests interfaces
     *
     * @see     xp://lang.XPClass#isInterface
     * @see     xp://lang.XPClass#getInterfaces
     */
    #[@test]
    public function interfaces() {
      $this->assertFalse($this->class->isInterface());
      $interfaces= $this->class->getInterfaces();
      $this->assertArray($interfaces);
      foreach ($interfaces as $interface) {
        $this->assertClass($interface, 'lang.XPClass');
        $this->assertTrue($interface->isInterface());
      }
    }

    /**
     * Tests the constructor
     *
     * @see     xp://lang.XPClass#hasConstructor
     * @see     xp://lang.XPClass#getConstructor
     */
    #[@test]
    public function constructor() {
      $this->assertTrue($this->class->hasConstructor());
      if ($constructor= $this->class->getConstructor()) {
        $this->assertClass($constructor, 'lang.reflect.Constructor');
      }
    }

    /**
     * Tests the field reflection
     *
     * @see     xp://lang.XPClass#getFields
     */
    #[@test]
    public function fields() {
      $fields= $this->class->getFields();
      $this->assertArray($fields);
      foreach ($fields as $field) {
        $this->assertClass($field, 'lang.reflect.Field');
      }
    }

    /**
     * Tests the field reflection for the "date" field
     *
     * @see     xp://lang.XPClass#getField
     * @see     xp://lang.XPClass#hasField
     */
    #[@test]
    public function dateField() {
      $this->assertTrue($this->class->hasField('date'));
      if ($field= $this->class->getField('date')) {
        $this->assertClass($field, 'lang.reflect.Field');
        $this->assertEquals('date', $field->getName());
        $this->assertEquals('util.Date', $field->getType());
        $this->assertTrue($this->class->equals($field->getDeclaringClass()));
      }
    }

    /**
     * Tests retrieving the "date" field's value
     *
     * @see     xp://lang.reflect.Field#get
     */
    #[@test]
    public function dateFieldValue() {
      if ($field= $this->class->getField('date')) {
        $this->assertClass($field->get($this->class->newInstance()), 'util.Date');
      }
    }

    /**
     * Tests retrieving the "date" field's value on a wrong object
     *
     * @see     xp://lang.reflect.Field#get
     */
    #[@test, @expect('lang.IllegalArgumentException')]
    public function dateFieldValueOnWrongObject() {
      if ($field= $this->class->getField('date')) {
        $field->get(new Object());
      }
    }
    
    /**
     * Tests the method reflection
     *
     * @see     xp://lang.XPClass#getMethods
     */
    #[@test]
    public function methods() {
      $methods= $this->class->getMethods();
      $this->assertArray($methods);
      foreach ($methods as $method) {
        $this->assertClass($method, 'lang.reflect.Method');
      }
    }
    
    /**
     * Tests the method reflection for the getDate() method
     *
     * @see     xp://lang.XPClass#getMethod
     * @see     xp://lang.XPClass#hasMethod
     */
    #[@test]
    public function getDateMethod() {
      $this->assertTrue($this->class->hasMethod('getDate'));
      if ($method= $this->class->getMethod('getDate')) {
        $this->assertClass($method, 'lang.reflect.Method');
        $this->assertEquals('getDate', $method->getName(TRUE));
        $this->assertTrue($this->class->equals($method->getDeclaringClass()));
        $this->assertEquals('util.Date', $method->getReturnType());
      }
    }

    /**
     * Tests the method argument reflection for the setDate() method
     *
     * @see     xp://lang.reflect.Routine#numArguments
     * @see     xp://lang.reflect.Routine#getArgument
     * @see     xp://lang.reflect.Argument
     */
    #[@test]
    public function setDateMethodArguments() {
      if ($method= $this->class->getMethod('setDate')) {
        $this->assertEquals(1, $method->numArguments());
        if ($argument= $method->getArgument(0)) {
          $this->assertClass($argument, 'lang.reflect.Argument');
          $this->assertEquals('date', $argument->getName());
          $this->assertEquals('util.Date', $argument->getType());
          $this->assertTrue($argument->isPassedByReference());
        }
        $this->assertNull($method->getArgument(1));
      }
    }

    /**
     * Tests invoking the setTrace() method which will always throw an 
     * IllegalStateException.
     *
     * @see     xp://lang.reflect.Method#invoke
     */
    #[@test, @expect('lang.IllegalStateException')]
    public function invokeSetTrace() {
      if ($method= $this->class->getMethod('setTrace')) {
        $method->invoke($this->class->newInstance(), array(NULL));
      }
    }

    /**
     * Tests invoking the setTrace() method on a wrong object
     *
     * @see     xp://lang.reflect.Method#invoke
     */
    #[@test, @expect('lang.IllegalArgumentException')]
    public function invokeSetTraceOnWrongObject() {
      if ($method= $this->class->getMethod('setTrace')) {
        $method->invoke(new Object(), array(NULL));
      }
    }
    
    /**
     * Tests annotations
     *
     * @see     xp://lang.XPClass#getAnnotations
     * @see     xp://lang.XPClass#hasAnnotations
     */
    #[@test]
    public function annotations() {
      $this->assertTrue($this->class->hasAnnotations());
      $annotations= $this->class->getAnnotations();
      $this->assertArray($annotations);
    }

    /**
     * Tests annotation "test"
     *
     * @see     xp://lang.XPClass#getAnnotation
     * @see     xp://lang.XPClass#hasAnnotation
     */
    #[@test]
    public function testAnnotation() {
      $this->assertTrue($this->class->hasAnnotation('test'));
      $this->assertEquals('Annotation', $this->class->getAnnotation('test'));
    }
    
    /**
     * Tests dynamic class loading via forName()
     *
     * @see     xp://lang.XPClass#forName
     */
    #[@test]
    public function forName() {
      $class= XPClass::forName('util.Date');
      $this->assertClass($class, 'lang.XPClass');
      $this->assertEquals('util.Date', $class->getName());
    }
    
    /**
     * Tests that forName() throws a lang.ClassNotFoundException when 
     * passed with the name of a nonexistant class
     *
     * @see     xp://lang.ClassNotFoundException
     * @see     xp://lang.XPClass#forName
     */
    #[@test, @expect('lang.ClassNotFoundException')]
    public function nonExistantforName() {
      $class= XPClass::forName('class.does.not.Exist');
    }
    
    /**
     * Tests generic return value
     *
     * @see     xp://net.xp_framework.unittest.reflection.TestClass#getMap
     */
    #[@test]
    public function genericReturnValue() {
      $method= $this->class->getMethod('getMap');
      $this->assertEquals('array<string, &lang.Object>', $method->getReturnType());
    }
  }
?>
