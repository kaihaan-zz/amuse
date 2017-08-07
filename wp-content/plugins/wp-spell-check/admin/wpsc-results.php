<?php
/* Admin Classes */
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
class sc_table extends WP_List_Table {

	function __construct() {
		global $status, $page;
		
		
		parent::__construct( array(
			'singular' => 'word',
			'plural' => 'words',
			'ajax' => true
		) );
	}
	
	function column_default($item, $column_name) {
		return print_r($item,true);
	}
	
	
	function column_word($item) {
		set_time_limit(600); 
		global $wpdb;
		global $dict_list;
		global $wpsc_settings;
		$table_name = $wpdb->prefix . 'spellcheck_options';
		$dict_table = $wpdb->prefix . "spellcheck_dictionary";
		$language_setting = $wpsc_settings[11];
		$dict_words = $dict_list;
		
		$loc = dirname(__FILE__) . "/dict/" . $language_setting->option_value . ".pws";
		$file = fopen($loc, 'r');
		$contents = fread($file,filesize($loc));
		fclose($file);
		
		$word_list = array();
		foreach ($dict_words as $dict_word) {
			array_push($word_list,$dict_word->word);
		}
	
		$contents = str_replace("\r\n", "\n", $contents);
		$main_list = explode("\n", $contents);

		$word_list = array_merge($word_list,$main_list);
	
		$suggestions = array();
		
		foreach ($word_list as $words) {
			
			$first_word = stripslashes($item['word']);
			if (gettype($words) == 'string') similar_text(strtoupper($first_word),strtoupper($words),$percentage);
			if ($percentage > 80.00)
				array_push($suggestions,$words);
				
			if (sizeof($suggestions) >= 4) break;
		}
		if (sizeof($suggestions) < 4) {
			foreach ($word_list as $words) {
				
				$first_word = stripslashes($item['word']);
				if (gettype($words) == 'string') similar_text(strtoupper($first_word),strtoupper($words),$percentage);
				if ($percentage > 60.00)
					array_push($suggestions,$words);
					
				if (sizeof($suggestions) >= 4) break;
			}
		}
		if (sizeof($suggestions) < 4) {
			foreach ($word_list as $words) {
				
				$first_word = stripslashes($item['word']);
				if (gettype($words) == 'string') similar_text(strtoupper($first_word),strtoupper($words),$percentage);
				if ($percentage > 40.00)
					array_push($suggestions,$words);
					
				if (sizeof($suggestions) >= 4) break;
			}
		}

		$sorting = '';
		if ($_GET['orderby'] != '') $sorting .= '&orderby=' . $_GET['orderby'];
		if ($_GET['order'] != '') $sorting .= '&order=' . $_GET['order'];
		if ($_GET['paged'] != '') $sorting .= '&paged=' . $_GET['paged'];

		
		if ($item['word'] == "Empty Field") {
			if ($item['page_type'] == 'Page Slug' || $item['page_type'] == 'Post Slug' || $item['page_type'] == 'Tag Slug' || $item['page_type'] == 'Category Slug') {
				$actions = array (
					'Ignore'      			=> sprintf('<input type="checkbox" class="wpsc-ignore-checkbox" name="ignore-word[]" value="' . $item['id'] . '" />Ignore'),
				);
			} else {
				$actions = array (
					'Ignore'      			=> sprintf('<input type="checkbox" class="wpsc-ignore-checkbox" name="ignore-word[]" value="' . $item['id'] . '" />Ignore'),
					'Edit'					=> sprintf('<a href="#" class="wpsc-edit-button" page_type="' . $item['page_type'] . '" id="wpsc-word-' . $item['word'] . '">Edit</a>')
				);
			}
		} else {
			if ($item['page_type'] == 'Page Slug' || $item['page_type'] == 'Post Slug' || $item['page_type'] == 'Tag Slug' || $item['page_type'] == 'Category Slug') {
				$actions = array (
					'Ignore'      			=> sprintf('<input type="checkbox" class="wpsc-ignore-checkbox" name="ignore-word[]" value="' . $item['id'] . '" />Ignore'),
					'Add to Dictionary'		=> sprintf('<input type="checkbox" class="wpsc-add-checkbox" name="add-word[]" value="' . $item['id'] . '" />Add to Dictionary')
				);
			} else {
				$actions = array (
					'Ignore'      			=> sprintf('<input type="checkbox" class="wpsc-ignore-checkbox" name="ignore-word[]" value="' . $item['id'] . '" />Ignore'),
					'Suggested Spelling'	=> sprintf('<a href="#" class="wpsc-suggest-button" suggestions="' . $suggestions[0] . '-' . $suggestions[1] . '-' . $suggestions[2] . '-' . $suggestions[3] . '">Suggested Spelling</a>'),
					'Edit'					=> sprintf('<a href="#" class="wpsc-edit-button" page_type="' . $item['page_type'] . '" id="wpsc-word-' . $item['word'] . '">Edit</a>'),
					'Add to Dictionary'		=> sprintf('<br /><input type="checkbox" class="wpsc-add-checkbox" name="add-word[]" value="' . $item['id'] . '" />Add to Dictionary')
				);
			}
		}
		
		
		return sprintf('%1$s<span style="background-color:#0096ff; float: left; margin: 3px 5px 0 -30px; display: block; width: 12px; height: 12px; border-radius: 16px; opacity: 1.0;"></span>%3$s',
            stripslashes(stripslashes($item['word'])),
            $item['ID'],
            $this->row_actions($actions)
        );
	}
	
	
	function column_page_name($item) {
		
		
		global $wpdb;
		$link = urldecode ( get_permalink( $item['page_id'] ) );
		$handle = curl_init($url);
		curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

		$response = curl_exec($handle);

		$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		if($httpCode == 404) {
			$output = '';
		} elseif ($item['page_type'] == 'Menu Item') {
			$output = '<a href="/wp-admin/nav-menus.php?action=edit&menu='.$item['page_id'].'" id="wpsc-page-name" page="' . $item['page_id'] . '" target="_blank">View</a>';
		} elseif ($item['page_type'] == 'Contact Form 7') {
			$output = '<a href="admin.php?page=wpcf7&post='.$item['page_id'].'&action=edit" id="wpsc-page-name" page="' . $item['page_id'] . '" target="_blank">View</a>';
		} elseif ($item['page_type'] == 'Post Title' || $item['page_type'] == 'Page Title' || $item['page_type'] == 'Yoast SEO Description' || $item['page_type'] == 'All in One SEO Description' || $item['page_type'] == 'Ultimate SEO Description' || $item['page_type'] == 'SEO Description' || $item['page_type'] == 'Yoast SEO Title' || $item['page_type'] == 'All in One SEO Title' || $item['page_type'] == 'Ultimate SEO Title' || $item['page_type'] == 'SEO Title' || $item['page_type'] == 'Post Slug' || $item['page_type'] == 'Page Slug') {
			$output = '<a href="/wp-admin/post.php?post=' . $item['page_id'] . '&action=edit" id="wpsc-page-name" page="' . $item['page_id'] . '" target="_blank">View</a>';
		} elseif ($item['page_type'] == 'Slider Title' || $item['page_type'] == 'Slider Caption' || $item['page_type'] == 'Smart Slider Title' || $item['page_type'] == 'Smart Slider Caption') {
			$output = '<a href="/wp-admin/post.php?post=' . $item['page_id'] . '&action=edit" id="wpsc-page-name" page="' . $item['page_id'] . '" target="_blank">View</a>';
		} elseif ($item['page_type'] == 'Huge IT Slider Title' || $item['page_type'] == 'Huge IT Slider Caption') {
			$output = '<a href="/wp-admin/admin.php?page=sliders_huge_it_slider&task=edit_cat&id=' . $item['page_id'] . '" id="wpsc-page-name" page="' . $item['page_id'] . '" target="_blank">View</a>';
		} elseif ($item['page_type'] == 'Media Title' || $item['page_type'] == 'Media Description' || $item['page_type'] == 'Media Caption' || $item['page_type'] == 'Media Alternate Text') {
			$output = '<a href="/wp-admin/post.php?post=' . $item['page_id'] . '&action=edit" id="wpsc-page-name" page="' . $item['page_id'] . '" target="_blank">View</a>';
		} elseif ($item['page_type'] == 'Tag Title' || $item['page_type'] == 'Tag Description' || $item['page_type'] == 'Post Category' || $item['page_type'] == 'Category Description' || $item['page_type'] == 'Tag Slug' || $item['page_type'] == 'Category Slug') {
			$output = '<a href="/wp-admin/term.php?taxonomy=post_tag&tag_ID=' . $item['page_id'] . '&post_type=post" id="wpsc-page-name" page="' . $item['page_id'] . '" target="_blank">View</a>';
		} elseif($item['page_type'] == 'Author Nickname' || $item['page_type'] == 'Author First Name' || $item['page_type'] == 'Author Last Name' || $item['page_type'] == 'Author Biographical Info' || $item['page_type'] == 'Author SEO Title' || $item['page_type'] == 'Author SEO Description') {
			$output = '<a href="/wp-admin/user-edit.php?user_id=' . $item['page_id'] . ' " id="wpsc-page-name" page="' . $item['page_id'] . '" target="_blank">View</a>';
		} elseif($item['page_type'] == "Site Name" || $item['page_type'] == "Site Tagline") {
			$output = '<a href="/wp-admin/options-general.php" target="_blank">View</a>';
		} else {
			$output = '<a href="' . $link . '" id="wpsc-page-name" page="' . $item['page_id'] . '" target="_blank">View</a>';
		}
		if (($item['page_type'] == "WP eCommerce Product Excerpt" || $item['page_type'] == "WP eCommerce Product Name" || $item['page_type'] == "WooCommerce Product Excerpt" || $item['page_type'] == "WooCommerce Product Name" || $item['page_type'] == "Page Title" || $item['page_type'] == "Post Title" || $item['page_type'] == 'Yoast SEO Page Description' || $item['page_type'] == 'All in One SEO Page Description' || $item['page_type'] == 'Ultimate SEO Page Description' || $item['page_type'] == 'SEO Page Description' || $item['page_type'] == 'Yoast SEO Page Title' || $item['page_type'] == 'All in One SEO Page Title' || $item['page_type'] == 'Ultimate SEO Page Title' || $item['page_type'] == 'SEO Page Title' || $item['page_type'] == 'Yoast SEO Post Description' || $item['page_type'] == 'All in One SEO Post Description' || $item['page_type'] == 'Ultimate SEO Post Description' || $item['page_type'] == 'SEO Post Description' || $item['page_type'] == 'Yoast SEO Post Title' || $item['page_type'] == 'All in One SEO Post Title' || $item['page_type'] == 'Ultimate SEO Post Title' || $item['page_type'] == 'SEO Post Title' || $item['page_type'] == 'Yoast SEO Media Description' || $item['page_type'] == 'All in One SEO Media Description' || $item['page_type'] == 'Ultimate SEO Media Description' || $item['page_type'] == 'SEO Media Description' || $item['page_type'] == 'Yoast SEO Media Title' || $item['page_type'] == 'All in One SEO Media Title' || $item['page_type'] == 'Ultimate SEO Media Title' || $item['page_type'] == 'SEO Media Title') && $item['word'] == "Empty Field") {
			$output = '<a href="/wp-admin/post.php?post=' . $item['page_id'] . '&action=edit" id="wpsc-page-name" page="' . $item['page_id'] . '" target="_blank">View</a>';
		}

		curl_close($handle);
		$actions = array (
			'View'      			=> sprintf($output),
		);
		
		
		return sprintf('%1$s <span style="color:silver"></span>%3$s',
            $item['page_name'],
            $item['ID'],
            $this->row_actions($actions)
        );
	}

	
	function column_page_type($item) {
		
		$actions = array ();
		
		
		return sprintf('%1$s <span style="color:silver"></span>%3$s',
            $item['page_type'],
            $item['ID'],
            $this->row_actions($actions)
        );
	}

	
	function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'word' => 'Misspelled Words',
			'page_name' => 'Page',
			'page_type' => 'Page Type'
		);
		return $columns;
	}
	
	
	function get_sortable_columns() {
		$sortable_columns = array(
			'word' => array('word',false),
			'page_name' => array('page_name',false),
			'page_type' => array('page_type',false)
		);
		return $sortable_columns;
	}

	
	function single_row( $item ) {
		static $row_class = 'wpsc-row';
		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );

		echo '<tr class="wpsc-row" id="wpsc-row-' . $item['id'] . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}
	
	
	function prepare_items() {
		error_reporting(0);
		global $wpdb;
		
		$per_page = 20;
		
		
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		
		$table_name = $wpdb->prefix . 'spellcheck_words';
		$dictionary_table = $wpdb->prefix . 'spellcheck_dictionary';
		if ($_GET['s'] != '') {
			$results = $wpdb->get_results('SELECT id, word, page_name, page_type, page_id FROM ' . $table_name . ' WHERE ignore_word is false AND word LIKE "%' . $_GET['s'] . '%"', OBJECT); 
		} elseif ($_GET['s-top'] != '') {
			$results = $wpdb->get_results('SELECT id, word, page_name, page_type, page_id FROM ' . $table_name . ' WHERE ignore_word is false AND word LIKE "%' . $_GET['s-top'] . '%"', OBJECT); 
		} else {
			$results = $wpdb->get_results('SELECT id, word, page_name, page_type, page_id FROM ' . $table_name . ' WHERE ignore_word is false', OBJECT);
		}
		$data = array();
		foreach($results as $word) {
			if ($word->word != '') {
				array_push($data, array('id' => $word->id, 'word' => $word->word, 'page_name' => $word->page_name, 'page_type' => $word->page_type, 'page_url' => $word->page_url, 'page_id' => $word->page_id));
			}
		}
		
		function usort_reorder($a, $b) {
			$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'word'; 
			$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; 
			
			$result = strcmp($a[$orderby], $b[$orderby]); 
			return ($order==='asc') ? $result : -$result;
		}
		usort($data, 'usort_reorder');
		
		
		$current_page = $this->get_pagenum();
		$total_items = count($data);
		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
		$this->items = $data;
		
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil($total_items/$per_page)
		) );		
	}

	function prepare_empty_items() {
		error_reporting(0);
		global $wpdb;
		
		$per_page = 20;
		
		
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		
		$table_name = $wpdb->prefix . 'spellcheck_empty';
		$dictionary_table = $wpdb->prefix . 'spellcheck_dictionary';
		if ($_GET['s'] != '') {
			$results = $wpdb->get_results('SELECT id, word, page_name, page_type, page_id FROM ' . $table_name . ' WHERE ignore_word is false AND page_name LIKE "%' . $_GET['s'] . '%"', OBJECT); 
		} elseif($_GET['s-top'] != '') {
			$results = $wpdb->get_results('SELECT id, word, page_name, page_type, page_id FROM ' . $table_name . ' WHERE ignore_word is false AND page_name LIKE "%' . $_GET['s-top'] . '%"', OBJECT); 
		} else {
			$results = $wpdb->get_results('SELECT id, word, page_name, page_type, page_id FROM ' . $table_name . ' WHERE ignore_word is false', OBJECT);
		}
		$data = array();
		foreach($results as $word) {
			if ($word->word != '') {
				array_push($data, array('id' => $word->id, 'word' => $word->word, 'page_name' => $word->page_name, 'page_type' => $word->page_type, 'page_url' => $word->page_url, 'page_id' => $word->page_id));
			}
		}
		
		function usort_empty_reorder($a, $b) {
			$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'word'; 
			$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; 
			
			$result = strcmp($a[$orderby], $b[$orderby]); 
			return ($order==='asc') ? $result : -$result;
		}
		usort($data, 'usort_empty_reorder');
		
		
		$current_page = $this->get_pagenum();
		$total_items = count($data);
		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
		$this->items = $data;
		
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil($total_items/$per_page)
		) );		
	}
}

