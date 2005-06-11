/*
   +----------------------------------------------------------------------+
   | Zend Engine                                                          |
   +----------------------------------------------------------------------+
   | Copyright (c) 1998-2004 Zend Technologies Ltd. (http://www.zend.com) |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.00 of the Zend license,     |
   | that is bundled with this package in the file LICENSE, and is        |
   | available through the world-wide-web at the following url:           |
   | http://www.zend.com/license/2_00.txt.                                |
   | If you did not receive a copy of the Zend license and are unable to  |
   | obtain it through the world-wide-web, please send a note to          |
   | license@zend.com so we can mail you a copy immediately.              |
   +----------------------------------------------------------------------+
   | Authors: Sterling Hughes <sterling@php.net>                          |
   |          Marcus Boerger <helly@php.net>                              |
   +----------------------------------------------------------------------+
*/

/* $Id: zend_default_classes.c 4845 2005-03-06 11:55:21Z kiesel $ */

#include "zend.h"
#include "zend_API.h"
#include "zend_reflection_api.h"
#include "zend_builtin_functions.h"
#include "zend_interfaces.h"
#include "zend_exceptions.h"
#include "zend_enumerations.h"


ZEND_API void zend_register_default_classes(TSRMLS_D)
{
	zend_register_interfaces(TSRMLS_C);
	zend_register_default_exception(TSRMLS_C);
	zend_register_reflection_api(TSRMLS_C);
	zend_register_iterator_wrapper(TSRMLS_C);
	zend_register_default_enumeration(TSRMLS_C);
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: t
 * End:
 */
