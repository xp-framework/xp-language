<?php
  import('xml.XML');
  import('xml.XMLParser');
  
  class Tree extends XML {
    /*public*/var 
      $root,
      $children;
      
    /*private*/var
      $_cnt,
      $_cdata,
      $_objs;
    
    var $nodeType= 'node';
		
    function __construct($params= NULL) {
      $this->_objs= array();		
      $this->root= new Node(array(
        'name'  => 'document'
      ));
      XML::__construct($params);
    }
    
    function getSource($indent= TRUE) {
      return (isset($this->root)
        ? $this->root->getSource($indent)
        : NULL
      );
    }
        
    function &addChild($child) {
      return $this->root->addChild($child);
    }

    function _pCallStartElement($parser, $name, $attrs) {
      $this->_cdata= "";

      $element= new $this->nodeType(array(
        'name'          => $name,
        'attribute'     => $attrs,
        'content'       => ''
      ));  

      if (!isset($this->_cnt)) {
        $this->root= &$element;
        $this->_objs[1]= &$element;
        $this->_cnt= 1;
      } else {
        $this->_cnt++;
        $this->_objs[$this->_cnt]= &$element;
      }
    }
   
    function _pCallEndElement($parser, $name) {
      if ($this->_cnt > 1) {
        $node= &$this->_objs[$this->_cnt];
        $node->content= $this->_cdata;
        $parent= &$this->_objs[$this->_cnt- 1];
        $parent->addChild($node);
        //var_dump('adding '.$node->name.' ['.$this->_cnt.'] to '.$parent->name.' ['.($this->_cnt- 1).']');
        $this->_cdata= "";
      }
      $this->_cnt--;
    }

    function _pCallCData($parser, $cdata) {
      $this->_cdata.= $cdata;
    }
    
    function _pCallDefault($parser, $data) {
    }
       
    function fromString($string) {
      $parser= new XMLParser();
      $parser->callback= &$this;
      $parser->dataSource= $string;
      $result= $parser->parse($string, 1);
      $parser->__destruct();
      return $result;
    }
    
    function fromFile($fileName) {
      $parser= new XMLParser();
      $parser->callback= &$this;
      $parser->dataSource= $fileName;
      
      $fp= @fopen($fileName, 'r');
      if (!$fp) {
        return throw(E_IO_EXCEPTION, 'cannot read from '.$fileName);
      }
      
      $parser->parse(fread($fp, filesize($fileName)));
      fclose($fp);
      $parser->__destruct();
    }
  }
?>
