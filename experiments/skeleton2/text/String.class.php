<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('lang.IndexOutOfBoundsException');

  /**
   * Represents a string. 
   *
   * This class is useful in two situations:
   * <ul>
   *  <li>You have very large strings. The overhead is thus not 
   *      noticeable and as objects are passed by reference instead
   *      of by value, it will actually save memory!
   *  </li>
   *  <li>You want an object-oriented API</li>
   * </ul>
   *
   * @see      php://strings
   * @purpose  Type wrapper
   */
  class String extends Object {
    const 
      CR    = "\r",
      LF    = "\n",
      CRLF  = "\r\n";

    protected
      $buffer   = '';

    /**
     * Constructor
     *
     * @access  public
     * @param   string initial default ''
     */
    public function __construct($initial= '') {
      $this->buffer= $initial;
    }
    
    /**
     * Retrieve string's length
     *
     * @access  public
     * @return  int
     */
    public function length() {
      return strlen($this->buffer);
    }

    /**
     * Set Buffer
     *
     * @access  public
     * @param   string buffer
     */
    public function setBuffer($buffer) {
      $this->buffer= $buffer;
    }

    /**
     * Get Buffer
     *
     * @access  public
     * @return  string
     */
    public function getBuffer() {
      return $this->buffer;
    }
    
    /**
     * Returns the character at the specified index. Index counting starts
     * at 0 and ends at length() - 1. Use -1 as value for the pos argument
     * to retrieve the last character in this string.
     *
     * @access  public
     * @param   int pos
     * @return  string character
     * @throws  lang.IndexOutOfBoundsException
     */
    public function charAt($pos) {
      if (-1 == $pos) {
        $pos= strlen($this->buffer)- 1;
      } elseif ($pos < 0 || $pos >= strlen($this->buffer)) {
        throw (new IndexOutOfBoundsException($pos.' is not a valid string offset'));
      }

      return $this->buffer{$pos};
    }
    
    /**
     * Compares two strings lexicographically.
     *
     * @access  public
     * @param   &text.String string
     * @param   bool cs default TRUE whether to compare case-sensitively
     * @return  int
     * @see     php://strcmp for case-sensitive comparison
     * @see     php://strcasecmp for case-insensitive comparison
     */
    public function compareTo(String $string, $cs= TRUE) {
      return ($cs 
        ? strcmp($string->buffer, $this->buffer) 
        : strcasecmp($string->buffer, $this->buffer)
      );
    }

    /**
     * Returns true if the specified string matches this string.
     *
     * @access  public
     * @param   mixed str
     * @return  bool
     */
    public function equals($str, $cs= TRUE) {
      return 0 == ($cs 
        ? strcmp((string)$str, $this->buffer) 
        : strcasecmp((string)$str, $this->buffer)
      );
    }
     
    /**
     * Compares two strings lexicographically using a "natural order" 
     * algorithm
     *
     * @access  public
     * @param   &text.String string
     * @param   bool cs default TRUE whether to compare case-sensitively
     * @return  int
     * @see     php://strnatcmp for case-sensitive comparison
     * @see     php://strnatcasecmp for case-insensitive comparison
     */
    public function compareToNat(String $string, $cs= TRUE) {
      return ($cs 
        ? strnatcmp($string->buffer, $this->buffer) 
        : strnatcasecmp($string->buffer, $this->buffer)
      );
    }
   
    /**
     * Tests if this string starts with the specified prefix beginning 
     * a specified index.
     *
     * @access  public
     * @param   string prefix
     * @param   int offset default 0 where to begin looking in the string
     * @return  bool
     */
    public function startsWith($prefix, $offset= 0) {
      return substr($this->buffer, $offset, strlen($prefix)) == $prefix;
    }
    
    /**
     * Tests if this string ends with the specified suffix.
     *
     * @access  public
     * @param   string suffix
     * @return  bool
     */
    public function endsWith($suffix) {
      return substr($this->buffer, -1 * strlen($suffix)) == $suffix;
    }
    
    /**
     * Returns the index within this string of the first occurrence of the 
     * specified substring
     *
     * @access  public
     * @param   string substr
     * @param   int offset default 0 the index to start the search from
     * @return  int the index of the first occurrence of the substring or FALSE
     * @see     php://strpos
     */
    public function indexOf($substr, $offset= 0) {
      return strpos($this->buffer, $substr, $offset);
    }
    
    /**
     * Returns the index within this string of the last occurrence of the 
     * specified substring
     *
     * @access  public
     * @param   string substr
     * @return  int the index of the first occurrence of the substring or FALSE
     * @see     php://strrpos
     */
    public function lastIndexOf($substr) {
      return strrpos($this->buffer, $substr);
    }
    
    /**
     * Returns whether the specified substring is contained in this string
     *
     * @access  public
     * @param   string substr
     * @param   bool cs default TRUE whether to check case-sensitively
     * @return  bool
     */
    public function contains($substr, $cs= TRUE) {
      return ($cs 
        ? FALSE !== strpos($this->buffer, $substr)
        : FALSE !== stripos($this->buffer, $substr)
      );
    }
    
    /**
     * Find first occurrence of a string.  Returns part of haystack string 
     * from the first occurrence of needle to the end of haystack. 
     *
     * Example:
     * <code>
     *   $s= new String('xp@php3.de');
     *   if ($portion= $s->substrAfter('@')) {
     *     echo $portion;   // php3.de
     *   }
     * </code>
     *
     * @access  public
     * @param   string substr
     * @param   bool cs default TRUE whether to check case-sensitively
     * @return  string or FALSE if substr is not found
     */
    public function substrAfter($substr, $cs= TRUE) {
      if (FALSE === ($p= $cs 
        ? strpos($this->buffer, $substr)
        : stripos($this->buffer, $substr)
      )) return FALSE;
      return substr($this->buffer, $p+ 1);
    }

    /**
     * Find first occurrence of a string.  Returns part of haystack string 
     * from the first occurrence of needle to the end of haystack. 
     *
     * @access  public
     * @param   string substr
     * @param   bool cs default TRUE whether to check case-sensitively
     * @return  &text.String or NULL if substr is not found
     */
    public function substringAfter($substr, $cs= TRUE) {
      if (FALSE === ($p= $cs 
        ? strpos($this->buffer, $substr)
        : stripos($this->buffer, $substr)
      )) return xp::$null;
      return new String(substr($this->buffer, $p+ 1));
    }

    /**
     * Find first occurrence of a string. Returns part of haystack string 
     * from the beginning until first occurrence of needle 
     *
     * Example:
     * <code>
     *   $s= new String('xp@php3.de');
     *   if ($portion= $s->substrBefore('@')) {
     *     echo $portion;   // xp
     *   }
     * </code>
     *
     * @access  public
     * @param   string substr
     * @param   bool cs default TRUE whether to check case-sensitively
     * @return  string or FALSE if substr is not found
     */
    public function substrBefore($substr, $cs= TRUE) {
      if (FALSE === ($p= $cs 
        ? strpos($this->buffer, $substr)
        : stripos($this->buffer, $substr)
      )) return FALSE;
      return substr($this->buffer, 0, $p);
    }

    /**
     * Find first occurrence of a string. Returns part of haystack string 
     * from the beginning until first occurrence of needle 
     *
     * @access  public
     * @param   string substr
     * @param   bool cs default TRUE whether to check case-sensitively
     * @return  &text.String or NULL if substr is not found
     */
    public function substringBefore($substr, $cs= TRUE) {
      if (FALSE === ($p= $cs 
        ? strpos($this->buffer, $substr)
        : stripos($this->buffer, $substr)
      )) return xp::$null;
      return new String(substr($this->buffer, 0, $p));
    }
    
    /**
     * Returns a new string that is a substring of this string.
     *
     * @access  public
     * @param   int begin
     * @param   int end default -1
     * @return  &text.String
     * @see     php://substr
     */
    public function substring($begin, $end= -1) {
      return new String(substr($this->buffer, $begin, $end));
    }

    /**
     * Returns a new string that is a substring of this string.
     *
     * @access  public
     * @param   int begin
     * @param   int end default -1
     * @return  string
     * @see     php://substr
     */
    public function substr($begin, $end= -1) {
      return substr($this->buffer, $begin, $end);
    }
    
    /**
     * Concatenates the specified string to the end of this string
     * and returns a new string containing the result.
     *
     * @access  public
     * @param   mixed string
     * @return  &text.String a new string
     */
    public function concat($string) {
      return new String($this->buffer.(string)$string);
    }
    
    /**
     * Concatenates the specified string to the end of this string,
     * changing this string. Returns this string so the following
     * will be possible:
     *
     * <code>
     *   $s= new String('Hello');
     *   $s->append(' ')->append('World');
     * </code>
     *
     * @access  public
     * @param   mixed string
     * @return  &text.String
     */
    public function append($string) {
      $this->buffer.= (string)$string;
      return $this;
    }
    
    /**
     * Replaces search value(s) with replacement value(s) in this string
     *
     * @access  public
     * @param   mixed search
     * @param   mixed replace
     * @param   bool cs default TRUE whether to check case-sensitively
     * @see     php://str_replace
     * @return  &text.String
     */
    public function replace($search, $replace, $cs= TRUE) {
      $this->buffer= ($cs
        ? str_replace($search, $replace, $this->buffer)
        : str_ireplace($search, $replace, $this->buffer)
      );
      return $this;
    }
    
    /**
     * Replaces pairs in this this string
     *
     * @access  public
     * @param   array pairs an associative array, where keys are replaced by values
     * @see     php://strtr
     * @return  &text.String
     */
    public function replacePairs($pairs) {
      $this->buffer= strtr($search, $pairs);
      return $this;
    }
    
    /**
     * Delete a specified amount of characters from this string as
     * of a specified position.
     *
     * @access  public
     * @param   int pos
     * @param   int len default 1
     * @return  &text.String
     */
    public function delete($pos, $len= 1) {
      $this->buffer= substr($this->buffer, 0, $pos).substr($this->buffer, $pos+ 1);
      return $this;
    }
    
    /**
     * Insert a substring into this string at a specified position. 
     *
     * @access  public
     * @param   �nt pos
     * @param   string substring
     * @return  &text.String
     */
    public function insert($pos, $substring) {
      $this->buffer= substr($this->buffer, 0, $pos).$substring.substr($this->buffer, $pos);
      return $this;
    }
    
    /**
     * Tells whether or not this string matches the given regular expression.
     *
     * @access  public
     * @param   string regex
     * @param   &array matches default NULL
     * @return  bool
     * @see     php://preg_match
     */
    public function matches($regex, &$matches= NULL) {
      return (bool)preg_match($regex, $this->buffer, $matches);
    }
    
    /**
     * Split this string into portions delimited by separator
     *
     * @access  public
     * @param   string separator
     * @param   int limit default 0
     * @return  &text.String[]
     * @see     php://explode
     */
    public function explode($separator, $limit= 0) {
      for (
        $a= ($limit 
          ? explode($separator, $this->buffer) 
          : explode($separator, $this->buffer, $limit)
        ), $s= sizeof($a), $i= 0; 
        $i < $s; 
        $i++
      ) {
        $a[$i]= new String($a[$i]);
      }
      return $a;
    }

    /**
     * Split this string into portions delimited by separator regex
     *
     * @access  public
     * @param   string separator
     * @param   int limit default 0
     * @return  &text.String[]
     * @see     php://preg_split
     */
    public function split($separator, $limit= 0) {
      for (
        $a= ($limit 
          ? preg_split($separator, $this->buffer) 
          : preg_split($separator, $this->buffer, $limit)
        ), $s= sizeof($a), $i= 0; 
        $i < $s; 
        $i++
      ) {
        $a[$i]= new String($a[$i]);
      }
      return $a;
    }
    
    /**
     * Pad this string to a certain length with another string
     *
     * @access  public
     * @param   int length
     * @param   string str default ' '
     * @param   int type default STR_PAD_RIGHT
     * @see     php://str_pad
     */
    public function pad($length, $str= ' ', $type= STR_PAD_RIGHT) {
      $this->buffer= str_pad($this->buffer, $length, $str, $type);
    }
    
    /**
     * Strip whitespace from the beginning and end of this string.
     *
     * If the parameter charlist is omitted, these characters will
     * be stripped:
     * <ul>
     *   <li>" " (ASCII 32 (0x20)), an ordinary space.</li>
     *   <li>"\t" (ASCII 9 (0x09)), a tab.</li>
     *   <li>"\n" (ASCII 10 (0x0A)), a new line (line feed).</li>
     *   <li>"\r" (ASCII 13 (0x0D)), a carriage return.</li>
     *   <li>"\0" (ASCII 0 (0x00)), the NUL-byte.</li>
     *   <li>"\x0B" (ASCII 11 (0x0B)), a vertical tab. </li>
     * </ul>
     *
     * @access  public
     * @param   string charlist default NULL
     * @see     php://trim
     * @return  &text.String
     */
    public function trim($charlist= NULL) {
      if ($charlist) {
        $this->buffer= trim($this->buffer, $charlist);
      } else {
        $this->buffer= trim($this->buffer);
      }
      return $this;
    }

    /**
     * Strip whitespace from the beginning of this string.
     *
     * @access  public
     * @param   string charlist default NULL
     * @see     php://ltrim
     * @see     xp://text.String#trim
     * @return  &text.String
     */
    public function ltrim($charlist= NULL) {
      if ($charlist) {
        $this->buffer= ltrim($this->buffer, $charlist);
      } else {
        $this->buffer= ltrim($this->buffer);
      }
      return $this;
    }

    /**
     * Strip whitespace from the end of this string.
     *
     * @access  public
     * @param   string charlist default NULL
     * @see     php://ltrim
     * @see     xp://text.String#trim
     * @return  &text.String
     */
    public function rtrim($charlist= NULL) {
      if ($charlist) {
        $this->buffer= rtrim($this->buffer, $charlist);
      } else {
        $this->buffer= rtrim($this->buffer);
      }
      return $this;
    }
    
    /**
     * Converts all of the characters in this string to upper case using 
     * the rules of the current locale.
     *
     * @access  public
     * @see     php://strtoupper
     * @return  &text.String this string
     */
    public function toUpperCase() {
      $this->buffer= strtoupper($this->buffer);
      return $this;
    }

    /**
     * Converts all of the characters in this string to lower case using 
     * the rules of the current locale.
     *
     * @access  public
     * @see     php://strtolower
     * @return  &text.String this string
     */
    public function toLowerCase() {
      $this->buffer= strtolower($this->buffer);
      return $this;
    }
    
    /**
     * Parses input from this string according to a format
     *
     * @access  public
     * @param   string format
     * @return  array
     * @see     php://sscanf
     */
    public function scan($format) {
      return sscanf($this->buffer, $format);
    }
    
    /**
     * Returns an array of strings
     *
     * Examples:
     * <code>
     *   $s= new String('Hello');
     *   $a= $s->toArray();         // array('H', 'e', 'l', 'l', 'o')
     *
     *   $s= new String('Friebe,Timm');
     *   $a= $s->toArray(',');      // array('Friebe', 'Timm')
     * </code>
     *
     * @access  public
     * @param   string delim default ''
     * @return  string[]
     */
    public function toArray($delim= '') {
      if ($delim) return explode($delim, $this->buffer);
      
      $a= array();
      for ($i= 0, $s= strlen($this->buffer); $i < $s; $i++) {
        $a[]= $this->buffer{$i};
      }
      return $a;
    }
    
    /**
     * Creates a new string from an array, imploding it using the 
     * specified delimiter.
     *
     * Examples:
     * <code>
     *   $s= String::fromArray(array('a', 'b', 'c'));  // "abc"
     *   $s= String::fromArray(array(1, 2, 3), ',');   // "1,2,3"
     * </code>
     *
     * @model   static
     * @access  public
     * @param   string delim default ''
     * @return  &text.String string
     */
    public static function fromArray($arr, $delim= '') {
      return new String(implode($delim, $arr));
    }
    
    /**
     * Returns the string representation of the given argument. Calls the
     * toString() method on objects and implode() on arrays.
     *
     * @model   static
     * @access  public
     * @param   mixed arg
     * @return  &text.String string
     */
    public static function valueOf($arg) {
      if (is_a($arg, 'Object')) {
        return new String($arg->toString());
      } elseif (is_array($arg)) {
        return new String(implode('', $arg));
      }
      return new String(strval($arg));
    }
    
    /**
     * Magic method - called from PHP when casting to a string
     *
     * @access  public
     * @return  string
     */
    public function __toString() {
      return $this->buffer;
    }
  }
?>
