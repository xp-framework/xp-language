<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'xml.Tree',
    'xml.Node',
    'xml.soap.SOAPNode',
    'xml.soap.SOAPFault'
  );
  
  /**
   * SOAP Message
   *
   * @see   xp://xml.Tree
   */
  class SOAPMessage extends Tree {
    var 
      $body         = '',
      $namespace    = 'ctl',
      $encoding     = XML_ENCODING_DEFAULT,
      $nodeType     = 'SOAPNode';

    /**
     * Create a message
     *
     * @access  public
     * @param   string action
     * @param   string method
     */
    function create($action, $method) {
      $this->action= $action;
      $this->method= $method;

      $this->root= &new Node(array(
        'name'          => 'SOAP-ENV:Envelope',
        'attribute'     => array(
          'xmlns:SOAP-ENV'              => 'http://schemas.xmlsoap.org/soap/envelope/', 
          'xmlns:xsd'                   => 'http://www.w3.org/2001/XMLSchema', 
          'xmlns:xsi'                   => 'http://www.w3.org/2001/XMLSchema-instance', 
          'xmlns:SOAP-ENC'              => 'http://schemas.xmlsoap.org/soap/encoding/', 
          'xmlns:si'                    => 'http://soapinterop.org/xsd', 
          'SOAP-ENV:encodingStyle'      => 'http://schemas.xmlsoap.org/soap/encoding/',
          'xmlns:'.$this->namespace     => $this->action   
        )
      ));
      $this->root->addChild(new Node(array('name' => 'SOAP-ENV:Body')));
      $this->root->children[0]->addChild(new Node(array('name' => $this->namespace.':'.$this->method)));
    }
    
    /**
     * Setzt die Daten
     *
     * @param   array arr Array aller zu übergebender Daten
     */
    function setData($arr) {
      $node= &new SOAPNode();
      $node->namespace= $this->namespace;
      $node->fromArray($arr, 'item');
      if (empty($node->children)) return;
      
      // Copy all of node's children to root element
      foreach (array_keys($node->children) as $i) {
        $this->root->children[0]->children[0]->addChild($node->children[$i]);
      }
    }

    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */    
    function _recurseData(&$node, $names= FALSE, $context= NULL) {
      $results= array();
      if (!empty($node->children)) foreach ($node->children as $child) {
        $idx= $names ? $child->name : sizeof($results);
        
        if (
          isset($child->attribute['xsi:null']) or       // Java
          isset($child->attribute['xsi:nil'])           // SOAP::Lite
        ) {
          $results[$idx]= NULL;
          continue;
        }
       
        // Typenabhängig
        if (!isset($child->attribute['xsi:type']) || !preg_match(
          '#^([^:]+):([^\[]+)(\[[0-9+]\])?$#', 
          $child->attribute['xsi:type'],
          $regs
        )) {
          // Zum Beispiel SOAP-ENV:Fault
          $regs= array(0, 'xsd', 'string');
        }

        // SOAP-ENC:arrayType="xsd:anyType[4]"
        if (isset($child->attribute['SOAP-ENC:arrayType'])) {
          $regs[2]= 'Array';
        }
        
        // echo "*** {$child->name} [{$names}, {$context}, {$idx}] is {$regs[2]}\n";
        // echo $child->getSource(0);
        
        switch (strtolower($regs[2])) {
          case 'array':
          case 'vector':
            $results[$idx]= $this->_recurseData($child, FALSE, 'ARRAY');
            break;
            
          case 'map':
            // <old_data xmlns:ns4="http://xml.apache.org/xml-soap" xsi:type="ns4:Map">
            // <item>
            // <key xsi:type="xsd:string">Nachname</key>
            // <value xsi:type="xsd:string">Braun</value>
            // </item>
            // <item>
            // <key xsi:type="xsd:string">PLZ</key>
            // <value xsi:type="xsd:string">76135</value>
            // </item>
            // <item>
            if (!empty($node->children)) foreach ($child->children as $item) {
              $key= $item->children[0]->getContent($this->encoding);
              $results[$idx][$key]= (empty($item->children[1]->children) 
                ? $item->children[1]->getContent($this->encoding)
                : $this->_recurseData($item->children[1], FALSE, 'MAP')
              );
            }
            break;
   
          case 'soapstruct':
          case 'struct':      
          case 'ur-type':
            if ($regs[1]== 'xsd') {
              $results[$idx]= $this->_recurseData($child, TRUE, 'HASHMAP');
              break;
            }
            
            $n= 'stdClass';
            if (isset($child->attribute['xmlns:xp'])) {
              try(); {
                $n= ClassLoader::loadClass($child->attribute['xmlns:xp']);
              } if (catch('Exception', $e)) {
                $n= 'stdClass';
              }
            }
            $results[$idx]= &new $n();
            foreach ($this->_recurseData($child, TRUE, 'OBJECT') as $key=> $val) {
              $results[$idx]->$key= $val;
            }
            break;
          
          default:
            if (!empty($child->children)) {
              if ($regs[1]== 'xsd') {
                $results[$idx]= $this->_recurseData($child, TRUE, 'STRUCT');
                break;
              }
              
              $results[$idx]= &new stdClass();
              foreach ($this->_recurseData($child, TRUE, 'OBJECT') as $key=> $val) {
                $results[$idx]->$key= $val;
              }
              break;
            }

            $results[$idx]= $child->getContent($this->encoding);
        }
        
        /* HACK */
        if (
          $context == NULL and 
          $child->name != 'item' and
          substr($child->name, 0, 8) != 'c-gensym'
        ) {
          $results[$idx]= &new SOAPNamedItem($child->name, $results[$idx]);
        }

      }
      
      // DEBUG var_dump($results);
      return $results;
    }

    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */    
    function setFault($faultcode, $faultstring, $faultactor= NULL, $detail= NULL) {
      $this->root->children[0]->children[0]= &new SOAPNode();
      $this->root->children[0]->children[0]->fromObject(new SOAPFault(array(
        'faultcode'      => $faultcode,
        'faultstring'    => $faultstring,
        'faultactor'     => $faultactor,
        'detail'         => $detail
      )));
      unset($this->root->children[0]->children[0]->attribute);
      $this->root->children[0]->children[0]->name= 'SOAP-ENV:Fault';
    }

    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function getFault() {
      //echo "+++ SOAPMessage::getFault\n";
      if ($this->root->children[0]->children[0]->name != 'SOAP-ENV:Fault') return NULL;
      
      list($return)= $this->_recurseData($this->root->children[0], FALSE, 'OBJECT');
      return new SOAPFault(array(
        'faultcode'      => $return['faultcode'],
        'faultstring'    => $return['faultstring'],
        'faultactor'     => $return['faultactor'],
        'detail'         => $return['detail']
      ));
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function getData($context= 'ENUM') {
      foreach ($this->root->attribute as $key=> $val) { // Namespace suchen
        if ($val == $this->action) $this->namespace= substr($key, strlen('xmlns:'));
      }
      
      $return= $this->_recurseData($this->root->children[0]->children[0], FALSE, $context);
      return $return;
    }
  }
?>
