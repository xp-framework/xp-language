<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Represents a single definition of a word
   *
   * @see      xp://org.dict.DictClient
   * @purpose  Definition wrapper
   */
  class DictDefinitionEntry extends Object {
    public
      $database     = '',
      $definition   = '';

    /**
     * Constructor
     *
     * @access  public
     * @param   string database
     * @param   string definition
     */
    public function __construct($database, $definition) {
      $this->database= $database;
      $this->definition= $definition;
    }

    /**
     * Get Database
     *
     * @access  public
     * @return  string
     */
    public function getDatabase() {
      return $this->database;
    }

    /**
     * Get Definition
     *
     * @access  public
     * @return  string
     */
    public function getDefinition() {
      return $this->definition;
    }

  }
?>
