<?php

/**
 * Project: Welcompose
 * File: generatorformindex.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

// load the display interface
if (!interface_exists('Display')) {
	$path_parts = array(
		dirname(__FILE__),
		'display.interface.php'
	);
	require(implode(DIRECTORY_SEPARATOR, $path_parts));
}

/**
 * Class loader compatible to loader.php. Wrapps around constructor.
 * 
 * @param array
 * @return object
 */
function Display_GeneratorFormIndex ($args)
{
	// check input
	if (!is_array($args)) {
		trigger_error('Constructor args are not an array', E_USER_ERROR);
	}
	if (!array_key_exists(0, $args)) {
		trigger_error('Constructor arg project does not exist', E_USER_ERROR);
	}
	if (!array_key_exists(1, $args)) {
		trigger_error('Constructor arg page does not exist', E_USER_ERROR);
	}

	return new Display_GeneratorFormIndex($args[0], $args[1]);
}

class Display_GeneratorFormIndex implements Display {
	
	/**
	 * Reference to base class
	 *
	 * @var object
	 */
	public $base = null;
	
	/**
	 * Reference to session class
	 *
	 * @var object
	 */
	public $session = null;
	
	/**
	 * Reference to captcha class
	 * 
	 * @var object
	 */
	public $captcha = null;
	
	/**
	 * Container for project information
	 * 
	 * @var array
	 */
	protected $_project = array();
	
	/**
	 * Container for page information
	 * 
	 * @var array
	 */
	protected $_page = array();
	
	/**
	 * Container for generator form information
	 * 
	 * @var array
	 */
	protected $_generator_form = array();
	
	/**
	 * Container for generator form fields
	 *
	 * @var array
	 */
	protected $_generator_form_fields = array();
	
/**
 * Creates new instance of display driver. Takes an array
 * with the project information as first argument, an array
 * with the information about the current page as second
 * argument.
 * 
 * @param array Project information
 * @param array Page information
 */
public function __construct($project, $page)
{
	try {
		// get base instance
		$this->base = load('base:base');
		
		// establish database connection
		$this->base->loadClass('database');
				
	} catch (Exception $e) {
		
		// trigger error
		printf('%s on Line %u: Unable to start base class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
	
	// input check
	if (!is_array($project)) {
		throw new Display_SimpleFormIndexException("Input for parameter project is expected to be an array");
	}
	if (!is_array($page)) {
		throw new Display_SimpleFormIndexException("Input for parameter page is expected to be an array");
	}
	
	// load session class
	$this->session = load('Base:Session');
	
	// assign project, page info to class properties
	$this->_project = $project;
	$this->_page = $page;
	
	// get generator form
	$GENERATORFORM = load('Content:GeneratorForm');
	$this->_generator_form = $GENERATORFORM->selectGeneratorForm(WCOM_CURRENT_PAGE);
	
	// assign simple form to smarty
	$this->base->utility->smarty->assign('generator_form', $this->_generator_form);
	
	// get generator form fields
	$GENERATORFORMFIELDS = load('Content:GeneratorFormField');
	$select_params = array(
		'form' => WCOM_CURRENT_PAGE
	);
	$this->_generator_form_fields = $GENERATORFORMFIELDS->selectGeneratorFormFields();
	
	// load captcha class
	$this->captcha = load('Utility:Captcha');
}

/**
 * Default method that will be called from the display script
 * and has to care about the page preparation. Returns boolean
 * true on success.
 * 
 * @return bool
 */ 
public function render ()
{
	// start new HTML_QuickForm
	$FORM = $this->base->utility->loadQuickForm('generator_form', 'post',
		$this->getLocationSelf());
	
	foreach ($this->_generator_form_fields as $_field) {
		// prepare id
		$field_id = sprintf('generator_form_%s', strtolower($_field['name']));
		
		// prepare regex
		$field_regex = sprintf('=%s=', $_field['validator_regex']);
		
		// create element depending on the field type
		switch ((string)$_field['type']) {
			case 'checkbox':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'class' => 'checkbox'
					);
					
					$element = $FORM->addElement('checkbox', $_field['name'], $_field['label'], null, $attributes);
				break;
			case 'hidden':
					// create element
					$element = $FORM->addElement('hidden', $_field['name']);
				break;
			case 'select':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'class' => 'select'
					);
					
					// prepare values
					$values = array();
					foreach (explode(',', $_field['value']) as $_value) {
						$values[$_value] = $_value;
					}
					
					// create element
					$element = $FORM->addElement('select', $_field['name'], $_field['label'], $values, $attributes);
					
					// add 'in_array_keys' rule
					$FORM->addRule($_field['name'], sprintf('Selection in field %s is out of range', $_field['name']),
						'in_array_keys', $values);
				break;
			case 'submit':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'class' => 'submit'
					);
					
					// create element
					$element = $FORM->addElement('submit', $_field['name'], $_field['label'], $attributes);
				break;
			case 'text':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'maxlength' => 255,
						'class' => 'textfield'
					);
					
					// create element
					$element = $FORM->addElement('text', $_field['name'], $_field['label'], $attributes);
				break;
			case 'textarea':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'class' => 'textarea',
						'cols' => 30,
						'rows' => 6
					);
					
