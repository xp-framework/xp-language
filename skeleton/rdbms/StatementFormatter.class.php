<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  /**
   * Formatter class for database queries.
   *
   * Example usage:
   * <code>
   *   $formatter= &new StatementFormatter();
   *   $formatter->setEscapeRules(array(
   *     '"'   => '""',
   *     '\\'  => '\\\\'
   *   ));
   *   $formatter->setDateFormat('Y-m-d h:iA');
   *   $formatter->format('select foo from table where id= %d', 123);
   * </code>
   *
   * @test    xp://net.xp_framework.unittest.rdbms.TokenizerTest
   * @see     xp://rdbms.sybase.SybaseConnection
   * @see     xp://rdbms.mysql.MysqlConnection
   * @see     xp://rdbms.pgsql.PostgresqlConnection
   * @purpose Format database query strings
   */
  class StatementFormatter extends Object {
    public
      $escape       = '',
      $escapeRules  = array(),
      $dateFormat   = '';
  
  
    /**
     * Embed the given arguments into the format string.
     *
     * @param   string fmt
     * @param   mixed[] args
     * @return  string
     */
    public function format($fmt, $args) {
      static $tokens= 'sdcfu';
      
      $statement= '';
      $argumentOffset= 0;
      while (TRUE) {

        // Find next token (or end of string)
        $offset= strcspn($fmt, '%');
        $statement.= substr($fmt, 0, $offset);

        // If offset == length, it was the last token, so return
        if ($offset == strlen($fmt)) return $statement;
        
        if (is_numeric($fmt{$offset + 1})) {
        
          // Numeric argument type specifier, e.g. %1$s
          sscanf(substr($fmt, $offset), '%%%d$', $overrideOffset);
          $type= $fmt{$offset + strlen($overrideOffset) + 2};
          $fmt= substr($fmt, $offset + strlen($overrideOffset) + 3);
          if (!array_key_exists($overrideOffset - 1, $args)) {
            throw new SQLStateException('Missing argument #'.($overrideOffset - 1).' @offset '.$offset);
          }
          $argument= $args[$overrideOffset - 1];
        } else if (FALSE !== strpos($tokens, $fmt{$offset + 1})) {
        
          // Known tokens
          $type= $fmt{$offset + 1};
          $fmt= substr($fmt, $offset + 2);
          if (!array_key_exists($argumentOffset, $args)) {
            throw new SQLStateException('Missing argument #'.$argumentOffset.' @offset '.$offset);
          }
          $argument= $args[$argumentOffset];
          $argumentOffset++;
        } else if ('%' == $fmt{$offset + 1}) {
        
          // Escape sign
          $statement.= '%';
          $fmt= substr($fmt, $offset + 2);
          continue;
        } else {
        
          // Unknown tokens
          $statement.= '%'.$fmt{$offset + 1};
          $fmt= substr($fmt, $offset + 2);
          continue;
        }
        
        $statement.= $this->prepare($type, $argument);
      }
    }
    
    /**
     * Prepare a value for insertion with a given type.
     *
     * @param   string type
     * @param   mixed var
     * @return  string
     */
    public function prepare($type, $var) {

      // Type-based conversion
      if ($var instanceof Date) {
        $type= 's';
        $a= array($var->toString($this->dateFormat));
      } else if ($var instanceof Generic) {
        $a= array($var->toString());
      } else if (is_array($var)) {
        $a= $var;
      } else {
        $a= array($var);
      }

      $r= '';
      foreach ($a as $arg) {
        if (NULL === $arg) { 
          $r.= 'NULL, '; 
          continue; 
        } else if ($arg instanceof Date) {
          $p= $arg->toString($this->dateFormat);
        } else if ($arg instanceof Generic) {
          $p= $arg->toString();
        } else {
          $p= $arg;
        }

        switch ($type) {
          case 's': $r.= $this->escape.strtr($p, $this->escapeRules).$this->escape; break;
          case 'd': $r.= $this->numval($p); break;
          case 'c': $r.= $p; break;
          case 'f': $r.= $this->numval($p); break;
          case 'u': $r.= $this->escape.date($this->dateFormat, $p).$this->escape; break;
        }
        $r.= ', ';
      }

      return substr($r, 0, -2);
    }
    
    /**
     * Set date format
     *
     * @param   string format
     */
    public function setDateFormat($format) {
      $this->dateFormat= $format;
    }
    
    /**
     * Set date format
     *
     * @param   array<String,String> rules
     */
    public function setEscapeRules($rules) {
      $this->escapeRules= $rules;
    }
    
    /**
     * Sets the escaping character.
     *
     * @param   string escape
     */
    public function setEscape($escape) {
      $this->escape= $escape;
    }
    
    /**
     * Format a number
     *
     * @param   mixed arg
     * @return  string
     */
    public function numval($arg) {
      if (
        (0 >= sscanf($arg, '%[0-9.+-]%[eE]%[0-9-]', $n, $s, $e)) ||
        !is_numeric($n)
      ) return 'NULL';
        
      return $n.($e ? $s.$e : '');
    }
  }
?>
