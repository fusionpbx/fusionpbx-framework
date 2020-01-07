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
 Portions created by the Initial Developer are Copyright (C) 2008-2019
 the Initial Developer. All Rights Reserved.

 Contributor(s):
 Mark J Crane <markjcrane@fusionpbx.com>
*/

//includes
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('domain_setting_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//get the http post data
	if ($_POST['action'] != '') {
		$action = $_POST['action'];
		$domain_uuid = $_POST['domain_uuid'];
		$domain_settings = $_POST['domain_settings'];
		$domain_uuid_target = $_POST['domain_uuid_target'];

		//process the http post data by action
			if (is_array($domain_settings) && @sizeof($domain_settings) != 0) {
				switch ($action) {
					case 'copy':
						if (permission_exists('domain_setting_add') && permission_exists('domain_select') && count($_SESSION['domains']) > 1) {
							$obj = new domain_settings;
							$obj->domain_uuid = $domain_uuid;
							$obj->domain_uuid_target = $domain_uuid_target;
							$obj->copy($domain_settings);
						}
						break;
					case 'toggle':
						if (permission_exists('domain_setting_edit')) {
							$obj = new domain_settings;
							$obj->domain_uuid = $domain_uuid;
							$obj->toggle($domain_settings);
						}
						break;
					case 'delete':
						if (permission_exists('domain_setting_delete')) {
							$obj = new domain_settings;
							$obj->domain_uuid = $domain_uuid;
							$obj->delete($domain_settings);
						}
						break;
				}
			}

		//redirect
			header('Location: '.PROJECT_PATH.'/core/domains/domain_edit.php?id='.urlencode($_REQUEST['domain_uuid']));
			exit;
	}

//get the variables
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

//prepare to page the results
	$sql = "select count(domain_setting_uuid) from v_domain_settings ";
	$sql .= "where domain_uuid = :domain_uuid ";
	$parameters['domain_uuid'] = $domain_uuid;
	$database = new database;
	$num_rows = $database->select($sql, $parameters, 'column');

//get the list
	$sql = str_replace('count(domain_setting_uuid)', '*', $sql);
	if ($order_by == '') {
		$sql .= " order by domain_setting_category, domain_setting_subcategory, domain_setting_order asc, domain_setting_name, domain_setting_value ";
	}
	else {
		$sql .= order_by($order_by, $order);
	}
	$database = new database;
	$domain_settings = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

//create token
	$object = new token;
	$token = $object->create('/core/domain_settings/domain_settings.php');

//copy settings javascript
	if (
		permission_exists("domain_select") &&
		permission_exists("domain_setting_add") &&
		is_array($_SESSION['domains']) &&
		@sizeof($_SESSION['domains']) > 1
		) {
		echo "<script language='javascript' type='text/javascript'>\n";
		echo "	var fade_speed = 400;\n";
		echo "	function show_domains() {\n";
		echo "		$('#button_copy').fadeOut(fade_speed, function() {\n";
		echo "			$('#button_reset').fadeIn(fade_speed);\n";
		echo "			$('#target_domain').fadeIn(fade_speed);\n";
		echo "			$('#button_paste').fadeIn(fade_speed);\n";
		echo "			document.getElementById('domain_uuid_target').value = '';\n";
		echo "		});";
		echo "	}";
		echo "	function hide_domains() {\n";
		echo "		$('#button_reset').fadeOut(fade_speed);\n";
		echo "		$('#target_domain').fadeOut(fade_speed);\n";
		echo "		$('#button_paste').fadeOut(fade_speed, function() {\n";
		echo "			$('#button_copy').fadeIn(fade_speed);\n";
		echo "			document.getElementById('target_domain').selectedIndex = 0;\n";
		echo "			document.getElementById('domain_uuid_target').value = '';\n";
		echo "		});\n";
		echo "	}\n";
		echo "</script>";
	}

//show the content
	echo "<div class='action_bar sub'>\n";
	echo "	<div class='heading'><b>".$text['header-domain_settings']." (".$num_rows.")</b></div>\n";
	echo "	<div class='actions'>\n";
	if (permission_exists('domain_setting_add')) {
		echo button::create(['type'=>'button','label'=>$text['button-add'],'icon'=>$_SESSION['theme']['button_icon_add'],'link'=>PROJECT_PATH."/core/domain_settings/domain_setting_edit.php?domain_uuid=".urlencode($domain_uuid)]);
	}
	if (permission_exists("domain_select") && permission_exists("domain_setting_add") && $num_rows) {
		echo button::create(['type'=>'button','label'=>$text['button-copy'],'id'=>'button_copy','icon'=>$_SESSION['theme']['button_icon_copy'],'onclick'=>'show_domains();']);
		echo button::create(['type'=>'button','label'=>$text['button-reset'],'id'=>'button_reset','icon'=>$_SESSION['theme']['button_icon_reset'],'style'=>'display: none;','onclick'=>'hide_domains();']);
		echo 	"<select class='formfld' style='display: none; width: auto;' id='target_domain' onchange=\"document.getElementById('domain_uuid_target').value = this.options[this.selectedIndex].value;\">\n";
		echo "		<option value='' selected='selected' disabled='disabled'>".$text['label-domain']."...</option>\n";
		foreach ($_SESSION['domains'] as $domain) {
			if ($domain['domain_uuid'] == $domain_uuid) { continue; }
			echo "	<option value='".escape($domain["domain_uuid"])."'>".escape($domain["domain_name"])."</option>\n";
		}
		if (permission_exists('default_setting_add') && permission_exists('default_setting_edit')) {
			echo "	<option value='' disabled='disabled'></option>\n";
			echo "	<option value='default'>".$text['label-default_settings']."</option>\n";
		}
		echo "	</select>";
		echo button::create(['type'=>'button','label'=>$text['button-paste'],'id'=>'button_paste','icon'=>$_SESSION['theme']['button_icon_paste'],'style'=>'display: none;','onclick'=>"if (confirm('".$text['confirm-copy']."')) { list_action_set('copy'); list_form_submit('form_list'); } else { this.blur(); return false; }"]);
	}
	if (permission_exists('domain_setting_edit') && $num_rows) {
		echo button::create(['type'=>'button','label'=>$text['button-toggle'],'icon'=>$_SESSION['theme']['button_icon_toggle'],'onclick'=>"if (confirm('".$text['confirm-toggle']."')) { list_action_set('toggle'); list_form_submit('form_list'); } else { this.blur(); return false; }"]);
	}
	if (permission_exists('domain_setting_delete') && $num_rows) {
		echo button::create(['type'=>'button','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'onclick'=>"if (confirm('".$text['confirm-delete']."')) { list_action_set('delete'); list_form_submit('form_list'); } else { this.blur(); return false; }"]);
	}
	if (permission_exists('default_setting_view') && $num_rows) {
		echo button::create(['type'=>'button','label'=>$text['button-reload'],'icon'=>$_SESSION['theme']['button_icon_reload'],'style'=>'margin-left: 15px;','link'=>PROJECT_PATH.'/core/default_settings/default_settings_reload.php?id='.$domain_uuid]);
	}
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	echo $text['header_description-domain_settings']."\n";
	echo "<br /><br />\n";

	echo "<form id='form_list' method='post' action='".PROJECT_PATH."/core/domain_settings/domain_settings.php'>\n";
	echo "<input type='hidden' id='action' name='action' value=''>\n";
	echo "<input type='hidden' name='domain_uuid' value='".escape($domain_uuid)."'>\n";
	echo "<input type='hidden' name='domain_uuid_target' id='domain_uuid_target' value=''>\n";

	echo "<table class='list'>\n";
	if (is_array($domain_settings) && @sizeof($domain_settings) != 0) {
		$x = 0;
		foreach ($domain_settings as $row) {
			$domain_setting_category = strtolower($row['domain_setting_category']);

			$label_domain_setting_category = $row['domain_setting_category'];
			switch (strtolower($label_domain_setting_category)) {
				case "api" : $label_domain_setting_category = "API"; break;
				case "cdr" : $label_domain_setting_category = "CDR"; break;
				case "ldap" : $label_domain_setting_category = "LDAP"; break;
				case "ivr_menu" : $label_domain_setting_category = "IVR Menu"; break;
				default:
					$label_domain_setting_category = str_replace("_", " ", $label_domain_setting_category);
					$label_domain_setting_category = str_replace("-", " ", $label_domain_setting_category);
					$label_domain_setting_category = ucwords($label_domain_setting_category);
			}

			if ($previous_domain_setting_category != $row['domain_setting_category']) {
				if ($previous_domain_setting_category != '') {
					echo "</table>\n";

					echo "<br>\n";
				}
				echo "<b>".escape($label_domain_setting_category)."</b><br>\n";

				echo "<table class='list'>\n";
				echo "<tr class='list-header'>\n";
				if (permission_exists('domain_setting_add') || permission_exists('domain_setting_edit') || permission_exists('domain_setting_delete')) {
					echo "	<th class='checkbox'>\n";
					echo "		<input type='checkbox' id='checkbox_all_".$domain_setting_category."' name='checkbox_all' onclick=\"list_all_toggle('".$domain_setting_category."');\">\n";
					echo "	</th>\n";
				}
				if ($_GET['show'] == 'all' && permission_exists('domain_setting_all')) {
					echo th_order_by('domain_name', $text['label-domain'], $order_by, $order);
				}
				echo th_order_by('domain_setting_subcategory', $text['label-subcategory'], $order_by, $order, null, "class='pct-35'");
				echo th_order_by('domain_setting_name', $text['label-type'], $order_by, $order, null, "class='pct-10 hide-sm-dn'");
				echo th_order_by('domain_setting_value', $text['label-value'], $order_by, $order, null, "class='pct-30'");
				echo th_order_by('domain_setting_enabled', $text['label-enabled'], $order_by, $order, null, "class='center'");
				echo "	<th class='pct-25 hide-sm-dn'>".$text['label-description']."</th>\n";
				if (permission_exists('domain_setting_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
					echo "	<td class='action-button'>&nbsp;</td>\n";
				}
				echo "</tr>\n";
			}
			if (permission_exists('domain_setting_edit')) {
				$list_row_url = PROJECT_PATH."/core/domain_settings/domain_setting_edit.php?domain_uuid=".escape($row['domain_uuid'])."&id=".escape($row['domain_setting_uuid']);
			}
			echo "<tr class='list-row' href='".$list_row_url."'>\n";
			if (permission_exists('domain_setting_add') || permission_exists('domain_setting_edit') || permission_exists('domain_setting_delete')) {
				echo "	<td class='checkbox'>\n";
				echo "		<input type='checkbox' name='domain_settings[$x][checked]' id='checkbox_".$x."' class='checkbox_".$domain_setting_category."' value='true' onclick=\"if (!this.checked) { document.getElementById('checkbox_all_".$domain_setting_category."').checked = false; }\">\n";
				echo "		<input type='hidden' name='domain_settings[$x][uuid]' value='".escape($row['domain_setting_uuid'])."' />\n";
				echo "	</td>\n";
			}
			echo "	<td class='overflow no-wrap'>";
			if (permission_exists('domain_setting_edit')) {
				echo "	<a href='".$list_row_url."'>".escape($row['domain_setting_subcategory'])."</a>";
			}
			else {
				echo escape($row['domain_setting_subcategory']);
			}
			echo "	</td>\n";
			echo "	<td class='hide-sm-dn'>".escape($row['domain_setting_name'])."</td>\n";
			echo "	<td class='overflow no-wrap'>\n";
			$category = $row['domain_setting_category'];
			$subcategory = $row['domain_setting_subcategory'];
			$name = $row['domain_setting_name'];
			if ($category == "domain" && $subcategory == "menu" && $name == "uuid" ) {
				$sql = "select * from v_menus ";
				$sql .= "where menu_uuid = :menu_uuid ";
				$parameters['menu_uuid'] = $row['domain_setting_value'];
				$database = new database;
				$sub_result = $database->select($sql, $parameters, 'all');
				if (is_array($sub_result) && sizeof($sub_result) != 0) {
					foreach ($sub_result as &$sub_row) {
						echo escape($sub_row["menu_language"])." - ".escape($sub_row["menu_name"])."\n";
					}
				}
				unset($sql, $parameters, $sub_result, $sub_row);
			}
			else if ($category == "domain" && $subcategory == "template" && $name == "name" ) {
				echo "		".ucwords($row['domain_setting_value']);
			}
			else if ($category == "domain" && $subcategory == "time_format" && $name == "text" ) {
				switch ($row['domain_setting_value']) {
					case '12h': echo $text['label-12-hour']; break;
					case '24h': echo $text['label-24-hour']; break;
				}
			}
			else if (
				( $category == "theme" && $subcategory == "menu_main_icons" && $name == "boolean" ) ||
				( $category == "theme" && $subcategory == "menu_sub_icons" && $name == "boolean" ) ||
				( $category == "theme" && $subcategory == "menu_brand_type" && $name == "text" ) ||
				( $category == "theme" && $subcategory == "menu_style" && $name == "text" ) ||
				( $category == "theme" && $subcategory == "menu_position" && $name == "text" ) ||
				( $category == "theme" && $subcategory == "logo_align" && $name == "text" )
				) {
				echo "		".$text['label-'.escape($row['domain_setting_value'])];
			}
			else if ($subcategory == 'password' || substr_count($subcategory, '_password') > 0 || $category == "login" && $subcategory == "password_reset_key" && $name == "text") {
				echo "		".str_repeat('*', strlen(escape($row['domain_setting_value'])));
			}
			else if ($category == 'theme' && $subcategory == 'button_icons' && $name == 'text') {
				echo "		".$text['option-button_icons_'.$row['domain_setting_value']]."\n";
			}
			else if ($category == "theme" && substr_count($subcategory, "_color") > 0 && ($name == "text" || $name == 'array')) {
				echo "		".(img_spacer('15px', '15px', 'background: '.escape($row['domain_setting_value']).'; margin-right: 4px; vertical-align: middle; border: 1px solid '.(color_adjust($row['domain_setting_value'], -0.18)).'; padding: -1px;'));
				echo "<span style=\"font-family: 'Courier New'; line-height: 6pt;\">".escape($row['domain_setting_value'])."</span>\n";
			}
			else if ($category == 'recordings' && $subcategory == 'storage_type' && $name == 'text') {
				echo "		".$text['label-'.$row['domain_setting_value']]."\n";
			}
			else {
				echo "		".escape($row['domain_setting_value'])."\n";
			}
			echo "	</td>\n";
			if (permission_exists('domain_setting_edit')) {
				echo "	<td class='no-link center'>\n";
				echo button::create(['type'=>'submit','class'=>'link','label'=>$text['label-'.$row['domain_setting_enabled']],'title'=>$text['button-toggle'],'onclick'=>"list_self_check('checkbox_".$x."'); list_action_set('toggle'); list_form_submit('form_list')"]);
			}
			else {
				echo "	<td class='center'>\n";
				echo $text['label-'.$row['domain_setting_enabled']];
			}
			echo "	</td>\n";
			echo "	<td class='description overflow hide-sm-dn' title=\"".escape($row['domain_setting_description'])."\">".escape($row['domain_setting_description'])."&nbsp;</td>\n";
			if (permission_exists('domain_setting_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
				echo "	<td class='action-button'>\n";
				echo button::create(['type'=>'button','title'=>$text['button-edit'],'icon'=>$_SESSION['theme']['button_icon_edit'],'link'=>$list_row_url]);
				echo "	</td>\n";
			}
			echo "</tr>\n";

			//set the previous category
			$previous_domain_setting_category = $row['domain_setting_category'];
			$x++;
		}
		unset($domain_settings);
	}

	echo "</table>\n";
	echo "<br />\n";
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";
	echo "</form>\n";

?>