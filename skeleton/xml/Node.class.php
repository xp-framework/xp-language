<?php
/* This class is part of the XP framework
 *
 * $Id$
 *
 */

  uses('xml.XML', 'xml.PCData', 'xml.CData');
  
  define('INDENT_DEFAULT',    0);
  define('INDENT_WRAPPED',    1);
  define('INDENT_NONE',       2);

  /**
   * Represents a node
   *
   * @see   xp://xml.Tree#addChild
   */
  class Node extends XML {
    var 
      $name         = '',
      $attribute    = array(),
      $content      = '',
      $children     = array();

    /**
     * Constructor
     *
     * <code>
     *   $n= &new Node('document');
     *   $n= &new Node('text', 'Hello World');
     *   $n= &new Node('article', '', array('id' => 42));
     *   $n= &new Node(array(
     *     'name'    => 'changedby',
     *     'content' => 'me'
     *   ));
     * </code>
     *
     * @access  public
     * @param   mixed*
     * @throws  IllegalArgumentException
     */
    function __construct() {
      switch (func_num_args()) {
        case 0: 
          parent::__construct();
          break;
          
        case 1:
          if (is_array($arg= func_get_arg(0))) {
            parent::__construct($arg);
            break;
          }
          $this->name= $arg;
          break;
          
        case 2:
          list($this->name, $this->content)= func_get_args();
          parent::__construct();
          break;
          
        case 3:
          list($this->name, $this->content, $this->attribute)= func_get_args();
          parent::__construct();
          break;
          
        default:
          return throw(new IllegalArgumentException('Wrong number of arguments passed'));
      }
    }

    /**
     * Recurse an array
     *
     * @access  protected
     * @param   &xml.Node e element to add array to
     * @param   array a
     */
    function _recurse(&$e, $a) {
      foreach (array_keys($a) as $field) {
        $child= &$e->addChild(new Node(is_numeric($field) 
          ? preg_replace('=s$=', '', $e->name) 
          : $field
        ));
        if (is_array($a[$field])) {
          $this->_recurse($child, $a[$field]);
        } else if (is_object($a[$field])) {
          $this->_recurse($child, get_object_vars($a[$field]));
        } else {
          $child->setContent($a[$field]);
        }
      }
    }
    
    /**
     * Create a node from an array
     *
     * Usage example:
     * <code>
     *   $n= &Node::fromArray($array, 'elements');
     * </code>
     *
     * @model   static
     * @access  public
     * @param   array arr
     * @param   string name default 'array'
     * @return  &xml.Node
     */
    function &fromArray($arr, $name= 'array') {
      $n= &new Node($name);
      $n->_recurse($n, $arr);
      return $n;  
    }
    
    /**
     * Create a node from an object. Will use class name as node name
     * if the optional argument name is omitted.
     *
     * Usage example:
     * <code>
     *   $n= &Node::fromObject($object);
     * </code>
     *
     * @model   static
     * @access  public
     * @param   object obj
     * @param   string name default NULL
     * @return  &xml.Node
     */
    function &fromObject($obj, $name= NULL) {
      return Node::fromArray(
        get_object_vars($obj), 
        (NULL === $name) ? get_class($obj) : $name
      );
    }
    
    /**
     * Set content
     *
     * @access  public
     * @param   string contennt
     */
    function setContent($content) {
      $this->content= $content;
    }
    
    /**
     * Get content (all CDATA)
     *
     * @access  public
     * @return  string content
     */
    function getContent() {
      return $this->content;
    }

    /**
     * Set an attribute
     *
     * @access  public
     * @param   string name
     * @param   string value
     */
    function setAttribute($name, $value) {
      $this->attribute[$name]= $value;
    }
    
    /**
     * Retrieve an attribute by its name
     *
     * @access  public
     * @param   string name
     * @return  string
     */
    function getAttribute($name) {
      return $this->attribute[$name];
    }

    /**
     * Checks whether a specific attribute is existant
     *
     * @access  public
     * @param   string name
     * @return  bool
     */
    function hasAttribute($name) {
      return isset($this->attribute[$name]);
    }
    
    /**
     * Retrieve XML representation
     *
     * Setting indent to 0 (INDENT_DEFAULT) yields this result:
     * <pre>
     *   <item>  
     *     <title>Website created</title>
     *     <link/>
     *     <description>The first version of the XP web site is online</description>
     *     <dc:date>2002-12-27T13:10:00</dc:date>
     *   </item>
     * </pre>
     *
     * Setting indent to 1 (INDENT_WRAPPED) yields this result:
     * <pre>
     *   <item>
     *     <title>
     *       Website created
     *     </title>
     *     <link/>
     *     <description>
     *       The first version of the XP web site is online
     *     </description>
     *     <dc:date>
     *       2002-12-27T13:10:00
     *     </dc:date>  
     *   </item>
     * </pre>
     *
     * Setting indent to 2 (INDENT_NONE) yields this result (wrapped for readability,
     * returned XML is on one line):
     * <pre>
     *   <item><title>Website created</title><link></link><description>The 
     *   first version of the XP web site is online</description><dc:date>
     *   2002-12-27T13:10:00</dc:date></item>
     * </pre>
     *
     * @access  public
     * @param   int indent default INDENT_WRAPPED
     * @param   string inset default ''
     * @return  string XML
     */
    function getSource($indent= INDENT_WRAPPED, $inset= '') {
      $xml= $inset.'<'.$this->name;
      if (is_a($this->content, 'PCData')) {
        $content= $this->content->pcdata;
      } elseif (is_a($this->content, 'CData')) {
        $content= '<![CDATA['.str_replace(']]>', ']]&gt;', $this->content->cdata).']]>';
      } else {
        if (is_float ($this->content)) 
          $content= number_format($this->content, 0, NULL, NULL);
        else
          $content= htmlspecialchars($this->content);
      }

      switch ($indent) {
        case INDENT_DEFAULT:
        case INDENT_WRAPPED:
          if (!empty($this->attribute)) {
            $sep= (sizeof($this->attribute) < 3) ? '' : "\n".$inset;
            foreach (array_keys($this->attribute) as $key) {
              $xml.= $sep.' '.$key.'="'.htmlspecialchars($this->attribute[$key]).'"';
            }
            $xml.= $sep;
          }

          // No content and no children => close tag
          if (0 == strlen($content)) {
            if (empty($this->children)) {
              return $xml."/>\n";
            }
            $xml.= '>';
          } else {
            $xml.= '>'.($indent ? "\n  ".$inset.$content : trim($content));
          }

          if (!empty($this->children)) {
            $xml.= ($indent ? '' : $inset)."\n";
            foreach (array_keys($this->children) as $key) {
              $xml.= $this->children[$key]->getSource($indent, $inset.'  ');
            }
            $xml= ($indent ? substr($xml, 0, -1) : $xml).$inset;
          }
          return $xml.($indent ? "\n".$inset : '').'</'.$this->name.">\n";
          
        case INDENT_NONE:
          foreach (array_keys($this->attribute) as $key) {
            $xml.= ' '.$key.'="'.htmlspecialchars($this->attribute[$key]).'"';
          }
          $xml.= '>'.trim($content);
          
          if (!empty($this->children)) {
            foreach (array_keys($this->children) as $key) {
              $xml.= $this->children[$key]->getSource($indent, $inset);
            }
          }
          return $xml.'</'.$this->name.'>';
      }
    }
    
    /**
     * Add a child node
     *
     * @access  public
     * @param   &xml.Node child
     * @return  &xml.Node added child
     * @throws  lang.IllegalArgumentException
     */
    function &addChild(&$child) {
      if (!is_a($child, 'Node')) {
        return throw(new IllegalArgumentException(
          'Parameter child must be an xml.Node (given: '.xp::typeOf($child).')'
        ));
      }

      $this->children[]= &$child;
      return $child;
    }
  }
?>
