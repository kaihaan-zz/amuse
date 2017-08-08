<?php
	/*
	Plugin Name: WP Spell Check
	Description: Check your website for spelling errors and empty fields to improve SEO
	Version: 5.3
	Author: Persyo
	Requires at least: 4.1.1
	Tested up to: 4.7
	Stable tag: 5.3
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
	Copyright: © 2016 Persyo
	Contributors: wpspellcheck
	Donate Link: www.wpspellcheck.com
	Tags: spelling, SEO, Spell Check, WordPress spell check, Spell Checker, WordPress spell checker, spelling errors, spelling mistakes, spelling report, fix spelling, WP Spell Check
	
	Author URI: https://www.wpspellcheck.com
	
	Works in the background: yes
	Pro version scans the entire website: yes
	Sends email reminders: yes
	Finds place holder text: yes
	Custom Dictionary for unusual words: yes
	Scans Password Protected membership Sites: yes
	Unlimited scans on my website: Yes

	Scans Categories: Yes WP Spell Check Pro
	Scans SEO Titles: Yes WP Spell Check Pro
	Scans SEO Descriptions: Yes WP Spell Check Pro
	Scans WordPress Menus: Yes WP Spell Check Pro
	Scans Page Titles: Yes WP Spell Check Pro
	Scans Post Titles: Yes WP Spell Check Pro
	Scans Page slugs: Yes WP Spell Check Pro
	Scans Post Slugs: Yes WP Spell Check Pro
	Scans Post categories: Yes WP Spell Check Pro

	Privacy URI: https://www.wpspellcheck.com/privacy-policy/
	Pro Add-on / Home Page: https://www.wpspellcheck.com/
	Pro Add-on / Prices: https://www.wpspellcheck.com/purchase-options/
	*/
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	require_once( ABSPATH . 'wp-includes/pluggable.php' );

	/* Include the plugin files */
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	
	wp_enqueue_style( 'jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_style( 'admin-styles', plugin_dir_url( __FILE__ ) . 'css/admin-styles.css' );
	wp_enqueue_script('admin-js', plugin_dir_url( __FILE__ ) . 'js/feature-request.js');
	wp_enqueue_script('feature-request', plugin_dir_url( __FILE__ ) . 'js/admin-js.js');
	wp_enqueue_style( 'global-admin-styles', plugin_dir_url( __FILE__ ) . 'css/global-admin-styles.css' );
	
	function wpsc_set_global_vars() {
		global $wpdb;
		global $ignore_list;
		global $dict_list;
		global $wpsc_settings;
		
		$words_table = $wpdb->prefix . 'spellcheck_words';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		
		$check_opt = $wpdb->get_results("SHOW TABLES LIKE '$options_table'");
		$check_word = $wpdb->get_results("SHOW TABLES LIKE '$words_table'");
		$check_ig = $wpdb->get_results("SHOW TABLES LIKE '$ignore_table'");
		$check_dict = $wpdb->get_results("SHOW TABLES LIKE '$dictionary_table'");
		
		if (sizeof($check_opt) != 0 && sizeof($check_word) != 0 && sizeof($check_ig) != 0 && sizeof($check_dict) != 0) {
			if (sizeof($ignore_list) < 1) $ignore_list = $wpdb->get_results("SELECT word FROM $words_table WHERE ignore_word = true");
			if (sizeof($dict_list) < 1) $dict_list = $wpdb->get_results("SELECT word FROM $dict_table");
			if (sizeof($wpsc_settings) < 1) $wpsc_settings = $wpdb->get_results("SELECT * FROM $options_table");
		}
	}
	
	
	if (is_plugin_active('wp-spell-check-pro/wpspellcheckpro.php')) {
		include dirname(__FILE__) . '-pro/pro-loader.php';
	}
		if (is_plugin_active('wp-spell-check-enterprise/wpspellcheckenterprise.php')) {
		include dirname(__FILE__) . '-enterprise/enterprise-loader.php';
	}
	require_once( 'admin/wpsc-framework.php' );
	require_once( 'admin/wpsc-options.php' );
	require_once( 'admin/wpsc-dictionary.php' );
	require_once( 'admin/wpsc-ignore.php' );
	require_once( 'admin/wpsc-results.php' );
	require_once( 'admin/wpsc-empty.php' );
	global $scdb_version;
	global $scan_delay;
	$scan_delay = 0;
	$scdb_version = '1.0';
	wpsc_set_global_vars();
	
	/* Initialization Code */
	
	function install_spellcheck() {
		global $wpdb;
		global $scdb_version;
		
		$table_name = $wpdb->prefix . 'spellcheck_words';
		$dictionary_table = $wpdb->prefix . 'spellcheck_dictionary';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		
		$charset_collate = '';
		
		if (!empty($wpdb->charset)) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		
		if (!empty($wpdb->collate)) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}
		
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			word varchar(100) NOT NULL,
			page_name varchar(100) NOT NULL,
			page_type varchar(100) NOT NULL,
			ignore_word bool DEFAULT false,
			UNIQUE KEY id (id)
		) $charset_collate;"; 
		
		
		
		dbDelta($sql);

		$sql = "CREATE TABLE $dictionary_table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			word VARCHAR(100) NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;"; 

		dbDelta($sql);

		$sql = "CREATE TABLE $options_table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			option_name VARCHAR(100) NOT NULL,
			option_value VARCHAR(100) NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;"; 

		dbDelta($sql);

		$sql = "CREATE TABLE $ignore_table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			keyword VARCHAR(100) NOT NULL,
			type VARCHAR(100) NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;"; 

		dbDelta($sql);

		$check = $wpdb->get_results ('SELECT * FROM ' . $options_table);

		if (sizeof($check) < 1) {
			$wpdb->insert($options_table, array('option_name' => 'email', 'option_value' => 'false'));
			$wpdb->insert($options_table, array('option_name' => 'email_address', 'option_value' => ''));
			$wpdb->insert($options_table, array('option_name' => 'email_frequency', 'option_value' => '1'));
			$wpdb->insert($options_table, array('option_name' => 'ignore_caps', 'option_value' => 'false'));
			$wpdb->insert($options_table, array('option_name' => 'check_pages', 'option_value' => 'true'));
			$wpdb->insert($options_table, array('option_name' => 'check_posts', 'option_value' => 'true'));
			$wpdb->insert($options_table, array('option_name' => 'check_theme', 'option_value' => 'false'));
			$wpdb->insert($options_table, array('option_name' => 'check_menus', 'option_value' => 'true'));
			$wpdb->insert($options_table, array('option_name' => 'scan_frequency', 'option_value' => '1'));
			$wpdb->insert($options_table, array('option_name' => 'scan_frequency_interval', 'option_value' => 'daily'));
			$wpdb->insert($options_table, array('option_name' => 'email_frequency_interval', 'option_value' => 'daily'));
			$wpdb->insert($options_table, array('option_name' => 'language_setting', 'option_value' => 'en_CA'));
			$wpdb->insert($options_table, array('option_name' => 'page_titles', 'option_value' => 'true'));
			$wpdb->insert($options_table, array('option_name' => 'post_titles', 'option_value' => 'true'));
			$wpdb->insert($options_table, array('option_name' => 'tags', 'option_value' => 'true'));
			$wpdb->insert($options_table, array('option_name' => 'categories', 'option_value' => 'true'));
			$wpdb->insert($options_table, array('option_name' => 'seo_desc', 'option_value' => 'true'));
			$wpdb->insert($options_table, array('option_name' => 'seo_titles', 'option_value' => 'true'));
			$wpdb->insert($options_table, array('option_name' => 'page_slugs', 'option_value' => 'false'));
			$wpdb->insert($options_table, array('option_name' => 'post_slugs', 'option_value' => 'false'));
			$wpdb->insert($options_table, array('option_name' => 'api_key', 'option_value' => ''));
			$wpdb->insert($options_table, array('option_name' => 'pro_word_count', 'option_value' => '0'));
			$wpdb->insert($options_table, array('option_name' => 'total_word_count', 'option_value' => '0'));
			$wpdb->insert($options_table, array('option_name' => 'ignore_emails', 'option_value' => 'false'));
			$wpdb->insert($options_table, array('option_name' => 'ignore_websites', 'option_value' => 'false'));
			$wpdb->insert($options_table, array('option_name' => 'scan_in_progress', 'option_value' => 'false'));
			$wpdb->insert($options_table, array('option_name' => 'last_scan_started', 'option_value' => '0'));
			$wpdb->insert($options_table, array('option_name' => 'last_scan_finished', 'option_value' => '0'));
			$wpdb->insert($options_table, array('option_name' => 'page_count', 'option_value' => '0'));
			$wpdb->insert($options_table, array('option_name' => 'post_count', 'option_value' => '0'));
		}

		$check = $wpdb->get_results ('SELECT * FROM ' . $dictionary_table);

		if (sizeof($check) < 1) {
		
		$wpdb->insert($dictionary_table, array('word' => 'Facebook'));
		$wpdb->insert($dictionary_table, array('word' => 'LinkedIn'));
		$wpdb->insert($dictionary_table, array('word' => 'Twitter'));
		$wpdb->insert($dictionary_table, array('word' => 'Digg'));
		$wpdb->insert($dictionary_table, array('word' => 'http'));
		$wpdb->insert($dictionary_table, array('word' => 'SEO'));
		$wpdb->insert($dictionary_table, array('word' => 'FTP'));
		$wpdb->insert($dictionary_table, array('word' => 'Online'));
		$wpdb->insert($dictionary_table, array('word' => 'Internet'));
		$wpdb->insert($dictionary_table, array('word' => 'Blog'));
		$wpdb->insert($dictionary_table, array('word' => 'Blogging'));
		$wpdb->insert($dictionary_table, array('word' => 'Blogged'));
		$wpdb->insert($dictionary_table, array('word' => 'Google'));
		$wpdb->insert($dictionary_table, array('word' => 'Google+'));
		$wpdb->insert($dictionary_table, array('word' => 'Groupon'));
		$wpdb->insert($dictionary_table, array('word' => 'YouTube'));
		$wpdb->insert($dictionary_table, array('word' => 'Vimeo'));
		$wpdb->insert($dictionary_table, array('word' => 'unparalleled'));
		$wpdb->insert($dictionary_table, array('word' => 'iPhone'));
		$wpdb->insert($dictionary_table, array('word' => 'iPod'));
		$wpdb->insert($dictionary_table, array('word' => 'www'));
		$wpdb->insert($dictionary_table, array('word' => 'StumbleUpon'));
		$wpdb->insert($dictionary_table, array('word' => 'username'));
		$wpdb->insert($dictionary_table, array('word' => 'yellowpage'));
		$wpdb->insert($dictionary_table, array('word' => 'WordPress'));
		$wpdb->insert($dictionary_table, array('word' => 'Permalinks'));
		$wpdb->insert($dictionary_table, array('word' => 'Plugin'));
		$wpdb->insert($dictionary_table, array('word' => 'Firefox'));
		$wpdb->insert($dictionary_table, array('word' => 'Adwords'));
		$wpdb->insert($dictionary_table, array('word' => 'Yoast'));
		$wpdb->insert($dictionary_table, array('word' => 'Blogs'));
		$wpdb->insert($dictionary_table, array('word' => 'PHP'));
		$wpdb->insert($dictionary_table, array('word' => 'JS'));
		}
		
		add_option( 'scdb_version', $scdb_version );
	}

	
	function install_spellcheck_main($networkwide) {
		global $wpdb;
		
		if (function_exists('is_multisite') && is_multisite()) {
			if ($networkwide) {
				$old_blog = $wpdb->blogid;
				
				
				$blogids = $wpdb->get_col("SELECT blog_ID FROM $wpdb->blogs");
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					install_spellcheck();
				}
				switch_to_blog($old_blog);
			}
		}
		install_spellcheck();
	}
	
	register_activation_hook( __FILE__, 'install_spellcheck_main' );

	function update_db_check() {
		global $wpdb;
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$empty_table = $wpdb->prefix . 'spellcheck_empty';
		$table_name = $wpdb->prefix . 'spellcheck_words';
		
		$check_db = $wpdb->get_results("SHOW TABLES LIKE '$options_table'");
		
		$charset_collate = '';
	
		if (!empty($wpdb->charset)) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		
		if (!empty($wpdb->collate)) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}
	
		$sql = "CREATE TABLE $empty_table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			word varchar(100) NOT NULL,
			page_name varchar(100) NOT NULL,
			page_type varchar(100) NOT NULL,
			ignore_word bool DEFAULT false,
			page_id mediumint(9),
			UNIQUE KEY id (id)
		) $charset_collate;"; 
		
		dbDelta($sql);
		
		
		$charset_collate = '';
	
		if (!empty($wpdb->charset)) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		
		if (!empty($wpdb->collate)) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}
	
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			word varchar(100) NOT NULL,
			page_name varchar(100) NOT NULL,
			page_type varchar(100) NOT NULL,
			ignore_word bool DEFAULT false,
			page_id mediumint(9),
			UNIQUE KEY id (id)
		) $charset_collate;"; 
		
		dbDelta($sql);

		if(sizeof($check_db) != 0) {	
			global $wpsc_settings;
			if (sizeof($wpsc_settings) > 0) { $check = $wpsc_settings;
			} else { $check = $wpdb->get_results ('SELECT * FROM ' . $options_table); }

			
			if (sizeof($check) < 32) {
				$wpdb->insert($options_table, array('option_name' => 'check_sliders', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'media_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'highlight_word', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'highlight_word', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'highlight_word', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_ecommerce', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cf7', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_desc', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_slug', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_desc', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_slug', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_custom', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'last_scan_date', 'option_value' => time()));
				$wpdb->insert($options_table, array('option_name' => 'check_authors', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'last_scan_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'empty_checked', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'check_authors_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_menu_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_ecommerce_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'empty_scan_in_progress', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'pro_empty_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'last_empty_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'literary_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 37) {
				$wpdb->insert($options_table, array('option_name' => 'check_ecommerce', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cf7', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_desc', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_slug', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_desc', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_slug', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_custom', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'last_scan_date', 'option_value' => time()));
				$wpdb->insert($options_table, array('option_name' => 'check_authors', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'last_scan_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'empty_checked', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'check_authors_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_menu_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_ecommerce_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'empty_scan_in_progress', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'pro_empty_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'last_empty_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'literary_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 38) {
				$wpdb->insert($options_table, array('option_name' => 'check_cf7', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_desc', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_slug', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_desc', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_slug', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_custom', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'last_scan_date', 'option_value' => time()));
				$wpdb->insert($options_table, array('option_name' => 'check_authors', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'last_scan_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'empty_checked', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'check_authors_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_menu_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_ecommerce_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'empty_scan_in_progress', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'pro_empty_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'last_empty_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'literary_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 39) {
				$wpdb->insert($options_table, array('option_name' => 'check_tag_desc', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_slug', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_desc', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_slug', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_custom', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'last_scan_date', 'option_value' => time()));
				$wpdb->insert($options_table, array('option_name' => 'check_authors', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'last_scan_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'empty_checked', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'check_authors_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_menu_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_ecommerce_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'empty_scan_in_progress', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'pro_empty_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'last_empty_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'literary_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 43) {
				$wpdb->insert($options_table, array('option_name' => 'check_custom', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'last_scan_date', 'option_value' => time()));
				$wpdb->insert($options_table, array('option_name' => 'check_authors', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'last_scan_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'empty_checked', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'check_authors_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_menu_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_ecommerce_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'empty_scan_in_progress', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'pro_empty_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'last_empty_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'literary_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 45) {
				$wpdb->insert($options_table, array('option_name' => 'check_authors', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'last_scan_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'empty_checked', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'check_authors_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_menu_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_ecommerce_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'empty_scan_in_progress', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'pro_empty_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'last_empty_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'literary_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 46) {
				$wpdb->insert($options_table, array('option_name' => 'last_scan_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'empty_checked', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'check_authors_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_menu_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_ecommerce_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'empty_scan_in_progress', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'pro_empty_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'last_empty_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'literary_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 47) {
				$wpdb->insert($options_table, array('option_name' => 'empty_checked', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'check_authors_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_menu_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_ecommerce_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'empty_scan_in_progress', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'pro_empty_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'last_empty_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'literary_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 48) {
				$wpdb->insert($options_table, array('option_name' => 'check_authors_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_menu_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_titles_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_tag_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_cat_desc_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_page_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_post_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_seo_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_media_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'check_ecommerce_empty', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'empty_scan_in_progress', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'pro_empty_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'last_empty_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'literary_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 58) {
				$wpdb->insert($options_table, array('option_name' => 'empty_scan_in_progress', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'pro_empty_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'last_empty_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'literary_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 60) {
				$wpdb->insert($options_table, array('option_name' => 'empty_page_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'pro_empty_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'last_empty_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'literary_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 63) {
				$wpdb->insert($options_table, array('option_name' => 'pro_empty_count', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'last_empty_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'literary_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 64) {
				$wpdb->insert($options_table, array('option_name' => 'last_empty_type', 'option_value' => 'None'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 65) {
				$wpdb->insert($options_table, array('option_name' => 'literary_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_factor', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 67) {
				$wpdb->insert($options_table, array('option_name' => 'page_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'entire_empty_scan', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'empty_start_time', 'option_value' => '0'));
				$wpdb->insert($options_table, array('option_name' => 'page_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'seo_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cf7_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'tag_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'cat_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'page_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'post_slug_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'slider_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_seo_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_author_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_menu_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_page_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_post_title_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_tag_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_cat_desc_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_ecommerce_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_media_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'empty_free_sip_finish', 'option_value' => 'false'));
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			} elseif (sizeof($check) < 137) {
				$wpdb->insert($options_table, array('option_name' => 'scan_page_drafts', 'option_value' => 'true'));
				$wpdb->insert($options_table, array('option_name' => 'scan_post_drafts', 'option_value' => 'true'));
			}
		}
	}
	
	function update_db_check_main() {
		global $wpdb;
		
		if (function_exists('is_multisite') && is_multisite()) {
			if ($networkwide) {
				$old_blog = $wpdb->blogid;
				
				
				$blogids = $wpdb->get_col("SELECT blog_ID FROM $wpdb->blogs");
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					update_db_check();
				}
				switch_to_blog($old_blog);
			}
		}
		update_db_check();
	}
	add_action( 'plugins_loaded', 'update_db_check_main' );
	
	/* Clear out the database for uninstallation */
	function prepare_uninstall() {
		global $wpdb;
		
		
		$sql = "DROP TABLE " . $wpdb->prefix . "spellcheck_dictionary;";
		$wpdb->query($sql);
		$sql = "DROP TABLE " . $wpdb->prefix . "spellcheck_ignore;";
		$wpdb->query($sql);
		$sql = "DROP TABLE " . $wpdb->prefix . "spellcheck_options;";
		$wpdb->query($sql);
		$sql = "DROP TABLE " . $wpdb->prefix . "spellcheck_words;";
		$wpdb->query($sql);
		$sql = "DROP TABLE " . $wpdb->prefix . "spellcheck_empty;";
		$wpdb->query($sql);
		
		
		global $current_user;
		$user_id = $current_user->ID;
		delete_user_meta($user_id, 'wpsc_pro_notice_date');
		delete_user_meta($user_id, 'wpsc_pro_dismissed');
		delete_user_meta($user_id, 'wpsc_ignore_review_notice');
		delete_user_meta($user_id, 'wpsc_review_date');
		delete_user_meta($user_id, 'wpsc_times_dismissed_review');
		delete_user_meta($user_id, 'wpsc_pro_ignore_notice');
		delete_user_meta($user_id, 'wpsc_pro_notice_date');
		delete_user_meta($user_id, 'wpsc_ignore_install_notice');
		delete_user_meta($user_id, 'wpsc_last_check');
		delete_user_meta($user_id, 'wpsc_version');
		delete_user_meta($user_id, 'wpsc_outdated');
		delete_user_meta($user_id, 'wpsc_pro_last_check');
		delete_user_meta($user_id, 'wpsc_pro_version');
		delete_user_meta($user_id, 'wpsc_pro_outdated');
		delete_user_meta($user_id, 'wpsc_ent_last_check');
		delete_user_meta($user_id, 'wpsc_ent_version');
		delete_user_meta($user_id, 'wpsc_ent_outdated');
		delete_user_meta($user_id, 'wpsc_update_notice_date');

	}
	
	/*Create Network Page*/
	function wpsc_uninstall_page() {
		if ($_POST['uninstall'] == 'Uninstall') {
			global $wpdb;
		
			if (function_exists('is_multisite') && is_multisite()) {
				if ($networkwide) {
					$old_blog = $wpdb->blogid;
				
					
					$blogids = $wpdb->get_col("SELECT blog_ID FROM $wpdb->blogs");
					foreach ($blogids as $blog_id) {
						switch_to_blog($blog_id);
						prepare_uninstall();
					}
					switch_to_blog($old_blog);
				}
			}
			prepare_uninstall();
			deactivate_plugins( 'wp-spell-check/wpspellcheck.php' );
			if ($pro_included) deactivate_plugins( 'wp-spell-check-pro/wpspellcheckpro.php' );
			if ($ent_included) deactivate_plugins( 'wp-spell-check-enterprise/wpspellcheckenterprise.php' );
			wp_die( 'WP Spell Check has been deactivated. If you wish to use the plugin again you may activate it on the WordPress plugin page' );
		}
	
		?>
		<h2><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logo.png'; ?>" alt="WP Spell Check" /> <span style="position: relative; top: -15px;">Network Uninstall</span></h2>
		<p>This will deactivate WP Spell Check on all sites on the network and clean up the database of any changes made by WP Spell Check. If you wish to use WP Spell Check again after, you may activate it on the WordPress plugins page</p>
		<form action="settings.php?page=wpsc_uninstall_page" method="post" name="uninstall">
			<input type="submit" name="uninstall" value="Clean up Database and Deactivate Plugin" />
		</form>
		<?php
	}
	

	/* Menu Functions */
	function add_network_menu() {
		add_submenu_page('settings.php', 'WP Spell Check Database Cleanup and Deactivation', 'WP Spell Check Database Cleanup and Deactivation', 'manage_options', 'wpsc_uninstall_page', 'wpsc_uninstall_page');
	}
	add_action( 'network_admin_menu', 'add_network_menu' );
	
	if ($_POST['uninstall'] != "Clean up Database and Deactivate Plugin") {
	
	function add_menu() {	
		global $pro_included;
		global $ent_included;

		if ($pro_included) {
			add_menu_page( 'WP Spell Checker', 'WP Spell Check (Pro)', 'manage_options', 'wp-spellcheck.php', 'admin_render', plugin_dir_url( __FILE__ ) . 'images/logo-icon-16x16.png');
		} elseif ($ent_included) {
			add_menu_page( 'WP Spell Checker', 'WP Spell Check (Enterprise)', 'manage_options', 'wp-spellcheck.php', 'admin_render', plugin_dir_url( __FILE__ ) . 'images/logo-icon-16x16.png');
		} else {
			add_menu_page( 'WP Spell Checker', 'WP Spell Check', 'manage_options', 'wp-spellcheck.php', 'admin_render', plugin_dir_url( __FILE__ ) . 'images/logo-icon-16x16.png');
		}
		add_submenu_page( 'wp-spellcheck.php', 'WP Scanner', 'WP Scanner', 'manage_options', 'wp-spellcheck.php', 'admin_render');
	}
	add_action('admin_menu', 'add_menu');
	
	}
	
	function add_tools_scan_menu() {
		add_submenu_page( 'tools.php', 'WP Spell Check', 'WP Spell Check', 'manage_options', 'wp-spellcheck.php', 'admin_render');
	}
	add_action ('admin_menu', 'add_tools_scan_menu');

	function add_settings_menu() {
		add_submenu_page( 'options-general.php', 'WP Spell Check', 'WP Spell Check', 'manage_options', 'wpsc-options.php', 'render_options');
	}
	add_action ('admin_menu', 'add_settings_menu');

	function add_options_menu() {
		add_submenu_page( 'wp-spellcheck.php', 'Options', 'Options', 'manage_options', 'wp-spellcheck-options.php', 'render_options');
	}
	add_action ('admin_menu', 'add_options_menu');

	function add_dictionary_menu() {	
		add_submenu_page( 'wp-spellcheck.php', 'My Dictionary', 'My Dictionary', 'manage_options', 'wp-spellcheck-dictionary.php', 'dictionary_render');
	}
	add_action('admin_menu', 'add_dictionary_menu');

	function add_ignore_menu() {	
		add_submenu_page( 'wp-spellcheck.php', 'Ignore List', 'Ignore List', 'manage_options', 'wp-spellcheck-ignore.php', 'ignore_render');
	}
	add_action('admin_menu', 'add_ignore_menu');
	
	function wpsc_add_toolbar_menu( $wp_admin_bar ) {
		$site_url = get_option( 'siteurl' );
		$args = array(
			'id'    => 'WP_Spell_Check',
			'title' => 'WP Spell Check',
			'href'  => $site_url . '/wp-admin/admin.php?page=wp-spellcheck.php',
			'meta'  => array( 'class' => 'wpsc-toolbar-page' ),
			'parent' => false
		);
		$wp_admin_bar->add_node( $args );
		
		$args = array(
			'id'    => 'WP_Spell_Check_Scanner',
			'title' => 'WP Scanner',
			'href'  => $site_url . '/wp-admin/admin.php?page=wp-spellcheck.php',
			'meta'  => array( 'class' => 'wpsc-toolbar-page' ),
			'parent' => 'WP_Spell_Check'
		);
		$wp_admin_bar->add_node( $args );
		
		$args = array(
			'id'    => 'WP_Spell_Check_Options',
			'title' => 'Options',
			'href'  => $site_url . '/wp-admin/admin.php?page=wp-spellcheck-options.php',
			'meta'  => array( 'class' => 'wpsc-toolbar-page' ),
			'parent' => 'WP_Spell_Check'
		);
		$wp_admin_bar->add_node( $args );
		
		$args = array(
			'id'    => 'WP_Spell_Check_Dictinary',
			'title' => 'My Dictionary',
			'href'  => $site_url . '/wp-admin/admin.php?page=wp-spellcheck-dictionary.php',
			'meta'  => array( 'class' => 'wpsc-toolbar-page' ),
			'parent' => 'WP_Spell_Check'
		);
		$wp_admin_bar->add_node( $args );
		
		$args = array(
			'id'    => 'WP_Spell_Check_Ignore',
			'title' => 'My Ignore List',
			'href'  => $site_url . '/wp-admin/admin.php?page=wp-spellcheck-ignore.php',
			'meta'  => array( 'class' => 'wpsc-toolbar-page' ),
			'parent' => 'WP_Spell_Check'
		);
		$wp_admin_bar->add_node( $args );
		
		$args = array(
			'id'    => 'WP_Spell_Check_Tutorials',
			'title' => 'Online Training',
			'href'  => 'https://www.wpspellcheck.com/tutorials',
			'meta'  => array( 'class' => 'wpsc-toolbar-page' ),
			'parent' => 'WP_Spell_Check'
		);
		$wp_admin_bar->add_node( $args );
		
	}
	if (current_user_can('manage_options') && $_POST['uninstall'] != "Clean up Database and Deactivate Plugin") add_action( 'admin_bar_menu', 'wpsc_add_toolbar_menu', 999 );
	
	function wpse_my_custom_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready( function($) {
            $( "#re-direct[href$='https://www.example.com']" ).attr( 'target', '_blank' );
        });
    </script>
    <?php
}
add_action( 'admin_head', 'wpse_my_custom_script' );
	
	function add_tutorial_menu() {
		
		add_submenu_page('wp-spellcheck.php','Tutorials','<a id="re-direct" target="_blank" href="https://www.wpspellcheck.com/tutorials">Online Training</a>','manage_options','https://www.wpspellcheck.com/tutorials/');
	}
	add_action('admin_menu', 'add_tutorial_menu');

	function plugin_add_settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=wp-spellcheck-options.php">' . __( 'Settings' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}
	$plugin = plugin_basename( __FILE__ );
	add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );

	function plugin_add_premium_link( $links ) {
		unset($links['edit']); 
		$settings_link = '<a href="https://www.wpspellcheck.com/purchase-options" target="_blank">' . __( 'Premium Features' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}
	$plugin = plugin_basename( __FILE__ );
	add_filter( "plugin_action_links_$plugin", 'plugin_add_premium_link' );

	/* Dashboard Widget */
	function spellcheck_add_dashboard_widget() {
		if (current_user_can('manage_options')) {
			wp_add_dashboard_widget(
				'wp_spellcheck_widget',			
				'WP Spell Check',			
				'spellcheck_create_dashboard_widget'	
			);
		}
	}
	add_action( 'wp_dashboard_setup', 'spellcheck_add_dashboard_widget' );

	function spellcheck_create_dashboard_widget() {
		global $wpdb;
		
		
		$table_name = $wpdb->prefix . "spellcheck_words";
		
		$options_table = $wpdb->prefix . "spellcheck_options";
		$empty_table = $wpdb->prefix . "spellcheck_empty";
		
		$check_db = $wpdb->get_results("SHOW TABLES LIKE '$options_table'");
		
		if (sizeof($check_db) >= 1) {
		$empty_count = $wpdb->get_var ( "SELECT COUNT(*) FROM $empty_table WHERE ignore_word=false" );
		$word_count = $wpdb->get_var ( "SELECT COUNT(*) FROM $table_name WHERE ignore_word=false" );
		
		$literacy_factor = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='literary_factor';");
		$literacy_factor = $literacy_factor[0]->option_value;
		$empty_factor = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='empty_factor';");
		$empty_factor = $empty_factor[0]->option_value;
		echo "<p><span style='color: rgb(0, 115, 0); font-weight: bold;'>Website Literacy Factor: </span><span style='color: red; font-weight: bold;'>" . $literacy_factor . "%</span><br />";
		echo "<span style='color: rgb(0, 115, 0); font-weight: bold;'>Website Empty Fields Factor: </span><span style='color: red; font-weight: bold;'>" . $empty_factor . "%</span><br />";
		echo "The last spell check scan found $word_count spelling errors<br />";
		echo "The last empty fields scan found $empty_count empty fields<br />";
		echo "<a href='/wp-admin/admin.php?page=wp-spellcheck.php'>Click here</a> To view and fix errors</p>";
		}
	}

	/* Cron timer functions */
	function cron_add_custom( $schedules ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_options';
		$check_db = $wpdb->get_results("SHOW TABLES LIKE '$table_name'");
		if(sizeof($check_db) != 0) {
			$scan_frequency = $wpdb->get_results('SELECT option_value FROM ' . $table_name . ' WHERE option_name="scan_frequency";');
			$scan_frequency_interval = $wpdb->get_results('SELECT option_value FROM ' . $table_name . ' WHERE option_name="scan_frequency_interval";');

			switch($scan_frequency_interval[0]->option_value) {
				case "hourly":
					$scan_recurrence = intval($scan_frequency[0]->option_value) * 3600;
					break;
				case "daily":
					$scan_recurrence = intval($scan_frequency[0]->option_value) * 86400;
					break;
				case "weekly":
					$scan_recurrence = intval($scan_frequency[0]->option_value) * 604800;
					break;
				case "monthly":
					$scan_recurrence = intval($scan_frequency[0]->option_value) * 2592000;
					break;
				default:
					$scan_recurrence = 604800;
			}

			$schedules['wpsc'] = array(
				'interval' => $scan_recurrence,
				'display' => __( 'wpsc' )
			);
			return $schedules;
		}
	}
	add_filter( 'cron_schedules', 'cron_add_custom' );

	function show_upgrade_message()
	{ 
		$page = $_GET['page'];
		$loc = "http://www.wpspellcheck.com/api/upgrade-to-pro.php";
		$output = file_get_contents($loc);
		$output = preg_replace("/\?wpsc_ignore_notice=1&page=WPSC-PAGE-LINK/",esc_url( add_query_arg( array( 'wpsc_pro_ignore_notice' => '1' ) ) ),$output);
		echo $output;
	} 

	function check_upgrade_message() {
		global $current_user;
		global $pro_included;
		global $ent_included;
		$user_id = $current_user->ID;
		$notice_date = get_user_meta($user_id, 'wpsc_pro_notice_date', true);
		$times_dismissed = get_user_meta($user_id, 'wpsc_pro_dismissed', true);
		$show_notice = false;

		
		if ($notice_date == '') {
			$notice_date = time();
			add_user_meta($user_id, 'wpsc_pro_notice_date', $notice_date, true);
		}

		
		if ($times_dismissed == '') {
			add_user_meta($user_id, 'wpsc_pro_dismissed', '0', true);
		}
		
		$loc = "http://www.wpspellcheck.com/api/notice-timing.php";
		$input = file_get_contents($loc);
		
		
		$timing = explode(";",$input);
		$timing_numbers = str_replace("Upgrade: ","",$timing[1]);
		$timing_list = explode(",",$timing_numbers);
		
		$time = $notice_date;
		$first_notice = (time()-(60*60*24*intval($timing_list[0]))); 
		$second_notice = (time()-(60*60*24*intval($timing_list[1]))); 
		$third_notice = (time()-(60*60*24*intval($timing_list[2]))); 
		$last_notices = (time()-(60*60*24*intval($timing_list[3])));
		
		if ($times_dismissed == '0') {
			if ($first_notice > $time) {
				$show_notice = true;
			}
		} elseif ($times_dismissed == '1') {
			if ($second_notice > $time) {
				$show_notice = true;
			}
		} elseif ($times_dismissed == '2') {
			if ($third_notice > $time) {
				$show_notice = true;
			}
		} elseif ($last_notices > $time) {
			$show_notice = true;
		}

		if ((current_user_can('manage_options')) && !is_plugin_active('wp-spell-check-pro/wpspellcheckpro.php') && !is_plugin_active('wp-spell-check-enterprise/wpspellcheckenterprise.php') && $show_notice && !$pro_included && !$ent_included) {
			show_upgrade_message();
		}
	}

	add_action('admin_notices', 'check_upgrade_message');
	
	
	function show_inactive_notice() {
		
	}
	
	function check_inactive_notice() {
		global $current_user;
		$user_id = $current_user->ID;
		$show_notice = false;
		global $wpdb;
		$table_name = $wpdb->prefix . "spellcheck_options";
		
		$option = $wpdb->get_results("SELECT option_value FROM $table_name WHERE option_name = 'last_scan_date'");
		$time = $option[0]->option_value;
		$last_active = (time()+(60)); 
		
		
		$time = strtotime($notice_date);
		$first_notice = (time()+(60*60*24*5)); 
		$second_notice = (time()+(60*60*24*20)); 
		$third_notice = (time()+(60*60*24*30)); 
		$last_notices = (time() + (60*60*24*30)); 

		if ($times_dismissed == '0') {
			if ($first_notice > $time) {
				$show_notice = true;
			}
		} elseif ($times_dismissed == '1') {
			if ($second_notice > $time) {
				$show_notice = true;
			}
		} elseif ($times_dismissed == '2') {
			if ($third_notice > $time) {
				$show_notice = true;
			}
		} elseif ($last_notices > $time) {
			$show_notice = true;
		}
		
		if ((current_user_can('manage_options')) && $show_notice) {
			
		}
		
	}
	
	add_action('admin_notices', 'check_inactive_notice');
	
	
	function show_review_notice() {
		global $current_user;
		$user_id = $current_user->ID;
		$page = $_GET['page'];
		$plugins = $_GET['plugin_status'];
		if ($page != '') $page = '&page=' . $page;		
			$loc = "http://www.wpspellcheck.com/api/survey.php";
			$output = file_get_contents($loc);
			$output = preg_replace("/\?wpsc_ignore_review_notice=1&page=WPSC-PAGE-LINK/",html_entity_decode( esc_url( add_query_arg( array( 'wpsc_ignore_review_notice' => '1' ) ) ), ENT_QUOTES, 'utf-8'),$output);
			if (preg_match("/hide-message/m", $output)) { } else {
				echo $output;
			}
	}
	
	function ignore_review_notice() {
		global $current_user;
		$user_id = $current_user->ID;
		if ( isset($_GET['wpsc_ignore_review_notice']) && $_GET['wpsc_ignore_review_notice'] == '1') {
			add_user_meta($user_id, 'wpsc_ignore_review_notice', 'true', true);
			update_user_meta($user_id, 'wpsc_ignore_review_notice', 'true');

			
			$notice_date = time();
			add_user_meta($user_id, 'wpsc_review_date', $notice_date, true);
			update_user_meta($user_id, 'wpsc_review_date', $notice_date);
			
			
			$times_dismissed = get_user_meta($user_id, 'wpsc_times_dismissed_review', true);
			if ($times_dismissed == '0') $times_dismissed = '1';
			if ($times_dismissed == '1') $times_dismissed = '2';
			if ($times_dismissed == '2') $times_dismissed = '3';
			if ($times_dismissed == '3') $times_dismissed = '4';
			update_user_meta($user_id, 'wpsc_times_dismissed_review', $times_dismissed);
		} elseif ( isset($_GET['wpsc_ignore_review_notice']) && $_GET['wpsc_ignore_review_notice'] == '2') {
			add_user_meta($user_id, 'wpsc_ignore_review_notice', 'hide', true);
			update_user_meta($user_id, 'wpsc_ignore_review_notice', 'hide');

			
			$notice_date = time();
			add_user_meta($user_id, 'wpsc_review_date', $notice_date, true);
			update_user_meta($user_id, 'wpsc_review_date', $notice_date);
			
			
			$times_dismissed = get_user_meta($user_id, 'wpsc_times_dismissed_review', true);
			if ($times_dismissed == '0') $times_dismissed = '1';
			if ($times_dismissed == '1') $times_dismissed = '2';
			if ($times_dismissed == '2') $times_dismissed = '3';
			if ($times_dismissed == '3') $times_dismissed = '4';
			update_user_meta($user_id, 'wpsc_times_dismissed_review', $times_dismissed);
		}
	}
	
	add_action('admin_init', 'ignore_review_notice');
	
	function check_review_notice() {
		global $current_user;
		$user_id = $current_user->ID;
		
		
		$notice_date = get_user_meta($user_id, 'wpsc_review_date', true);
		$ignore_review = get_user_meta($user_id, 'wpsc_ignore_review_notice', true);
		$times_dismissed = get_user_meta($user_id, 'wpsc_times_dismissed_review', true);
		$show_notice = false;
		
		
		if ($notice_date == '') {
			$notice_date = time();
			add_user_meta($user_id, 'wpsc_review_date', $notice_date, true);
		}
		
				
		if ($times_dismissed == '') {
			add_user_meta($user_id, 'wpsc_times_dismissed_review', '0', true);
		}
		
		$loc = "http://www.wpspellcheck.com/api/notice-timing.php";
		$input = file_get_contents($loc);
		
		$timing = explode(";",$input);
		$timing_numbers = str_replace("Survey: ","",$timing[0]);
		$timing_list = explode(",",$timing_numbers);
		
		$time = $notice_date;
		$first_notice = (time()-(60*60*24*intval($timing_list[0]))); 
		$second_notice = (time()-(60*60*24*intval($timing_list[1]))); 
		$third_notice = (time()-(60*60*24*intval($timing_list[2]))); 
		$last_notices = (time()-(60*60*24*intval($timing_list[3])));
		
		if ($times_dismissed == '0') {
			if ($first_notice > $time) {
				$show_notice = true;
			}
		} elseif ($times_dismissed == '1') {
			if ($second_notice > $time) {
				$show_notice = true;
			}
		} elseif ($times_dismissed == '2') {
			if ($third_notice > $time) {
				$show_notice = true;
			}
		} elseif ($last_notices > $time) {
			$show_notice = true;
		}
		
		if ((current_user_can('manage_options')) && $show_notice && $ignore_review != 'hide') {
			show_review_notice();
		}
		
	}
	add_action('admin_notices', 'check_review_notice');
	
	function wpsc_ignore_notice() {
		global $current_user;
		$user_id = $current_user->ID;
		if ( isset($_GET['wpsc_pro_ignore_notice']) && $_GET['wpsc_pro_ignore_notice'] == '1') {
			add_user_meta($user_id, 'wpsc_pro_ignore_notice', 'true', true);
			update_user_meta($user_id, 'wpsc_pro_ignore_notice', 'true');

			
			$notice_date = time();
			update_user_meta($user_id, 'wpsc_pro_notice_date', $notice_date);

			
			$times_dismissed = get_user_meta($user_id, 'wpsc_pro_times_dismissed', true);
			if ($times_dismissed == '0') $times_dismissed = '1';
			if ($times_dismissed == '1') $times_dismissed = '2';
			if ($times_dismissed == '2') $times_dismissed = '3';
			if ($times_dismissed == '3') $times_dismissed = '4';
			update_user_meta($user_id, 'wpsc_pro_times_dismissed', $times_dismissed);
		}
	}

	add_action('admin_init', 'wpsc_ignore_notice');

	function show_install_notice() { 
		$page = $_GET['page'];
		$plugins = $_GET['plugin_status'];
		$loc = "http://www.wpspellcheck.com/api/install-popup.php";
		$output = file_get_contents($loc);
		$output = preg_replace("/\?wpsc_ignore_install_notice=1&page=WPSC-PAGE-LINK/",esc_url( add_query_arg( array( 'wpsc_ignore_install_notice' => '1' ) ) ),$output);
		echo $output;
	}
	
	function wpsc_ignore_install_notice() {
		global $current_user;
		$user_id = $current_user->ID;

		if ( isset($_GET['wpsc_ignore_install_notice']) && $_GET['wpsc_ignore_install_notice'] == '1') {
			$dismissed = get_user_meta($user_id, 'wpsc_ignore_install_notice', true);
			if ($dismissed == '') {
				add_user_meta($user_id, 'wpsc_ignore_install_notice', 'true', true);
			} else {
				update_user_meta($user_id, 'wpsc_ignore_install_notice', 'true');
			}
		}
	}
	add_action('admin_init', 'wpsc_ignore_install_notice');

	function check_install_notice() {
		global $current_user;
		$user_id = $current_user->ID;
		$dismissed = get_user_meta($user_id, 'wpsc_ignore_install_notice', true);
		
		if ((current_user_can('manage_options')) && !is_plugin_active('wp-spell-check-pro/wpspellcheckpro.php') && $dismissed != 'true') {
				show_install_notice();
		}
	}
	add_action('admin_init', 'wpsc_ignore_install_notice');

	function wpsc_check_version() {
		$plugin_data = get_plugin_data( __FILE__, false);
		$current_version = $plugin_data['Version'];
		global $current_user;
		$user_id = $current_user->ID;
		
		$last_check = get_user_meta($user_id, 'wpsc_last_check', true);
		$check_version = get_user_meta($user_id, 'wpsc_version', true);
		$is_outdated = get_user_meta($user_id, 'wpsc_outdated', true);
		
		if ($last_check == '') {
			$last_check = time();
			add_user_meta($user_id, 'wpsc_last_check', $last_check, true);
			add_user_meta($user_id, 'wpsc_version', $current_version, true);
			add_user_meta($user_id, 'wpsc_outdated', 'false', true);
			$check_version = $current_version;
			$is_outdated = 'false';
		}
		
		$recheck_time = (time()-(60*60*24*2)); 
		if ($recheck_time > $last_check) {
			$url = 'https://www.wpspellcheck.com/api/check-version.php';
			
			
			$params = array('current_version' => $current_version);
			
			$args = array(
				'body' => $params,
				'timeout' => '5',
				'redirection' => '5',
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'cookies' => array()
			);
			
			$response = wp_remote_post($url, $args);
			
			global $current_user;
			$user_id = $current_user->ID;
			$notice_date = get_user_meta($user_id, 'wpsc_update_notice_date', true);
			
			
			$time = intval($notice_date) + (60);
			$reshow_notice = time();
			
			update_user_meta($user_id, 'wpsc_last_check', time());
			
			if ( !is_wp_error( $response ) ) {
				if ($response['response']['code'] == 403) {
					update_user_meta($user_id, 'wpsc_outdated','true');
					update_user_meta($user_id, 'wpsc_version', $current_version);
					if (($time <= $reshow_notice) || $time == '')
						show_upgrade_notice();
				} else {
					
				}
			}
		} else {
			if ((($time <= $reshow_notice) || $time == '') && $current_version == $check_version && $is_outdated == 'true')
				show_upgrade_notice();
		}
	}
	add_action('admin_notices', 'wpsc_check_version');

	function show_upgrade_notice() {
		global $current_user;
		$user_id = $current_user->ID;
		$page = $_GET['page'];
		if ($page != '') $page = '&page=' . $page;
		$upgrade_url = '/wp-admin/update-core.php';
		echo '<div class="update-nag" style="display: block;">There is an update available for <span style="font-weight: bold">WP Spell Check</span>. <a href="' . $upgrade_url . '" style="font-weight: bold;">Click here</a> to update to the latest version.</div>';
	}
	
	function wpsc_check_version_pro() {
		global $pro_included;
		global $ent_included;
		global $pro_loc;
		global $ent_loc;
		
		if ($pro_included) {
			$plugin_data = get_plugin_data( $pro_loc, false);
			$current_version = $plugin_data['Version'];
			global $current_user;
			$user_id = $current_user->ID;
			
			$last_check = get_user_meta($user_id, 'wpsc_pro_last_check', true);
			$check_version = get_user_meta($user_id, 'wpsc_pro_version', true);
			$is_outdated = get_user_meta($user_id, 'wpsc_pro_outdated', true);
			
			if ($last_check == '') {
				$last_check = time();
				add_user_meta($user_id, 'wpsc_pro_last_check', $last_check, true);
				add_user_meta($user_id, 'wpsc_pro_version', $current_version, true);
				add_user_meta($user_id, 'wpsc_pro_outdated', 'false', true);
				$check_version = $current_version;
				$is_outdated = 'false';
			}
			
			$recheck_time = (time()-(60*60*24*2)); 
			//if ($recheck_time > $last_check) {
			if (true) {
				$url = 'https://www.wpspellcheck.com/api/check-pro-version.php';
				
				
				$params = array('current_version' => $current_version);
				
				$args = array(
					'body' => $params,
					'timeout' => '5',
					'redirection' => '5',
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'cookies' => array()
				);
				
				$response = wp_remote_post($url, $args);
				
				global $current_user;
				$user_id = $current_user->ID;
				$notice_date = get_user_meta($user_id, 'wpsc_pro_update_notice_date', true);
				
				
				$time = intval($notice_date) + (60);
				$reshow_notice = time();
				
				update_user_meta($user_id, 'wpsc_pro_last_check', time());
				
				if ( !is_wp_error( $response ) ) {
					if ($response['response']['code'] == 403) {
						update_user_meta($user_id, 'wpsc_pro_outdated','true');
						update_user_meta($user_id, 'wpsc_pro_version', $current_version);
						if (($time <= $reshow_notice) || $time == '')
							show_upgrade_notice_pro("Pro");
					} else {
						
					}
				}
			} else {
				if ((($time <= $reshow_notice) || $time == '') && $current_version == $check_version && $is_outdated == 'true')
					show_upgrade_notice_pro("Pro");
			}
		} elseif ($ent_included) {
			$plugin_data = get_plugin_data( $ent_loc, false);
			$current_version = $plugin_data['Version'];
			global $current_user;
			$user_id = $current_user->ID;
			
			$last_check = get_user_meta($user_id, 'wpsc_ent_last_check', true);
			$check_version = get_user_meta($user_id, 'wpsc_ent_version', true);
			$is_outdated = get_user_meta($user_id, 'wpsc_ent_outdated', true);
			
			if ($last_check == '') {
				$last_check = time();
				add_user_meta($user_id, 'wpsc_ent_last_check', $last_check, true);
				add_user_meta($user_id, 'wpsc_ent_version', $current_version, true);
				add_user_meta($user_id, 'wpsc_ent_outdated', 'false', true);
				$check_version = $current_version;
				$is_outdated = 'false';
			}
			
			$recheck_time = (time()-(60*60*24*2)); 
			if ($recheck_time > $last_check) {
				$url = 'https://www.wpspellcheck.com/api/check-pro-version.php';
				
				
				$params = array('current_version' => $current_version);
				
				$args = array(
					'body' => $params,
					'timeout' => '5',
					'redirection' => '5',
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'cookies' => array()
				);
				
				$response = wp_remote_post($url, $args);
				
				global $current_user;
				$user_id = $current_user->ID;
				$notice_date = get_user_meta($user_id, 'wpsc_ent_update_notice_date', true);
				
				
				$time = intval($notice_date) + (60);
				$reshow_notice = time();
				
				update_user_meta($user_id, 'wpsc_ent_last_check', time());
				
				if ( !is_wp_error( $response ) ) {
					if ($response['response']['code'] == 403) {
						update_user_meta($user_id, 'wpsc_ent_outdated','true');
						update_user_meta($user_id, 'wpsc_ent_version', $current_version);
						if (($time <= $reshow_notice) || $time == '')
							show_upgrade_notice_pro("Enterprise");
					} else {
						
					}
				}
			} else {
				if ((($time <= $reshow_notice) || $time == '') && $current_version == $check_version && $is_outdated == 'true')
					show_upgrade_notice_pro("Enterprise");
			}
		}
	}
	add_action('admin_notices', 'wpsc_check_version_pro');

	function show_upgrade_notice_pro($plugin_string) {
		global $current_user;
		$user_id = $current_user->ID;
		$page = $_GET['page'];
		if ($page != '') $page = '&page=' . $page;
		$upgrade_url = '/wp-admin/update-core.php';
		echo '<div class="update-nag" style="display: block;"><img src="/wp-content/plugins/wp-spell-check/images/logo-square.png" style="margin: 0px 10px 0px 0;display: inline-block;width: 40px;vertical-align: middle;"><div style="display: inline-block; vertical-align: middle; width: 90%;">There is an update available for <span style="font-weight: bold">WP Spell Check ' . $plugin_string . '</span>. <a href="https://www.wpspellcheck.com/wp-login.php" target="_blank" style="font-weight: bold;" >Click here</a> to log into your WP Spell Check account To download the latest version. It only takes a minute.</div></div>';
	}
	
	function wpsc_ignore_upgrade_notice() {
		global $current_user;
		$user_id = $current_user->ID;
		if ( isset($_GET['wpsc_ignore_upgrade_notice']) && $_GET['wpsc_ignore_upgrade_notice'] == '1') {
			delete_user_meta($user_id, 'wpsc_update_notice_date');
			add_user_meta($user_id, 'wpsc_update_notice_date', time(), true);
		}
	}
	add_action('admin_init', 'wpsc_ignore_upgrade_notice');

	add_action( 'wp_ajax_results_sc', 'wpsc_scan_function');
	add_action( 'wp_ajax_nopriv_results_sc', 'wpsc_scan_function');
	add_action( 'wp_ajax_emptyresults_sc', 'wpsc_empty_scan_function');
	add_action( 'wp_ajax_nopriv_emptyresults_sc', 'wpsc_empty_scan_function');
	add_action( 'wp_ajax_finish_scan', 'wpsc_finish_scan');
	add_action( 'wp_ajax_nopriv_finish_scan', 'wpsc_finish_scan');
	add_action( 'wp_ajax_finish_empty_scan', 'wpsc_finish_empty_scan');
	add_action( 'wp_ajax_nopriv_finish_empty_scan', 'wpsc_finish_empty_scan');
	
	function wpsc_finish_scan() {
		sleep(1);
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_words';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		
		$settings = $wpdb->get_results('SELECT option_value FROM ' . $options_table);
			
		if($settings[99]->option_value == 'true') {
			if ($settings[0]->option_value == 'true')
			email_admin();
		
			$total_word = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='total_word_count'");
			$total_words = $total_word[0]->option_value;
		
			$word_count = $wpdb->get_var ( "SELECT COUNT(*) FROM $table_name WHERE ignore_word='false'" );
		
			$literacy_factor = 0;
			if ($total_words > 0) { $literacy_factor = (($total_words - $word_count) / $total_words) * 100;
			} else { $literacy_factor = 100; }
			$literacy_factor = number_format((float)$literacy_factor, 2, '.', '');
			
			$time = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='scan_start_time'");
			$time = $time[0]->option_value;
			$end_time = time();
			$total_time = time_elapsed($end_time - $time);
			
		
			$wpdb->update($options_table, array('option_value' => $literacy_factor), array('option_name' => 'literary_factor')); 
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'entire_scan'));
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'last_scan_finished'));
		}
	}
	
	function wpsc_finish_empty_scan() {
		sleep(1);
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		
		$settings = $wpdb->get_results('SELECT option_value FROM ' . $options_table);
		
		if ($settings[100]->option_value == 'true') {
			if ($settings[0]->option_value == 'true')
			email_admin();
			
			$total_fields =  $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked'");
			$total_fields = $total_fields[0]->option_value;
			$empty_count = $wpdb->get_var ( "SELECT COUNT(*) FROM $table_name WHERE ignore_word='false'" );
		
			$empty_factor = 0;
			if ($total_fields > 0) { $empty_factor = (($total_fields - $empty_count) / $total_fields) * 100;
			} else { $empty_factor = 100; }
			$empty_factor = number_format((float)$empty_factor, 2, '.', '');
		
			$wpdb->update($options_table, array('option_value' => $empty_factor), array('option_name' => 'empty_factor')); 
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'entire_empty_scan'));
			
			$time = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='scan_start_time'");
			$time = $time[0]->option_value;
			
			$end_time = time();
			$total_time = time_elapsed($end_time - $time);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
	}

	function wpsc_scan_function() {
		require_once( 'admin/wpsc-framework.php' );
		
		global $wpdb;
		global $wpsc_settings;
		
		$scan_in_progress = false;
		
		if ($wpsc_settings[66]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[67]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[68]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[69]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[70]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[71]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[72]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[73]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[74]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[75]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[76]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[77]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[78]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[79]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[80]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[81]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[82]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[83]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[84]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[85]->option_value == 'true') $scan_in_progress = true;
		
		if (!$scan_in_progress) {
			echo "false";
		} else {
			echo "true";
		}
		die();
	}
	
	function wpsc_empty_scan_function() {
		require_once( 'admin/wpsc-framework.php' );
	
		global $wpdb;
		global $wpsc_settings;
		
		$scan_in_progress = false;
		
		if ($wpsc_settings[87]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[88]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[89]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[90]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[91]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[92]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[93]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[94]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[95]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[96]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[97]->option_value == 'true') $scan_in_progress = true;
		if ($wpsc_settings[98]->option_value == 'true') $scan_in_progress = true;
		
		
		if (!$scan_in_progress) {
			echo "false";
		} else {
			echo "true";
		}
		die();
	}

?>