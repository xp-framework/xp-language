<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('rdbms.ResultSet');

  /**
   * Result set
   *
   * @ext      sqlsrv
   * @purpose  Resultset wrapper
   */
  class SqlSrvResultSet extends ResultSet {
  
    /**
     * Constructor
     *
     * @param   resource handle
     */
    public function __construct($result, TimeZone $tz= NULL) {
      $fields= array();
      if (is_resource($result)) {
        foreach (sqlsrv_field_metadata($result) as $meta) {
          $fields[$meta['Name']]= $meta['Type'];
        }
      }
      parent::__construct($result, $fields, $tz);
    }

    /**
     * Seek
     *
     * @param   int offset
     * @return  bool success
     * @throws  rdbms.SQLException
     */
    public function seek($offset) { 
      if (!sqlsrv_data_seek($this->handle, $offset)) {
        throw new SQLException('Cannot seek to offset '.$offset);
      }
      return TRUE;
    }
    
    /**
     * Iterator function. Returns a rowset if called without parameter,
     * the fields contents if a field is specified or FALSE to indicate
     * no more rows are available.
     *
     * @param   string field default NULL
     * @return  mixed
     */
    public function next($field= NULL) {
      if (
        !is_resource($this->handle) ||
        FALSE === ($row= sqlsrv_fetch_array($this->handle, SQLSRV_FETCH_ASSOC))
      ) {
        return FALSE;
      }

      foreach (array_keys($row) as $key) {
        if (NULL === $row[$key] || !isset($this->fields[$key])) continue;
        if ($row[$key] instanceof DateTime) {
          $row[$key]= new Date($row[$key]);
        }
      }
      
      if ($field) return $row[$field]; else return $row;
    }
    
    /**
     * Close resultset and free result memory
     *
     * @return  bool success
     */
    public function close() { 
      return sqlsrv_free_stmt($this->handle);
    }
  }
?>
