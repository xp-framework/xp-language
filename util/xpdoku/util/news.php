<?php
/* This file is part of the XP framework
 *
 * $Id$
 */
 
  require('lang.base.php');
  uses(
    'xml.rdf.RDFNewsFeed'
  );
  
  $news= &new RDFNewsFeed();
  $news->setChannel(
    'XP News', 
    'http://xp.php3.de/',
    'XP Newsflash',
    NULL,
    'en_US',
    'XP-Team <xp@php3.de>',
    'XP-Team <xp@php3.de>',
    'http://xp.php3.de/copyright.html'
  );
  
  // TBD: Get from database?
  $news->addItem(
    'API Docs released', 
    'http://xp.php3.de/apidoc/',
    'An initial release of the XP api docs has been created. There is still some missing functionality but the documentation is already quite usable.',
    new Date('2002-12-28 19:18:12')
  );
  $news->addItem(
    'Website created', 
    'http://xp.php3.de/',
    'The first version of the XP web site is online.',
    new Date('2002-12-27 13:10:01')
  );
  
  echo $news->getSource(0);
?>
