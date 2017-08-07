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
	function render_options() {
	global $wpdb;
	global $key_valid;
	global $pro_included;
	global $ent_included;
	$table_name = $wpdb->prefix . 'spellcheck_options';
	$ignore_table = $wpdb->prefix . 'spellcheck_ignore';
	$message = '';
	if ($_POST['uninstall'] == 'Clean up Database and Deactivate Plugin') {
		prepare_uninstall();
		deactivate_plugins( 'wp-spell-check/wpspellcheck.php' );
		if ($pro_included) deactivate_plugins( 'wp-spell-check-pro/wpspellcheckpro.php' );
		if ($ent_included) deactivate_plugins( 'wp-spell-check-enterprise/wpspellcheckenterprise.php' );
		wp_die( 'WP Spell Check has been deactivated. If you wish to use the plugin again you may activate it on the WordPress plugin page' );
	}
	
	if ($_POST['submit'] == 'Update' || $_POST['submit'] == 'Send Test') {
		
		$message = "<h3 style='color: rgb(0, 115, 0);'>Options Updated</h3>";
		if ($_POST['email'] == 'email') {
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'email'));
		} else { 
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'email'));
		}
		$wpdb->update($table_name, array('option_value' => $_POST['email_address']), array('option_name' => 'email_address'));
		if ($_POST['ignore-caps'] == 'ignore-caps')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'ignore_caps'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'ignore_caps'));
		if ($_POST['check-pages'] == 'check-pages')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_pages'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_pages'));
		if ($_POST['check-posts'] == 'check-posts')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_posts'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_posts'));
		if ($_POST['check-authors'] == 'check-authors')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_authors'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_authors'));
		if ($_POST['check-sliders'] == 'check-sliders')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_sliders'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_sliders'));
		if ($_POST['check-media'] == 'check-media')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_media'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_media'));;
		if ($_POST['check-menu'] == 'check-menu')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_menus'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_menus'));
		if ($_POST['page-titles'] == 'page-titles')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'page_titles'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'page_titles'));
		if ($_POST['post-titles'] == 'post-titles')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'post_titles'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'post_titles'));
		if ($_POST['tags'] == 'tags')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'tags'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'tags'));
		if ($_POST['check-tag-desc'] == 'check-tag-desc')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_tag_desc'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_tag_desc'));
		if ($_POST['check-tag-slug'] == 'check-tag-slug')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_tag_slug'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_tag_slug'));
		if ($_POST['categories'] == 'categories')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'categories'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'categories'));
		if ($_POST['check-cat-desc'] == 'check-cat-desc')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_cat_desc'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_cat_desc'));
		if ($_POST['check-cat-slug'] == 'check-cat-slug')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_cat_slug'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_cat_slug'));
		if ($_POST['seo-titles'] == 'seo-titles')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'seo_titles'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'seo_titles'));
		if ($_POST['seo-desc'] == 'seo-desc')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'seo_desc'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'seo_desc'));
		if ($_POST['page-slugs'] == 'page-slugs')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'page_slugs'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'page_slugs'));
		if ($_POST['post-slugs'] == 'post-slugs')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'post_slugs'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'post_slugs'));
		if ($_POST['check-custom'] == 'check-custom')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_custom'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_custom'));
		if ($_POST['ignore-emails'] == 'ignore-emails')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'ignore_emails'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'ignore_emails'));
		if ($_POST['ignore-websites'] == 'ignore-websites')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'ignore_websites'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'ignore_websites'));
		if ($_POST['highlight-words'] == 'highlight-words')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'highlight_word'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'highlight_word'));
		if ($_POST['check-ecommerce'] == 'check-ecommerce')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_ecommerce'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_ecommerce'));
		if ($_POST['check-cf7'] == 'check-cf7')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_cf7'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_cf7'));
			
		if ($_POST['check-post-drafts'] == 'check-post-drafts')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'scan_post_drafts'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'scan_post_drafts'));
		if ($_POST['check-page-drafts'] == 'check-page-drafts')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'scan_page_drafts'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'scan_page_drafts'));
			
		
		
		if ($_POST['check-authors-empty'] == 'check-authors')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_authors_empty'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_authors_empty'));
		if ($_POST['check-menu-empty'] == 'check-menu')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_menu_empty'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_menu_empty'));
		if ($_POST['check-page-titles-empty'] == 'check-page-titles')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_page_titles_empty'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_page_titles_empty'));
		if ($_POST['check-post-titles-empty'] == 'check-post-titles')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_post_titles_empty'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_post_titles_empty'));
		if ($_POST['check-tag-desc-empty'] == 'check-tag-desc')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_tag_desc_empty'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_tag_desc_empty'));
		if ($_POST['check-cat-desc-empty'] == 'check-cat-desc')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_cat_desc_empty'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_cat_desc_empty'));
		if ($_POST['check-page-seo-empty'] == 'check-page-seo')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_page_seo_empty'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_page_seo_empty'));
		if ($_POST['check-post-seo-empty'] == 'check-post-seo')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_post_seo_empty'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_post_seo_empty'));
		if ($_POST['check-media-seo-empty'] == 'check-media-seo')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_media_seo_empty'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_media_seo_empty'));
		if ($_POST['check-ecommerce-empty'] == 'check-ecommerce')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_ecommerce_empty'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_ecommerce_empty'));
		if ($_POST['check-media-empty'] == 'check-media')
			$wpdb->update($table_name, array('option_value' => 'true'), array('option_name' => 'check_media_empty'));
		else
			$wpdb->update($table_name, array('option_value' => 'false'), array('option_name' => 'check_media_empty'));

		if (is_numeric($_POST['scan_frequency'])) {
			$wpdb->update($table_name, array('option_value' => $_POST['scan_frequency']), array('option_name' => 'scan_frequency'));

			
			$next_scan = wp_next_scheduled('adminscansite');
			wp_unschedule_event($next_scan, 'adminscansite');

			switch($_POST['scan_frequency_interval']) {
				case 'hourly':
					$scan_timer = intval($_POST['scan_frequency']) * 3600;
					break;
				case 'daily':
					$scan_timer = intval($_POST['scan_frequency']) * 86400;
					break;
				case 'weekly':
					$scan_timer = intval($_POST['scan_frequency']) * 604800;
					break;
				case 'monthly':
					$scan_timer = intval($_POST['scan_frequency']) * 2592000;
					break;
				default:
					$scan_timer = 604800;
			}

			wp_schedule_event(time() + $scan_timer, 'wpsc', 'adminscansite');
		} else {
			$message = "Please enter a valid number for scan frequency";
		}
		$wpdb->update($table_name, array('option_value' => $_POST['scan_frequency_interval']), array('option_name' => 'scan_frequency_interval'));
		$wpdb->update($table_name, array('option_value' => $_POST['language_setting']), array('option_name' => 'language_setting'));
		$wpdb->update($table_name, array('option_value' => $_POST['api_key']), array('option_name' => 'api_key'));

		
		$pages = explode(PHP_EOL, $_POST['pages-ignore']);

		$wpdb->query('TRUNCATE TABLE ' . $ignore_table); 

		foreach($pages as $page) {
			if ($page != '')
				$wpdb->insert($ignore_table, array('keyword' => $page, 'type' => 'page')); 
		}
	}
	
	
	$settings = $wpdb->get_results('SELECT option_name, option_value FROM ' . $table_name);
	$email = $settings[0]->option_value;
	$email_address = $settings[1]->option_value;
	$ignore_caps = $settings[3]->option_value;
	$check_pages = $settings[4]->option_value;
	$check_posts = $settings[5]->option_value;
	$check_menus = $settings[7]->option_value;
	$scan_frequency = $settings[8]->option_value;
	$scan_frequency_interval = $settings[9]->option_value;
	$email_frequency_interval = $settings[10]->option_value;
	$language_setting = $settings[11]->option_value;
	$page_titles = $settings[12]->option_value;
	$post_titles = $settings[13]->option_value;
	$tags = $settings[14]->option_value;
	$categories = $settings[15]->option_value;
	$seo_desc = $settings[16]->option_value;
	$seo_titles = $settings[17]->option_value;
	$page_slugs = $settings[18]->option_value;
	$post_slugs = $settings[19]->option_value;
	$api_key = $settings[20]->option_value;
	$ignore_emails = $settings[23]->option_value;
	$ignore_websites = $settings[24]->option_value;
	$check_sliders = $settings[30]->option_value;
	$check_media = $settings[31]->option_value;
	$highlight_words = $settings[33]->option_value;
	$check_ecommerce = $settings[36]->option_value;
	$check_cf7 = $settings[37]->option_value;
	$check_tag_desc = $settings[38]->option_value;
	$check_tag_slug = $settings[39]->option_value;
	$check_cat_desc = $settings[40]->option_value;
	$check_cat_slug = $settings[41]->option_value;
	$check_custom = $settings[42]->option_value;
	$check_authors = $settings[44]->option_value;
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
	$check_page_drafts = $settings[136]->option_value;
	$check_post_drafts = $settings[137]->option_value;
	
	$page_data = $wpdb->get_results("SELECT keyword FROM " . $ignore_table . " WHERE type='page';");
	$page_list = '';
	foreach ($page_data as $page) {
		$page_list .= $page->keyword . PHP_EOL;
	}
	if ($_POST['test-email'] == 'Send Test') {
		$wpdb->update($table_name, array('option_value' => $_POST['email_address']), array('option_name' => 'email_address'));
		$message = send_test_email();
		$email_address = $_POST['email_address'];
	}
		
	wp_enqueue_script('options-nav', plugin_dir_url( __FILE__ ) . 'options-nav.js');
	?>
		<style> p.submit { display: inline-block; margin-left: 10px; } .hidden { display: none; } .wpsc-scan-nav-bar { border-bottom: 1px solid #BBB; margin-botton: 15px; } .wpsc-scan-nav-bar a { text-decoration: none; margin: 5px 5px -1px 5px; padding: 8px; border: 1px solid #BBB; display: inline-block; font-weight: bold; color: black; font-size: 14px; } .wpsc-scan-nav-bar a.selected { border-bottom: 1px solid white; background: white; } .wpsc-mouseover-text-pro-feature { color: black; font-size: 12px; width: 225px; display: inline-block; position: absolute; margin: -13px 0 0 -270px; padding: 3px; border: 1px solid black; border-radius: 10px; opacity: 0; background: white; z-index: -100; } </style>
		<?php show_feature_window(); ?>
		<?php check_install_notice(); ?>
		<div class="wrap">
			<h2><a href="admin.php?page=wp-spellcheck.php"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logo.png'; ?>" alt="WP Spell Check" /></a> <span style="position: relative; top: -15px;">Options</span></h2>
			<?php if(!$key_valid && $api_key != '') echo "<div class='error' style='color: red; font-weight: bold; font-size: 14px'>API Key not valid</div>"; ?>
			<?php if ($key_valid) echo "<div class='updated' style='color: rgb(0, 115, 0); font-weight: bold; font-size: 14px'>API Key is valid</div>"; ?>
			<?php if($message != '') echo "<span class='wpsc-message'>" . $message . "</span>"; ?>
			<div class="wpsc-scan-nav-bar" style="width: 75%;">
				<a href="#general-options" id="wpsc-general-options" <?php if ($_POST['wpsc-scan-tab'] != 'scan' && $_POST['wpsc-scan-tab'] != 'empty') echo 'class="selected"';?> name="wpsc-general-options">General Settings</a>
				<a href="#scan-options" id="wpsc-scan-options" <?php if ($_POST['wpsc-scan-tab'] == 'scan') echo 'class="selected"';?> name="wpsc-scan-options">Spellcheck Options</a>
				<a href="#empty-options" id="wpsc-empty-options" <?php if ($_POST['wpsc-scan-tab'] == 'empty') echo 'class="selected"';?> name="wpsc-empty-options">Empty Fields Options</a>
			</div>
			<form action="admin.php?page=wp-spellcheck-options.php" method="post" name="options" style="margin-top: -7px;">
			<input type="hidden" name="wpsc-scan-tab" class="wpsc-nav-tab" value="<?php echo $_POST['wpsc-scan-tab']; ?>">
			<div id="wpsc-general-options-tab" <?php if ($_POST['wpsc-scan-tab'] == 'scan' || $_POST['wpsc-scan-tab'] == 'empty') echo 'class="hidden"';?>>
			<table class="form-table wpsc-general-options-table" style="width: 75%; float: left; background: white;" cellpadding="10"><tbody>
				<tr><td scope="row" align="left" style="padding-top: 30px;"><label style="display: inline-block; margin-bottom: 6px;">API Key (For <a target="_blank" href="https://www.wpspellcheck.com/purchase-options/">Pro/Enterprise</a> Version)</label><br /><input type="text" name="api_key" value="<?php echo $api_key; ?>"></td>
				<td style="padding-top: 30px;"><label style="display: inline-block; margin-bottom: 6px;">Language</label><br /><select style="display: inline-block; width: 140px; height: 27px; margin-top: 1px;" name="language_setting">
<option value="en_CA" <?php if ($language_setting == 'en_CA') echo "selected='selected'"; ?>>English (Canada)</option>
<option value="en_US" <?php if ($language_setting == 'en_US') echo "selected='selected'"; ?>>English (US)</option>
<option value="en_UK" <?php if ($language_setting == 'en_UK') echo "selected='selected'"; ?>>English (UK)</option>
</select></td></tr>
				<tr><td scope="row" align="left"><span style="display: inline-block; margin-bottom: 6px;"><input type="checkbox" name="email" value="email" <?php if ($email == 'true') echo 'checked'; ?>>Send Email Reports</span><br /><input type="text" name="email_address" value="<?php echo $email_address; ?>"><input type="hidden" name="page" value="wp-spellcheck-options.php">
				<input type="hidden" name="action" value="check">
				<input type="submit" name="test-email" id="test-email" class="button button-primary" value="Send Test"></td><td scope="row" align="left"><label style="display: inline-block; margin-bottom: 6px; margin-top: -2px;">Scan Frequency</label><br />Every <input size="5" name="scan_frequency" style="border: 1px solid #ddd" value="<?php echo $scan_frequency; ?>"><select style="height: 27px; margin-top: 0px; display: inline-block;" name="scan_frequency_interval">
<option value="hourly" <?php if ($scan_frequency_interval == 'hourly') echo "selected='selected'"; ?>>Hour(s)</option>
<option value="daily" <?php if ($scan_frequency_interval == 'daily') echo "selected='selected'"; ?>>Day(s)</option>
<option value="weekly" <?php if ($scan_frequency_interval == 'weekly') echo "selected='selected'"; ?>>Week(s)</option>
<option value="monthly" <?php if ($scan_frequency_interval == 'monthly') echo "selected='selected'"; ?>>Month(s)</option>
</select></td></tr>
				<?php if (is_plugin_active('wp-mail-smtp/wp_mail_smtp.php') != true) { ?>
					<tr><td colspan="2">To ensure emails are delivered, try using <a target="_blank" href="https://en-ca.wordpress.org/plugins/wp-mail-smtp/">WP Mail SMTP</a></td></tr>
				<?php } ?>
				<?php if ($email_address == '' && $email == 'true') { ?>
					<tr><td colspan="2">An email address must be entered in order to receive email alerts</td></tr>
				<?php } ?>
				<tr><td scope="row" align="left"><label>Pages/Posts to ignore (Please enter Page/Post titles and place one on each line)</label></td><td colspan="2"><textarea name="pages-ignore" rows="4" cols="50"><?php echo $page_list; ?></textarea></td></tr>
				<tr><td scope="row" align="left"><input class="ignore-check-all" type="checkbox" name="ignore-caps" value="ignore-caps" <?php if ($ignore_caps == 'true') echo 'checked'; ?>>Ignore fully capitalized words</td>
				<td scope="row" align="left"><input class="ignore-check-all" type="checkbox" name="ignore-emails" value="ignore-emails" <?php if ($ignore_emails == 'true') echo 'checked'; ?>>Ignore Email Addresses</td></tr>
				<tr><td scope="row" align="left"><input class="ignore-check-all" type="checkbox" name="ignore-websites" value="ignore-websites" <?php if ($ignore_websites == 'true') echo 'checked'; ?>>Ignore Website URLs</td>
				<td scope="row" align="left"><input <?php if (!$pro_included && !$ent_included) echo "disabled" ?> class="ignore-check-all" type="checkbox" name="highlight-words" value="highlight-words" <?php if ($highlight_words == 'true' && ($pro_included || $ent_included)) echo 'checked'; ?>>Highlight Misspelled Words (For logged in admin only)<?php if (!$pro_included && !$ent_included) echo "<span class='wpsc-mouseover-pro-feature' style='border-radius: 29px; border: 1px solid green; display: inline-block; margin-left: 10px; padding: 4px 10px; cursor: help;'>?<span class='wpsc-mouseover-text-pro-feature'>This is a pro version feature</span></span>"; ?></td></tr>
				<tr><td scope="row" align="left"><input class="ignore-check-all" type="checkbox" name="check-page-drafts" value="check-page-drafts" <?php if ($check_page_drafts == 'true') echo 'checked'; ?>>Scan Page Drafts</td>
				<td scope="row" align="left"><input class="ignore-check-all" type="checkbox" name="check-post-drafts" value="check-post-drafts" <?php if ($check_post_drafts == 'true') echo 'checked'; ?>>Scan Post Drafts</td></tr>
				
				<tr colspan="2"><td><input type="submit" name="submit" value="Update" class="button button-primary" /></td></tr>
				<tr><td colspan="3" scope="row" align="left"><input type="submit" name="uninstall" value="Clean up Database and Deactivate Plugin" /><span style="margin-left: 10px;">This will deactivate WP Spell Check on all sites on the network and clean up the database of any changes made by WP Spell Check. If you wish to use WP Spell Check again after, you may activate it on the WordPress plugins page</span></td></tr>
			</tbody>
			</table>
			</div>
			<div id="wpsc-scan-options-tab" <?php if ($_POST['wpsc-scan-tab'] != 'scan') echo 'class="hidden"';?>>
				<table class="form-table" style="width: 75%; float: left; background: white;" cellpadding="10"><tbody>
				<?php if ($pro_included || $ent_included) { ?>
				<tr><td scope="row" align="left"><input type="checkbox" id="check-all" name="check-all" value="check-all">Select All</td></tr>
				<tr><td scope="row" align="left"><input type="checkbox" name="check-pages" value="check-pages" <?php if ($check_pages == 'true') echo 'checked'; ?>>Check Pages</td>
				<td scope="row" align="left"><input type="checkbox" name="check-posts" value="check-posts" <?php if ($check_posts == 'true') echo 'checked'; ?>>Check Posts</td>
				<td scope="row" align="left"><input type="checkbox" name="check-menu" value="check-menu" <?php if ($check_menus == 'true') echo 'checked'; ?>>Check Wordpress Menus</td>
				</tr>
				<tr><td scope="row" align="left"><input type="checkbox" name="page-titles" value="page-titles" <?php if ($page_titles == 'true') echo 'checked'; ?>>Check Page Titles</td>
				<td scope="row" align="left"><input type="checkbox" name="post-titles" value="post-titles" <?php if ($post_titles == 'true') echo 'checked'; ?>>Check Post Titles</td>
				<td scope="row" align="left"><input type="checkbox" name="tags" value="tags" <?php if ($tags == 'true') echo 'checked'; ?>>Check Tags</td></tr>
				<tr><td scope="row" align="left"><input type="checkbox" name="check-tag-desc" value="check-tag-desc" <?php if ($check_tag_desc == 'true') echo 'checked'; ?>>Check Tag Descriptions</td>
				<td scope="row" align="left"><input type="checkbox" name="check-tag-slug" value="check-tag-slug" <?php if ($check_tag_slug == 'true') echo 'checked'; ?>>Check Tag Slugs</td>
				<td scope="row" align="left"><input type="checkbox" name="categories" value="categories" <?php if ($categories == 'true') echo 'checked'; ?>>Check Categories</td></tr>
				<tr><td scope="row" align="left"><input type="checkbox" name="check-cat-desc" value="check-cat-desc" <?php if ($check_cat_desc == 'true') echo 'checked'; ?>>Check Category Descriptions</td>
				<td scope="row" align="left"><input type="checkbox" name="check-cat-slug" value="check-cat-slug" <?php if ($check_cat_slug == 'true') echo 'checked'; ?>>Check Category Slugs</td>
				<td scope="row" align="left"><input type="checkbox" name="seo-desc" value="seo-desc" <?php if ($seo_desc == 'true') echo 'checked'; ?>>Check SEO Descriptions</td></tr>
				<tr><td scope="row" align="left"><input type="checkbox" name="seo-titles" value="seo-titles" <?php if ($seo_titles == 'true') echo 'checked'; ?>>Check SEO Titles</td>
				<td scope="row" align="left"><input type="checkbox" name="page-slugs" value="page-slugs" <?php if ($page_slugs == 'true') echo 'checked'; ?>>Check Page Slugs</td>
				<td scope="row" align="left"><input type="checkbox" name="post-slugs" value="post-slugs" <?php if ($post_slugs == 'true') echo 'checked'; ?>>Check Post Slugs</td></tr>
				<tr><td scope="row" align="left"><input type="checkbox" name="check-sliders" value="check-sliders" <?php if ($check_sliders == 'true') echo 'checked'; ?>>Check Sliders</td>
				<td scope="row" align="left"><input type="checkbox" name="check-media" value="check-media" <?php if ($check_media == 'true') echo 'checked'; ?>>Check Media Files</td>
				<td scope="row" align="left"><input type="checkbox" name="check-ecommerce" value="check-ecommerce" <?php if ($check_ecommerce == 'true') echo 'checked'; ?>>Check WooCommerce and WP-eCommerce Products</td></tr>
				<tr>
				<td scope="row" align="left"><input type="checkbox" name="check-cf7" value="check-cf7" <?php if ($check_cf7 == 'true') echo 'checked'; ?>>Check Contact Form 7</td>
				<td scope="row" align="left"><input type="checkbox" name="check-authors" value="check-authors" <?php if ($check_authors == 'true') echo 'checked'; ?>>Check Authors</td></tr>
				<?php } else { ?>
				<tr><td scope="row" align="left"><input type="checkbox" id="check-all" name="check-all" value="check-all">Select All</td></tr>
				<tr><td scope="row" align="left"><input type="checkbox" name="check-pages" value="check-pages" <?php if ($check_pages == 'true') echo 'checked'; ?>>Check Pages</td>
				<td scope="row" align="left"><input type="checkbox" name="check-posts" value="check-posts" <?php if ($check_posts == 'true') echo 'checked'; ?>>Check Posts</td>
				<td scope="row" align="left"><input type="checkbox" name="check-cf7" value="check-cf7" <?php if ($check_cf7 == 'true') echo 'checked'; ?>>Check Contact Form 7</td></tr>
				<tr><td scope="row" align="left"><input type="checkbox" name="check-authors" value="check-authors" <?php if ($check_authors == 'true') echo 'checked'; ?>>Check Authors</td></tr></tr>
					<tr style="background: white;"><td colspan="3"><h3 style="color: red;"><a href="https://www.wpspellcheck.com/purchase-options/" target="_blank">Upgrade to Pro</a> to scan the following</h3></td></tr>
					<tr style="background: white;"><td>WordPress Menus</td><td>Page Titles</td><td>Post Titles</td></tr>
					<tr style="background: white;"><td>Tags</td><td>Tag Descriptions</td><td>Tag Slugs</td></tr>
					<tr style="background: white;"><td>Category Slugs</td><td>Categories</td><td>Category Descriptions</td></tr>
					<tr style="background: white;"><td>SEO Descriptions</td><td>SEO Titles</td><td>Page Slugs</td></tr>
					<tr style="background: white;"><td>Post Slugs</td><td>Sliders</td><td>Media Files</td></tr>
					<tr style="background: white;"><td>WooCommerce and WP-eCommerce Products</td></tr>
				<?php } ?>
				<tr colspan="2"><td><input type="submit" name="submit" value="Update" class="button button-primary" /></td></tr>
				<?php if ($pro_included || $ent_included) { ?><tr><td colspan="3" scope="row" align="left"><span style="font-size: 14px; font-weight: bold; color: red;">Warning: When updating <span style="color: black; text-decoration: underline;">page/post slugs</span>, some links contained within the theme may not be updated. Consult your webmaster before updating page/post slugs.<br /><a href="https://www.wpspellcheck.com/about/faqs#update-slugs" target="_blank">Click here to learn more</a></span><br /><br /><span style="font-size: 14px; font-weight: bold; color: red;">When updating <span style="color: black; text-decoration: underline;">Media filenames</span> this may cause images to stop working on your website. This does not apply to descriptions, alternate text, or captions.</span></td></tr> <?php } ?>
			</tbody></table>
		</div>
		<div id="wpsc-empty-options-tab" <?php if ($_POST['wpsc-scan-tab'] != 'empty') echo 'class="hidden"';?>>
				<table class="form-table" style="width: 75%; float: left; background: white;" cellpadding="10"><tbody>
				<tr><td scope="row" align="left"><input type="checkbox" id="check-all-empty" name="check-all-empty" value="check-all-empty">Select All</td></tr>
				<?php if ($pro_included || $ent_included) { ?>
				<tr><td scope="row" align="left"><input type="checkbox" name="check-authors-empty" value="check-authors" <?php if ($check_authors_empty == 'true') echo 'checked'; ?>>Check Authors</td>
				<td scope="row" align="left"><input type="checkbox" name="check-menu-empty" value="check-menu" <?php if ($check_menu_empty == 'true') echo 'checked'; ?>>Check Wordpress Menus</td>
				<td scope="row" align="left"><input type="checkbox" name="check-page-titles-empty" value="check-page-titles" <?php if ($check_page_titles_empty == 'true') echo 'checked'; ?>>Check Page Titles</td></tr>
				<tr><td scope="row" align="left"><input type="checkbox" name="check-post-titles-empty" value="check-post-titles" <?php if ($check_post_titles_empty == 'true') echo 'checked'; ?>>Check Post Titles</td>
				<td scope="row" align="left"><input type="checkbox" name="check-tag-desc-empty" value="check-tag-desc" <?php if ($check_tag_desc_empty == 'true') echo 'checked'; ?>>Check Tag Descriptions</td>
				<td scope="row" align="left"><input type="checkbox" name="check-cat-desc-empty" value="check-cat-desc" <?php if ($check_cat_desc_empty == 'true') echo 'checked'; ?>>Check Category Descriptions</td></tr>
				<tr><td scope="row" align="left"><input type="checkbox" name="check-page-seo-empty" value="check-page-seo" <?php if ($check_page_seo_empty == 'true') echo 'checked'; ?>>Check Page SEO</td>
				<td scope="row" align="left"><input type="checkbox" name="check-post-seo-empty" value="check-post-seo" <?php if ($check_post_seo_empty == 'true') echo 'checked'; ?>>Check Post SEO</td>
				<td scope="row" align="left"><input type="checkbox" name="check-media-seo-empty" value="check-media-seo" <?php if ($check_media_seo_empty == 'true') echo 'checked'; ?>>Check Media Files SEO</td></tr>
				<tr><td scope="row" align="left"><input type="checkbox" name="check-media-empty" value="check-media" <?php if ($check_media_empty == 'true') echo 'checked'; ?>>Check Media Files</td>
				<td scope="row" align="left"><input type="checkbox" name="check-ecommerce-empty" value="check-ecommerce" <?php if ($check_ecommerce_empty == 'true') echo 'checked'; ?>>Check WooCommerce and WP-eCommerce Products</td>
				</tr>
				<?php } else { ?>
				<tr><td scope="row" align="left"><input type="checkbox" name="check-authors-empty" value="check-authors" <?php if ($check_authors_empty == 'true') echo 'checked'; ?>>Check Authors</td>
					<td scope="row" align="left"><input type="checkbox" name="check-page-titles-empty" value="check-page-titles" <?php if ($check_page_titles_empty == 'true') echo 'checked'; ?>>Check Page Titles</td>
					<td scope="row" align="left"><input type="checkbox" name="check-post-titles-empty" value="check-post-titles" <?php if ($check_post_titles_empty == 'true') echo 'checked'; ?>>Check Post Titles</td></tr>
					<tr style="background: white;"><td colspan="3"><h3 style="color: red;"><a href="https://www.wpspellcheck.com/purchase-options/" target="_blank">Upgrade to Pro</a> to scan the following</h3></td></tr>
					<tr style="background: white;"><td>WordPress Menus</td><td>Tag Descriptions</td><td>Category Descriptions</td></tr>
					<tr style="background: white;"><td>Page SEO</td><td>Post SEO</td><td>Media Files SEO</td></tr>
					<tr style="background: white;"><td>Media Files</td><td colspan="2">WooCommerce and WP-eCommerce Products</td></tr>
				<?php } ?>
				<tr colspan="2"><td><input type="submit" name="submit" value="Update" class="button button-primary" /></td></tr>
			</tbody></table>
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
				<a href="https://www.wpspellcheck.com/" target="_blank"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/logo.png'; ?>" alt="WP Spell Check" /></a>
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
</div>
	<?php
	}
?>