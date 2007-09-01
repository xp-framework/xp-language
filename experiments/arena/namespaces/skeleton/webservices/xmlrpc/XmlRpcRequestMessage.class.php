<?php
/* This class is part of the XP framework
 *
 * $Id: XmlRpcRequestMessage.class.php 10594 2007-06-11 10:04:54Z friebe $ 
 */

  namespace webservices::xmlrpc;

  uses('webservices.xmlrpc.XmlRpcMessage');

  /**
   * XmlRpc request message. 
   *
   * @ext      xml
   * @see      xp://webservices.xmlrpc.XmlRpcResponseMessage
   * @purpose  Wrap XML-RPC Request
   */
  class XmlRpcRequestMessage extends XmlRpcMessage {
  
    /**
     * Create message with the given methodName
     *
     * @param   string method
     */
    public function create($method= ) {
      $this->tree= new xml::Tree(XMLRPC_METHODCALL);
      $this->tree->root->addChild(new xml::Node('methodName', $method));
    }
    
    /**
     * Construct a XML-RPC message from a string
     *
     * <code>
     *   $msg= &XmlRpcRequestMessage::fromString('<methodCall>...</methodCall>');
     * </code>
     *
     * @param   string string
     * @return  webservices.xmlrpc.XmlRpcMessage
     */
    public static function fromString($string) {
      $msg= new XmlRpcRequestMessage();
      $msg->tree= xml::Tree::fromString($string);

      // Set class and method members from XML structure
      $target= $msg->tree->root->children[0]->getContent();
      list($msg->class, $msg->method)= explode('.', $target);

      return $msg;
    }
    
    /**
     * Set the data for the message.
     *
     * @param   mixed arr
     */
    public function setData($arr) {
      $encoder= new XmlRpcEncoder();

      $params= $this->tree->root->addChild(new xml::Node('params'));
      if (sizeof($arr)) foreach (array_keys($arr) as $idx) {
        $n= $params->addChild(new xml::Node('param'));
        $n->addChild($encoder->encode($arr[$idx]));
      }
    }
    
    /**
     * Return the data from the message.
     *
     * @return  mixed
     */
    public function getData() {
      $ret= array();
      foreach (array_keys($this->tree->root->children) as $idx) {
        if ('params' != $this->tree->root->children[$idx]->getName())
          continue;
        
        // Process params node
        $decoder= new XmlRpcDecoder();
        foreach (array_keys($this->tree->root->children[$idx]->children) as $params) {
          $ret[]= $decoder->decode($this->tree->root->children[$idx]->children[$params]->children[0]);
        }
        
        return $ret;
      }
      
      throw(new lang::IllegalStateException('No node "params" found.'));
    }
  }
?>