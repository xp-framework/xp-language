<?php
/* This class is part of the XP framework
 *
 * $Id: Console.class.php 10073 2007-04-21 19:00:02Z friebe $ 
 */

  namespace util::cmd;

  ::uses('io.streams.StringWriter', 'io.streams.ConsoleOutputStream');

  /**
   * Represents system console
   *
   * Example: Writing to standard output
   * <code>
   *   uses('util.cmd.Console');
   *
   *   Console::writeLine('Hello ', 'a', 'b', 1);   // Hello ab1
   *   Console::writeLinef('Hello %s', 'World');    // Hello World
   *
   *   Console::$out->write('.');
   * </code>
   *
   * Example: Writing to standard error
   * <code>
   *   uses('util.cmd.Console');
   *
   *   Console::$err->writeLine('*** An error occured: ', $e->toString());
   * </code>
   *
   * @see      http://msdn.microsoft.com/library/default.asp?url=/library/en-us/cpref/html/frlrfSystemConsoleClassTopic.asp
   * @purpose  I/O functions
   */
  class Console extends lang::Object {
    public static 
      $out= NULL,
      $err= NULL;

    static function __static() {
      self::$out= new io::streams::StringWriter(new io::streams::ConsoleOutputStream(STDOUT));
      self::$err= new io::streams::StringWriter(new io::streams::ConsoleOutputStream(STDERR));
    }

    /**
     * Flush output buffer
     *
     */
    public static function flush() {
      self::$out->flush();
    }

    /**
     * Write a string to standard output
     *
     * @param   mixed* args
     */
    public static function write() {
      $a= func_get_args();
      call_user_func_array(array(self::$out, 'write'), $a);
    }
    
    /**
     * Write a string to standard output and append a newline
     *
     * @param   mixed* args
     */
    public static function writeLine() {
      $a= func_get_args();
      call_user_func_array(array(self::$out, 'writeLine'), $a);
    }
    
    /**
     * Write a formatted string to standard output
     *
     * @param   string format
     * @param   mixed* args
     * @see     php://printf
     */
    public static function writef() {
      $a= func_get_args();
      call_user_func_array(array(self::$out, 'writef'), $a);
    }

    /**
     * Write a formatted string to standard output and append a newline
     *
     * @param   string format
     * @param   mixed* args
     */
    public static function writeLinef() {
      $a= func_get_args();
      call_user_func_array(array(self::$out, 'writeLinef'), $a);
    }
    
    /**
     * Read a line from standard input. The line ending (\r and/or \n)
     * is trimmed off the end.
     *
     * @param   string prompt = NULL
     * @return  string
     */    
    public function readLine($prompt= NULL) {
      $prompt && self::$out->write($prompt.' ');
      $r= '';
      while ($bytes= fgets(STDIN, 0x20)) {
        $r.= $bytes;
        if (FALSE !== strpos("\r\n", substr($r, -1))) break;
      }
      return rtrim($r, "\r\n");
    }

    /**
     * Read a single character from standard input.
     *
     * @param   string prompt = NULL
     * @return  string
     */    
    public function read($prompt= NULL) {
      $prompt && self::$out->write($prompt.' ');
      return fgetc(STDIN);
    }
  }
?>
