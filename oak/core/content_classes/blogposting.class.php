<?php

/**
 * Project: Oak
 * File: blogposting.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

class Content_Blogposting {
	
	/**
	 * Singleton
	 * @var object
	 */
	private static $instance = null;
	
	/**
	 * Reference to base class
	 * @var object
	 */
	public $base = null;

/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
protected function __construct()
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
}

/**
 * Singleton. Returns instance of the Content_Blogposting object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Content_Blogposting::$instance == null) {
		Content_Blogposting::$instance = new Content_Blogposting(); 
	}
	return Content_Blogposting::$instance;
}

/**
 * Adds blog posting to the blog posting table. Takes a field=>value
 * array with blog posting data as first argument. Returns insert id. 
 * 
 * @throws Content_BlogpostingException
 * @param array Row data
 * @return int Insert id
 */
public function addBlogPosting ($sqlData)
{
	if (!is_array($sqlData)) {
		throw new Content_BlogpostingException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	return $this->base->db->insert(OAK_DB_CONTENT_BLOG_POSTINGS, $sqlData);
}

/**
 * Updates blog posting. Takes the blog posting id as first argument, a
 * field=>value array with the new blog posting data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_BlogpostingException
 * @param int Blog posting id
 * @param array Row data
 * @return int Affected rows
*/
public function updateBlogPosting ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BlogpostingException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_BlogpostingException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_CONTENT_BLOG_POSTINGS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes blog posting from the blog postings table. Takes the
 * blog posting id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Content_BlogpostingException
 * @param int Blog posting id
 * @return int Amount of affected rows
 */
public function deleteBlogPosting ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BlogpostingException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_CONTENT_BLOG_POSTINGS, $where, $bind_params);
}

/**
 * Selects one blog posting. Takes the blog posting id as first
 * argument. Returns array with blog posting information.
 * 
 * @throws Content_BlogpostingException
 * @param int Blog posting id
 * @return array
 */
public function selectBlogPosting ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BlogpostingException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_blog_postings`.`id` AS `id`,
			`content_blog_postings`.`page` AS `page`,
			`content_blog_postings`.`user` AS `user`,
			`content_blog_postings`.`title` AS `title`,
			`content_blog_postings`.`title_url` AS `title_url`,
			`content_blog_postings`.`summary_raw` AS `summary_raw`,
			`content_blog_postings`.`summary` AS `summary`,
			`content_blog_postings`.`content_raw` AS `content_raw`,
			`content_blog_postings`.`content` AS `content`,
			`content_blog_postings`.`draft` AS `draft`,
			`content_blog_postings`.`ping` AS `ping`,
			`content_blog_postings`.`comments_enable` AS `comments_enable`,
			`content_blog_postings`.`comment_count` AS `comment_count`,
			`content_blog_postings`.`trackbacks_enable` AS `trackbacks_enable`,
			`content_blog_postings`.`trackback_count` AS `trackback_count`,
			`content_blog_postings`.`pingbacks_enable` AS `pingbacks_enable`,
			`content_blog_postings`.`pingback_count` AS `pingback_count`,
			`content_blog_postings`.`tag_count` AS `tag_count`,
			`content_blog_postings`.`tag_array` AS `tag_array`,
			`content_blog_postings`.`date_modified` AS `date_modified`,
			`content_blog_postings`.`date_added` AS `date_added`,
			`content_nodes`.`id` AS `node_id`,
			`content_nodes`.`navigation` AS `node_navigation`,
			`content_nodes`.`root_node` AS `node_root_node`,
			`content_nodes`.`parent` AS `node_parent`,
			`content_nodes`.`lft` AS `node_lft`,
			`content_nodes`.`rgt` AS `node_rgt`,
			`content_nodes`.`level` AS `node_level`,
			`content_nodes`.`sorting` AS `node_sorting`,
			`content_pages`.`id` AS `page_id`,
			`content_pages`.`project` AS `page_project`,
			`content_pages`.`type` AS `page_type`,
			`content_pages`.`template_set` AS `page_template_set`,
			`content_pages`.`name` AS `page_name`,
			`content_pages`.`name_url` AS `page_name_url`,
			`content_pages`.`url` AS `page_url`,
			`content_pages`.`protect` AS `page_protect`,
			`content_pages`.`index_page` AS `page_index_page`,
			`content_pages`.`image_small` AS `page_image_small`,
			`content_pages`.`image_medium` AS `page_image_medium`,
			`content_pages`.`image_big` AS `page_image_big`
		FROM
			".OAK_DB_CONTENT_BLOG_POSTINGS." AS `content_blog_postings`
		JOIN
			".OAK_DB_USER_USERS." AS `user_users`
		  ON
			`content_blog_postings`.`user` = `user_users`.`id`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_blog_postings`.`page` = `content_pages`.`id`
		JOIN
			".OAK_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`			
		WHERE
			`content_blog_postings`.`id` = :id
		  AND
			`content_pages`.`project` = :project
		  AND
			`user_users`.`id` = :user
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT,
		'user' => OAK_CURRENT_USER
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more blog postings. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>user, int, optional: User/author id</li>
 * <li>page, int, optional: Page id</li>
 * <li>draft, int, optional: Draft bit (0/1)</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *        <li>USER_NAME: sort by user name</li>
 *        <li>PAGE: sorty by page id</li>
 *        <li>DATE_MODIFIED: sorty by date modified</li>
 *        <li>DATE_ADDED: sort by date added</li>
 *    </ul>
 * </li>
 * </ul>
 * 
 * @throws Content_BlogpostingException
 * @param array Select params
 * @return array
 */
