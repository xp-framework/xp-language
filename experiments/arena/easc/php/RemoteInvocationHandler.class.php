<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * (Insert class' description here)
   *
   * @ext      extension
   * @see      reference
   * @purpose  purpose
   */
  class RemoteInvocationHandler extends Object {
    var
      $oid= NULL;

    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function &newInstance($oid, &$handler) {
      $i= &new RemoteInvocationHandler();
      $i->oid= $oid;
      $i->handler= &$handler;
      return $i;
    }
    
    /**
     * Processes a method invocation on a proxy instance and returns
     * the result.
     *
     * @access  public
     * @param   lang.reflect.Proxy proxy
     * @param   string method the method name
     * @param   mixed* args an array of arguments
     * @return  mixed
     */
    function invoke(&$proxy, $method, $args) { 
      return $this->handler->invoke($this->oid, $method, $args);
    }
  
  } implements(__FILE__, 'lang.reflect.InvocationHandler');
?>
