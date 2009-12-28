<?php

/**
 * Project: Welcompose_Plugins
 * File: wcom_plugin_textconverter_textile.php
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
 * @package Welcompose_Plugins
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

require_once('wcom_plugin_textconverter_xhtml.php');

class TextConverter_Textile extends TextConverter_Xhtml
{

public function mmInsertImage ($text, $src, $width, $height, $alt, $title)
{
	$tag = '!%1$s (%4$s)!%6$s';
	$html = sprintf($tag, $src, $width, $height, $alt, $title, $text);
	
	return $html;
}

public function mmInsertDocument ($text, $href)
{
	$tag = '"%2$s":%1$s';
	$html = sprintf($tag, $href, $text);
	
	return $html;
}

public function mmInsertInternalLink ($text, $href)
{
	// Omit the Doublequotes if $text is a image reference
	if (preg_match("=^\!(.*)\!$=", $text)) {
		$tag = '%2$s:%1$s';
	} else {
		$tag = '"%2$s":%1$s';
	}
	$html = sprintf($tag, $href, $text);

	if (ini_get('magic_quotes_gpc')) {
		$html = stripslashes($html);
	}	
	return $html;
}

public function mmInsertFlickr ($text, $src, $href)
{
	$tag = '!%1$s!:%2$s %3$s';
	
	$html = sprintf($tag, $src, $href, $text);
	
	return $html;
}

public function apply ($str)
{
	// input check
	if (!is_scalar($str)) {
		throw new TextConverter_MarkdownException('Input for parameter str is expected to be scalar');
	}
	
	// load textile
	if (!class_exists('Textile')) {
		$path = dirname(__FILE__).'/../../third_party/textile.php';
		require(Base_Compat::fixDirectorySeparator($path));
	}
	$TEXTILE = new Textile();
	
	// apply textile
	return $TEXTILE->TextileThis($str);
}

}

?>