public function selectBlogPostings ($params = array())
{
	// define some vars
	$user = null;
	$page = null;
	$draft = null;
	$timeframe = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_BlogpostingException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'timeframe':
			case 'order_macro':
					$$_key = (string)$_value;
				break;
			case 'user':
			case 'page':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			case 'draft':
					$$_key = (is_null($_value) ? null : (string)$_value);
				break;
			default:
				throw new Content_BlogpostingException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'USER_NAME' => '`application_users`.`name`',
		'DATE_ADDED' => '`content_blog_postings`.`date_added`',
		'DATE_MODIFIED' => '`content_blog_postings`.`date_modified`'
	);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT 
			`content_blog_postings`.`id` AS `id`,
			`content_blog_postings`.`page` AS `page`,
			`content_blog_postings`.`user` AS `user`,
			`content_blog_postings`.`title` AS `title`,
			`content_blog_postings`.`title_url` AS `title_url`,
			`content_blog_postings`.`summary_raw` AS `summary_raw`,
			`content_blog_postings`.`summary` AS `summary`,
			`content_blog_postings`.`content_raw` AS `content_raw`,
			`content_blog_postings`.`content` AS `content`,
			`content_blog_postings`.`draft` AS `draft`,
			`content_blog_postings`.`ping` AS `ping`,
			`content_blog_postings`.`comments_enable` AS `comments_enable`,
			`content_blog_postings`.`comment_count` AS `comment_count`,
			`content_blog_postings`.`trackbacks_enable` AS `trackbacks_enable`,
			`content_blog_postings`.`trackback_count` AS `trackback_count`,
			`content_blog_postings`.`pingbacks_enable` AS `pingbacks_enable`,
			`content_blog_postings`.`pingback_count` AS `pingback_count`,
			`content_blog_postings`.`tag_count` AS `tag_count`,
			`content_blog_postings`.`tag_array` AS `tag_array`,
			`content_blog_postings`.`date_modified` AS `date_modified`,
			`content_blog_postings`.`date_added` AS `date_added`,
			`content_nodes`.`id` AS `node_id`,
			`content_nodes`.`navigation` AS `node_navigation`,
			`content_nodes`.`root_node` AS `node_root_node`,
			`content_nodes`.`parent` AS `node_parent`,
			`content_nodes`.`lft` AS `node_lft`,
			`content_nodes`.`rgt` AS `node_rgt`,
			`content_nodes`.`level` AS `node_level`,
			`content_nodes`.`sorting` AS `node_sorting`,
			`content_pages`.`id` AS `page_id`,
			`content_pages`.`project` AS `page_project`,
			`content_pages`.`type` AS `page_type`,
			`content_pages`.`template_set` AS `page_template_set`,
			`content_pages`.`name` AS `page_name`,
			`content_pages`.`name_url` AS `page_name_url`,
			`content_pages`.`url` AS `page_url`,
			`content_pages`.`protect` AS `page_protect`,
			`content_pages`.`index_page` AS `page_index_page`,
			`content_pages`.`image_small` AS `page_image_small`,
			`content_pages`.`image_medium` AS `page_image_medium`,
			`content_pages`.`image_big` AS `page_image_big`
		FROM
			".OAK_DB_CONTENT_BLOG_POSTINGS." AS `content_blog_postings`
		JOIN
			".OAK_DB_USER_USERS." AS `user_users`
		  ON
			`content_blog_postings`.`user` = `user_users`.`id`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_blog_postings`.`page` = `content_pages`.`id`
		JOIN
			".OAK_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($user) && is_numeric($user)) {
		$sql .= " AND `user_users`.`id` = :user ";
		$bind_params['user'] = $user;
	}
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_pages`.`id` = :page ";
		$bind_params['page'] = $page;
	}
	if (!is_null($draft) && is_numeric($draft)) {
		$sql .= " AND `content_blog_postings`.`draft` = :draft ";
		$bind_params['draft'] = (string)$draft;
	}
	if (!empty($timeframe)) {
		$sql .= " AND ".$HELPER->_sqlForTimeFrame('`content_blog_postings`.`date_added`',
			$timeframe);
	}
	
	// add sorting
	if (!empty($order_macro)) {
		$HELPER = load('utility:helper');
		$sql .= " ORDER BY ".$HELPER->_sqlForOrderMacro($order_macro, $macros);
	}
	
	// add limits
	if (empty($start) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u", $limit);
	}
	if (!empty($start) && is_numeric($start) && !empty($limit) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u, %u", $start, $limit);
	}

	return $this->base->db->select($sql, 'multi', $bind_params);
}

