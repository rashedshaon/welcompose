<?php

/**
 * Project: Welcompose
 * File: smarty.inc.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 * 
 * $Id$
 * 
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

// define constants
if (!defined('SMARTY_DIR')) {
	$path_parts = array(
		dirname(__FILE__),
		'..',
		'core',
		'smarty'
	);
	define('SMARTY_DIR', implode(DIRECTORY_SEPARATOR, $path_parts));
}
if (!defined('SMARTY_TPL_DIR')) {
	$path_parts = array(
		dirname(__FILE__),
		'smarty'
	);
	define('SMARTY_TPL_DIR', implode(DIRECTORY_SEPARATOR, $path_parts));
}

// configure i18n
require(SMARTY_DIR.DIRECTORY_SEPARATOR.'gettext_plugin'.DIRECTORY_SEPARATOR.'Smarty_GettextHelper.class.php');
require(SMARTY_DIR.DIRECTORY_SEPARATOR.'gettext_plugin'.DIRECTORY_SEPARATOR.'compiler.i18n.php');
$smarty->register_compiler_function('i18n', 'smarty_compiler_i18n');

// configure smarty
$smarty->debug = false;
$smarty->template_dir = SMARTY_TPL_DIR.DIRECTORY_SEPARATOR.'templates';
$smarty->compile_dir = SMARTY_TPL_DIR.DIRECTORY_SEPARATOR.'compiled';
$smarty->cache_dir = SMARTY_TPL_DIR.DIRECTORY_SEPARATOR.'cache';
$smarty->plugins_dir = array(
	SMARTY_DIR.DIRECTORY_SEPARATOR.'my_plugins',
	SMARTY_DIR.DIRECTORY_SEPARATOR.'plugins',
	SMARTY_DIR.DIRECTORY_SEPARATOR.'software_plugins'
);

?>
