<?php

/**
 * Project: Welcompose
 * File: textmacros_edit.php
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

// define area constant
define('WCOM_CURRENT_AREA', 'ADMIN');

// get loader
$path_parts = array(
	dirname(__FILE__),
	'..',
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
$deregister_globals_path = dirname(__FILE__).'/../../core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

// admin_navigation
$admin_navigation_path = dirname(__FILE__).'/../../core/includes/admin_navigation.inc.php';
require(Base_Compat::fixDirectorySeparator($admin_navigation_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_admin_conf = dirname(__FILE__).'/../../core/conf/smarty_admin.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_admin_conf), true);
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
	
	// start session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// load user class
	/* @var $USER User_User */
	$USER = load('user:user');
	
	// load login class
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');
	
	// load project class
	/* @var $PROJECT Application_Project */
	$PROJECT = load('application:project');
	
	// load textmacro class
	/* @var $TEXTMACRO Application_Textmacro */
	$TEXTMACRO = load('application:textmacro');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Application', 'TextMacro', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// get text macro
	$text_macro = $TEXTMACRO->selectTextMacro(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
	// prepare types
	$types = array(
		'startup' => gettext("Startup macro"),
		'pre' => gettext("Pre text filter macro"),
		'post' => gettext("Post text filter macro"),
		'shutdown' => gettext("Shutdown macro")
	);
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('text_macro', 'post');
	$FORM->registerRule('testForNameUniqueness', 'callback', 'testForUniqueName', $TEXTMACRO);
	
	// hidden for id
	$FORM->addElement('hidden', 'id');
	$FORM->applyFilter('id', 'trim');
	$FORM->applyFilter('id', 'strip_tags');
	$FORM->addRule('id', gettext('Id is not expected to be empty'), 'required');
	$FORM->addRule('id', gettext('Id is expected to be numeric'), 'numeric');
	
	// hidden for start
	$FORM->addElement('hidden', 'start');
	$FORM->applyFilter('start', 'trim');
	$FORM->applyFilter('start', 'strip_tags');
	$FORM->addRule('start', gettext('start is expected to be numeric'), 'numeric');
	
	// textfield for name
	$FORM->addElement('text', 'name', gettext('Name'), 
		array('id' => 'text_macro_name', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('name', 'trim');
	$FORM->applyFilter('name', 'strip_tags');
	$FORM->addRule('name', gettext('Please enter a name'), 'required');
	$FORM->addRule('name', gettext('A text macro with the given name already exists'),
		'testForNameUniqueness', $FORM->exportValue('id'));

	// textfield for internal_name
	$FORM->addElement('text', 'internal_name', gettext('Internal name'), 
		array('id' => 'text_macro_internal_name', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('internal_name', 'trim');
	$FORM->applyFilter('internal_name', 'strip_tags');
	$FORM->addRule('internal_name', gettext('Please enter an internal name'), 'required');
	$FORM->addRule('internal_name', gettext('Please enter a valid internal name'), 'regex',
		WCOM_REGEX_TEXT_CONVERTER_INTERNAL_NAME);
	
	// select for type
	$FORM->addElement('select', 'type', gettext('Type'), $types,
		array('id' => 'text_macro_type'));
	$FORM->applyFilter('type', 'trim');
	$FORM->applyFilter('type', 'strip_tags');
	$FORM->addRule('type', gettext('Chosen type is out of range'),
		'in_array_keys', $types);
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Save edit'),
		array('class' => 'submit200'));
	
	// set defaults
	$FORM->setDefaults(array(
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], WCOM_REGEX_NUMERIC),
		'id' => Base_Cnc::ifsetor($text_macro['id'], null),
		'name' => Base_Cnc::ifsetor($text_macro['name'], null),
		'internal_name' => Base_Cnc::ifsetor($text_macro['internal_name'], null),
		'type' => Base_Cnc::ifsetor($text_macro['type'], null)
	));
	
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
		$quickform_tpl_path = dirname(__FILE__).'/../quickform.tpl.php';
		include(Base_Compat::fixDirectorySeparator($quickform_tpl_path));

		// remove attribute on form tag for XHTML compliance
		$FORM->removeAttribute('name');
		$FORM->removeAttribute('target');
		
		$FORM->accept($renderer);
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $renderer->toArray());
		
		// assign paths
		$BASE->utility->smarty->assign('wcom_admin_root_www',
			$BASE->_conf['path']['wcom_admin_root_www']);
	    
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);
		$BASE->utility->smarty->assign('wcom_current_project', WCOM_CURRENT_PROJECT);

		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('application/textmacros_edit.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// create the article group
		$sqlData = array();
		$sqlData['internal_name'] = $FORM->exportValue('internal_name');
		$sqlData['name'] = $FORM->exportValue('name');
		$sqlData['type'] = $FORM->exportValue('type');
		
		// check sql data
		$HELPER = load('utility:helper');
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$TEXTMACRO->updateTextMacro($FORM->exportValue('id'),
				$sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
		
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}

		// save request start range
		$start = $FORM->exportValue('start');
		$start = (!empty($start)) ? $start : 0;
		
		// redirect
		header("Location: textmacros_select.php?start=".$start);
		exit;
	}
} catch (Exception $e) {
	// clean the buffer
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