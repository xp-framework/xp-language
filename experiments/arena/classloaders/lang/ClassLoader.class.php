<?php
/* This class is part of the XP framework
 * 
 * $Id: ClassLoader.class.php 9281 2007-01-15 17:46:00Z kiesel $
 */
 
  uses('lang.ClassNotFoundException');
  
  /** 
   * Loads a class
   * 
   * @purpose  Load classes
   * @test     xp://net.xp_framework.unittest.reflection.ClassLoaderTest
   * @see      xp://lang.XPClass#forName
   */
  class ClassLoader extends Object {
    public 
      $path= '';
    
    /**
     * Constructor. 
     *
     * The path argument is optional and lets you define where to search for
     * classes (it will be prefixed to the class name)
     *
     * @param   string path default '' classpath
     */
    public function __construct($path= '') {
      $this->path= $path;
    }

    /**
     * Creates a string representation
     *
     * @return  string
     */
    public function toString() {
      return (
        $this->getClassName().
        ($this->path ? '<'.rtrim($this->path, '.').'>' : '').
        '(search= '.xp::stringOf(explode(PATH_SEPARATOR, ini_get('include_path'))).')'
      );
    }

    /**
     * Load class bytes
     *
     * @param   string name fully qualified class name
     * @return  string
     */
    public function loadClassBytes($name) {
      return file_get_contents($this->findClass($name));
    }
    
    /**
     * Retrieve the default class loader
     *
     * @return  lang.ClassLoader
     */
    public static function getDefault() {
      return xp::$registry['loader'];
    }
    
    /**
     * Checks whether this loader can provide the requested class
     *
     * @param   string class
     * @return  bool
     */
    public function providesClass($class) {
      return file_exists($this->path.DIRECTORY_SEPARATOR.strtr($class, '.', '/').'.class.php');
    }
    
    /**
     * Load the class by the specified name
     *
     * @param   string class fully qualified class name io.File
     * @return  lang.XPClass
     * @throws  lang.ClassNotFoundException in case the class can not be found
     */
    public function loadClass($class) {
      $name= xp::reflect($class);

      if (!class_exists($name) && !interface_exists($name)) {
        $qname= $this->path.$class;
        if (FALSE === include(strtr($qname, '.', DIRECTORY_SEPARATOR).'.class.php')) {
          throw new ClassNotFoundException('Class "'.$qname.'" not found');
        }
        xp::$registry['class.'.$name]= $qname;
        is_callable(array($name, '__static')) && call_user_func(array($name, '__static'));
      }

      return new XPClass($name);
    }

    /**
     * Define a class with a given name
     *
     * @param   string class fully qualified class name
     * @param   string bytes sourcecode of the class
     * @return  lang.XPClass
     * @throws  lang.FormatException in case the class cannot be defined
     */
    protected function _defineClassFromBytes($class, $bytes) {
      $name= xp::reflect($class);

      if (!class_exists($name) && !interface_exists($name)) {
        
        // Load InlineLoader
        XPClass::forName('lang.InlineLoader');
        InlineLoader::setClassBytes($class, $bytes);
        if (FALSE === include('inline://'.$class)) {
          throw new FormatException('Cannot define class "'.$class.'"');
        }
        InlineLoader::removeClassBytes($class);
        
        if (!class_exists($name) && !interface_exists($name)) {
          throw new FormatException('Class "'.$class.'" not defined');
        }
        xp::$registry['class.'.$name]= $class;
        is_callable(array($name, '__static')) && call_user_func(array($name, '__static'));
      }      

      return new XPClass($name);
    }
    
    /**
     * Define a class with a given name
     *
     * @param   string class fully qualified class name
     * @param   string parent either sourcecode of the class or FQCN of parent
     * @param   string[] interfaces default NULL FQCNs of implemented interfaces
     * @param   string bytes default NULL inner sourcecode of class (containing {}) 
     * @return  lang.XPClass
     * @throws  lang.FormatException in case the class cannot be defined
     * @throws  lang.ClassNotFoundException if given parent class does not exist
     */
    public function defineClass($class, $parent, $interfaces= NULL, $bytes= NULL) {
      
      // If invoked with less than four arguments, old behaviour will be executed
      if (NULL === $bytes) {
        return $this->_defineClassFromBytes($this->path.$class, $parent);
      }
      
      $name= xp::reflect($class);
      if (!class_exists($name)) {
        $qname= $this->path.$class;
        $parentName= xp::reflect($parent);
        
        if (!class_exists($parentName)) {
          throw(new ClassNotFoundException('Parent class '.$parent.' does not exist.'));
        }
        
        $newBytes= 'class '.$name.' extends '.$parentName;
        if (sizeof($interfaces)) {
          $newBytes.= ' implements ';

          $ifaces= array();
          foreach ($interfaces as $i) { $ifaces[]= xp::reflect($i); }
          
          $newBytes.= implode(', ', $ifaces);
        }
        
        $newBytes.= ' '.$bytes;
        
        return $this->_defineClassFromBytes($qname, $newBytes);
      }
      
      return new XPClass($name);
    }
    
    /**
     * Loads a resource.
     *
     * @param   string filename name of resource
     * @return  string
     * @throws  lang.ElementNotFoundException in case the resource cannot be found
     */
    public function getResource($filename) {
      foreach (array_unique(explode(PATH_SEPARATOR, ini_get('include_path'))) as $dir) {
        if (!file_exists($dir.DIRECTORY_SEPARATOR.$filename)) continue;
        return file_get_contents($dir.DIRECTORY_SEPARATOR.$filename);
      }
    
      return raise('lang.ElementNotFoundException', 'Could not load resource '.$filename);
    }
    
    /**
     * Retrieve a stream to the resource
     *
     * @param   string filename name of resource
     * @return  &io.File
     * @throws  lang.ElementNotFoundException in case the resource cannot be found
     */
    public function getResourceAsStream($filename) {
      foreach (array_unique(explode(PATH_SEPARATOR, ini_get('include_path'))) as $dir) {
        if (!file_exists($dir.DIRECTORY_SEPARATOR.$filename)) continue;
        return new File($filename);
      }
    
      return raise('lang.ElementNotFoundException', 'Could not load resource '.$filename);
    }

    
    /**
     * Fetch instance of classloader by the path to the archive
     *
     * @param   string path
     * @return  lang.archive.ArchiveClassLoader
     */
    public static function instanceFor($path) {
      static $pool= array();
      
      if (!isset($pool[$path])) {
        $pool[$path]= new self($path);
      }
      
      return $pool[$path];
    }
  }
?>
