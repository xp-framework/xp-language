<?php
/* This class is part of the XP framework
 *
 * $Id: HashmapMapping.class.php 10594 2007-06-11 10:04:54Z friebe $ 
 */

  namespace remote::protocol;

  uses('util.Hashmap', 'remote.protocol.SerializerMapping');

  /**
   * Mapping for Hashmaps
   *
   * @see      xp://remote.protocol.Serializer
   * @purpose  Mapping
   */
  class HashmapMapping extends lang::Object implements SerializerMapping {

    /**
     * Returns a value for the given serialized string
     *
     * @param   server.protocol.Serializer serializer
     * @param   remote.protocol.SerializedData serialized
     * @param   array<string, mixed> context default array()
     * @return  mixed
     */
    public function valueOf($serializer, $serialized, $context= array()) {
      // No implementation
    }

    /**
     * Returns an on-the-wire representation of the given value
     *
     * @param   server.protocol.Serializer serializer
     * @param   lang.Object value
     * @param   array<string, mixed> context default array()
     * @return  string
     */
    public function representationOf($serializer, $value, $context= array()) {
      return $serializer->representationOf($value->_hash, $context);
    }
    
    /**
     * Return XPClass object of class supported by this mapping
     *
     * @return  lang.XPClass
     */
    public function handledClass() {
      return lang::XPClass::forName('util.Hashmap');
    }
  } 
?>
