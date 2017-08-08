<?php
/*
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

	function check_page_title_empty($rng_seed, $is_running = false) {
		//$loc = dirname(__FILE__) . "/events-debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Page Titles Started. Time: " . time() . "\r\n" );
		//$fclose($debug_file);
	
		if (!$is_running) sleep(1);
		global $wpdb;
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		set_time_limit(6000); 
		$error_count = 0;
		$total_count = 0;
		
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");

		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}
		$page_ids = get_all_page_ids();
		
		if ($options_list[136]->option_value == 'true') { $post_status = true; }
		else { $post_status = false; }
		
		$ignore_pages = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');
		
		$max_pages = 500;
		if (sizeof($page_ids) < 500) $max_pages = sizeof($page_ids);

		for ($x=0; $x<$max_pages; $x++) {
			$ignore_flag = 'false';
			foreach($ignore_pages as $ignore_check) {
				if (strtoupper(trim($page->post_title)) == strtoupper(trim($ignore_check->keyword))) {
					$ignore_flag = 'true';
				}
			}
			if ($ignore_flag == 'true') { continue; }
			$total_count++;
			$page = get_post( $page_ids[$x] );
			if ($page->post_status == 'draft' && !$post_status) continue;
			$word_list = html_entity_decode(strip_tags($page->post_title), ENT_QUOTES, 'utf-8');
			if ($word_list == '') {
				$error_count++;
				$wpdb->insert($table_name, array('word' => "Empty Field", 'page_name' => $page->post_title, 'page_type' => 'Page Title', 'page_id' => $page->ID));
			}
		}
		$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_page_count')); 
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
		
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_page_title_sip'));
	}
	add_action('admincheckpagetitlesemptybase', 'check_page_title_empty');
	
	function check_post_title_empty($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		set_time_limit(6000);
		$error_count = 0;
		$total_count = 0;


		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");

		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$ignore_pages = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');

		$post_types = get_post_types();
		$post_type_list = array();
		foreach ($post_types as $type) {
			if ($type != 'revision' && $type != 'page' && $type != 'nav_menu_item' && $type != 'optionsframework' && $type != 'slider' && $type != 'attachment')
				array_push($post_type_list, $type);
		}
		
		if ($options_list[137]->option_value == 'true') { $post_status = array('publish', 'draft'); }
		else { $post_status = array('publish'); }

		$posts_list = get_posts(array('posts_per_page' => 500, 'post_type' => $post_type_list, 'post_status' => $post_status));

		foreach ($posts_list as $post) {
			$ignore_flag = 'false';
			foreach($ignore_pages as $ignore_check) {
				if (strtoupper(trim($page->post_title)) == strtoupper(trim($ignore_check->keyword))) {
					$ignore_flag = 'true';
				}
			}
			if ($ignore_flag == 'true') { continue; }
			$total_count++;
			$word_list = html_entity_decode(strip_tags($post->post_title), ENT_QUOTES, 'utf-8');
			if ($word_list == '') {
				$error_count++;
				$wpdb->insert($table_name, array('word' => "Empty Field", 'page_name' => $post->post_title, 'page_type' => 'Post Title', 'page_id' => $post->ID));
			}
		}
		$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_post_count')); 
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_post_title_sip'));
	}
	add_action('admincheckposttitlesemptybase', 'check_post_title_empty');

	function check_author_firstname_empty($rng_seed, $is_running = false) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$post_table = $wpdb->prefix . 'posts';
		$user_table = $wpdb->prefix . 'usermeta';
		$username_table = $wpdb->prefix . 'users';
		set_time_limit(600);
		$error_count = 0;
		$total_count = 0;

		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$ignore_posts = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");

		$posts_list = $wpdb->get_results("SELECT * FROM $post_table GROUP By post_author");

		foreach ($posts_list as $post) {
			$total_count++;
			$author = $wpdb->get_results("SELECT * FROM $user_table WHERE meta_key='first_name' AND user_id='$post->post_author'");
			$author_name = $wpdb->get_results("SELECT * FROM $username_table WHERE id='$post->post_author'");

			$words_list = $author[0]->meta_value;
			if ($words_list == '') {
				$error_count++;
				$wpdb->insert($table_name, array('word' => "Empty Field", 'page_name' => $author_name[0]->user_login, 'page_type' => 'Author First Name', 'page_id' => $post->post_author));	
			}
		}
		if ($post_count > $total_posts) $post_count = $total_posts;
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Author First Name Total Words Checked: " . $total_count . ". Empty fields found: " . $error_count . "\r\n" );
		//$fclose($debug_file);
		$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		$total_count = $total_count + intval($counter[0]->option_value);
		$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_checked'));
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); //$ Flag that a scan is in progress
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); //$ Update the total time of the scan
		}
	}
	
	function check_author_lastname_empty($rng_seed, $is_running = false) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$post_table = $wpdb->prefix . 'posts';
		$user_table = $wpdb->prefix . 'usermeta';
		$username_table = $wpdb->prefix . 'users';
		set_time_limit(600);
		$error_count = 0;
		$total_count = 0;

		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$ignore_posts = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");

		$posts_list = $wpdb->get_results("SELECT * FROM $post_table GROUP By post_author");

		foreach ($posts_list as $post) {
			$total_count++;
			$author = $wpdb->get_results("SELECT * FROM $user_table WHERE meta_key='last_name' AND user_id='$post->post_author'");
			$author_name = $wpdb->get_results("SELECT * FROM $username_table WHERE id='$post->post_author'");

			$words_list = $author[0]->meta_value;
			if ($words_list == '') {
				$error_count++;
				$wpdb->insert($table_name, array('word' => "Empty Field", 'page_name' => $author_name[0]->user_login, 'page_type' => 'Author Last Name', 'page_id' => $post->post_author));	
			}
		}
		if ($post_count > $total_posts) $post_count = $total_posts;
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Author Last Name Total Words Checked: " . $total_count . ". Empty fields found: " . $error_count . "\r\n" );
		//$fclose($debug_file);
		$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		$total_count = $total_count + intval($counter[0]->option_value);
		$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_checked'));
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); //$ Flag that a scan is in progress
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); //$ Update the total time of the scan
		}
	}
	
	function check_author_bio_empty($rng_seed, $is_running = false) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$post_table = $wpdb->prefix . 'posts';
		$user_table = $wpdb->prefix . 'usermeta';
		$username_table = $wpdb->prefix . 'users';
		set_time_limit(600);
		$error_count = 0;
		$total_count = 0;

		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$ignore_posts = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");

		$posts_list = $wpdb->get_results("SELECT * FROM $post_table GROUP By post_author");

		foreach ($posts_list as $post) {
			$total_count++;
			$author = $wpdb->get_results("SELECT * FROM $user_table WHERE meta_key='description' AND user_id='$post->post_author'");
			$author_name = $wpdb->get_results("SELECT * FROM $username_table WHERE id='$post->post_author'");

			$words_list = $author[0]->meta_value;
			if ($words_list == '') {
				$error_count++;
				$wpdb->insert($table_name, array('word' => "Empty Field", 'page_name' => $author_name[0]->user_login, 'page_type' => 'Author Biographical Information', 'page_id' => $post->post_author));	
			}
		}
		if ($post_count > $total_posts) $post_count = $total_posts;
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Author Biographical Info Total Words Checked: " . $total_count . ". Empty fields found: " . $error_count . "\r\n" );
		$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		$total_count = $total_count + intval($counter[0]->option_value);
		$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_checked'));
		//$fclose($debug_file);
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); //$ Flag that a scan is in progress
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); //$ Update the total time of the scan
		}
	}
	
	function check_author_empty($rng_seed) {
		if (!$is_running) sleep(1);
		global $wpdb;
		global $ent_included;
		global $pro_included;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$start_time = time(); 

		check_author_firstname_empty(true);
		check_author_lastname_empty(true);
		check_author_bio_empty(true);
		if ($pro_included) {
			check_author_seotitle_empty_pro(true);
			check_author_seodesc_empty_pro(true);
		} elseif ($ent_included) {
			check_author_seotitle_empty_ent(true);
			check_author_seodesc_empty_ent(true);
		}
		
		if (!$is_running) sleep(1);
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_author_sip'));
		$end_time = time();
		$total_time = time_elapsed($end_time - $start_time + 6);
		$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Author Empty Finished" . "\r\n" );
		//$fclose($debug_file);
	}
	add_action ('admincheckauthorsempty', 'check_author_empty');
	
	function clear_results_empty() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$wpdb->update($options_table, array('option_value' => '0'), array('option_name' => 'pro_word_count')); //$ Clear out the pro errors count
		$wpdb->update($options_table, array('option_value' => '0'), array('option_name' => 'empty_checked')); //$ Clear out the total empty field count
		$wpdb->update($options_table, array('option_value' => '0'), array('option_name' => 'page_count')); //$ Clear out the page count
		$wpdb->update($options_table, array('option_value' => '0'), array('option_name' => 'post_count')); //$ Clear out the post count
		$wpdb->update($options_table, array('option_value' => '0'), array('option_name' => 'media_count')); //$Clear out the media count

		$wpdb->delete($table_name, array('ignore_word' => false));
	}
	
	function wpsc_clear_empty_scan() {
		global $wpdb;
		global $wpsc_settings;
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'entire_empty_scan'));
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_author_sip'));
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_page_title_sip'));
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_post_title_sip'));
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_menu_sip'));
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_page_seo_sip'));
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_post_seo_sip'));
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_media_seo_sip'));
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_media_sip'));
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_ecommerce_sip'));
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_tag_desc_sip'));
		$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_cat_desc_sip'));
	}
	
	function set_empty_scan_in_progress($rng_seed = 0) {
		global $wpdb;
		global $pro_included;
		global $ent_included;
		global $wpsc_settings;
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'entire_empty_scan'));
		
		$settings = $wpsc_settings;

		
		if ($settings[47]->option_value =='true') {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_author_sip'));
		if ($settings[49]->option_value =='true')
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_page_title_sip'));
		if ($settings[50]->option_value =='true')
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_post_title_sip'));
			
		if ($ent_included || $pro_included) {
		if ($settings[48]->option_value =='true')
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_menu_sip'));
		if ($settings[53]->option_value =='true') {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_page_seo_sip'));
		}
		if ($settings[54]->option_value =='true') {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_post_seo_sip'));
		}
		if ($settings[55]->option_value =='true') {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_media_seo_sip'));
		}
		if ($settings[56]->option_value =='true') {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_media_sip'));
		}
		if ($settings[57]->option_value =='true') {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_ecommerce_sip'));
		}
		if ($settings[51]->option_value =='true')
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_tag_desc_sip'));
		if ($settings[52]->option_value =='true')
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_cat_desc_sip'));
		}
		}
	}
	
	
	function scan_site_empty($rng_seed = 0) {
		global $wpdb;
		global $pro_included;
		global $ent_included;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		set_time_limit(600); //$ Set PHP timeout limit
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
		$start_time = time(); 

		$settings = $wpdb->get_results('SELECT option_value FROM ' . $options_table); //4 = Pages, 5 = Posts, 6 = Theme, 7 = Menus
		
		if (!$ent_included && !$pro_included) {
			wp_schedule_single_event(time(), 'admincheckemptywpsc', array (rand(0,999999999) ));
		}
		
		if ($ent_included) {
		if ($settings[48]->option_value =='true')
			wp_schedule_single_event(time(), 'admincheckmenusempty_ent', array ($rng_seed ));
		if ($settings[49]->option_value =='true')
			wp_schedule_single_event(time(), 'admincheckpagetitlesempty_ent', array ($rng_seed ));
		if ($settings[50]->option_value =='true')
			wp_schedule_single_event(time(), 'admincheckposttitlesempty_ent', array ($rng_seed ));
		if ($settings[53]->option_value =='true') {
			wp_schedule_single_event(time(), 'admincheckpageseoempty_ent', array ($rng_seed ));
		}
		if ($settings[54]->option_value =='true') {
			wp_schedule_single_event(time(), 'admincheckpostseoempty_ent', array ($rng_seed ));
		}
		if ($settings[55]->option_value =='true') {
			wp_schedule_single_event(time(), 'admincheckmediaseoempty_ent', array ($rng_seed ));
		}
		if ($settings[56]->option_value =='true') {
			wp_schedule_single_event(time(), 'admincheckmediaempty_ent', array ($rng_seed ));
		}
		if ($settings[57]->option_value =='true') {
			wp_schedule_single_event(time(), 'admincheckecommerceempty_ent', array ($rng_seed ));
		}
		if ($settings[51]->option_value =='true')
			wp_schedule_single_event(time(), 'admincheckposttagsdescempty_ent', array ($rng_seed ));
		if ($settings[52]->option_value =='true')
			wp_schedule_single_event(time(), 'admincheckcategoriesdescempty_ent', array ($rng_seed ));
		if ($settings[47]->option_value =='true') {
			wp_schedule_single_event(time(), 'admincheckauthorsempty', array ($rng_seed ));
		}
		} else {
		if ($pro_included) {
		if ($settings[48]->option_value =='true')
			wp_schedule_single_event(time(), 'admincheckmenusempty', array ($rng_seed ));
		if ($settings[49]->option_value =='true')
			wp_schedule_single_event(time(), 'admincheckpagetitlesempty', array ($rng_seed ));
		if ($settings[50]->option_value =='true')
			wp_schedule_single_event(time(), 'admincheckposttitlesempty', array ($rng_seed ));
		if ($settings[53]->option_value =='true') {
			wp_schedule_single_event(time(), 'admincheckpageseoempty', array ($rng_seed ));
		}
		if ($settings[54]->option_value =='true') {
			wp_schedule_single_event(time(), 'admincheckpostseoempty', array ($rng_seed ));
		}
		if ($settings[55]->option_value =='true') {
			wp_schedule_single_event(time(), 'admincheckmediaseoempty', array ($rng_seed ));
		}
		if ($settings[56]->option_value =='true') {
			wp_schedule_single_event(time(), 'admincheckmediaempty_pro', array ($rng_seed ));
		}
		if ($settings[57]->option_value =='true') {
			wp_schedule_single_event(time(), 'admincheckecommerceempty', array ($rng_seed ));
		}
		if ($settings[51]->option_value =='true')
			wp_schedule_single_event(time(), 'admincheckposttagsdescempty', array ($rng_seed ));
		if ($settings[52]->option_value =='true')
			wp_schedule_single_event(time(), 'admincheckcategoriesdescempty', array ($rng_seed ));
		if ($settings[47]->option_value =='true') {
			wp_schedule_single_event(time(), 'admincheckauthorsempty', array ($rng_seed ));
		}
		} else {
			if ($settings[47]->option_value =='true') {
				wp_schedule_single_event(time(), 'admincheckauthorsempty', array ($rng_seed ));
			}
			if ($settings[49]->option_value =='true')
				wp_schedule_single_event(time(), 'admincheckpagetitlesemptybase', array ($rng_seed ));
			if ($settings[50]->option_value =='true')
				wp_schedule_single_event(time(), 'admincheckposttitlesemptybase', array ($rng_seed ));
		}
		}
	}
	add_action ('adminscansiteempty', 'scan_site_empty');
	
	function check_empty_wpsc() {
		global $wpdb;
		global $pro_included;
		global $ent_included;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		set_time_limit(600); //$ Set PHP timeout limit
		$pro_errors = 0;
		
		$pro_errors += check_menus_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Menu Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_yoast_page_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Page SEO Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_seo_titles_page_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Page SEO Titles Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_yoast_post_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Post SEO Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_seo_titles_post_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Post SEO Titles Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_yoast_media_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Media SEO Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_seo_titles_media_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Media SEO Title Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_media_descriptions_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Media Desc Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_media_captions_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Media Caption Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_media_alt_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Media Alt Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_woocommerce_name_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "WC Name Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_woocommerce_excerpt_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "WC Exerpt Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_wpecommerce_name_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "WPEC Name Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_wpecommerce_excerpt_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "WPEC ExcerptChecked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_post_tag_descriptions_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Tag Desc Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_post_categories_description_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Cat Desc Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_author_seotitle_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Author SEO Title Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		$pro_errors += check_author_seodesc_empty_free(true);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		//$debug_var = fwrite( $debug_file, "Author SEO Desc Checked: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Errors on other parts of site checked. Error Count: " . $pro_errors . "\r\n" );
		//$fclose($debug_file);
		
		$wpdb->update($options_table, array('option_value' => $pro_errors), array('option_name' => 'pro_empty_count'));
	}
	add_action ('admincheckemptywpsc', 'check_empty_wpsc');
	
	//$Functions to check for errors found on other parts of your site
	
	function check_menus_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$table_name = $wpdb->prefix . 'posts';
		$empty_table = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$sql_count = 1;;
		set_time_limit(6000); 
		$error_count = 0;
		$total_count = 0;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		

		$menus = $wpdb->get_results('SELECT ID, post_title FROM ' . $table_name . ' WHERE post_type ="nav_menu_item" LIMIT 500000;');
		
		foreach($menus as $menu) {
			$total_count++;
			$word_list = html_entity_decode(strip_tags($menu->post_title), ENT_QUOTES, 'utf-8');
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $empty_table . ' WHERE page_type="Menu Item" AND page_id="' . $desc->ID . '" AND ignore_word = true');
			if ($word_list == '' && sizeof($ignore_word) < 1) {
				
				$sql_count++;
				$error_count++;
			}
		}
		if (!$is_running) sleep(1);
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Menu Items Checked: " . $total_count . ". Empty fields found: " . $error_count . "\r\n" );
		//$fclose($debug_file);
		$loc = dirname(__FILE__) . "/DB-Queries.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Menu: " . $sql_count . "\r\n" );
		//$fclose($debug_file);
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
		return $error_count;
	}
	
	function check_post_tag_descriptions_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$sql_count = 1;;
		set_time_limit(6000); 
		$error_count = 0;
		$total_count = 0;
		
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$tags_list = get_tags();
		
		foreach ($tags_list as $tag) {	
			$total_count++;
			$word = strip_tags(html_entity_decode($tag->description));
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE page_type="Tag Description" AND page_name="' . $tag->name . '" AND ignore_word = true');
			if ($word == '' && $total_count < 500 && sizeof($ignore_word) < 1) {
				
				$sql_count++;
				$error_count++;
			}
		}
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Tag Descriptions Checked: " . $total_count . ". Empty fields found: " . $error_count . "\r\n" );
		//$fclose($debug_file);
		$loc = dirname(__FILE__) . "/DB-Queries.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Tag Desc: " . $sql_count . "\r\n" );
		//$fclose($debug_file);
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
	}
	
	function check_post_categories_description_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$sql_count = 1;;
		set_time_limit(6000); 
		$error_count = 0;
		$total_count = 0;
		
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		
		$cats_list = get_categories();

		foreach ($cats_list as $cat) {
			$total_count++;
			$words = strip_tags(html_entity_decode($cat->description));
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE page_type="Category Description" AND page_name="' . $cat->name . '" AND ignore_word = true');
			if ($words == '' && $total_count < 500 && sizeof($ignore_word) < 1) {
				
				$sql_count++;
				$error_count++;
			}
		}
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Category Descriptions Checked: " . $total_count . ". Empty fields found: " . $error_count . "\r\n" );
		//$fclose($debug_file);
		$loc = dirname(__FILE__) . "/DB-Queries.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Cat Desc: " . $sql_count . "\r\n" );
		//$fclose($debug_file);
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
	}
	
	function check_media_descriptions_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$sql_count = 1;;
		set_time_limit(6000); 
		$error_count = 0;
		$total_count = 0;

		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");

		$posts_list = get_posts(array('posts_per_page' => 500000, 'post_type' => 'attachment'));

		foreach ($posts_list as $post) {
			$total_count++;
			$words_list = $post->post_content;
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE page_type="Media Description" AND page_name="' . $post->post_title . '" AND ignore_word = true');
			if ($words_list == '' && sizeof($ignore_word) < 1) {
				
				$sql_count++;
				$error_count++;
			}
		}
		//$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_media_count')); 
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}	
	}

	function check_media_captions_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$sql_count = 1;;
		set_time_limit(6000); 
		$error_count = 0;
		$total_count = 0;

		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");

		$posts_list = get_posts(array('posts_per_page' => 500000, 'post_type' => 'attachment'));

		foreach ($posts_list as $post) {
			$total_count++;
			$words_list = $post->post_excerpt;
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE page_type="Media Caption" AND page_name="' . $post->post_title . '" AND ignore_word = true');
			if ($words_list == '' && sizeof($ignore_word) < 1) {
				
				$sql_count++;
				$error_count++;
			}
		}
		//$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_media_count')); 
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}	
	}

	function check_media_alt_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$sql_count = 1;;
		set_time_limit(6000); 
		$error_count = 0;
		$total_count = 0;

		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$posts_list = get_posts(array('posts_per_page' => 500000, 'post_type' => 'attachment'));

		foreach ($posts_list as $post) {
			$total_count++;
			$word_list = get_post_meta ($post->ID, '_wp_attachment_image_alt', true );
			$word_list = html_entity_decode(strip_tags($word_list), ENT_QUOTES, 'utf-8');
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE page_type="Media Alternate Text" AND page_name="' . $post->post_title . '" AND ignore_word = true');
			if ($word_list == '' && sizeof($ignore_word) < 1) {
				
				$sql_count++;
				$error_count++;
			}
		}
		//$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_media_count')); 
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}	
	}

	function check_woocommerce_name_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$empty_table = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$sql_count = 1;;
		set_time_limit(6000); 
		$error_count = 0;
		$total_count = 0;

		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$ignore_posts = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");

		$posts_list = get_posts(array('posts_per_page' => 500000, 'post_type' => 'product', 'post_status' => array('publish', 'draft')));

		foreach ($posts_list as $post) {
			$total_count++;
			$ignore_flag = 'false';
			foreach($ignore_posts as $ignore_check) {
				if (strtoupper(trim($post->post_title)) == strtoupper(trim($ignore_check->keyword))) {
					$ignore_flag = 'true';
				}
			}
			if ($ignore_flag == 'true') { continue; }
			$words_list = $post->post_title;
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $empty_table . ' WHERE page_type="WooCommerce Product Name" AND page_name="' . $post->post_title . '" AND ignore_word = true');
			if ($words_list == '' && sizeof($ignore_word) < 1) {
				
				$sql_count++;
				$error_count++;
			}
		}
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "WooCommerce Name Checked: " . $total_count . ". Empty fields found: " . $error_count . "\r\n" );
		//$fclose($debug_file);
		$loc = dirname(__FILE__) . "/DB-Queries.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "WC Name: " . $sql_count . "\r\n" );
		//$fclose($debug_file);
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
	}
	
	function check_woocommerce_excerpt_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$empty_table = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$sql_count = 1;;
		set_time_limit(6000); 
		$error_count = 0;
		$total_count = 0;
		
		$total_posts = 500;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$ignore_posts = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");

		$posts_list = get_posts(array('posts_per_page' => 500000, 'post_type' => 'product', 'post_status' => array('publish', 'draft')));

		foreach ($posts_list as $post) {
			$total_count++;
			$ignore_flag = 'false';
			foreach($ignore_posts as $ignore_check) {
				if (strtoupper(trim($post->post_title)) == strtoupper(trim($ignore_check->keyword))) {
					$ignore_flag = 'true';
				}
			}
			if ($ignore_flag == 'true') { continue; }
			$words_list = $post->post_excerpt;
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $empty_table . ' WHERE page_type="WooCommerce Product Excerpt" AND page_name="' . $post->post_title . '" AND ignore_word = true');
			if ($words_list == '' && sizeof($ignore_word) < 1) {
				
				$sql_count++;
				$error_count++;
			}
		}
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "WooCommerce Excerpt Checked: " . $total_count . ". Empty fields found: " . $error_count . "\r\n" );
		//$fclose($debug_file);
		$loc = dirname(__FILE__) . "/DB-Queries.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "WC Excerpt: " . $sql_count . "\r\n" );
		//$fclose($debug_file);
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
	}
	
	function check_wpecommerce_name_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$empty_table = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$sql_count = 1;;
		set_time_limit(6000); 
		$error_count = 0;
		$total_count = 0;
		
		$total_posts = 500;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$ignore_posts = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");

		$posts_list = get_posts(array('posts_per_page' => 500000, 'post_type' => 'wpsc-product', 'post_status' => array('publish', 'draft')));

		foreach ($posts_list as $post) {
			$total_count++;
			$ignore_flag = 'false';
			foreach($ignore_posts as $ignore_check) {
				if (strtoupper(trim($post->post_title)) == strtoupper(trim($ignore_check->keyword))) {
					$ignore_flag = 'true';
				}
			}
			if ($ignore_flag == 'true') { continue; }
			$words_list = $post->post_title;
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE page_type="WP eCommerce Product Name" AND page_name="' . $post->post_title . '" AND ignore_word = true');
			if ($words_list == '' && sizeof($ignore_word) < 1) {
				
				$sql_count++;
				$error_count++;
			}
		}
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "WP eCommerce Name Checked: " . $total_count . ". Empty fields found: " . $error_count . "\r\n" );
		//$fclose($debug_file);
		$loc = dirname(__FILE__) . "/DB-Queries.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "WPEC Name: " . $sql_count . "\r\n" );
		//$fclose($debug_file);
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
	}
	
	function check_wpecommerce_excerpt_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$empty_table = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$sql_count = 1;;
		set_time_limit(6000); 
		$error_count = 0;
		$total_count = 0;
		
		$total_posts = 500;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$ignore_posts = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");

		$posts_list = get_posts(array('posts_per_page' => 500000, 'post_type' => 'wpsc-product', 'post_status' => array('publish', 'draft')));

		foreach ($posts_list as $post) {
			$total_count++;
			$ignore_flag = 'false';
			foreach($ignore_posts as $ignore_check) {
				if (strtoupper(trim($post->post_title)) == strtoupper(trim($ignore_check->keyword))) {
					$ignore_flag = 'true';
				}
			}
			if ($ignore_flag == 'true') { continue; }
			$words_list = $post->post_excerpt;
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE page_type="WP eCommerce Product Excerpt" AND page_name="' . $post->post_title . '" AND ignore_word = true');
			if ($words_list == '' && sizeof($ignore_word) < 1) {
				
				$sql_count++;
				$error_count++;
			}
		}
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "WP eCommerce Excerpt Checked: " . $total_count . ". Empty fields found: " . $error_count . "\r\n" );
		//$fclose($debug_file);
		$loc = dirname(__FILE__) . "/DB-Queries.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "WPEC Excerpt: " . $sql_count . "\r\n" );
		//$fclose($debug_file);
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
	}
	
	function check_author_seotitle_empty_free($rng_seed, $is_running = false) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$post_table = $wpdb->prefix . 'posts';
		$user_table = $wpdb->prefix . 'usermeta';
		$username_table = $wpdb->prefix . 'users';
		set_time_limit(600);
		$error_count = 0;
		$total_count = 0;
		
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$ignore_posts = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");

		$posts_list = $wpdb->get_results("SELECT * FROM $post_table GROUP By post_author LIMIT 500000");

		foreach ($posts_list as $post) {
			$total_count++;
			$author = $wpdb->get_results("SELECT * FROM $user_table WHERE meta_key='wpseo_title' AND user_id='$post->post_author'");
			$author_name = $wpdb->get_results("SELECT * FROM $username_table WHERE id='$post->post_author'");

			$words_list = $author[0]->meta_value;
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE page_type="Author SEO Title" AND page_name="' . $author_name->user_login . '" AND ignore_word = true');
			if ($words_list == '' && sizeof($ignore_word) < 1 && $author_name->user_login != '') {
				
				$sql_count++;
				$error_count++;
			}
		}
		if ($post_count > $total_posts) $post_count = $total_posts;
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Author SEO Title Checked: " . $total_count . ". Empty fields found: " . $error_count . "\r\n" );
		//$fclose($debug_file);
		$loc = dirname(__FILE__) . "/DB-Queries.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Author SEO Title: " . $sql_count . "\r\n" );
		//$fclose($debug_file);
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); //$ Flag that a scan is in progress
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); //$ Update the total time of the scan
		}
	}
	
	function check_author_seodesc_empty_free($rng_seed, $is_running = false) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$post_table = $wpdb->prefix . 'posts';
		$user_table = $wpdb->prefix . 'usermeta';
		$username_table = $wpdb->prefix . 'users';
		set_time_limit(600);
		$total_count = 0;
		$error_count = 0;;

		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}

		$ignore_posts = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");

		$posts_list = $wpdb->get_results("SELECT * FROM $post_table GROUP By post_author LIMIT 500000");
		

		foreach ($posts_list as $post) {
			if ($ignore_flag == 'true') { continue; }
			$total_count++;
			$author = $wpdb->get_results("SELECT * FROM $user_table WHERE meta_key='wpseo_metadesc' AND user_id='$post->post_author'");
			$author_name = $wpdb->get_results("SELECT * FROM $username_table WHERE id='$post->post_author'");

			$words_list = $author[0]->meta_value;
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE page_type="Author SEO Description" AND page_name="' . $author_name->user_login . '" AND ignore_word = true');
			if ($words_list == '' && sizeof($ignore_word) < 1 && $author_name->user_login != '') {
				
				$sql_count++;
				$error_count++;
			}
		}
		if ($post_count > $total_posts) $post_count = $total_posts;
		$loc = dirname(__FILE__) . "/debug.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Author SEO Desc Checked: " . $total_count . ". Empty fields found: " . $error_count . "\r\n" );
		//$fclose($debug_file);
		$loc = dirname(__FILE__) . "/DB-Queries.log";
		//$debug_file = fopen($loc, 'a');
		////$debug_var = fwrite( $debug_file,, "Author SEO Desc: " . $sql_count . "\r\n" );
		//$fclose($debug_file);
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); //$ Flag that a scan is in progress
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); //$ Update the total time of the scan
		}
	}
	
	function check_yoast_page_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$table_name = $wpdb->prefix . 'postmeta';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$error_count = 0;
		$total_count = 0;
		$sql_count = 1;;
		set_time_limit(6000); 
		
		$words_table = $wpdb->prefix . 'spellcheck_empty';
		$posts_table = $wpdb->prefix . 'posts';
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");
		
		$post_types = get_post_types();
		$post_type_list = array();
		foreach ($post_types as $type) {
			if ($type != 'revision' && $type != 'slider' && $type != 'optionsframework' && $type != 'product' && $type != 'wpsc-product' && $type != 'wpcf7_contact_form')
				array_push($post_type_list, $type);
		}
		
		$results = get_posts(array('posts_per_page' => 500000, 'post_type' => 'page', 'post_status' => array('publish', 'draft')));
		
		$ignore_pages = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');

		foreach($results as $desc) {
			$ignore_flag = 'false';
			foreach($ignore_pages as $ignore_check) {
				if (strtoupper(trim($desc->post_title)) == strtoupper(trim($ignore_check->keyword))) {
					$ignore_flag = 'true';
				}
			}
			if ($ignore_flag == 'true') { continue; }
			$total_count++;
			$seo_check = $wpdb->get_results('SELECT post_id, meta_value, meta_key FROM ' . $table_name . ' WHERE (meta_key="_yoast_wpseo_metadesc" OR meta_key="_aioseop_description" OR meta_key="_su_description") AND post_id=' . $desc->ID);
			
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $words_table . ' WHERE page_type="SEO Page Description" AND page_id="' . $desc->ID . '" AND ignore_word = true');
			
			if ((sizeof($seo_check) <= 0 || $seo_check[0]->meta_value == '') && sizeof($ignore_word) < 1) {
				$desc_type = $seo_check[0]->meta_key;
				
				$sql_count++;
				if ($desc_type == '_yoast_wpseo_metadesc') {
					$error_count++;
				} elseif ($desc_type == '_aioseop_description') {
					$error_count++;
				} elseif ($desc_type == '_su_description') {
					$error_count++;
				} else  {
					$error_count++;
				}
			}
		}
		//$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_page_count')); 
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
	}
	
	function check_seo_titles_page_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$table_name = $wpdb->prefix . 'postmeta';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$error_count = 0;
		$total_count = 0;
		$sql_count = 1;;
		set_time_limit(6000); 

		$words_table = $wpdb->prefix . 'spellcheck_empty';
		$posts_table = $wpdb->prefix . 'posts';
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");
		
		$post_types = get_post_types();
		$post_type_list = array();
		foreach ($post_types as $type) {
			if ($type != 'revision' && $type != 'slider' && $type != 'optionsframework' && $type != 'product' && $type != 'wpsc-product' && $type != 'wpcf7_contact_form' && $type != 'nav_menu_item')
				array_push($post_type_list, $type);
		}
		
		$results = get_posts(array('posts_per_page' => 500000, 'post_type' => 'page', 'post_status' => array('publish', 'draft')));
		
		$ignore_pages = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');
		
		foreach($results as $desc) {
			$ignore_flag = 'false';
			foreach($ignore_pages as $ignore_check) {
				if (strtoupper(trim($desc->post_title)) == strtoupper(trim($ignore_check->keyword))) {
					$ignore_flag = 'true';
				}
			}
			if ($ignore_flag == 'true') { continue; }
			$total_count++;
			$seo_check = $wpdb->get_results('SELECT post_id, meta_value, meta_key FROM ' . $table_name . ' WHERE (meta_key="_yoast_wpseo_title" OR meta_key="_aioseop_title" OR meta_key="_su_title") AND post_id=' . $desc->ID);
			
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $words_table . ' WHERE page_type="SEO Page Title" AND page_id="' . $desc->ID . '" AND ignore_word = true');
			
			if ((sizeof($seo_check) <= 0 || $seo_check[0]->meta_value == '') && sizeof($ignore_word) < 1) {
				$desc_type = $seo_check[0]->meta_key;
				
				$sql_count++;
				if ($desc_type == '_yoast_wpseo_title') {
					$error_count++;
				} elseif ($desc_type == '_aioseop_title') {
					$error_count++;
				} elseif ($desc_type == '_su_title') {
					$error_count++;
				} else  {
					$error_count++;
				}
			}
		}
		//$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_page_count')); 
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
	}
	
	function check_yoast_post_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$table_name = $wpdb->prefix . 'postmeta';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$error_count = 0;
		$total_count = 0;
		$sql_count = 1;;
		set_time_limit(6000); 
		
		$words_table = $wpdb->prefix . 'spellcheck_empty';
		$posts_table = $wpdb->prefix . 'posts';
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");
		
		$post_types = get_post_types();
		$post_type_list = array();
		foreach ($post_types as $type) {
			if ($type != 'revision' && $type != 'slider' && $type != 'optionsframework' && $type != 'product' && $type != 'wpsc-product' && $type != 'wpcf7_contact_form' && $type != 'nav_menu_item' && $type != 'page' && $type != 'attachment')
				array_push($post_type_list, $type);
		}
		
		$results = get_posts(array('posts_per_page' => 500000, 'post_type' => $post_type_list, 'post_status' => array('publish', 'draft')));
		
		$ignore_pages = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');

		foreach($results as $desc) {
			$ignore_flag = 'false';
			foreach($ignore_pages as $ignore_check) {
				if (strtoupper(trim($desc->post_title)) == strtoupper(trim($ignore_check->keyword))) {
					$ignore_flag = 'true';
				}
			}
			if ($ignore_flag == 'true') { continue; }
			$total_count++;
			$seo_check = $wpdb->get_results('SELECT post_id, meta_value, meta_key FROM ' . $table_name . ' WHERE (meta_key="_yoast_wpseo_metadesc" OR meta_key="_aioseop_description" OR meta_key="_su_description") AND post_id=' . $desc->ID);
			
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $words_table . ' WHERE page_type="SEO Post Description" AND page_id="' . $desc->ID . '" AND ignore_word = true');
			
			if ((sizeof($seo_check) <= 0 || $seo_check[0]->meta_value == '') && sizeof($ignore_word) < 1) {
				$desc_type = $seo_check[0]->meta_key;
				
				$sql_count++;
				if ($desc_type == '_yoast_wpseo_metadesc') {
					$error_count++;
				} elseif ($desc_type == '_aioseop_description') {
					$error_count++;
				} elseif ($desc_type == '_su_description') {
					$error_count++;
				} else  {
					$error_count++;
				}
			}
		}
		////$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_post_count')); 
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
	}
	
	function check_seo_titles_post_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$table_name = $wpdb->prefix . 'postmeta';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
		$error_count = 0;
		$total_count = 0;
		$sql_count = 1;;
		set_time_limit(6000); 

		$words_table = $wpdb->prefix . 'spellcheck_empty';
		$posts_table = $wpdb->prefix . 'posts';
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");
		
		$ignore_pages = $wpdb->get_results('SELECT keyword FROM ' . $ignore_table . ' WHERE type="page";');
		
		$post_types = get_post_types();
		$post_type_list = array();
		foreach ($post_types as $type) {
			if ($type != 'revision' && $type != 'slider' && $type != 'optionsframework' && $type != 'product' && $type != 'wpsc-product' && $type != 'wpcf7_contact_form' && $type != 'nav_menu_item' && $type != 'page' && $type != 'attachment')
				array_push($post_type_list, $type);
		}
		
		$results = get_posts(array('posts_per_page' => 500000, 'post_type' => $post_type_list, 'post_status' => array('publish', 'draft')));
		
		foreach($results as $desc) {
			$ignore_flag = 'false';
			foreach($ignore_pages as $ignore_check) {
				if (strtoupper(trim($desc->post_title)) == strtoupper(trim($ignore_check->keyword))) {
					$ignore_flag = 'true';
				}
			}
			if ($ignore_flag == 'true') { continue; }
			$total_count++;
			$seo_check = $wpdb->get_results('SELECT post_id, meta_value, meta_key FROM ' . $table_name . ' WHERE (meta_key="_yoast_wpseo_title" OR meta_key="_aioseop_title" OR meta_key="_su_title") AND post_id=' . $desc->ID);
			
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $words_table . ' WHERE page_type="SEO Post Title" AND page_id="' . $desc->ID . '" AND ignore_word = true');
			
			if ((sizeof($seo_check) <= 0 || $seo_check[0]->meta_value == '') && sizeof($ignore_word) < 1) {
				$desc_type = $seo_check[0]->meta_key;
				
				$sql_count++;
				if ($desc_type == '_yoast_wpseo_title') {
					$error_count++;
				} elseif ($desc_type == '_aioseop_title') {
					$error_count++;
				} elseif ($desc_type == '_su_title') {
					$error_count++;
				} else  {
					$error_count++;
				}
			}
		}
		////$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_post_count')); 
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
	}
	
	function check_yoast_media_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$table_name = $wpdb->prefix . 'postmeta';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$error_count = 0;
		$total_count = 0;
		$sql_count = 1;;
		set_time_limit(6000); 
		
		$words_table = $wpdb->prefix . 'spellcheck_empty';
		$posts_table = $wpdb->prefix . 'posts';
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");
		
		$post_types = get_post_types();
		$post_type_list = array();
		foreach ($post_types as $type) {
			if ($type != 'revision' && $type != 'slider' && $type != 'optionsframework' && $type != 'product' && $type != 'wpsc-product' && $type != 'wpcf7_contact_form' && $type != 'nav_menu_item' && $type != 'page' && $type != 'attachment')
				array_push($post_type_list, $type);
		}
		
		$results = get_posts(array('posts_per_page' => 500000, 'post_type' => 'attachment'));

		foreach($results as $desc) {
			$total_count++;
			$seo_check = $wpdb->get_results('SELECT post_id, meta_value, meta_key FROM ' . $table_name . ' WHERE (meta_key="_yoast_wpseo_metadesc" OR meta_key="_aioseop_description" OR meta_key="_su_description") AND post_id=' . $desc->ID);
			
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $words_table . ' WHERE page_type="SEO Media Description" AND page_id="' . $desc->ID . '" AND ignore_word = true');
			
			if ((sizeof($seo_check) <= 0 || $seo_check[0]->meta_value == '') && sizeof($ignore_word) < 1) {
				$desc_type = $seo_check[0]->meta_key;
				
				$sql_count++;
				if ($desc_type == '_yoast_wpseo_metadesc') {
					$error_count++;
				} elseif ($desc_type == '_aioseop_description') {
					$error_count++;
				} elseif ($desc_type == '_su_description') {
					$error_count++;
				} else  {
					$error_count++;
				}
			}
		}
		//$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_media_count')); 
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
	}
	
	function check_seo_titles_media_empty_free($rng_seed, $is_running = false) {
		if (!$is_running) sleep(1);
		global $wpdb;
		$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
		$table_name = $wpdb->prefix . 'postmeta';
		$options_table = $wpdb->prefix . 'spellcheck_options';
		$error_count = 0;
		$total_count = 0;
		$sql_count = 1;;
		set_time_limit(6000); 

		$words_table = $wpdb->prefix . 'spellcheck_empty';
		$posts_table = $wpdb->prefix . 'posts';
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress')); 
			$start_time = time();
		}
		$options_list = $wpdb->get_results("SELECT option_value FROM $options_table");
		
		$post_types = get_post_types();
		$post_type_list = array();
		foreach ($post_types as $type) {
			if ($type != 'revision' && $type != 'slider' && $type != 'optionsframework' && $type != 'product' && $type != 'wpsc-product' && $type != 'wpcf7_contact_form' && $type != 'nav_menu_item' && $type != 'page' && $type != 'attachment')
				array_push($post_type_list, $type);
		}
		
		$results = get_posts(array('posts_per_page' => 500000, 'post_type' => 'attachment'));
		
		foreach($results as $desc) {
			$total_count++;
			$seo_check = $wpdb->get_results('SELECT post_id, meta_value, meta_key FROM ' . $table_name . ' WHERE (meta_key="_yoast_wpseo_title" OR meta_key="_aioseop_title" OR meta_key="_su_title") AND post_id=' . $desc->ID);
			
			$ignore_word = $wpdb->get_results('SELECT * FROM ' . $words_table . ' WHERE page_type="SEO Media Title" AND page_id="' . $desc->ID . '" AND ignore_word = true');
			
			if ((sizeof($seo_check) <= 0 || $seo_check[0]->meta_value == '') && sizeof($ignore_word) < 1) {
				$desc_type = $seo_check[0]->meta_key;
				
				$sql_count++;
				if ($desc_type == '_yoast_wpseo_title') {
					$error_count++;
				} elseif ($desc_type == '_aioseop_title') {
					$error_count++;
				} elseif ($desc_type == '_su_title') {
					$error_count++;
				} else  {
					$error_count++;
				}
			}
		}
		//$wpdb->update($options_table, array('option_value' => $total_count), array('option_name' => 'empty_media_count'));
		//$counter = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name ='empty_checked';");
		//$total_count = $total_count + intval($counter[0]->option_value);
		return $error_count;
		if ($is_running != true) {
			$wpdb->update($options_table, array('option_value' => 'false'), array('option_name' => 'empty_scan_in_progress')); 
			$end_time = time();
			$total_time = time_elapsed($end_time - $start_time + 6);
			$wpdb->update($options_table, array('option_value' => $total_time), array('option_name' => 'empty_start_time')); 
		}
	}
?>