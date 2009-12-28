<?php

/**
 * Project: Welcompose
 * File: smarty_admin.inc.php
 * 
 * Copyright (c) 2008 creatics
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// define constants
if (!defined('SMARTY_ADMIN_DIR')) {
	$path_parts = array(
		dirname(__FILE__),
		'..',
		'smarty'
	);
	define('SMARTY_ADMIN_DIR', implode(DIRECTORY_SEPARATOR, $path_parts));
}
if (!defined('SMARTY_ADMIN_TPL_DIR')) {
	$path_parts = array(
		dirname(__FILE__),
		'..',
		'..',
		'admin',
		'smarty'
	);
	define('SMARTY_ADMIN_TPL_DIR', implode(DIRECTORY_SEPARATOR, $path_parts));
}

// configure i18n
require(SMARTY_ADMIN_DIR.DIRECTORY_SEPARATOR.'gettext_plugin'.DIRECTORY_SEPARATOR.'Smarty_GettextHelper.class.php');
require(SMARTY_ADMIN_DIR.DIRECTORY_SEPARATOR.'gettext_plugin'.DIRECTORY_SEPARATOR.'compiler.i18n.php');
$smarty->register_compiler_function('i18n', 'smarty_compiler_i18n');

// configure smarty
$smarty->debug = false;
$smarty->template_dir = SMARTY_ADMIN_TPL_DIR.DIRECTORY_SEPARATOR.'templates';
$smarty->compile_dir = SMARTY_ADMIN_TPL_DIR.DIRECTORY_SEPARATOR.'compiled';
$smarty->cache_dir = SMARTY_ADMIN_TPL_DIR.DIRECTORY_SEPARATOR.'cache';
$smarty->plugins_dir = array(
	SMARTY_ADMIN_DIR.DIRECTORY_SEPARATOR.'my_plugins',
	SMARTY_ADMIN_DIR.DIRECTORY_SEPARATOR.'plugins',
	SMARTY_ADMIN_DIR.DIRECTORY_SEPARATOR.'software_plugins'
);

?>