/* Admin Functions */
function ignore_word($ids) {
	global $wpdb;
	$word_list = array();
	$table_name = $wpdb->prefix . 'spellcheck_words';
	$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
	$show_error_ignore = false;
	$show_error_dict = false;
	$word_list[0] = '';
	$added = '';
	$dict_msg = '';
	$ignore_msg = '';
	foreach ($ids as $id) {
		$words = $wpdb->get_results('SELECT word FROM ' . $table_name . ' WHERE id='. $id . ';');
		$word = $words[0]->word;
		$ignore_word = str_replace("'","\'",$word);
		$ignore_word = str_replace("'","\'",$ignore_word);
		$check_word = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE word="' . $ignore_word . '" AND ignore_word = true');
		$check_dict = $wpdb->get_results('SELECT * FROM ' . $dict_table . ' WHERE word="' . $word . '"');
		if (sizeof($check_word) <= 0 && sizeof($check_dict) <= 0) {
			$wpdb->update($table_name, array('ignore_word' => true), array('id' => $id));
			$wpdb->query("DELETE FROM $table_name WHERE id != $id AND word='$word'");
			$added .= stripslashes($word) . ", ";
			
		} else {
			if (sizeof($check_dict) <= 0) {
				$ignore_msg .= stripslashes($word) . ", ";
				$show_error_ignore = true;
			} else {
				$dict_msg .= stripslashes($word) . ", ";
				$show_error_dict = true;
			}
		}
	}
	if ($show_error_ignore) {
		$ignore_msg =trim($dict_msg, ", ");
		$word_list[1] = "The following words were already found in the ignore list: " . $ignore_msg;
	}
	if ($show_error_dict) {
		$dict_msg =trim($dict_msg, ", ");
		$word_list[2] = "The following words were already found in the dictionary: " . $dict_msg;
	}
	$added =trim($added, ", ");
	if (strpos($added, ", ") !== false) {
		$word_list[0] = "The following words have been added to ignore list: " . $added;
	} else {
		$word_list[0] = "The following word has been added to ignore list: " . $added;
	}
	return $word_list;
}

function ignore_word_empty($ids) {
	global $wpdb;
	$word_list = array();
	$table_name = $wpdb->prefix . 'spellcheck_empty';
	$dict_table = $wpdb->prefix . 'spellcheck_dictionary';
	$show_error_ignore = false;
	$show_error_dict = false;
	$word_list[0] = '';
	foreach ($ids as $id) {
		$words = $wpdb->get_results('SELECT word FROM ' . $table_name . ' WHERE id='. $id . ';');
		$word = $words[0]->word;
		$ignore_word = str_replace("'","\'",$word);
		$ignore_word = str_replace("'","\'",$ignore_word);
		
		$check_dict = $wpdb->get_results('SELECT * FROM ' . $dict_table . ' WHERE word="' . $word . '"');
		if (sizeof($check_dict) <= 0) {
			$wpdb->update($table_name, array('ignore_word' => true), array('id' => $id));
			
			$word_list[0] .= stripslashes($word) . ", ";
			
		} else {
			if (sizeof($check_dict) <= 0) {
				$word_list[1] .= stripslashes($word) . ", ";
				$show_error_ignore = true;
			} else {
				$word_list[2] .= stripslashes($word) . ", ";
				$show_error_dict = true;
			}
		}
	}
	if ($show_error_ignore) {
		$word_list[1] =trim($word_list[1], ", ");
		$word_list[1] = "The following words were already found in the ignore list: " . $word_list[1];
	}
	if ($show_error_dict) {
		$word_list[2] =trim($word_list[2], ", ");
		$word_list[2] = "The following words were already found in the dictionary: " . $word_list[2];
	}
	$word_list[0] =trim($word_list[0], ", ");
	if (strpos($word_list[0], ", ") !== false) {
		$word_list[0] = "The following words have been added to ignore list: " . $word_list[0];
	} else {
		$word_list[0] = "The following word has been added to ignore list: " . $word_list[0];
	}
	return $word_list;
}

function add_to_dictionary($ids) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'spellcheck_words';
	$dictionary_table = $wpdb->prefix . 'spellcheck_dictionary';
	$word_list = '';
	$show_error_ignore = false;
	$show_error_dict = false;
	foreach ($ids as $id) {
		$words = $wpdb->get_results('SELECT word FROM ' . $table_name . ' WHERE id='. $id . ';');
		$word = $words[0]->word;
		$word = str_replace('%28', '(', $word);
		$ignore_word = str_replace("'","\'",$word);
		$ignore_word = str_replace("'","\'",$ignore_word);
		$check = $wpdb->get_results('SELECT * FROM ' . $dictionary_table . ' WHERE word="' . $word . '"'); 
		$ignore_check = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE word="' . $ignore_word . '" AND ignore_word = true');

		if (sizeof($check) < 1 && sizeof($ignore_check) < 1) {
			$wpdb->insert($dictionary_table, array('word' => stripslashes($word))); 

			$wpdb->delete($table_name, array('word' => $word)); 
			$word_list[0] .= $word . ", ";
			
		} else {
			if (sizeof($check_dict) <= 0) {
				$word_list[1] .= stripslashes($word) . ", ";
				$show_error_ignore = true;
			} else {
				$word_list[2] .= stripslashes($word) . ", ";
				$show_error_dict = true;
			}
		}
	}
	if ($show_error_ignore) {
		$word_list[1] =trim($word_list[1], ", ");
		$word_list[1] = "The following words were already found in the ignore list: " . $word_list[1];
	}
	if ($show_error_dict) {
		$word_list[2] =trim($word_list[2], ", ");
		$word_list[2] = "The following words were already found in the dictionary: " . $word_list[2];
	}
	$word_list[0] =trim($word_list[0], ", ");
	if (strpos($word_list[0], ", ") !== false) {
		$word_list[0] = "The following words have been added to dictionary: " . $word_list[0];
	} else {
		$word_list[0] = "The following word has been added to dictionary: " . $word_list[0];
	}
	return $word_list;
}

function update_word_admin($old_words, $new_words, $page_names, $page_types, $old_word_ids) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'posts';
	$words_table = $wpdb->prefix . 'spellcheck_words';
	$terms_table = $wpdb->prefix . 'terms';
	$meta_table = $wpdb->prefix . 'postmeta';
	$taxonomy_table = $wpdb->prefix . 'term_taxonomy';
	$user_table = $wpdb->prefix . 'usermeta';
	$word_list = '';