					// create element
					$element = $FORM->addElement('textarea', $_field['name'], $_field['label'], $attributes);
				break;
			case 'radio':
					// smoking: radios (yes|no)
					$form->addElement('radio', $_field['name'], $_field['label'], $_field['value'], $attributes);
				break;
			case 'reset':
					// prepare attributes
					$attributes = array(
						'id' => $field_id,
						'class' => 'reset'
					);
					
					// create element
					$element = $FORM->addElement('reset', $_field['name'], $_field['label'], $attributes);
				break;
		}
		
		// set value
		if (!$FORM->isSubmitted()) {
			$element->setValue($_field['value']);
		}
		
		// apply default filters
		$FORM->applyFilter($_field['name'], 'trim');
		$FORM->applyFilter($_field['name'], 'strip_tags');
		
		// add required rule?
		if ((int)$_field['required']) {
			$FORM->addRule($_field['name'], $_field['required_message'], 'required');
		}
		
		// add regex rule?
		if (!empty($_field['regular_expression'])) {
			$FORM->addRule($_field['name'], $_field['validator_message'], 'regex', $field_regex);
		}
	}
	
	// textfield for captcha if the captcha is enabled
	if ($this->_generator_form['use_captcha'] != 'no') {
		$FORM->addElement('text', '_qf_captcha', gettext('Captcha text'),
			array('id' => 'simple_form_captcha', 'maxlength' => 255, 'class' => 'w300'));
		$FORM->applyFilter('_qf_captcha', 'trim');
		$FORM->applyFilter('_qf_captcha', 'strip_tags');
		$FORM->addRule('_qf_captcha', gettext('Please enter the captcha text'), 'required');
		$FORM->addRule('_qf_captcha', gettext('Invalid captcha text entered'), 'is_equal',
			$this->captcha->captchaValue());
	}
	
	// test if the form validates. if it validates, process it and
	// skip the rest of the page
	if ($FORM->validate()) {
		// freeze the form
		$FORM->freeze();
		
		// prepare & assign form data
		$form_data = array(
			'now' => mktime()
		);
		foreach ($this->_generator_form_fields as $_field) {
			$form_data[$_field['name']] = $FORM->exportValue($_field['name']);
		}
		$this->base->utility->smarty->assign('form_data', $form_data);
		
		// fetch mail body
		$body = $this->base->utility->smarty->fetch($this->getMailTemplateName(),
			md5($_SERVER['REQUEST_URI']));
		
		// load PEAR::Mail
		require_once('Mail.php');
		$MAIL = Mail::factory('mail');
		
		// prepare the rest of the email
		$recipients = $this->_generator_form['email_to'];
		
		// headers
		$headers = array();
		$headers['From'] = (($this->_generator_form['email_from'] == 'sender@simpleform.wcom') ?
			$FORM->exportValue('email') : $this->_generator_form['email_from']);
		$headers['Subject'] = $this->_generator_form['email_subject'];
		$headers['Reply-To'] = $FORM->exportValue('email');
		
		// send mail
		if ($MAIL->send($recipients, $headers, $body)) {
			// add response to session
			$_SESSION['form_submitted'] = 1;
			
			// save session
			$this->session->save();
			
			// redirect
			header($this->getRedirectLocationSelf());
			exit;
		} else {
			throw new Display_SimpleFormIndexException("E-mail couldn't be sent");
		}
	}
	
	// render form
	$renderer = $this->base->utility->loadQuickFormSmartyRenderer();
	$renderer->setRequiredTemplate($this->getRequiredTemplate());
	
	// remove attribute on form tag for XHTML compliance
	$FORM->removeAttribute('name');
	$FORM->removeAttribute('target');
	
	$FORM->accept($renderer);
	
	// assign the form to smarty
	$this->base->utility->smarty->assign('form', $renderer->toArray());
	
	// generate captcha if required
	if ($this->_generator_form['use_captcha'] != 'no') {
		// captcha generation
		$captcha = null;
		if ($this->_generator_form['use_captcha'] == 'image') {
			// generate image captcha
			$captcha = $this->captcha->createCaptcha('image');
			
			// let's tell the template that the captcha is an image
			$this->base->utility->smarty->assign('captcha_type', 'image');
		} elseif ($this->_generator_form['use_captcha'] == 'numeral') { 
			// generate numeral captcha
			$captcha = $this->captcha->createCaptcha('numeral');
			
			// let's tell the template that the captcha is an numeral captcha 
			$this->base->utility->smarty->assign('captcha_type', 'numeral');
		}
		$this->base->utility->smarty->assign('captcha', $captcha);
	}
	
	// empty $_SESSION
	if (!empty($_SESSION['form_submitted'])) {
		$_SESSION['form_submitted'] = '';
	}
	
	return true;
}

