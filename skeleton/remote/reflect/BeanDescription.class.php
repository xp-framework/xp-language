<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  define('HOME_INTERFACE',    0);
  define('REMOTE_INTERFACE',  1);

  /**
   * Describes an EJB
   *
   * @see      xp://remote.Remote
   * @purpose  Reflection
   */
  class BeanDescription extends Object {
    var 
      $jndiName   = '',
      $interfaces = array();

    /**
     * Set JndiName
     *
     * @access  public
     * @param   string jndiName
     */
    function setJndiName($jndiName) {
      $this->jndiName= $jndiName;
    }

    /**
     * Get JndiName
     *
     * @access  public
     * @return  string
     */
    function getJndiName() {
      return $this->jndiName;
    }

    /**
     * Set Interfaces
     *
     * @access  public
     * @param   remote.reflect.InterfaceDescription[] interfaces
     */
    function setInterfaces($interfaces) {
      $this->interfaces= $interfaces;
    }

    /**
     * Get Interfaces
     *
     * @access  public
     * @return  remote.reflect.InterfaceDescription[]
     */
    function getInterfaces() {
      return $this->interfaces;
    }

    /**
     * Creates a string representation of this object
     *
     * @access  public
     * @return  string
     */
    function toString() {
      return sprintf(
        "%s@(jndi= %s) {\n".
        "  [Home  ]: %s\n".
        "  [Remote]: %s\n".
        "}",
        $this->getClassName(),
        $this->jndiName,
        $this->interfaces[HOME_INTERFACE] ? str_replace("\n", "\n  ", $this->interfaces[HOME_INTERFACE]->toString()) : '(null)',
        $this->interfaces[REMOTE_INTERFACE] ? str_replace("\n", "\n  ", $this->interfaces[REMOTE_INTERFACE]->toString()) : '(null)'        
      );
    }
  }
?>
