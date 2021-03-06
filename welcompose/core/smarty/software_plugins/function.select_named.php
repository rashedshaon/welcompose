<?php

/**
 * Project: Welcompose
 * File: function.select_named.class.php
 * 
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *  
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

function smarty_function_select_named ($params, &$smarty)
{
	// define some vars
	$var = null;
	$ns = null;
	$class = null;
	$method = null;
	$select_params = array();
	
	// check input vars
	if (!is_array($params)) {
		throw new Exception("select_named: Functions params are not in an array");	
	}
	
	// separate function params from the rest
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'ns':
					$ns = (string)$_value;
				break;
			case 'var':
					$var = (string)$_value;
				break;
			case 'class':
					$class = (string)$_value;
				break;
			case 'method':
					$method = (string)$_value;
				break;
			default:
					$select_params[$_key] = $_value;
				break;
		}
	}
	
	// check input
	if (is_null($var) || !preg_match(WCOM_REGEX_SMARTY_VAR_NAME, $var)) {
		throw new Exception("select_named: Invalid var name supplied");
	}
	if (is_null($ns) || !preg_match(WCOM_REGEX_SMARTY_NS_NAME, $ns)) {
		throw new Exception("select_named: Invalid namespace name supplied");
	}
	if (is_null($class) || !preg_match(WCOM_REGEX_SMARTY_CLASS_NAME, $class)) {
		throw new Exception("select_named: Invalid class name supplied");
	}
	if (is_null($method) || !preg_match(WCOM_REGEX_SMARTY_METHOD_NAME, $method)) {
		throw new Exception("select_named: Invalid method name supplied");
	}
	
	// let's see if we can safely call the desired method
	if (!wcom_smarty_select_whitelist($ns, $class, $method)) {
		throw new Exception("select_named: Function call did not pass the whitelist");	
	}
	
	// load class loader
	$path_parts = array(
		dirname(__FILE__),
		'..',
		'..',
		'loader.php'
	);
	require_once(implode(DIRECTORY_SEPARATOR, $path_parts));
	
	// load requested class
	$OBJECT = load($ns.':'.$class);
	
	// check if the requested method is callable
	if (!is_callable(array($OBJECT, $method))) {
		throw new Exception("select_named: Requested method is not callable");	
	}
	
	// execute method and return requested data
	$smarty->assign($var, call_user_func(array($OBJECT, $method),
		$select_params));
}

?>