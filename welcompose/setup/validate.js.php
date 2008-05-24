<?php

/**
 * Project: Welcompose
 * File: validate.js.php
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
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

// define area constant
define('WCOM_CURRENT_AREA', 'ADMIN');

// get loader
$path_parts = array(
	dirname(__FILE__),
	'..',
	'core',
	'loader.php'
);
$loader_path = implode(DIRECTORY_SEPARATOR, $path_parts);
require($loader_path);

// start base
/* @var $BASE base */
$BASE = load('base:base');

// deregister globals
$deregister_globals_path = dirname(__FILE__).'/../core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_update_conf = dirname(__FILE__).'/smarty.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_update_conf), true);
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
	
	// map field id names to regexps and error messages 
	if (Base_Cnc::filterRequest($_POST['elemID'], WCOM_REGEX_FORM_FIELD_ID)) {
		switch ((string)$_POST['elemID']) {
			case 'database_database':
					$reg = WCOM_REGEX_DATABASE_NAME;
					$desc = gettext('Literal string with dashes');
				break;
			case 'database_port':
					$reg = WCOM_REGEX_NUMERIC;
					$desc = gettext('Numbers only');
				break;
			case 'database_unix_socket':
					$reg = WCOM_REGEX_DATABASE_SOCKET;
					$desc = gettext('Unix path');
				break;
			case 'configuration_locale':
				$reg = WCOM_REGEX_LOCALE_NAME;
				$desc = gettext('Invalid locale');
			break;
			default :
				$reg = null;
				$desc = null;
		}	
	}
	
	if (!empty($_POST['elemVal'])) {
		if (!empty($reg)) {
			if (Base_Cnc::filterRequest($_POST['elemVal'], $reg)) {
				print '<img src="static/img/icons/success.gif" />';
			} else {
				print '<img src="static/img/icons/error.gif" /> '.$desc;
			}
		} else {
			print '<img src="static/img/icons/success.gif" />';
		}
	} else {
		// print non-breaking space
		// safari doesn't recognized void properly
		print '&nbsp;';
	}
	
	
		
	// flush the buffer
	@ob_end_flush();
	exit;

} catch (Exception $e) {
	// clean buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	$BASE->error->displayException($e, $BASE->utility->smarty);
	$BASE->error->triggerException($e);
	
	// exit
	exit;
}
?>