/**
 * Returns the cache mode for the current template.
 * 
 * @return int
 */
public function getMainTemplateCacheMode ()
{
	return 0;
}

/**
 * Returns the cache lifetime of the current template.
 * 
 * @return int
 */
public function getMainTemplateCacheLifetime ()
{
	return 0;
}

/** 
 * Returns the name of the current template.
 * 
 * @return string
 */ 
public function getMainTemplateName ()
{
	return "wcom:generator_form_index.".WCOM_CURRENT_PAGE;
}

/**
 * Returns the name of the mail template.
 * 
 * @return string
 */
public function getMailTemplateName ()
{
	return "wcom:generator_form_mail.".WCOM_CURRENT_PAGE;
}

/**
 * Returns the redirect location of the the current
 * document (~ $PHP_SELF without it's problems) with the
 * Location: header prepended.
 * 
 * @return string
 */
public function getRedirectLocationSelf ()
{
	return "Location: ".$this->getLocationSelf();
}

/**
 * Returns the redirect location of the the current
 * document (~ $PHP_SELF without it's problems).
 * 
 * @return string
 */
public function getLocationSelf ()
{
	// prepare params
	$params = array(
		'project' => $this->_project['name_url'],
		'page_id' => $this->_page['id'],
		'action' => 'Index'
	);
	
	// send params to url generator. we hope to get back something useful.
	$URLGENERATOR = load('Utility:UrlGenerator');
	$url = $URLGENERATOR->generateInternalLink($params);
	
	// return the url or a hash mark if the url is empty 
	if (empty($url)) {
		return '#';
	} else {
		return $url;
	}
}

/**
 * Returns QuickForm template to indicate required field.
 * 
 * @return string
 */
public function getRequiredTemplate ()
{
	$tpl = '
		{if $error}
			{$label}<span style="color:red;">*</span>
		{else}
			{if $required}
				{$label}*
			{else}
				{$label}
			{/if}      
		{/if}
	';
	
	return $tpl;
}

/**
 * Returns information whether to skip authentication
 * or not.
 * 
 * @return bool
 */
public function skipAuthentication ()
{
	return false;
}

// end of class
}

class Display_SimpleFormIndexException extends Exception { }

?>