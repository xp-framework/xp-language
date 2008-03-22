<?php
/* This class is part of the XP framework
 *
 * $Id: DateInterval.class.php 10949 2007-08-24 15:57:44Z kiesel $ 
 */

  uses('lang.Enum');

  /**
   * Interval enumeration
   *
   * @see      xp://util.DateMath
   * @purpose  Intervals
   */
  class DateInterval extends Enum {
    public static
      $YEAR,
      $MONTH,
      $DAY,
      $HOURS,
      $MINUTES,
      $SECONDS;
    
    static function __static() {
      self::$YEAR=    new self(0, 'year');
      self::$MONTH=   new self(1, 'month');
      self::$DAY=     new self(2, 'day');
      self::$HOURS=   new self(3, 'hours');
      self::$MINUTES= new self(4, 'minutes');
      self::$SECONDS= new self(5, 'seconds');
    }
    
    /**
     * Retrieve enum members
     *
     * @return  util.DateInterval[]
     */
    public static function values() {
      return parent::membersOf(__CLASS__);
    }
  }
?>
