<?php
/* This file provides the PHP 4.2 compatibility sapi for the XP framework
 * 
 * $Id$
 */

  // {{{ proto array sybase_fetch_assoc(resource result)
  //     See php://sybase_fetch_assoc
  if (!function_exists('sybase_fetch_assoc')) { function sybase_fetch_assoc($res) {
    if (is_array($r= sybase_fetch_array($res))) foreach (array_keys($r) as $k) {
      if (is_int($r[$k])) unset($r[$k]);
    }
    return $r;
  }}
  // }}}
?>