for ($x= 0; $x < sizeof($old_words); $x++) {
	$old_words[$x] = str_replace('%28', '(', $old_words[$x]);
	$new_words[$x] = str_replace('%28', '(', $new_words[$x]);
	$old_words[$x] = str_replace('%27', "'", $old_words[$x]);
	$new_words[$x] = str_replace('%27', "'", $new_words[$x]);
	$old_words[$x] = stripslashes(stripslashes($old_words[$x]));
	$new_words[$x] = stripslashes($new_words[$x]);
	if ($page_types[$x] == 'Post Content' || $page_types[$x] == 'Page Content' || $page_types[$x] == 'Media Description' || $page_types[$x] == 'WooCommerce Product' || $page_types[$x] == 'WP eCommerce Product' ) {
		
		$page_result = $wpdb->get_results('SELECT post_content, post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$updated_content = preg_replace('#\\b' . $old_words[$x] . '\\b#', $new_words[$x], $page_result[0]->post_content);

		$old_name = $page_result[0]->post_title;
		$wpdb->update($table_name, array('post_content' => $updated_content), array('ID' => $page_names[$x]));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $old_name)); 
	} elseif ($page_types[$x] == 'Contact Form 7') {
		
		$page_result = $wpdb->get_results('SELECT post_content, post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$meta_result = $wpdb->get_results('SELECT meta_value FROM ' . $meta_table . ' WHERE post_id="' . $page_names[$x] . '"');

		
		$updated_content = preg_replace('#\\b' . $old_words[$x] . '\\b#', $new_words[$x], $page_result[0]->post_content);
		$updated_meta = str_replace($old_words[$x], $new_words[$x], $meta_result[0]->meta_value);

		$old_name = $page_result[0]->post_title;
		$wpdb->update($table_name, array('post_content' => $updated_content), array('ID' => $page_names[$x]));
		$wpdb->update($meta_table, array('meta_value' => $updated_meta), array('post_id' => $page_names[$x]));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $old_name)); 
	} elseif ($page_types[$x] == 'WooCommerce Product Excerpt' || $page_types[$x] == 'WP eCommerce Product Excerpt') {
		
		$page_result = $wpdb->get_results('SELECT post_content, post_title, post_excerpt FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');

		
		$updated_content = preg_replace('#\\b' . $old_words[$x] . '\\b#', $new_words[$x], $page_result[0]->post_excerpt);

		$old_name = $page_result[0]->post_title;
		$wpdb->update($table_name, array('post_excerpt' => $updated_content), array('ID' => $page_names[$x]));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $old_name)); 
	} elseif ($page_types[$x] == 'Menu Item' || $page_types[$x] == 'Post Title' || $page_types[$x] == 'Page Title' || $page_types[$x] == 'Slider Title' || $page_types[$x] == 'Media Title' || $page_types[$x] == 'WP eCommerce Product Name' || $page_types[$x] == 'WooCommerce Product Name') {
		
		$menu_result = $wpdb->get_results('SELECT post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$updated_content = str_replace($old_words[$x], $new_words[$x], $menu_result[0]->post_title);

		$old_name = $menu_result[0]->post_title;
		$wpdb->update($table_name, array('post_title' => $updated_content), array('ID' => $page_names[$x]));
		$wpdb->update($words_table, array('page_name' => $updated_content), array('page_name' => $old_name)); //Update the title of the page/post/menu in the spellcheck database
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $updated_content)); 
	} elseif ($page_types[$x] == 'Author Nickname') {
		$author_result = $wpdb->get_results("SELECT * FROM $user_table WHERE user_id=" . $page_names[$x] . " AND meta_key='nickname'");
		$updated_content = str_replace($old_words[$x], $new_words[$x], $author_result[0]->meta_value);
	
		$wpdb->update($user_table, array('meta_value' => $updated_content), array('user_id' => $page_result[0]->post_author, 'meta_key' => 'nickname'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Author First Name') {
		$author_result = $wpdb->get_results("SELECT * FROM $user_table WHERE user_id=" . $page_names[$x] . " AND meta_key='first_name'");
		$updated_content = str_replace($old_words[$x], $new_words[$x], $author_result[0]->meta_value);
	
		$wpdb->update($user_table, array('meta_value' => $updated_content), array('user_id' => $page_names[$x], 'meta_key' => 'first_name'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Author Last Name') {
		$author_result = $wpdb->get_results("SELECT * FROM $user_table WHERE user_id=" . $page_names[$x] . " AND meta_key='last_name'");
		$updated_content = str_replace($old_words[$x], $new_words[$x], $author_result[0]->meta_value);
	
		$wpdb->update($user_table, array('meta_value' => $updated_content), array('user_id' => $page_result[0]->post_author, 'meta_key' => 'last_name'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Author Biographical Info') {
		$author_result = $wpdb->get_results("SELECT * FROM $user_table WHERE user_id=" . $page_names[$x] . " AND meta_key='description'");
		
		$updated_content = preg_replace('#\\b' . $old_words[$x] . '\\b#', $new_words[$x], $author_result[0]->meta_value);
	
		$wpdb->update($user_table, array('meta_value' => $updated_content), array('user_id' => $page_names[$x], 'meta_key' => 'description'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Author SEO Title') {
		$author_result = $wpdb->get_results("SELECT * FROM $user_table WHERE user_id=" . $page_names[$x] . " AND meta_key='wpseo_title'");
		$updated_content = str_replace($old_words[$x], $new_words[$x], $author_result[0]->meta_value);
	
		$wpdb->update($user_table, array('meta_value' => $updated_content), array('user_id' => $page_names[$x], 'meta_key' => 'wpseo_title'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Author SEO Description') {
		$author_result = $wpdb->get_results("SELECT * FROM $user_table WHERE user_id=" . $page_names[$x] . " AND meta_key='wpseo_metadesc'");
		
		$updated_content = preg_replace('#\\b' . $old_words[$x] . '\\b#', $new_words[$x], $author_result[0]->meta_value);
	
		$wpdb->update($user_table, array('meta_value' => $updated_content), array('user_id' => $page_names[$x], 'meta_key' => 'wpseo_metadesc'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Site Name') {
		$opt_table = $wpdb->prefix . "options";
	
		$site_result = $wpdb->get_results("SELECT * FROM $opt_table WHERE option_name='blogname'");
		$updated_content = str_replace($old_words[$x], $new_words[$x], $site_result[0]->option_value);
	
		$wpdb->update($opt_table, array('option_value' => $updated_content), array('option_name' => 'blogname'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_type' => 'Site Name')); 
	} elseif ($page_types[$x] == 'Site Tagline') {
		$opt_table = $wpdb->prefix . "options";
	
		$site_result = $wpdb->get_results("SELECT * FROM $opt_table WHERE option_name='blogdescription'");
		$updated_content = str_replace($old_words[$x], $new_words[$x], $site_result[0]->option_value);
	
		$wpdb->update($opt_table, array('option_value' => $updated_content), array('option_name' => 'blogdescription'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_type' => 'Site Name')); 
	} elseif ($page_types[$x] == 'Slider Caption') {
		
		$menu_result = $wpdb->get_results('SELECT ID, post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$caption = get_post_meta($menu_result[0]->ID, 'my_slider_caption', true);
		$updated_content = str_replace($old_words[$x], $new_words[$x], $caption);

		update_post_meta($menu_result[0]->ID, 'my_slider_caption', $updated_content);
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $menu_result[0]->post_title)); 
	} elseif ($page_types[$x] == 'Huge IT Slider Caption') {
		
		$it_table = $wpdb->prefix . 'huge_itslider_images';
		$menu_result = $wpdb->get_results('SELECT name, description FROM ' . $it_table . ' WHERE id="' . $page_names[$x] . '"');
		
		$updated_content = str_replace($old_words[$x], $new_words[$x], $menu_result[0]->description);
		
		$wpdb->update($it_table, array('description' => $updated_content), array('id' => $page_names[$x]));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $menu_result[0]->name)); 
	} elseif ($page_types[$x] == 'Huge IT Slider Title') {
		
		$it_table = $wpdb->prefix . 'huge_itslider_images';
		$menu_result = $wpdb->get_results('SELECT name FROM ' . $it_table . ' WHERE id="' . $page_names[$x] . '"');
		
		$updated_content = str_replace($old_words[$x], $new_words[$x], $menu_result[0]->name);	

		$wpdb->update($it_table, array('name' => $updated_content), array('id' => $page_names[$x]));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $menu_result[0]->name)); 
	} elseif ($page_types[$x] == 'Smart Slider Caption') {
		
		$slider_table = $wpdb->prefix . 'wp_nextend_smartslider_slides';
		$menu_result = $wpdb->get_results('SELECT description FROM ' . $slider_table . ' WHERE id="' . $page_names[$x] . '"');
		$updated_content = str_replace($old_words[$x], $new_words[$x], $menu_result[0]->description);

		$wpdb->update($slider_table, array('description' => $updated_content), array('id' => $page_names[$x]));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $menu_result[0]->post_title)); 
	} elseif ($page_types[$x] == 'Smart Slider Title') {
		
		$slider_table = $wpdb->prefix . 'wp_nextend_smartslider_slides';
		$menu_result = $wpdb->get_results('SELECT title FROM ' . $slider_table . ' WHERE id="' . $page_names[$x] . '"');
		$updated_content = str_replace($old_words[$x], $new_words[$x], $menu_result[0]->title);

		$wpdb->update($slider_table, array('title' => $updated_content), array('id' => $page_names[$x]));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $menu_result[0]->post_title)); 
	} elseif ($page_types[$x] == 'Media Alternate Text') {
		
		$menu_result = $wpdb->get_results('SELECT ID, post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$caption = get_post_meta($menu_result[0]->ID, '_wp_attachment_image_alt', true);
		$updated_content = str_replace($old_words[$x], $new_words[$x], $caption);

		update_post_meta($menu_result[0]->ID, '_wp_attachment_image_alt', $updated_content);
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $menu_result[0]->post_title)); 
	} elseif ($page_types[$x] == 'Media Caption') {
		
		$page_result = $wpdb->get_results('SELECT post_excerpt, post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');

		$updated_content = str_replace($old_words[$x], $new_words[$x], $page_result[0]->post_excerpt);

		$old_name = $page_result[0]->post_title;
		$wpdb->update($table_name, array('post_excerpt' => $updated_content), array('ID' => $page_names[$x]));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $old_name)); 
	} elseif ($page_types[$x] == 'Tag Title' || $page_types[$x] == 'Category Title') {
		
		$tag_result = $wpdb->get_results('SELECT name FROM ' . $terms_table . ' WHERE term_id=' . $page_names[$x]);

		$updated_content = str_replace($old_words[$x], $new_words[$x], $tag_result[0]->name);

		$wpdb->update($terms_table, array('name' => $updated_content), array('name' => $tag_result[0]->name));
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Tag Description') {
		
		$tag_result = $wpdb->get_results('SELECT description FROM ' . $taxonomy_table . ' WHERE term_id=' . $page_names[$x]);

		$updated_content = str_replace($old_words[$x], $new_words[$x], $tag_result[0]->description);

		$wpdb->update($taxonomy_table, array('description' => $updated_content), array('description' => $tag_result[0]->description));
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Category Description') {
		
		$tag_result = $wpdb->get_results('SELECT description FROM ' . $taxonomy_table . ' WHERE term_id=' . $page_names[$x]);

		$updated_content = str_replace($old_words[$x], $new_words[$x], $tag_result[0]->description);

		$wpdb->update($taxonomy_table, array('description' => $updated_content), array('description' => $tag_result[0]->description));
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Post Custom Field') {
		
		$page_result = $wpdb->get_results('SELECT ID, post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$desc_result = $wpdb->get_results('SELECT meta_value FROM ' . $meta_table . ' WHERE post_id=' . $page_result[0]->ID . ' AND meta_value LIKE "%' . $old_words[$x] . '%"');

		$updated_content = str_replace($old_words[$x], $new_words[$x], $desc_result[0]->meta_value);

		$old_name = $page_result[0]->post_title;
		$wpdb->update($meta_table, array('meta_value' => $updated_content), array('post_id' => $page_result[0]->ID));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $old_name)); 
	} elseif ($page_types[$x] == 'Yoast SEO Description') {
		
		$page_result = $wpdb->get_results('SELECT ID, post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$desc_result = $wpdb->get_results('SELECT meta_value FROM ' . $meta_table . ' WHERE post_id=' . $page_result[0]->ID . ' AND meta_key="_yoast_wpseo_metadesc"');

		$updated_content = str_replace($old_words[$x], $new_words[$x], $desc_result[0]->meta_value);

		$old_name = $page_result[0]->post_title;
		$wpdb->update($meta_table, array('meta_value' => $updated_content), array('post_id' => $page_result[0]->ID, 'meta_key' => '_yoast_wpseo_metadesc'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $old_name)); 
	} elseif ($page_types[$x] == 'All in One SEO Description') {
		
		$page_result = $wpdb->get_results('SELECT ID, post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$desc_result = $wpdb->get_results('SELECT meta_value FROM ' . $meta_table . ' WHERE post_id=' . $page_result[0]->ID . ' AND meta_key="_aioseop_description"');

		$updated_content = str_replace($old_words[$x], $new_words[$x], $desc_result[0]->meta_value);

		$old_name = $page_result[0]->post_title;
		$wpdb->update($meta_table, array('meta_value' => $updated_content), array('post_id' => $page_result[0]->ID, 'meta_key' => '_aioseop_description'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $old_name)); 
	} elseif ($page_types[$x] == 'Ultimate SEO Description') {
		
		$page_result = $wpdb->get_results('SELECT ID, post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$desc_result = $wpdb->get_results('SELECT meta_value FROM ' . $meta_table . ' WHERE post_id=' . $page_result[0]->ID . ' AND meta_key="_su_description"');

		$updated_content = str_replace($old_words[$x], $new_words[$x], $desc_result[0]->meta_value);

		$old_name = $page_result[0]->post_title;
		$wpdb->update($meta_table, array('meta_value' => $updated_content), array('post_id' => $page_result[0]->ID, 'meta_key' => '_su_description'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $old_name)); 
	} elseif ($page_types[$x] == 'Yoast SEO Title') {
		
		$page_result = $wpdb->get_results('SELECT ID, post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$desc_result = $wpdb->get_results('SELECT meta_value FROM ' . $meta_table . ' WHERE post_id=' . $page_result[0]->ID . ' AND meta_key="_yoast_wpseo_title"');

		$updated_content = str_replace($old_words[$x], $new_words[$x], $desc_result[0]->meta_value);

		$old_name = $page_result[0]->post_title;
		$wpdb->update($meta_table, array('meta_value' => $updated_content), array('post_id' => $page_result[0]->ID, 'meta_key' => '_yoast_wpseo_title'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $old_name)); 
	} elseif ($page_types[$x] == 'All in One SEO Title') {
		$page_result = $wpdb->get_results('SELECT ID FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$desc_result = $wpdb->get_results('SELECT meta_value FROM ' . $meta_table . ' WHERE post_id=' . $page_result[0]->ID . ' AND meta_key="_aioseop_title"');

		$updated_content = str_replace($old_words[$x], $new_words[$x], $desc_result[0]->meta_value);

		$old_name = $page_result[0]->post_title;
		$wpdb->update($meta_table, array('meta_value' => $updated_content), array('post_id' => $page_result[0]->ID, 'meta_key' => '_aioseop_title'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $old_name)); 
	} elseif ($page_types[$x] == 'Ultimate SEO Title') {
		$page_result = $wpdb->get_results('SELECT ID FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$desc_result = $wpdb->get_results('SELECT meta_value FROM ' . $meta_table . ' WHERE post_id=' . $page_result[0]->ID . ' AND meta_key="_su_title"');

		$updated_content = str_replace($old_words[$x], $new_words[$x], $desc_result[0]->meta_value);

		$old_name = $page_result[0]->post_title;
		$wpdb->update($meta_table, array('meta_value' => $updated_content), array('post_id' => $page_result[0]->ID, 'meta_key' => '_su_title'));
		$wpdb->delete($words_table, array('word' => $old_words[$x], 'page_name' => $old_name)); 
	}
	

	
	$page_url = get_permalink( $page_names[$x] );
	$page_title = get_the_title( $page_names[$x] );
	$current_time = date( 'l F d, g:i a' );
	$loc = dirname(__FILE__) . "/spellcheck.debug";
	$debug_file = fopen($loc, 'a');
	$debug_var = fwrite( $debug_file, "Old Word: " . $old_words[$x] . " | New Word: " . $new_words[$x] . " | Type: " . $page_types[$x] . " | Page Name: " . $page_title . " | Page URL: " . $page_url . " | Timestamp: " . $current_time . "\r\n\r\n" );
	fclose($debug_file);
	$word_list .= $old_words[$x] . ", ";
	}
	
	$word_list =trim($word_list, ", ");
	if (strpos($word_list, ", ") !== false) {
		return "The following words have been updated: " . $word_list;
	} else {
		return "The following word has been updated: " . $word_list;
	}
}

function update_empty_admin($new_words, $page_names, $page_types, $old_word_ids) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'posts';
	$words_table = $wpdb->prefix . 'spellcheck_empty';
	$terms_table = $wpdb->prefix . 'terms';
	$meta_table = $wpdb->prefix . 'postmeta';
	$taxonomy_table = $wpdb->prefix . 'term_taxonomy';
	$user_table = $wpdb->prefix . 'usermeta';
	$word_list = '';
	$seo_error = false;

for ($x= 0; $x < sizeof($new_words); $x++) {
	$new_words[$x] = str_replace('%28', '(', $new_words[$x]);
	$new_words[$x] = str_replace('%27', "'", $new_words[$x]);
	$new_words[$x] = stripslashes($new_words[$x]);
	if ($page_types[$x] == 'Media Description') {
		
		$page_result = $wpdb->get_results('SELECT post_content FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');

		$updated_content = $new_words[$x];

		$wpdb->update($table_name, array('post_content' => $updated_content), array('ID' => $page_names[$x]));
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'WooCommerce Product Excerpt' || $page_types[$x] == 'WP eCommerce Product Excerpt') {
		
		$page_result = $wpdb->get_results('SELECT post_content, post_title, post_excerpt FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');

		$updated_content = $new_words[$x];

		$old_name = $page_result[0]->post_title;
		$wpdb->update($table_name, array('post_excerpt' => $updated_content), array('ID' => $page_names[$x]));
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Menu Item' || $page_types[$x] == 'Post Title' || $page_types[$x] == 'Page Title' || $page_types[$x] == 'Slider Title' || $page_types[$x] == 'WP eCommerce Product Name' || $page_types[$x] == 'WooCommerce Product Name') {
		
		$menu_result = $wpdb->get_results('SELECT post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$updated_content = $new_words[$x];

		$wpdb->update($table_name, array('post_title' => $updated_content), array('ID' => $page_names[$x]));
		$wpdb->update($words_table, array('page_name' => $updated_content), array('id' => $old_word_ids[$x])); //Update the title of the page/post/menu in the spellcheck database
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Author Nickname') {
		$author_result = $wpdb->get_results("SELECT * FROM $user_table WHERE user_id=" . $page_names[$x] . " AND meta_key='nickname'");
		$updated_content = $new_words[$x];
	
		$wpdb->update($user_table, array('meta_value' => $updated_content), array('user_id' => $page_names[$x], 'meta_key' => 'nickname'));
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Author First Name') {
		$author_result = $wpdb->get_results("SELECT * FROM $user_table WHERE user_id=" . $page_names[$x] . " AND meta_key='first_name'");
		$updated_content = $new_words[$x];
	
		$wpdb->update($user_table, array('meta_value' => $updated_content), array('user_id' => $page_names[$x], 'meta_key' => 'first_name'));
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Author Last Name') {
		$author_result = $wpdb->get_results("SELECT * FROM $user_table WHERE user_id=" . $page_names[$x] . " AND meta_key='last_name'");
		$updated_content = $new_words[$x];
	
		$wpdb->update($user_table, array('meta_value' => $updated_content), array('user_id' => $page_names[$x], 'meta_key' => 'last_name'));
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Author Biographical Information') {
		$author_result = $wpdb->get_results("SELECT * FROM $user_table WHERE user_id=" . $page_names[$x] . " AND meta_key='description'");
		$updated_content = $new_words[$x];
	
		$wpdb->update($user_table, array('meta_value' => $updated_content), array('user_id' => $page_names[$x], 'meta_key' => 'description'));
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Author SEO Title') {
		$author_result = $wpdb->get_results("SELECT * FROM $user_table WHERE user_id=" . $page_names[$x] . " AND meta_key='wpseo_title'");
		$updated_content = $new_words[$x];
		
		if (sizeof($author_result) <= 0) {
			$wpdb->insert($user_table, array('meta_value' => $updated_content, 'meta_key' => 'wpseo_title', 'user_id' => $page_names[$x]));
		} else {
			$wpdb->update($user_table, array('meta_value' => $updated_content), array('user_id' => $page_result[0]->post_author, 'meta_key' => 'wpseo_title'));
		}
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Author SEO Description') {
		$author_result = $wpdb->get_results("SELECT * FROM $user_table WHERE user_id=" . $page_names[$x] . " AND meta_key='wpseo_metadesc'");
		$updated_content = $new_words[$x];
	
		if (sizeof($author_result) <= 0) {
			$wpdb->insert($user_table, array('meta_value' => $updated_content, 'meta_key' => 'wpseo_metadesc', 'user_id' => $page_result[0]->post_author));
		} else {
			$wpdb->update($user_table, array('meta_value' => $updated_content), array('user_id' => $page_names[$x], 'meta_key' => 'wpseo_metadesc'));
		}
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Media Alternate Text') {
		
		$menu_result = $wpdb->get_results('SELECT ID, post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');
		$caption = get_post_meta($menu_result[0]->ID, '_wp_attachment_image_alt', true);
		$updated_content = $new_words[$x];

		update_post_meta($menu_result[0]->ID, '_wp_attachment_image_alt', $updated_content);
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Media Caption') {
		
		$page_result = $wpdb->get_results('SELECT post_excerpt, post_title FROM ' . $table_name . ' WHERE ID="' . $page_names[$x] . '"');

		$updated_content = $new_words[$x];

		$wpdb->update($table_name, array('post_excerpt' => $updated_content), array('ID' => $page_names[$x]));
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Tag Description') {
		
		$tag_result = $wpdb->get_results('SELECT description FROM ' . $taxonomy_table . ' WHERE term_id=' . $page_names[$x]);

		$updated_content = $new_words[$x];

		$wpdb->update($taxonomy_table, array('description' => $updated_content), array('term_id' => $page_names[$x]));
		$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
	} elseif ($page_types[$x] == 'Category Description') {
		
		$tag_result = $wpdb->get_results('SELECT description FROM ' . $taxonomy_table . ' WHERE term_id=' . $page_names[$x]);

		$updated_content = $new_words[$x];

		$wpdb->update($taxonomy_table, array('description' => $updated_content), array('term_id' => $page_names[$x]));
		$wpdb->delete($words_table, array('word' => $old_words[$x])); 
	} elseif ($page_types[$x] == 'SEO Page Title' || $page_types[$x] == 'SEO Post Title' || $page_types[$x] == 'SEO Media Title') {
		if (is_plugin_active('wordpress-seo/wp-seo.php')) {
			
			$wpdb->insert($meta_table, array('post_id' => $page_names[$x], 'meta_key' => "_yoast_wpseo_title", 
			'meta_value' => $new_words[$x]));
			
			$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
		} elseif (is_plugin_active('seo-ultimate/seo-ultimate.php')) {
			$wpdb->insert($meta_table, array('post_id' => $page_names[$x], 'meta_key' => "_su_title", 
			'meta_value' => $new_words[$x]));
			
			$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
		} elseif (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
			$wpdb->insert($meta_table, array('post_id' => $page_names[$x], 'meta_key' => "_aioseop_title", 
			'meta_value' => $new_words[$x]));
			
			$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
		} else {
			$seo_error = true;
		}
	} elseif ($page_types[$x] == 'SEO Page Description' || $page_types[$x] == 'SEO Post Description' || $page_types[$x] == 'SEO Media Description') {
		if (is_plugin_active('wordpress-seo/wp-seo.php')) {
			
			$wpdb->insert($meta_table, array('post_id' => $page_names[$x], 'meta_key' => "_yoast_wpseo_metadesc", 
			'meta_value' => $new_words[$x]));
			
			$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
		} elseif (is_plugin_active('seo-ultimate/seo-ultimate.php')) {
			$wpdb->insert($meta_table, array('post_id' => $page_names[$x], 'meta_key' => "_su_description", 
			'meta_value' => $new_words[$x]));
			
			$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
		} elseif (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
			$wpdb->insert($meta_table, array('post_id' => $page_names[$x], 'meta_key' => "_aioseop_description", 
			'meta_value' => $new_words[$x]));
			
			$wpdb->delete($words_table, array('id' => $old_word_ids[$x])); 
		} else {
			$seo_error = true;
		}
	}
	

	
	$page_url = get_permalink( $page_names[$x] );
	$page_title = get_the_title( $page_names[$x] );
	$current_time = date( 'l F d, g:i a' );
	$loc = dirname(__FILE__) . "/spellcheck.debug";
	$debug_file = fopen($loc, 'a');
	$debug_var = fwrite( $debug_file, " Empty Field | New Word: " . $new_words[$x] . " | Type: " . $page_types[$x] . " | Page Name: " . $page_title . " | Page URL: " . $page_url . " | Timestamp: " . $current_time . "\r\n\r\n" );
	fclose($debug_file);
	}
	
	$message = "";
	if ($seo_error) $message = "<div style='color: #FF0000'>SEO fields could not be updated because no active SEO plugin could be detected</div>";
	return "Empty Fields have been updated" . $message;
}

function admin_render() {
	
	

	ini_set('memory_limit','8192M'); 
	set_time_limit(600); 
	global $wpdb;
	global $ent_included;
	$table_name = $wpdb->prefix . "spellcheck_words";
	$empty_table = $wpdb->prefix . "spellcheck_empty";
	$options_table = $wpdb->prefix . "spellcheck_options";
	$post_table = $wpdb->prefix . "posts";
	
	$message = '';
	
	if ($_GET['submit'] == "Stop Scans") {
		$message = "All current spell check scans have been stopped.";
		wpsc_clear_scan();
	}
	if ($_GET['submit-empty'] == "Stop Scans") {
		$message = "All current empty field scans have been stopped.";
		wpsc_clear_empty_scan();
	}

	
	$settings = $wpdb->get_results('SELECT option_name, option_value FROM ' . $options_table);
	$check_pages = $settings[4]->option_value;
	$check_posts = $settings[5]->option_value;
	$check_menus = $settings[7]->option_value;
	$page_titles = $settings[12]->option_value;
	$post_titles = $settings[13]->option_value;
	$tags = $settings[14]->option_value;
	$categories = $settings[15]->option_value;
	$seo_desc = $settings[16]->option_value;
	$seo_titles = $settings[17]->option_value;
	$page_slugs = $settings[18]->option_value;
	$post_slugs = $settings[19]->option_value;
	$check_sliders = $settings[30]->option_value;
	$check_media = $settings[31]->option_value;
	$check_ecommerce = $settings[36]->option_value;
	$check_cf7 = $settings[37]->option_value;
	$check_tag_desc = $settings[38]->option_value;
	$check_tag_slug = $settings[39]->option_value;
	$check_cat_desc = $settings[40]->option_value;
	$check_cat_slug = $settings[41]->option_value;
	$check_custom = $settings[42]->option_value;
	$check_authors = $settings[44]->option_value;
	$check_authors_empty = $settings[46]->option_value;
	$check_authors_empty = $settings[47]->option_value;
	$check_menu_empty = $settings[48]->option_value;
	$check_page_titles_empty = $settings[49]->option_value;
	$check_post_titles_empty = $settings[50]->option_value;
	$check_tag_desc_empty = $settings[51]->option_value;
	$check_cat_desc_empty = $settings[52]->option_value;
	$check_page_seo_empty = $settings[53]->option_value;
	$check_post_seo_empty = $settings[54]->option_value;
	$check_media_seo_empty = $settings[55]->option_value;
	$check_media_empty = $settings[56]->option_value;
	$check_ecommerce_empty = $settings[57]->option_value;
	
	$postmeta_table = $wpdb->prefix . "postmeta";
	$post_table = $wpdb->prefix . "posts";
	$it_table = $wpdb->prefix . "huge_itslider_images";
	$smartslider_table = $wpdb->prefix . "nextend_smartslider_slides";
	
	
	
	
	

	$total_pages = $wpdb->get_var("SELECT COUNT(*) FROM $post_table WHERE post_type = 'page'");
	$total_posts = $wpdb->get_var("SELECT COUNT(*) FROM $post_table WHERE post_type = 'post'");
	$total_media = $wpdb->get_var("SELECT COUNT(*) FROM $post_table WHERE post_type = 'attachment'");
	
	
	
	$total_products = sizeof(get_posts(array('posts_per_page' => PHP_INT_MAX, 'post_type' => array('wpsc-product','product'), 'post_status' => array('publish', 'draft')))); 
	$total_cf7 = sizeof(get_posts(array('posts_per_page' => PHP_INT_MAX, 'post_type' => 'wpcf7_contact_form', 'post_status' => array('publish', 'draft')))); 
	$total_menu = sizeof($wpdb->get_results("SELECT * FROM $post_table WHERE post_type = 'nav_menu_item'")); 
	$total_authors = sizeof($wpdb->get_results("SELECT * FROM $post_table GROUP BY post_author"));
	$total_tags = sizeof(get_tags()); 
	$total_tag_desc = sizeof(get_tags()); 
	$total_tag_slug = sizeof(get_tags()); 
	$total_cat = sizeof(get_categories()); 
	$total_cat_desc = sizeof(get_categories()); 
	$total_cat_slug = sizeof(get_categories()); 
	$total_seo_title = sizeof($wpdb->get_results("SELECT * FROM $postmeta_table WHERE meta_key='_yoast_wpseo_title' OR meta_key='_aioseop_title' OR meta_key='_su_title'")); 
	$total_seo_desc = sizeof($wpdb->get_results("SELECT * FROM $postmeta_table WHERE meta_key='_yoast_wpseo_metadesc' OR meta_key='_aioseop_description' OR meta_key='_su_description'")); 
	
	
	
	
	
	
	
	$total_generic_slider = sizeof(get_pages(array('number' => PHP_INT_MAX, 'hierarchical' => 0, 'post_type' => 'slider', 'post_status' => array('publish', 'draft'))));
	$total_sliders = $total_huge_it + $total_smartslider + $total_generic_slider;
	
	$page_count = $total_pages;
	if (!$ent_included) {
		if ($total_pages > 1000) $total_pages = 1000;
		if ($total_posts > 1000) $total_posts = 1000;
		if ($total_media > 1000) $total_posts = 1000;
		if ($total_seo_title > 1000) $total_seo_title = 1000;
		if ($total_seo_desc > 1000) $total_seo_desc = 1000;
	}
	
	$total_other = $total_men + $total_authors + $total_tags + $total_tag_desc + $total_tag_slug + $total_cat + $total_cat_desc + $total_cat_slug + $total_seo_title + $total_seo_desc;
	
	$total_page_slugs = $total_pages; 
	$total_post_slugs = $total_posts; 
	$total_page_title = $total_pages; 
	$total_post_title = $total_posts; 
	
	$estimated_time = intval((($total_pages + $total_posts) / 3.5) + 3);
	$scan_message = '';
	
	$scan = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='scan_in_progress';");
	$empty_scan = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='empty_scan_in_progress';");
	
	$check_scan = wpsc_check_scan_progress();
	if ($check_scan && $_GET['wpsc-script'] != 'noscript') {
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		sleep(3);
	}
	$check_empty = wpsc_check_empty_scan_progress();
	if ($check_empty && $_GET['wpsc-script'] != 'noscript') {
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		sleep(3);
	}
	
	
	
	
	

	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Pages') {
		$estimated_time = 5 + intval($total_pages / 3.5);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Page Content</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		clear_results();
		$rng_seed = rand(0,999999999);
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'page_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Page Content'), array('option_name' => 'last_scan_type'));
		sleep(3);
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckpages_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckpages', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Posts') {
		$estimated_time = 5 + intval($total_posts / 3.5);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Post Content</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'post_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Post Content'), array('option_name' => 'last_scan_type'));
		sleep(3);
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckposts_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckposts', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Authors') {
		$estimated_time = 5 + intval($total_authors / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Authors</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'author_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Authors'), array('option_name' => 'last_scan_type'));
		sleep(3);
		wp_schedule_single_event(time(), 'admincheckauthors', array ($rng_seed));
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Menus') {
		$estimated_time = 5 + intval($total_menu / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Menus</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'menu_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Menus'), array('option_name' => 'last_scan_type'));
		sleep(3);
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckmenus_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckmenus', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Page Titles') {
		$estimated_time = 5 + intval($total_page_title / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Page Titles</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'page_title_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Page Titles'), array('option_name' => 'last_scan_type'));
		sleep(3);
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckpagetitles_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckpagetitles', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Post Titles') {
		$estimated_time = 5 + intval($total_post_title / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Post Titles</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'post_title_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Post Titles'), array('option_name' => 'last_scan_type'));
		sleep(3);
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckposttitles_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckposttitles', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Tags') {
		$estimated_time = 5 + intval($total_tags / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Tags</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'tag_title_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Tag Titles'), array('option_name' => 'last_scan_type'));
		sleep(3);
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckposttags_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckposttags', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Tag Descriptions') {
		$estimated_time = 5 + intval($total_tag_desc / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Tag Descriptions</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'tag_desc_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Tag Descriptions'), array('option_name' => 'last_scan_type'));
		sleep(3);
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckposttagsdesc_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckposttagsdesc', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Tag Slugs') {
		$estimated_time = 5 + intval($total_tag_slug / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Tag Slugs</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'tag_slug_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Tag Slugs'), array('option_name' => 'last_scan_type'));
		sleep(3);
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckposttagsslugs_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckposttagsslugs', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Categories') {
		$estimated_time = 5 + intval($total_cat / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Categories</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'cat_title_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Category Titles'), array('option_name' => 'last_scan_type'));
		sleep(3);
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckcategories_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckcategories', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Category Descriptions') {
		$estimated_time = 5 + intval($total_cat_desc / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Category Descriptions</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'cat_desc_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Category Descriptions'), array('option_name' => 'last_scan_type'));
		sleep(3);
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckcategoriesdesc_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckcategoriesdesc', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Category Slugs') {
		$estimated_time = 5 + intval($total_cat_slug / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Category Slugs</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'cat_slug_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Category Slugs'), array('option_name' => 'last_scan_type'));
		sleep(3);
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckcategoriesslugs_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckcategoriesslugs', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'SEO Descriptions') {
		$estimated_time = 5 + intval($total_seo_desc / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">SEO Descriptions</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'seo_desc_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'SEO Descriptions'), array('option_name' => 'last_scan_type'));
		sleep(3);
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckseodesc_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckseodesc', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'SEO Titles') {
		$estimated_time = 5 + intval($total_seo_title / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">SEO Titles</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'seo_title_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'SEO Titles'), array('option_name' => 'last_scan_type'));
		sleep(3);
		
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckseotitles_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckseotitles', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Page Slugs') {
		$estimated_time = 5 + intval($total_page_slugs / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Page Slugs</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'page_slug_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Page Slugs'), array('option_name' => 'last_scan_type'));
		sleep(3);
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckpageslugs_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckpageslugs', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Post Slugs') {
		$estimated_time = 5 + intval($total_post_slugs / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Post Slugs</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'post_slug_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Post Slugs'), array('option_name' => 'last_scan_type'));
		sleep(3);
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckpostslugs_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckpostslugs', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Sliders') {
		$estimated_time = 5 + intval($total_sliders / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Sliders</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'slider_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Sliders'), array('option_name' => 'last_scan_type'));
		sleep(3);
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'adminchecksliders_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'adminchecksliders_pro', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Media Files') {
		$estimated_time = 5 + intval($total_media / 3.5);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Media Files</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'media_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Media Files'), array('option_name' => 'last_scan_type'));
		sleep(3);
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckmedia_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckmedia_pro', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'WooCommerce and WP-eCommerce Products') {
		$estimated_time = 5 + intval($total_products / 3.5);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">eCommerce Products</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'ecommerce_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'eCommerce Products'), array('option_name' => 'last_scan_type'));
		sleep(3);
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckecommerce_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckecommerce', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Contact Form 7') {
		$estimated_time = 5 + intval($total_cf7 / 100);
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(0, 150, 255); font-weight: bold;">Contact Form 7</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'cf7_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Contact Form 7'), array('option_name' => 'last_scan_type'));
		sleep(3);
		
		wp_schedule_single_event(time(), 'admincheckcf7', array ($rng_seed, false));
	}
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Entire Site') {
		$estimated_time = intval((($total_pages + $total_posts) / 3.5) + 3);
		
		$scan_message = '';
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for the <span style="color: rgb(0, 150, 255); font-weight: bold;">Entire Site</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_results("full");
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'results-ajax', plugin_dir_url( __FILE__ ) . '/ajax.js', array('jquery') );
		wp_localize_script( 'results-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Entire Site'), array('option_name' => 'last_scan_type'));
		sleep(3);
		
		set_scan_in_progress($rng_seed);
		wp_schedule_single_event(time(), 'adminscansite', array($rng_seed));
	}
	
	
	if ($_GET['action'] == 'check' && $_GET['submit-empty'] == 'Menus') {
		$estimated_time = 5 + intval($total_menu / 100);
		$empty_scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(115, 1, 154); font-weight: bold;">Menus</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_empty_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'wpsc_scan_tab' => 'empty' ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_menu_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Menus'), array('option_name' => 'last_empty_type'));
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckmenusempty_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckmenusempty', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit-empty'] == 'Page Titles') {
		$estimated_time = 5 + intval($total_page_title / 100);
		$empty_scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(115, 1, 154); font-weight: bold;">Page Titles</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_empty_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'wpsc_scan_tab' => 'empty' ) );
		
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_page_title_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Page Titles'), array('option_name' => 'last_empty_type'));
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckpagetitlesempty_ent', array ($rng_seed ));
		} elseif ($pro_included) {
		wp_schedule_single_event(time(), 'admincheckpagetitlesempty', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckpagetitlesemptybase', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit-empty'] == 'Post Titles') {
		$estimated_time = 5 + intval($total_post_title / 100);
		$empty_scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(115, 1, 154); font-weight: bold;">Post Titles</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_empty_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'wpsc_scan_tab' => 'empty' ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_post_title_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Post Titles'), array('option_name' => 'last_empty_type'));
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckposttitlesempty_ent', array ($rng_seed ));
		} elseif ($pro_included) {
		wp_schedule_single_event(time(), 'admincheckposttitlesempty', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckposttitlesemptybase', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit-empty'] == 'Tag Descriptions') {
		$estimated_time = 5 + intval($total_tag_desc / 100);
		$empty_scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(115, 1, 154); font-weight: bold;">Tag Descriptions</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_empty_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'wpsc_scan_tab' => 'empty' ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_tag_desc_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Tag Descriptions'), array('option_name' => 'last_empty_type'));
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckposttagsdescempty_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckposttagsdescempty', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit-empty'] == 'Category Descriptions') {
		$estimated_time = 5 + intval($total_cat_desc / 100);
		$empty_scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(115, 1, 154); font-weight: bold;">Category Descriptions</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_empty_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'wpsc_scan_tab' => 'empty' ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_cat_desc_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Category Descriptions'), array('option_name' => 'last_empty_type'));
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckcategoriesdescempty_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckcategoriesdescempty', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit-empty'] == 'Media Files') {
		$estimated_time = 5 + intval($total_media / 3.5);
		$empty_scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(115, 1, 154); font-weight: bold;">Media Files</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_empty_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'wpsc_scan_tab' => 'empty' ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_media_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Media Files'), array('option_name' => 'last_empty_type'));
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckmediaempty_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckmediaempty_pro', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit-empty'] == 'WooCommerce and WP-eCommerce Products') {
		$estimated_time = 5 + intval($total_products / 3.5);
		$empty_scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(115, 1, 154); font-weight: bold;">eCommerce Products</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_empty_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'wpsc_scan_tab' => 'empty' ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_ecommerce_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'eCommerce Products'), array('option_name' => 'last_empty_type'));
		
		sleep(3);
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'wpsc_scan_tab' => 'empty') );
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckecommerceempty_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckecommerceempty', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit-empty'] == 'Authors') {
		$estimated_time = 5 + intval($total_authors / 100);
		$empty_scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(115, 1, 154); font-weight: bold;">Authors</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_empty_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'wpsc_scan_tab' => 'empty' ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_author_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Authors'), array('option_name' => 'last_empty_type'));
		wp_schedule_single_event(time(), 'admincheckauthorsempty', array ($rng_seed ));
	}
	if ($_GET['action'] == 'check' && $_GET['submit-empty'] == 'Page SEO') {
		$estimated_time = 5 + intval($total_seo_desc / 100);
		$empty_scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(115, 1, 154); font-weight: bold;">Page SEO</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_empty_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'wpsc_scan_tab' => 'empty' ) );
		
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_page_seo_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Page SEO'), array('option_name' => 'last_empty_type'));
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckpageseoempty_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckpageseoempty', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit-empty'] == 'Post SEO') {
		$estimated_time = 5 + intval($total_seo_title / 100);
		$empty_scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(115, 1, 154); font-weight: bold;">Post SEO</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_empty_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'wpsc_scan_tab' => 'empty' ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_post_seo_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Post SEO'), array('option_name' => 'last_empty_type'));
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckpostseoempty_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckpostseoempty', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit-empty'] == 'Media Files SEO') {
		$estimated_time = 5 + intval($total_seo_title / 100);
		$empty_scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for <span style="color: rgb(115, 1, 154); font-weight: bold;">Media Files SEO</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		clear_empty_results();
		$rng_seed = rand(0,999999999);
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'wpsc_scan_tab' => 'empty' ) );
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_media_seo_sip'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Media Files SEO'), array('option_name' => 'last_empty_type'));
		
		if ($ent_included) { 
		wp_schedule_single_event(time(), 'admincheckmediaseoempty_ent', array ($rng_seed ));
		} else {
		wp_schedule_single_event(time(), 'admincheckmediaseoempty', array ($rng_seed ));
		}
	}
	if ($_GET['action'] == 'check' && $_GET['submit-empty'] == 'Entire Site') {
		$estimated_time = intval((($total_pages + $total_posts) / 3.5) + 3);
	$empty_scan_message = '';
		$empty_scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> Scan has been started for the <span style="color: rgb(115, 1, 154); font-weight: bold;">Entire Site</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
		
		clear_results_empty("full");
		$rng_seed = rand(0,999999999);
		set_empty_scan_in_progress($rng_seed);
		$wpdb->update($options_table, array('option_value' => 'true'), array('option_name' => 'empty_scan_in_progress'));
		$wpdb->update($options_table, array('option_value' => time()), array('option_name' => 'last_scan_date'));
		$wpdb->update($options_table, array('option_value' => 'Entire Site'), array('option_name' => 'last_empty_type'));
		wp_enqueue_script( 'emptyresults-ajax', plugin_dir_url( __FILE__ ) . '/empty-ajax.js', array('jquery') );
		wp_localize_script( 'emptyresults-ajax', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'wpsc_scan_tab' => 'empty' ) );
		scan_site_empty($rng_seed);
	}
	
	if ($_GET['action'] == 'check' && $_GET['submit'] == 'Clear Results') {
		$message = 'All spell check results have been cleared';
		clear_results("full");
	}
	if ($_GET['action'] == 'check' && $_GET['submit-empty'] == 'Clear Results') {
		$message = 'All empty field results have been cleared';
		clear_empty_results("full");
	}
	if ($_GET['old_words'] != '' && $_GET['new_words'] != '' && $_GET['page_types'] != '' && $_GET['old_word_ids'] != '')  {
		$message = update_word_admin($_GET['old_words'], $_GET['new_words'], $_GET['page_names'], $_GET['page_types'], $_GET['old_word_ids']);
	} elseif ($_GET['new_words'] != '' && $_GET['page_types'] != '' && $_GET['old_word_ids'] != '') {
		$message = update_empty_admin($_GET['new_words'], $_GET['page_names'], $_GET['page_types'], $_GET['old_word_ids']);
	}
	
	if ($_GET['ignore_word'] != '' && $_GET['wpsc-scan-tab'] != 'empty') {
		$ignore_message = ignore_word($_GET['ignore_word']); 
	} elseif ($_GET['ignore_word'] != '' && $_GET['wpsc-scan-tab'] == 'empty') {
		$ignore_message = ignore_word_empty($_GET['ignore_word']); 
	}
	if ($_GET['add_word'] != '')
		$dict_message = add_to_dictionary($_GET['add_word']); 
		
	
	
	
	
	
		
	$word_count = $wpdb->get_var ( "SELECT COUNT(*) FROM $table_name WHERE ignore_word='false'" );
	$empty_count = $wpdb->get_var ( "SELECT COUNT(*) FROM $empty_table WHERE ignore_word='false'" );

		
	$list_table = new sc_table();
	$list_table->prepare_items();
	
	$empty_table = new sc_table();
	$empty_table->prepare_empty_items();
	
	$path = plugin_dir_path( __FILE__ ) . '../premium-functions.php';
	global $pro_included;
	
	
	
	
	
	

	
	$pro_words = 0;
	$empty_words = 0;
	if (!$pro_included && !$ent_included) {
		$pro_word_count = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='pro_word_count';");
		$pro_words = $pro_word_count[0]->option_value;
		$empty_word_count = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='pro_empty_count';");
		$empty_words = $empty_word_count[0]->option_value;
	}
	$total_word_count = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='total_word_count';");
	$literacy_factor = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='literary_factor';");
	$literacy_factor = $literacy_factor[0]->option_value;
	
	$empty_factor = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='empty_factor';");
	$empty_factor = $empty_factor[0]->option_value;
	
	$empty_results = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='empty_checked';");
	$empty_field_count = $empty_results[0]->option_value;
	
	$cron_tasks = _get_cron_array();
	$scan_progress = false;
	$scan_site = 0;
	
	foreach ($cron_tasks as $task) {
		if (key($task) == 'adminscansite') {
			$scan_site++;
		} elseif (substr(key($task), 0, strlen('admincheck')) === 'admincheck') {
			$scan_progress = true;
		}
	}
	if ($scan_site >= 2) $scan_progress = true;
	
	
	
	
	
	
	
	$scanning = $scan;
	$scan_progress = wpsc_check_scan_progress();
	if ($scan_progress && $scan_message == '') {
		$last_type = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='last_scan_type'");
		$scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> A scan is currently in progress for <span class="sc-message" style="color: rgb(0, 150, 255); font-weight: bold;">' . $last_type[0]->option_value . '</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
	} elseif ($scanning[0]->option_value == "error" && $scan_message == '' && !$scan_progress) {
		$scan_message = "<span style='color:red;'>No scan currently running. The previous scan was unable to finish scanning</style>";
	} elseif ($scan_message == '') {
		$scan_message = "No scan currently running";
	}
	
	$empty_scan_progress = wpsc_check_empty_scan_progress();
	if ($empty_scan_progress && $empty_scan_message == '') {
		$last_type = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='last_empty_type'");
		$empty_scan_message = '<img src="'. plugin_dir_url( __FILE__ ) . 'images/loading.gif" alt="Scan in Progress" /> A scan is currently in progress for <span class="sc-message" style="color: rgb(0, 150, 255); font-weight: bold;">' . $last_type[0]->option_value . '</span>. Estimated time for completion is '.$estimated_time.' Seconds. <a href="/wp-admin/admin.php?page=wp-spellcheck.php">Click here</a> to see scan results. <span class="wpsc-mouseover-button-refresh" style="border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;">?</span><span class="wpsc-mouseover-text-refresh">The page will automatically refresh when the scan is finished. You do not need to remain on this page for the scan to run.<br /><br />Time estimate may vary based on server strength.</span>';
	} elseif ($empty_scan_message == '') {
		$empty_scan_message = "No scan currently running";
	}
	
	$time_of_scan = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='last_scan_finished';");
	$time_of_empty = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='empty_start_time';");
	if ($time_of_scan[0]->option_value == "0") {
		$time_of_scan = "0 Minutes";
	} else {
		$time_of_scan = $time_of_scan[0]->option_value;
		if ($time_of_scan == '') $time_of_scan = "0 Seconds";
	}
	
	if ($time_of_empty[0]->option_value == "0") {
		$time_of_empty = "0 Minutes";
	} else {
		$time_of_empty = $time_of_empty[0]->option_value;
		if ($time_of_empty == '') $time_of_empty = "0 Seconds";
	}
	
	$options_table = $wpdb->prefix . "spellcheck_options";
	
	$scan_type = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='last_scan_type'");
	$empty_type = $wpdb->get_results("SELECT option_value FROM $options_table WHERE option_name='last_empty_type'");

	$post_types = get_post_types();
	$post_type_list = "";
	foreach ($post_types as $type) {
		if ($type != 'revision' && $type != 'page' && $type != 'optionsframework' && $type != 'attachment' && $type != 'leadpages_post' && $type != 'slider')
			$post_type_list .= "OR post_type = '" . $type . "'";
	}
	
	
	
	
	
	

	$post_count = $wpdb->get_var("SELECT COUNT(*) FROM $post_table WHERE post_type = 'post'" . $post_type_list);
	$media_count = $total_media;

	
	
	
	$page_scan = $wpdb->Get_results("SELECT option_value FROM $options_table WHERE option_name='page_count';");
	$post_scan = $wpdb->Get_results("SELECT option_value FROM $options_table WHERE option_name='post_count';");
	$media_scan = $wpdb->Get_results("SELECT option_value FROM $options_table WHERE option_name='media_count';");
	
	$empty_page_scan = $wpdb->Get_results("SELECT option_value FROM $options_table WHERE option_name='empty_page_count';");
	$empty_post_scan = $wpdb->Get_results("SELECT option_value FROM $options_table WHERE option_name='empty_post_count';");
	$empty_media_scan = $wpdb->Get_results("SELECT option_value FROM $options_table WHERE option_name='empty_media_count';");
	$options_list = $wpdb->Get_results("SELECT option_value FROM $options_table;");
	
	$total_words = $options_list[22]->option_value;
	
	wp_enqueue_script('results-nav', plugin_dir_url( __FILE__ ) . 'results-nav.js');
	
	
	
	
	
	
	?>
		<?php show_feature_window(); ?>
		<?php check_install_notice(); ?>
		
	<style>.search-box input[type=submit] { color: white; background-color: #00A0D2; border-color: #0073AA; } #cb-select-all-1,#cb-select-all-2 { display: none; } td.word { font-size: 15px; } p.submit { display: inline-block; margin-left: 10px; } h3.sc-message { width: 49%; display: inline-block; } .wpsc-mouseover-text-page,.wpsc-mouseover-text-post,.wpsc-mouseover-text-refresh { color: black; font-size: 12px; width: 225px; display: inline-block; position: absolute; margin: -13px 0 0 -270px; padding: 3px; border: 1px solid black; border-radius: 10px; opacity: 0; background: white; z-index: -100; } .wpsc-row .row-actions, .wpsc-row .row-actions *{ visibility: visible!important; left: 0!important; } #current-page-selector { width: 12%; } .hidden { display: none; } .wpsc-scan-nav-bar { border-bottom: 1px solid #BBB; margin-botton: 15px; } .wpsc-scan-nav-bar a { text-decoration: none; margin: 5px 5px -1px 5px; padding: 8px; border: 1px solid #BBB; display: inline-block; font-weight: bold; color: black; font-size: 14px; } .wpsc-scan-nav-bar a.selected { border-bottom: 1px solid white; background: white; } #wpsc-empty-fields-tab .button-primary { background: #73019a; border-color: #51006E; text-shadow: 1px 1px #51006d; box-shadow: 0 1px 0 #51006d; } #wpsc-empty-fields-tab .button-primary:hover { background: #9100c3 } #wpsc-empty-fields-tab .button-primary:active { background: #51006d; }.wpsc-scan-buttons input#submit:active { margin-top: -7px; }</style>
<div id="wpsc-dialog-confirm" title="Are you sure?" style="display: none;">
  <p>Would you like to Proceed with the changes?</p>
</div>
		<div class="wrap wpsc-table">
			<h2><a href="admin.php?page=wp-spellcheck.php"><img src="<?php echo plugin_dir_url( __FILE__ ) . '../images/logo.png'; ?>" alt="WP Spell Check" /></a> <span style="position: relative; top: -15px;">Scan Results</span></h2>
			<div class="wpsc-scan-nav-bar">
				<a href="#scan-results" id="wpsc-scan-results" <?php if ($_GET['wpsc-scan-tab'] != 'empty') echo 'class="selected"';?> name="wpsc-scan-results">Spelling Errors</a>
				<a href="#empty-fields" id="wpsc-empty-fields" <?php if ($_GET['wpsc-scan-tab'] == 'empty') echo 'class="selected"';?> name="wpsc-empty-fields">Empty Fields</a>
			</div>
			<div id="wpsc-scan-results-tab" <?php if ($_GET['wpsc-scan-tab'] == 'empty') echo 'class="hidden"';?>>
			<form action="<?php echo admin_url('admin.php'); ?>" method='GET'>
				<div style="background: white; padding: 5px;">
				<input type="hidden" name="page" value="wp-spellcheck.php">
				<input type="hidden" name="action" value="check">
				<?php echo "<h3 class='sc-message'style='color: rgb(0, 150, 255); font-size: 1.4em;'>Website Literacy Factor: " . $literacy_factor . "%"; ?>
				<?php echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>Errors found on <span style='color: rgb(0, 150, 255); font-weight: bold;'>".$scan_type[0]->option_value."</span>: {$word_count}</h3>"; ?>
				<?php echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>Pages scanned: " . $page_scan[0]->option_value . "/" . $page_count;
					if ($pro_included && $page_count >= 1000) { echo "<span class='wpsc-mouseover-button-page' style='border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;'>?<span class='wpsc-mouseover-text-page'>Our pro version scans up to 1000 pages.<br /><a href='https://www.wpspellcheck.com/purchase-options' target='_blank'>Click here</a> to upgrade to enterprise</span></span>";
					} elseif (!$pro_included && !$ent_included && $page_count >= 500) { echo "<span class='wpsc-mouseover-button-page' style='border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;'>?<span class='wpsc-mouseover-text-page'>Our free version scans up to 500 pages.<br /><a href='https://www.wpspellcheck.com/purchase-options' target='_blank'>Click here</a> to upgrade to pro</span></span>"; }
					echo "</h3>"; ?>
				<?php echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>Posts scanned: " . $post_scan[0]->option_value . "/" . $post_count;
				if ($pro_included && $post_count >= 1000) { echo "<span class='wpsc-mouseover-button-post' style='border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;'>?<span class='wpsc-mouseover-text-post'>Our pro version scans up to 1000 posts.<br /><a href='https://www.wpspellcheck.com/purchase-options' target='_blank'>Click here</a> to upgrade to enterprise</span></span>";
				} elseif (!$pro_included && !$ent_included && $post_count >= 500) { echo "<span class='wpsc-mouseover-button-post' style='border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;'>?<span class='wpsc-mouseover-text-post'>Our free version scans up to 500 posts.<br /><a href='https://www.wpspellcheck.com/purchase-options' target='_blank'>Click here</a> to upgrade to pro</span></span>"; }
				echo "</h3>"; ?>
				<?php if ($pro_included || $ent_included) { echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>Media files scanned: " . $media_scan[0]->option_value . "/" . $media_count . "</h3>"; } ?>
				<?php echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>Words scanned on <span style='color: rgb(0, 150, 255); font-weight: bold;'>".$scan_type[0]->option_value."</span>: $total_words</h3>"; ?>
				<?php echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>Last scan took $time_of_scan</h3>"; ?>
				<?php echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>$scan_message</h3><br />"; ?>
				<?php if (!$pro_included && !$ent_included) echo "<h3 class='sc-message' style='color: rgb(225, 0, 0);'>$pro_words spelling errors have been found on other parts of your website. <a target='_blank' href='https://www.wpspellcheck.com/purchase-options' target='_blank'>Upgrade to pro</a> to fix them now</h3><br />"; ?>
				<?php if ($pro_included && ($post_count > 1000 || $page_count > 1000)) echo "<h3 class='sc-message' style='color: rgb(225, 0, 0);'>You have more than 1000 Pages/Posts. <a href='https://www.wpspellcheck.com/purchase-options'>Upgrade to Enterprise</a> to scan all of your website.</h3>" ?>
				</div>
				<div class="wpsc-scan-buttons">
				<h3 style="display: inline-block;">Scan:</h3>
				<p class="submit"><input style="background-color: #ffb01f; border-color: #ffb01f; box-shadow: 0px 1px 0px #ffb01f; text-shadow: 1px 1px 1px #ffb01f; font-weight: bold;" type="submit" name="submit" id="submit" class="button button-primary" value="Entire Site" <?php if ($checked_pages == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Pages" <?php if ($check_pages == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Posts" <?php if ($check_posts == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<?php if ($pro_included || $ent_included) { ?>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="SEO Titles" <?php if ($seo_titles == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="SEO Descriptions" <?php if ($seo_desc == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Media Files" <?php if ($check_media == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<?php } ?>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Authors" <?php if ($check_authors == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Contact Form 7" <?php if ($check_cf7 == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>	
				<?php if ($pro_included || $ent_included) { ?>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Menus" <?php if ($check_menus == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Page Titles" <?php if ($page_titles == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Post Titles" <?php if ($post_titles == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Tags" <?php if ($tags == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Tag Descriptions" <?php if ($check_tag_desc == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Tag Slugs" <?php if ($check_tag_slug == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Categories" <?php if ($categories == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>	
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Category Descriptions" <?php if ($check_cat_desc == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>	
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Category Slugs" <?php if ($check_cat_slug == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>	
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Page Slugs" <?php if ($page_slugs == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Post Slugs" <?php if ($post_slugs == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Sliders" <?php if ($check_sliders == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="WooCommerce and WP-eCommerce Products" <?php if ($check_ecommerce == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<?php } ?>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Clear Results"></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" style="background-color: red;" value="See Scan Results"></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" style="background-color: red;" value="Stop Scans"></p>
				<!--<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" style="background-color: red;" value="Create Pages"></p>-->
</div>
			</form>
			<div style="float: right; width:23%; margin-left: 2%;">
				<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
				<a href="https://www.wpspellcheck.com/" target="_blank"><img src="<?php echo plugin_dir_url( __FILE__ ) . '../images/logo.png'; ?>" alt="WP Spell Check" /></a>
<script type="text/javascript">
//<![CDATA[
if (typeof newsletter_check !== "function") {
window.newsletter_check = function (f) {
    var re = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-]{1,})+\.)+([a-zA-Z0-9]{2,})+$/;
    if (!re.test(f.elements["ne"].value)) {
        alert("The email is not correct");
        return false;
    }
    for (var i=1; i<20; i++) {
    if (f.elements["np" + i] && f.elements["np" + i].value == "") {
        alert("");
        return false;
    }
    }
    if (f.elements["ny"] && !f.elements["ny"].checked) {
        alert("You must accept the privacy statement");
        return false;
    }
    return true;
}
}
//]]>
</script>

<div style="padding: 5px 5px 10px 5px; border: 3px solid #73019A; border-radius: 5px; background: white;">
<h2>Get on Our Priority Notification List</h2>
<form method="post" action="https://www.wpspellcheck.com/?na=s" onsubmit="return newsletter_check(this)">

<table cellspacing="0" cellpadding="3" border="0">

<!-- email -->
<tr>
	<th>Email</th>
	<td align="left"><input class="newsletter-email" type="email" name="ne" style="width: 100%;" size="30" required></td>
</tr>

<tr>
	<td colspan="2" class="newsletter-td-submit">
		<input class="newsletter-submit" type="submit" value="Sign me up"/>
	</td>
</tr>

</table>
</form>
</div>
<hr>
<div style="padding: 5px 5px 10px 5px; border: 3px solid #0096FF; border-radius: 5px; background: white;">
				<a href="https://www.wpspellcheck.com/tutorials" target="_blank"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/wp-spellcheck-tutorials.jpg'; ?>" style="max-width: 99%;" alt="Watch WP Spell Check Tutorials" /></a>
</div>
<hr>
<div style="padding: 5px 5px 10px 5px; border: 3px solid #D60000; border-radius: 5px; background: white; text-align: center;">
				<h2>Follow us on Facebook</h2>
				<div class="fb-page" data-href="https://www.facebook.com/wpspellcheck/" data-width="180px" data-small-header="true" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><blockquote cite="https://www.facebook.com/wpspellcheck/" class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/wpspellcheck/">WP Spell Check</a></blockquote></div>
</div>
<hr>
<div class="newsletter newsletter-subscription" style="padding: 5px 5px 10px 5px; border: 3px solid #008200; border-radius: 5px; background: white;">
<div class="wpsc-sidebar" style="margin-bottom: 15px;"><h2>Help to improve this plugin!</h2><center>Enjoyed this plugin? You can help by <a class="review-button" href="https://en-ca.wordpress.org/plugins/wp-spell-check/" target="_blank">rating this plugin on wordpress.org</a></center></div>
</div>
<hr>
<?php if (!$ent_included && !$pro_included) { ?>
<div style="padding: 5px 5px 10px 5px; border: 1px solid #00BBC1; border-radius: 5px; background: white;">
				<div class="wpsc-sidebars" style="margin-bottom: 15px;"><h2>Want your entire website scanned?</h2>
					<p><a href="https://www.wpspellcheck.com/purchase-options/" target="_blank">Upgrade to WP Spell Check Pro<br />
					See Benefits and Features here »</a></p>
				</div>
</div>
<?php } ?>
			</div>
			<?php if(($message != '' || $ignore_message[0] != '' || $dict_message[0] != '') && $_GET['wpsc-scan-tab'] != 'empty') { ?>
				<div style="text-align: center; background-color: white; padding: 5px; margin: 15px 0; width: 74%;">
					<?php if($message != '') echo "<div class='wpsc-message' style='font-size: 1.3em; color: rgb(0, 115, 0); font-weight: bold;'>" . $message . "</div>"; ?>
					<?php if($ignore_message[0] != '') echo "<div class='wpsc-message' style='font-size: 1.3em; color: rgb(0, 115, 0); font-weight: bold;'>" . $ignore_message[0] . "</div>"; ?>
					<?php if($dict_message[0] != '') echo "<div class='wpsc-message' style='font-size: 1.3em; color: rgb(0, 115, 0); font-weight: bold;'>" . $dict_message[0] . "</div>"; ?>
				</div>
				<?php } ?>
			<form id="words-list" method="get" style="width: 75%; float: left;">
				<p class="search-box" style="position: relative; margin-top: 4em;">
					<label class="screen-reader-text" for="search_id-search-input">search:</label>
					<input type="search" id="search_id-search-input-top" name="s-top" value="">
					<input type="submit" id="search-submit-top" class="button" value="search">
				</p>
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<input name="wpsc-edit-update-button" class="wpsc-edit-update-button" type="submit" value="Save all Changes" class="button button-primary" style="width: 33%; margin-left: 33%; display: block; background: #008200; border-color: #005200; color: white; font-weight: bold;"/>
				<?php 
	
	
	
	 ?>
				<?php $list_table->display() ?>
				<?php 
	
	
	
	 ?>
				<p class="search-box">
					<label class="screen-reader-text" for="search_id-search-input">search:</label>
					<input type="search" id="search_id-search-input" name="s" value="">
					<input type="submit" id="search-submit" class="button" value="search">
				</p>
				<input name="wpsc-edit-update-buttom" class="wpsc-edit-update-button" type="submit" value="Save all Changes" class="button button-primary" style="width: 33%; margin-left: auto; margin-right: auto; display: block;  background: #008200; border-color: #005200; color: white; font-weight: bold;"/>
			</form>
		</div>
		<!-- Empty Fields  Tab -->
		<div id="wpsc-empty-fields-tab" <?php if ($_GET['wpsc-scan-tab'] != 'empty') echo 'class="hidden"';?>>
			<form action="<?php echo admin_url('admin.php'); ?>" method='GET'>
				<div style="background: white; padding: 5px;">
				<input type="hidden" name="page" value="wp-spellcheck.php">
				<input type="hidden" name="action" value="check">
				<input type="hidden" name="wpsc-scan-tab" value="empty">
				<?php echo "<h3 class='sc-message'style='color: rgb(115, 1, 154); font-size: 1.4em;'>Website Empty Fields Factor: " . $empty_factor . "%"; ?>
				<?php echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>Empty fields found on <span style='color: rgb(115, 1, 154); font-weight: bold;'>".$empty_type[0]->option_value."</span>: {$empty_count}</h3>"; ?>
				<?php echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>Pages scanned: " . $empty_page_scan[0]->option_value . "/" . $page_count;
					if ($pro_included && $page_count >= 1000) { echo "<span class='wpsc-mouseover-button-page' style='border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;'>?<span class='wpsc-mouseover-text-page'>Our pro version scans up to 1000 pages.<br /><a href='https://www.wpspellcheck.com/purchase-options' target='_blank'>Click here</a> to upgrade to enterprise</span></span>";
					} elseif (!$pro_included && !$ent_included && sizeof($page_count) >= 500) { echo "<span class='wpsc-mouseover-button-page' style='border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;'>?<span class='wpsc-mouseover-text-page'>Our free version scans up to 500 pages.<br /><a href='https://www.wpspellcheck.com/purchase-options' target='_blank'>Click here</a> to upgrade to pro</span></span>"; }
					echo "</h3>"; ?>
				<?php echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>Posts scanned: " . $empty_post_scan[0]->option_value . "/" . $post_count;
				if ($pro_included && $post_count >= 1000) { echo "<span class='wpsc-mouseover-button-post' style='border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;'>?<span class='wpsc-mouseover-text-post'>Our pro version scans up to 1000 posts.<br /><a href='https://www.wpspellcheck.com/purchase-options' target='_blank'>Click here</a> to upgrade to enterprise</span></span>";
				} elseif (!$pro_included && !$ent_included && $post_count >= 500) { echo "<span class='wpsc-mouseover-button-post' style='border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;'>?<span class='wpsc-mouseover-text-post'>Our free version scans up to 500 posts.<br /><a href='https://www.wpspellcheck.com/purchase-options' target='_blank'>Click here</a> to upgrade to pro</span></span>"; }
				echo "</h3>"; ?>
				<?php if ($pro_included || $ent_included) { echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>Media files scanned: " . $empty_media_scan[0]->option_value . "/" . $media_count . "</h3>"; } ?>
				<?php echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>Total fields scanned on <span style='color: rgb(115, 1, 154); font-weight: bold;'>".$empty_type[0]->option_value."</span>: $empty_field_count</h3>"; ?>
				<?php echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>Last scan took $time_of_empty</h3>"; ?>
				<?php echo "<h3 class='sc-message' style='color: rgb(0, 115, 0);'>$empty_scan_message</h3><br />"; ?>
				<?php if (!$pro_included && !$ent_included) echo "<h3 class='sc-message' style='color: rgb(225, 0, 0);'>$empty_words empty fields have been found on other parts of your website. <a href='https://www.wpspellcheck.com/purchase-options' target='_blank'>Upgrade to pro</a> to fix them now</h3><br />"; ?>
				</div>
				<div class="wpsc-scan-buttons">
				<h3 style="display: inline-block;">Scan:</h3>
				<p class="submit"><input style="background-color: #ffb01f; border-color: #ffb01f; box-shadow: 0px 1px 0px #ffb01f; text-shadow: 1px 1px 1px #ffb01f; font-weight: bold;" type="submit" name="submit-empty" id="submit" class="button button-primary" value="Entire Site" <?php if ($checked_pages == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<?php if ($pro_included || $ent_included) { ?>
				<p class="submit"><input type="submit" name="submit-empty" id="submit" class="button button-primary" value="Page SEO" <?php if ($check_page_seo_empty == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit-empty" id="submit" class="button button-primary" value="Post SEO" <?php if ($check_post_seo_empty == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit-empty" id="submit" class="button button-primary" value="Media Files SEO" <?php if ($check_media_seo_empty == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit-empty" id="submit" class="button button-primary" value="Media Files" <?php if ($check_media_empty == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<?php } ?>
				<p class="submit"><input type="submit" name="submit-empty" id="submit" class="button button-primary" value="Authors" <?php if ($check_authors_empty == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<?php if ($pro_included || $ent_included) { ?>
				<p class="submit"><input type="submit" name="submit-empty" id="submit" class="button button-primary" value="Menus" <?php if ($check_menu_empty == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<?php } ?>
				<p class="submit"><input type="submit" name="submit-empty" id="submit" class="button button-primary" value="Page Titles" <?php if ($check_page_titles_empty == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit-empty" id="submit" class="button button-primary" value="Post Titles" <?php if ($check_post_titles_empty == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<?php if ($pro_included || $ent_included) { ?>
				<p class="submit"><input type="submit" name="submit-empty" id="submit" class="button button-primary" value="Tag Descriptions" <?php if ($check_tag_desc_empty == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit-empty" id="submit" class="button button-primary" value="Category Descriptions" <?php if ($check_cat_desc_empty == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<p class="submit"><input type="submit" name="submit-empty" id="submit" class="button button-primary" value="WooCommerce and WP-eCommerce Products" <?php if ($check_ecommerce_empty == 'false') echo "style='background: darkgrey!important; color: white!important; border-color: grey!important;' disabled" ?>></p>
				<?php } ?>
				<p class="submit"><input type="submit" name="submit-empty" id="submit" class="button button-primary" value="Clear Results"></p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" style="background-color: red;" value="See Scan Results"></p>
				<p class="submit"><input type="submit" name="submit-empty" id="submit" class="button button-primary" style="background-color: red;" value="Stop Scans"></p>
</div>
			</form>
			<div style="float: right; width:23%; margin-left: 2%;">
				<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
				<a href="https://www.wpspellcheck.com/" target="_blank"><img src="<?php echo plugin_dir_url( __FILE__ ) . '../images/logo.png'; ?>" alt="WP Spell Check" /></a>
<script type="text/javascript">
//<![CDATA[
if (typeof newsletter_check !== "function") {
window.newsletter_check = function (f) {
    var re = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-]{1,})+\.)+([a-zA-Z0-9]{2,})+$/;
    if (!re.test(f.elements["ne"].value)) {
        alert("The email is not correct");
        return false;
    }
    for (var i=1; i<20; i++) {
    if (f.elements["np" + i] && f.elements["np" + i].value == "") {
        alert("");
        return false;
    }
    }
    if (f.elements["ny"] && !f.elements["ny"].checked) {
        alert("You must accept the privacy statement");
        return false;
    }
    return true;
}
}
//]]>
</script>

<div class="newsletter newsletter-subscription" style="padding: 5px 5px 10px 5px; border: 3px solid #008200; border-radius: 5px; background: white;">
<div class="wpsc-sidebar" style="margin-bottom: 15px;"><h2>Help to improve this plugin!</h2><center>Enjoyed this plugin? You can help by <a class="review-button" href="https://en-ca.wordpress.org/plugins/wp-spell-check/" target="_blank">rating this plugin on wordpress.org</a></center></div>
</div>
<hr>
<div style="padding: 5px 5px 10px 5px; border: 3px solid #0096FF; border-radius: 5px; background: white;">
				<a href="https://www.wpspellcheck.com/tutorials" target="_blank"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/wp-spellcheck-tutorials.jpg'; ?>" style="max-width: 99%;" alt="Watch WP Spell Check Tutorials" /></a>
</div>
<hr>
<div style="padding: 5px 5px 10px 5px; border: 3px solid #D60000; border-radius: 5px; background: white; text-align: center;">
				<h2>Follow us on Facebook</h2>
				<div class="fb-page" data-href="https://www.facebook.com/wpspellcheck/" data-width="180px" data-small-header="true" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><blockquote cite="https://www.facebook.com/wpspellcheck/" class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/wpspellcheck/">WP Spell Check</a></blockquote></div>
</div>
<hr>
<div style="padding: 5px 5px 10px 5px; border: 3px solid #73019A; border-radius: 5px; background: white;">
<h2>Get on Our Priority Notification List</h2>
<form method="post" action="https://www.wpspellcheck.com/?na=s" onsubmit="return newsletter_check(this)">

<table cellspacing="0" cellpadding="3" border="0">

<!-- email -->
<tr>
	<th>Email</th>
	<td align="left"><input class="newsletter-email" type="email" name="ne" style="width: 100%;" size="30" required></td>
</tr>

<tr>
	<td colspan="2" class="newsletter-td-submit">
		<input class="newsletter-submit" type="submit" value="Sign me up"/>
	</td>
</tr>

</table>
</form>
</div>
<hr>
<?php if (!$ent_included && !$pro_included) { ?>
<div style="padding: 5px 5px 10px 5px; border: 1px solid #00BBC1; border-radius: 5px; background: white;">
				<div class="wpsc-sidebars" style="margin-bottom: 15px;"><h2>Want your entire website scanned?</h2>
					<p><a href="https://www.wpspellcheck.com/purchase-options/" target="_blank">Upgrade to WP Spell Check Pro<br />
					See Benefits and Features here »</a></p>
				</div>
</div>
<?php } ?>
			</div>
			<?php if(($message != '' || $ignore_message[0] != '' || $dict_message[0] != '') && $_GET['wpsc-scan-tab'] == 'empty') { ?>
				<div style="text-align: center; background-color: white; padding: 5px; margin: 15px 0;">
					<?php if($message != '') echo "<div class='wpsc-message' style='width: 74%; font-size: 1.3em; color: rgb(0, 115, 0); font-weight: bold;'>" . $message . "</div>"; ?>
					<?php if($ignore_message[0] != '') echo "<div class='wpsc-message' style='width: 74%; font-size: 1.3em; color: rgb(0, 115, 0); font-weight: bold;'>" . $ignore_message[0] . "</div>"; ?>
					<?php if($dict_message[0] != '') echo "<div class='wpsc-message' style='width: 74%; font-size: 1.3em; color: rgb(0, 115, 0); font-weight: bold;'>" . $dict_message[0] . "</div>"; ?>
				</div>
				<?php } ?>
			<form id="words-list" method="get" style="width: 75%; float: left;">
				<p class="search-box" style="position: relative; margin-top: 4em;">
					<label class="screen-reader-text" for="search_id-search-input">search:</label>
					<input type="search" id="search_id-search-input-top" name="s-top" value="">
					<input type="submit" id="search-submit-top" class="button" value="search">
				</p>
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<input type="hidden" name="wpsc-scan-tab" value="empty" />
				<input name="wpsc-edit-update-button" class="wpsc-edit-update-button empty-tab" type="submit" value="Save all Changes" class="button button-primary" style="width: 33%; margin-left: 33%; display: block; background: #008200; border-color: #005200; color: white; font-weight: bold;"/>
				<?php $empty_table->display() ?>
				<p class="search-box">
					<label class="screen-reader-text" for="search_id-search-input">search:</label>
					<input type="search" id="search_id-search-input" name="s" value="">
					<input type="submit" id="search-submit" class="button" value="search">
				</p>
				<input name="wpsc-edit-update-buttom" class="wpsc-edit-update-button empty-tab" type="submit" value="Save all Changes" class="button button-primary" style="width: 33%; margin-left: auto; margin-right: auto; display: block;  background: #008200; border-color: #005200; color: white; font-weight: bold;"/>
			</form>
		</div>
		</div>
		<!-- Quick Edit Clone Field -->
		<table style="display: none;">
			<tbody>
				<tr id="wpsc-editor-row" class="wpsc-editor">
					<td colspan="4">
						<div class="wpsc-edit-content">
							<h4 style="display: inline-block;">Edit %Word%</h4>
							<input type="text" size="60" name="word_update[]" style="margin-left: 3em;" value class="wpsc-edit-field edit-field">
							<input type="hidden" name="edit_page_name[]" value>
							<input type="hidden" name="edit_page_type[]" value>
							<input type="hidden" name="edit_old_word[]" value>
							<input type="hidden" name="edit_old_word_id[]" value>
						</div>
						<div class="wpsc-buttons">
							<input type="button" class="button-secondary cancel alignleft wpsc-cancel-button" value="Cancel">
							<!--<input type="checkbox" name="global-edit" value="global-edit"> Apply changes to entire website-->
							<div style="clear: both;"></div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- Suggested Spellings Clone Field -->
		<table style="display: none;">
			<tbody>
				<tr id="wpsc-suggestion-row" class="wpsc-editor">
					<td colspan="4">
						<div class="wpsc-suggestion-content">
							<label><span>Suggested Spellings</span>
							<select class="wpsc-suggested-spelling-list" name="suggested_word[]">
								<option id="wpsc-suggested-spelling-1" value></option>
								<option id="wpsc-suggested-spelling-2" value></option>
								<option id="wpsc-suggested-spelling-3" value></option>
								<option id="wpsc-suggested-spelling-4" value></option>
							</select>
							<input type="hidden" name="suggest_page_name[]" value>
							<input type="hidden" name="suggest_page_type[]" value>
							<input type="hidden" name="suggest_old_word[]" value>
							<input type="hidden" name="suggest_old_word_id[]" value>
						</div>
						<div class="wpsc-buttons">
							<input type="button" class="button-secondary cancel alignleft wpsc-cancel-suggest-button" value="Cancel">
							<!--<input type="checkbox" name="global-suggest" value="global-suggest"> Apply changes to entire website-->
							<div style="clear: both;"></div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	<?php 
	}
	
	
	
	
?>