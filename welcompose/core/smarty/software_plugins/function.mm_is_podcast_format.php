<?php

/**
 * Project: Welcompose
 * File: function.mm_is_podcast_format.php
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

function smarty_function_mm_is_podcast_format ($params, &$smarty)
{
	// input check
	if (!is_array($params)) {
		trigger_error("Input for parameter params is not an array");
	}
	
	// load media object class
	$OBJECT = load('Media:Object');
	
	// import mime type & var name from params array
	$mime_type = Base_Cnc::filterRequest($params['mime_type'], WCOM_REGEX_MIME_TYPE);
	$var = Base_Cnc::filterRequest($params['var'], WCOM_REGEX_SMARTY_VAR_NAME);
	
	// find out if the object with the given mime type can be used for a podcast
	// and assign the result to the var with the given name.
	$smarty->assign($var, $OBJECT->isPodcastFormat($mime_type));
}

?>