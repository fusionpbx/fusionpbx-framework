<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2015
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

if ($domains_processed == 1) {

	//get the background images
		$relative_path = PROJECT_PATH.'/themes/enhanced/images/backgrounds';
		$backgrounds = opendir($_SERVER["DOCUMENT_ROOT"].'/'.$relative_path);
		unset($array);
		$x = 0;
		while (false !== ($file = readdir($backgrounds))) {
			if ($file != "." AND $file != ".."){
				$ext = pathinfo($file, PATHINFO_EXTENSION);
				if ($ext == "png" || $ext == "jpg" || $ext == "jpeg" || $ext == "gif") {
					$x++;
					$array[$x]['default_setting_category'] = 'theme';
					$array[$x]['default_setting_subcategory'] = 'background_image';
					$array[$x]['default_setting_name'] = 'array';
					$array[$x]['default_setting_value'] = $relative_path.'/'.$file;
					$array[$x]['default_setting_enabled'] = 'false';
					$array[$x]['default_setting_description'] = 'Set a relative path or URL within a selected compatible template.';
				}
				if ($x > 300) { break; };
			}
		}

		if(!$set_session_theme){
		//get default settings
			$sql = "select * from v_default_settings ";
			$sql .= "where default_setting_category = 'theme' ";
			$sql .= "and default_setting_subcategory = 'background_image' ";
			$prep_statement = $db->prepare(check_sql($sql));
			$prep_statement->execute();
			$default_settings = $prep_statement->fetchAll(PDO::FETCH_NAMED);
			unset($prep_statement);

			$background_image_enabled = false;
		//add theme default settings
			foreach ($array as $row) {
				$found = false;
				foreach ($default_settings as $field) {
					if ($field["default_setting_value"] == $row["default_setting_value"]) {
						$found = true;
					}
					//enable_background_image is a new setting, if a user has any background images enabled we should turn it on
					if ($field["default_setting_enabled"] == 'enabled') {
						$background_image_enabled = true;
					}
				}
				if (!$found) {
					$orm = new orm;
					$orm->name('default_settings');
					$orm->save($row);
					$message = $orm->message;
					//print_r($message);
				}
			}
		}

	//define array of settings
		unset($array);
		$x = 0;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'login_opacity';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '0.35';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the opacity of the login box (decimal).';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'login_shadow_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#888888';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the shadow color (HTML compatible) of the login box.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'login_background_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#ffffff';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the background color (hexadecimal) for the login box.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'domain_visible';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = 'true';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the visibility of the name of the domain currently being managed.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'logout_icon_visible';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = 'false';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the visibility of the logout icon.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'domain_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#000000';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the text color for domain name.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'domain_shadow_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#ffffff';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the text shadow color for domain name (Enhanced theme only).';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'domain_background_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#000000';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = 'Set the background color (hexadecimal) for the domain name.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'domain_background_opacity';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '0.1';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = 'Set the background opacity of the domain name.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'footer_background_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#000000';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the background color (HTML compatible) for the footer bar.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'footer_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#ffffff';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the foreground color (HTML compatible) for the footer bar.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'footer_opacity';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '0.2';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the opacity of the footer bar (decimal).';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'message_default_background_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#ccffcc';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the background color (HTML compatible) for the positive (default) message bar.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'message_default_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#004200';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the foreground color (HTML compatible) for the positive (default) message bar text.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'message_negative_background_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#ffcdcd';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the background color (HTML compatible) for the negative message bar.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'message_negative_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#670000';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the foreground color (HTML compatible) for the negative message bar text.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'message_alert_background_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#ffe585';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the background color (HTML compatible) for the alert message bar.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'message_alert_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#d66721';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the foreground color (HTML compatible) for the alert message bar text.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'message_opacity';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '0.9';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the opacity of the message bar (decimal).';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'message_delay';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '1.75';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set the hide delay of the message bar (seconds).';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'body_opacity';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '0.93';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = 'Set the opacity of the body and content (decimal).';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'menu_opacity';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '0.96';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = 'Set the opacity of the main menu (decimal, Minimized theme only).';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'background_image_enabled';
		$array[$x]['default_setting_name'] = 'boolean';
		$array[$x]['default_setting_value'] = 'true';
		$array[$x]['default_setting_enabled'] = 'false';
		if($background_image_enabled) { $array[$x]['default_setting_enabled'] = 'true'; }
		$array[$x]['default_setting_description'] = 'Enable use of background images.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'body_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#fffff';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = 'Set the background color of the content';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'body_shadow_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#000000';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = '';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'cache';
		$array[$x]['default_setting_name'] = 'boolean';
		$array[$x]['default_setting_value'] = 'false';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = 'Set whether to cache the theme in the session.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'domain_selector_shadow_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#888888';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'true';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'logo_align';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = 'left';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'true';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'menu_main_background_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#ff0000';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = '';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'menu_main_background_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = '';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'menu_main_shadow_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#000000';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = '';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'menu_main_text_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#ffffff';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = '';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'menu_main_text_color_hover';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#69e5ff';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = '';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'menu_position';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = 'top';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = '';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'menu_style';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = 'fixed';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = '';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'menu_sub_background_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = '';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'menu_sub_icons';
		$array[$x]['default_setting_name'] = 'bolean';
		$array[$x]['default_setting_value'] = 'false';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = '';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'menu_sub_shadow_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#000000';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = '';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'menu_sub_text_color';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#ffffff';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = '';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'menu_sub_text_color_hover';
		$array[$x]['default_setting_name'] = 'text';
		$array[$x]['default_setting_value'] = '#69e5ff';
		$array[$x]['default_setting_enabled'] = 'false';
		$array[$x]['default_setting_description'] = '';
		$x++;
		if($set_session_theme){
			foreach ($array as $index => $default_settings) {
				$sub_category = $array[$index]['default_setting_subcategory'];
				$name = $array[$index]['default_setting_name'];
				if($array[$index]['default_setting_enabled'] == 'true'){
					$_SESSION['theme'][$sub_category][$name] = $array[$index]['default_setting_value'];
				}else{
					$_SESSION['theme'][$sub_category][$name] = '';
				}
			}
		}
		else{
		//iterate and add each, if necessary
			foreach ($array as $index => $default_settings) {
				//add theme default settings
				$sql = "select count(*) as num_rows from v_default_settings ";
				$sql .= "where default_setting_category = 'theme' ";
				$sql .= "and default_setting_subcategory = '".$default_settings['default_setting_subcategory']."' ";
				$prep_statement = $db->prepare($sql);
				if ($prep_statement) {
					$prep_statement->execute();
					$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
					unset($prep_statement);
					if ($row['num_rows'] == 0) {
						$orm = new orm;
						$orm->name('default_settings');
						$orm->save($array[$index]);
						$message = $orm->message;
					}
					unset($row);
				}
			}
		}

	//define secondary background color array
		unset($array);
		$x = 0;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'background_color';
		$array[$x]['default_setting_name'] = 'array';
		$array[$x]['default_setting_value'] = '#6c89b5';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_order'] = '0';
		$array[$x]['default_setting_description'] = 'Set a background (HTML compatible) color.';
		$x++;
		$array[$x]['default_setting_category'] = 'theme';
		$array[$x]['default_setting_subcategory'] = 'background_color';
		$array[$x]['default_setting_name'] = 'array';
		$array[$x]['default_setting_value'] = '#144794';
		$array[$x]['default_setting_order'] = '1';
		$array[$x]['default_setting_enabled'] = 'true';
		$array[$x]['default_setting_description'] = 'Set a secondary background (HTML compatible) color, for a gradient effect.';

		if($set_session_theme){
			foreach ($array as $index => $default_settings) {
				$sub_category = $array[$index]['default_setting_subcategory'];
				$idx = $array[$index]['default_setting_order'];
				if($array[$index]['default_setting_enabled'] == 'true'){
					$_SESSION['theme'][$sub_category][$idx] = $array[$index]['default_setting_value'];
				}
			}
			return;
		}
		else{
		//add secondary background color separately, if missing
			$sql = "select count(*) as num_rows from v_default_settings ";
			$sql .= "where default_setting_category = 'theme' ";
			$sql .= "and default_setting_subcategory = 'background_color' ";
			$prep_statement = $db->prepare($sql);
			if ($prep_statement) {
				$prep_statement->execute();
				$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
				unset($prep_statement);
				if ($row['num_rows'] == 0) {
					$orm = new orm;
					$orm->name('default_settings');
					foreach ($array as $index => $null) {
						$orm->save($array[$index]);
					}
					$message = $orm->message;
					//print_r($message);
				}
				unset($row);
			}
		}

	//unset the array variable
		unset($array);
}

?>