/**
 * Method to count blog postings. Takes key=>value array
 * with select params as first argument. Returns int.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>user, int, optional: User/author id</li>
 * <li>page, int, optional: Page id</li>
 * <li>draft, int, optional: Draft bit (0/1)</li>
 * </ul>
 * 
 * @throws Content_BlogpostingException
 * @param array Select params
 * @return int
 */
public function countBlogPostings ($params = array())
{
	// define some vars
	$user = null;
	$page = null;
	$draft = null;
	$timeframe = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_BlogpostingException('Input for parameter params is not an array');	
	}
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'user':
			case 'page':
					$$_key = (int)$_value;
				break;
			case 'timeframe':
					$$_key = (string)$_value;
				break;
			case 'draft':
					$$_key = (is_null($_value) ? null : (string)$_value);
				break;
			default:
				throw new Content_BlogpostingException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_CONTENT_BLOG_POSTINGS." AS `content_blog_postings`
		JOIN
			".OAK_DB_USER_USERS." AS `user_users`
		  ON
			`content_blog_postings`.`user` = `user_users`.`id`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_blog_postings`.`page` = `content_pages`.`id`
		WHERE
			1
	";
	
	// add where clauses
	if (!empty($user) && is_numeric($user)) {
		$sql .= " AND `application_users`.`id` = :user ";
		$bind_params['user'] = $user;
	}
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_pages`.`id` = :page ";
		$bind_params['page'] = $page;
	}
	if (is_numeric($draft)) {
		$sql .= " AND `content_blog_postings`.`draft` = :draft ";
		$bind_params['draft'] = $draft;
	}
	if (!empty($timeframe)) {
		$sql .= " AND ".$HELPER->_sqlForTimeFrame('`content_blog_postings`.`date_added`',
			$timeframe);
	}
	
	return $this->base->db->select($sql, 'field', $bind_params);
}

// end of class
}

class Content_BlogpostingException extends Exception { }

?>