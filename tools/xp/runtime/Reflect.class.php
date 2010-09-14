<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  $package= 'xp.runtime';
 
  uses('util.cmd.Console');

  /**
   * Dumps reflection information about a class
   *
   * @purpose  Tool
   */
  class xp�runtime�Reflect extends Object {
  
    /**
     * Prints class name (and generic components if this class is a 
     * generic definition)
     *
     * @param   lang.XPClass class
     */
    protected static function printName(XPClass $class) {
      Console::write($class->getName());
      if ($class->isGenericDefinition()) {
        Console::write('<', implode(', ', $class->genericComponents()), '>');
      }
    }
  
    /**
     * Prints methods - static first, rest then
     *
     * @param   lang.reflect.Method[] methods
     */
    protected static function printMethods(array $methods) {
      $i= 0;
      foreach ($methods as $method) {
        if (!Modifiers::isStatic($method->getModifiers())) continue;
        Console::writeLine('  ', $method);
        $i++;
      }

      $i && Console::writeLine();
      $i= 0;
      foreach ($methods as $method) {
        if (Modifiers::isStatic($method->getModifiers())) continue;
        Console::writeLine('  ', $method);
        $i++;
      }
    }
  
    /**
     * Handles enums
     *
     * @param   lang.XPClass class
     */
    protected static function printEnum(XPClass $enum) {
      Console::write(implode(' ', Modifiers::namesOf($enum->getModifiers())));
      Console::write(' enum ');
      self::printName($enum);

      // Parent class, if not lang.Enum
      if (!XPClass::forName('lang.Enum')->equals($parent= $enum->getParentClass())) {
        Console::write(' extends ');
        self::printName($parent);
      }

      // Interfaces
      if ($interfaces= $enum->getInterfaces()) {
        Console::write(' implements ');
        $s= sizeof($interfaces)- 1;
        foreach ($interfaces as $i => $iface) {
          self::printName($iface);
          $i < $s && Console::write(', ');
        }
      }

      // Members
      Console::writeLine(' {');
      foreach (Enum::valuesOf($enum) as $member) {
        Console::write('  ',  $member->ordinal(), ': ', $member->name());
        $class= $member->getClass();
        if ($class->isSubclassOf($enum)) {
          Console::writeLine(' {');
          foreach ($class->getDeclaredMethods() as $method) {
            Console::writeLine('    ', $method);
            $i++;
          }
          Console::writeLine('  }');
        } else {
          Console::writeLine();
        }
        $i++;
      }
      
      // Methods
      $i && Console::writeLine();
      self::printMethods($enum->getMethods());

      Console::writeLine('}');
    }

    /**
     * Handles interfaces
     *
     * @param   lang.XPClass class
     */
    protected static function printInterface(XPClass $iface) {
      Console::write(implode(' ', Modifiers::namesOf($iface->getModifiers() ^ MODIFIER_ABSTRACT)));
      Console::write(' interface ');
      self::printName($iface);

      // Interfaces are this interface's parents
      if ($interfaces= $iface->getDeclaredInterfaces()) {
        Console::write(' extends ');
        $s= sizeof($interfaces)- 1;
        foreach ($interfaces as $i => $parent) {
          self::printName($parent);
          $i < $s && Console::write(', ');
        }
      }
      Console::writeLine(' {');

      $i= 0;
      if ($iface->hasConstructor()) {
        Console::writeLine('  ', $iface->getConstructor());
        $i++;
      }

      // Methods
      foreach ($iface->getMethods() as $method) {
        Console::write('  ', $method->getReturnTypeName(), ' ', $method->getName(), '(');
        if ($params= $method->getParameters()) {
          $s= sizeof($params)- 1;
          foreach ($params as $i => $param) {
            Console::write($param->getTypeName(), ' $', $param->getName());
            $i < $s && Console::write(', ');
          }
        }
        Console::write(')');
        Console::writeLine();
      }

      Console::writeLine('}');
    }

    /**
     * Handles classes
     *
     * @param   lang.XPClass class
     */
    protected static function printClass(XPClass $class) {
      Console::write(implode(' ', Modifiers::namesOf($class->getModifiers())));
      Console::write(' class ');
      self::printName($class);
      
      if ($parent= $class->getParentClass()) {
        Console::write(' extends ');
        self::printName($parent);
      }
      if ($interfaces= $class->getDeclaredInterfaces()) {
        Console::write(' implements ');
        $s= sizeof($interfaces)- 1;
        foreach ($interfaces as $i => $iface) {
          self::printName($iface);
          $i < $s && Console::write(', ');
        }
      }
      
      // Fields
      Console::writeLine(' {');
      $i= 0;
      foreach ($class->getFields() as $field) {
        Console::writeLine('  ', $field);
        $i++;
      }
      
      // Constructor
      $i && Console::writeLine();
      $i= 0;
      if ($class->hasConstructor()) {
        Console::writeLine('  ', $class->getConstructor());
        $i++;
      }
      
      // Methods
      $i && Console::writeLine();
      self::printMethods($class->getMethods());
      Console::writeLine('}');
    }

    /**
     * Main
     *
     * @param   string[] args
     */
    public static function main(array $args) {
      try {
        $class= XPClass::forName($args[0]);
      } catch (ClassNotFoundException $e) {
        Console::$err->writeLine('*** ', $e->getMessage(), ', tried all of {');
        foreach ($e->getLoaders() as $loader) {
          Console::$err->writeLine('  ', $loader);
        }
        Console::$err->writeLine('}');
        exit(1);
      }
      
      Console::writeLine('@', $class->getClassLoader());
      if ($class->isInterface()) {
        self::printInterface($class);
      } else if ($class->isEnum()) {
        self::printEnum($class);
      } else {
        self::printClass($class);
      }
    }
  }
?>
