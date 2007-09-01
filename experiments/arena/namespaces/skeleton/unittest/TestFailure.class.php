<?php
/* This class is part of the XP framework
 *
 * $Id: TestFailure.class.php 10594 2007-06-11 10:04:54Z friebe $ 
 */

  namespace unittest;

  /**
   * Indicates a test failed
   *
   * @see      xp://unittest.TestResult
   * @purpose  Result wrapper
   */
  class TestFailure extends lang::Object {
    public
      $result   = NULL,
      $test     = NULL,
      $elapsed  = 0.0;
      
    /**
     * Constructor
     *
     * @param   unittest.TestCase test
     * @param   mixed reason
     * @param   float elapsed
     */
    public function __construct($test, $reason, $elapsed) {
      $this->test= $test;
      $this->reason= $reason;
      $this->elapsed= $elapsed;
    }

    /**
     * Return a string representation of this class
     *
     * @return  string
     */
    public function toString() {
      return (
        $this->getClassName().
        '(test= '.$this->test->getClassName().'::'.$this->test->getName().
        sprintf(', time= %.3f seconds', $this->elapsed).") {\n  ".
        str_replace("\n", "\n  ", ::xp::stringOf($this->reason))."\n".
        ' }'
      );
    }
  }
?>
