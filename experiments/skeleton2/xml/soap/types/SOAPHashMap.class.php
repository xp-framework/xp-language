<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'xml.soap.SOAPNode',
    'xml.soap.types.SoapType'
  );

  /**
   * Hashmap type as serialized and recogned by Apache SOAP.
   *
   * @see      xp://xml.soap.types.SoapType
   * @purpose  HashMap type
   */
  class SOAPHashMap extends SoapType {

    /**
     * Constructor
     *
     * @access  public
     * @param   array params
     */
    public function __construct($params) {
      $this->item= new SOAPNode('hash', NULL, array(
        'xmlns:hash'  => 'http://xml.apache.org/xml-soap',
        'xsi:type'    => 'hash:Map'
      ));
      foreach ($params as $key => $value) {
        $item= $this->item->addChild(new SOAPNode('item'));
        $item->addChild(new SOAPNode('key', $key, array(
          'xsi:type'  => 'xsd:string'
        )));
        $this->item->_recurse($item, array('value' => $value));
      }
      parent::__construct();
    }
    
    /**
     * Return a string representation for use in SOAP
     *
     * @access  public
     * @return  mixed
     */
    public function toString() {
      return '';
    }
    
    /**
     * Returns this type's name
     *
     * @access  public
     * @return  string
     */
    public function getType() {
      return 'hash:Map';
    }
  }
?>
