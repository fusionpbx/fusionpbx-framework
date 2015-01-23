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
 Portions created by the Initial Developer are Copyright (C) 2008-2014
 the Initial Developer. All Rights Reserved.

 Contributor(s):
 Mark J Crane <markjcrane@fusionpbx.com>
*/
require_once "root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";
if (permission_exists('default_setting_view')) {
	//access granted
}
else {
	echo "access denied";
	exit;
}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get posted values, if any
if (sizeof($_REQUEST) > 0) {

	$action = check_str($_REQUEST["action"]);
	$default_setting_uuids = $_REQUEST["id"];
	$enabled = check_str($_REQUEST['enabled']);
	$category = check_str($_REQUEST['category']);

	if (sizeof($default_setting_uuids) == 1 && $enabled != '') {
		$sql = "update v_default_settings set ";
		$sql .= "default_setting_enabled = '".$enabled."' ";
		$sql .= "where default_setting_uuid = '".$default_setting_uuids[0]."'";
		$db->exec(check_sql($sql));
		unset($sql);

		$_SESSION["message"] = $text['message-update'];
		header("Location: default_settings.php#".$category);
		exit;
	}

	if ($action == 'copy' && permission_exists('domain_setting_add')) {

		$target_domain_uuid = check_str($_POST["target_domain_uuid"]);

		if ($target_domain_uuid != '' && sizeof($default_setting_uuids) > 0) {

			$settings_copied = 0;

			foreach ($default_setting_uuids as $default_setting_uuid) {

				// get default setting from db
				$sql = "select * from v_default_settings ";
				$sql .= "where default_setting_uuid = '".$default_setting_uuid."' ";
				$prep_statement = $db->prepare(check_sql($sql));
				$prep_statement->execute();
				$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
				foreach ($result as &$row) {
					$default_setting_category = $row["default_setting_category"];
					$default_setting_subcategory = $row["default_setting_subcategory"];
					$default_setting_name = $row["default_setting_name"];
					$default_setting_value = $row["default_setting_value"];
					$default_setting_order = $row["default_setting_order"];
					$default_setting_enabled = $row["default_setting_enabled"];
					$default_setting_description = $row["default_setting_description"];
					break; //limit to 1 row
				}
				unset ($prep_statement);

				// check if exists
				$sql = "select domain_setting_uuid from v_domain_settings ";
				$sql .= "where domain_uuid = '".$target_domain_uuid."' ";
				$sql .= "and domain_setting_category = '".$default_setting_category."' ";
				$sql .= "and domain_setting_subcategory = '".$default_setting_subcategory."' ";
				$sql .= "and domain_setting_name = '".$default_setting_name."' ";
				$sql .= "and domain_setting_name <> 'array' ";
				$prep_statement = $db->prepare(check_sql($sql));
				$prep_statement->execute();
				$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
				if (sizeof($result) > 0) {
					foreach ($result as &$row) {
						$target_domain_setting_uuid = $row["domain_setting_uuid"];
						break;
					}
					$action = "update";
				}
				else {
					$action = "add";
				}
				unset ($prep_statement);

				// fix null
				$default_setting_order = ($default_setting_order != '') ? $default_setting_order : 'null';

				if ($action == "add" && permission_exists("domain_select") && permission_exists("domain_setting_add") && count($_SESSION['domains']) > 1) {

					// insert for target domain
					$sql = "insert into v_domain_settings ";
					$sql .= "(";
					$sql .= "domain_uuid, ";
					$sql .= "domain_setting_uuid, ";
					$sql .= "domain_setting_category, ";
					$sql .= "domain_setting_subcategory, ";
					$sql .= "domain_setting_name, ";
					$sql .= "domain_setting_value, ";
					$sql .= "domain_setting_order, ";
					$sql .= "domain_setting_enabled, ";
					$sql .= "domain_setting_description ";
					$sql .= ")";
					$sql .= "values ";
					$sql .= "(";
					$sql .= "'".$target_domain_uuid."', ";
					$sql .= "'".uuid()."', ";
					$sql .= "'".$default_setting_category."', ";
					$sql .= "'".$default_setting_subcategory."', ";
					$sql .= "'".$default_setting_name."', ";
					$sql .= "'".$default_setting_value."', ";
					$sql .= " ".$default_setting_order." , ";
					$sql .= "'".$default_setting_enabled."', ";
					$sql .= "'".$default_setting_description."' ";
					$sql .= ")";
					$db->exec(check_sql($sql));
					unset($sql);

					$settings_copied++;

				} // add

				if ($action == "update" && permission_exists('domain_setting_edit')) {

					$sql = "update v_domain_settings set ";
					$sql .= "domain_setting_category = '".$default_setting_category."', ";
					$sql .= "domain_setting_subcategory = '".$default_setting_subcategory."', ";
					$sql .= "domain_setting_name = '".$default_setting_name."', ";
					$sql .= "domain_setting_value = '".$default_setting_value."', ";
					$sql .= "domain_setting_order = ".$default_setting_order.", ";
					$sql .= "domain_setting_enabled = '".$default_setting_enabled."', ";
					$sql .= "domain_setting_description = '".$default_setting_description."' ";
					$sql .= "where domain_uuid = '".$target_domain_uuid."' ";
					$sql .= "and domain_setting_uuid = '".$target_domain_setting_uuid."' ";
					$db->exec(check_sql($sql));
					unset($sql);

					$settings_copied++;

				} // update

			} // foreach

			// set message
			$_SESSION["message"] = $text['message-copy'].": ".$settings_copied;

		}
		else {
			// set message
			$_SESSION["message"] = $text['message-copy_failed'];
		}

		header("Location: default_settings.php");
		exit;

	}

	if ($action == 'delete' && permission_exists('default_setting_delete')) {

		if (sizeof($default_setting_uuids) > 0) {
			foreach ($default_setting_uuids as $default_setting_uuid) {
				//delete default_setting(s)
				$sql = "delete from v_default_settings ";
				$sql .= "where default_setting_uuid = '".$default_setting_uuid."' ";
				$prep_statement = $db->prepare(check_sql($sql));
				$prep_statement->execute();
				unset($sql);
			}

			// set message
			$_SESSION["message"] = $text['message-delete'].": ".sizeof($default_setting_uuids);
		}
		else {
			// set message
			$_SESSION["message"] = $text['message-delete_failed'];
			$_SESSION["message_mood"] = "negative";
		}

		header("Location: default_settings.php");
		exit;

	}

} // post



require_once "resources/header.php";
$document['title'] = $text['title-default_settings'];

require_once "resources/paging.php";

//get variables used to control the order
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

// copy settings javascript
if (permission_exists("domain_select") && permission_exists("domain_setting_add") && count($_SESSION['domains']) > 1) {
	echo "<script language='javascript' type='text/javascript'>\n";
	echo "	var fade_speed = 400;\n";
	echo "	function show_domains() {\n";
	echo "		document.getElementById('action').value = 'copy';\n";
	echo "		$('#button_copy').fadeOut(fade_speed, function() {\n";
	echo "			$('#button_back').fadeIn(fade_speed);\n";
	echo "			$('#target_domain_uuid').fadeIn(fade_speed);\n";
	echo "			$('#button_paste').fadeIn(fade_speed);\n";
	echo "		});";
	echo "	}";
	echo "	function hide_domains() {\n";
	echo "		document.getElementById('action').value = '';\n";
	echo "		$('#button_back').fadeOut(fade_speed);\n";
	echo "		$('#target_domain_uuid').fadeOut(fade_speed);\n";
	echo "		$('#button_paste').fadeOut(fade_speed, function() {\n";
	echo "			$('#button_copy').fadeIn(fade_speed);\n";
	echo "			document.getElementById('target_domain_uuid').selectedIndex = 0;\n";
	echo "		});\n";
	echo "	}\n";
	echo "\n";
	echo "	$( document ).ready(function() {\n";
	echo "		// scroll to previous category\n";
	echo "		var category_span_id;\n";
	echo "		var url = document.location.href;\n";
	echo "		var hashindex = url.indexOf('#');\n";
	echo "		if (hashindex == -1) { }\n";
	echo "		else {\n";
	echo "			category_span_id = url.substr(hashindex + 1);\n";
	echo "		}\n";
	echo "		if (category_span_id) {\n";
	echo "			$('#page').animate({scrollTop: $('#anchor_'+category_span_id).offset().top - 200}, 'slow');\n";
	echo "		}\n";
	echo "	});\n";
	echo "</script>";
}

//show the content
	echo "<form name='frm' id='frm' method='post' action=''>";
	echo "<input type='hidden' name='action' id='action' value=''>";

	echo "<table width='100%' border='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='50%' align='left' valign='top' nowrap='nowrap'>";
	echo "			<b>".$text['header-default_settings']."</b>";
	echo "			<br><br>";
	echo "			".$text['description-default_settings'];
	echo "		</td>\n";
	echo "		<td width='50%' align='right' valign='top'>";
	if (permission_exists("domain_select") && permission_exists("domain_setting_add") && count($_SESSION['domains']) > 1) {
		echo "		<input type='button' class='btn' id='button_copy' alt='".$text['button-copy']."' onclick='show_domains();' value='".$text['button-copy']."'>";
		echo "		<input type='button' class='btn' style='display: none;' id='button_back' alt='".$text['button-back']."' onclick='hide_domains();' value='".$text['button-back']."'> ";
		echo "		<select class='formfld' style='display: none; width: auto;' name='target_domain_uuid' id='target_domain_uuid'>\n";
		echo "			<option value=''>Select Domain...</option>\n";
		foreach ($_SESSION['domains'] as $domain) {
			echo "		<option value='".$domain["domain_uuid"]."'>".$domain["domain_name"]."</option>\n";
		}
		echo "		</select>\n";
		echo "		<input type='button' class='btn' id='button_paste' style='display: none;' alt='".$text['button-paste']."' value='".$text['button-paste']."' onclick='document.forms.frm.submit();'>";
	}
	else {
		echo "		&nbsp;";
	}
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";
	echo "<br>";

	//prepare to page the results
		$sql = "select count(*) as num_rows from v_default_settings ";
		$prep_statement = $db->prepare($sql);
		if ($prep_statement) {
		$prep_statement->execute();
			$row = $prep_statement->fetch(PDO::FETCH_ASSOC);
			if ($row['num_rows'] > 0) {
				$num_rows = $row['num_rows'];
			}
			else {
				$num_rows = '0';
			}
		}

	//prepare to page the results
		$rows_per_page = 200;
		$param = "";
		$page = $_GET['page'];
		if (strlen($page) == 0) { $page = 0; $_GET['page'] = 0; }
		list($paging_controls, $rows_per_page, $var3) = paging($num_rows, $param, $rows_per_page);
		$offset = $rows_per_page * $page;

	//get the list
		$sql = "select * from v_default_settings ";
		if (strlen($order_by) == 0) {
			$sql .= "order by default_setting_category, default_setting_subcategory, default_setting_order asc ";
		}
		else {
			$sql .= "order by $order_by $order ";
		}
		$sql .= "limit $rows_per_page offset $offset ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll(PDO::FETCH_NAMED);
		$result_count = count($result);
		unset ($prep_statement, $sql);

	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";

	echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";

	if ($result_count > 0) {
		$previous_category = '';
		foreach($result as $row) {

			if ($previous_category != $row['default_setting_category']) {
				$c = 0;
				echo "<tr>\n";
				echo "	<td colspan='7' align='left'>\n";
				if ($previous_category != '') {
					echo "		<span id='anchor_".$row['default_setting_category']."'></span>";
					echo "		<br /><br />";
				}
				echo "		<br />\n";
				echo "		<b>\n";
				if (strtolower($row['default_setting_category']) == "cdr") {
					echo "		CDR";
				}
				elseif (strtolower($row['default_setting_category']) == "ldap") {
					echo "		LDAP";
				}
				else {
					echo "		".ucwords(str_replace("_", " ", $row['default_setting_category']));
				}
				echo "		</b>\n";
				echo "	</td>\n";
				echo "</tr>\n";
				echo "<tr>\n";
				if (
					(permission_exists("domain_select") && permission_exists("domain_setting_add") && count($_SESSION['domains']) > 1) ||
					permission_exists('default_setting_delete')
					) {
					echo "<th style='width: 30px; text-align: center; padding: 0px;'><input type='checkbox' onchange=\"(this.checked) ? check('all','".strtolower($row['default_setting_category'])."') : check('none','".strtolower($row['default_setting_category'])."');\"></th>";
				}
				echo "<th>".$text['label-subcategory']."</th>";
				echo "<th>".$text['label-type']."</th>";
				echo "<th>".$text['label-value']."</th>";
				echo "<th style='text-align: center;'>".$text['label-enabled']."</th>";
				echo "<th>".$text['label-description']."</th>";
				echo "<td class='list_control_icons'>";
				if (permission_exists('default_setting_add')) {
					echo "<a href='default_setting_edit.php?default_setting_category=".urlencode($row['default_setting_category'])."' alt='".$text['button-add']."'>".$v_link_label_add."</a>";
				}
				if (permission_exists('default_setting_delete')) {
					echo "<a href='javascript:void(0);' onclick=\"if (confirm('".$text['confirm-delete']."')) { document.getElementById('action').value = 'delete'; document.forms.frm.submit(); }\" alt='".$text['button-delete']."'>".$v_link_label_delete."</a>";
				}
				echo "</td>\n";
				echo "</tr>\n";
			}


			$tr_link = (permission_exists('default_setting_edit')) ? "href='default_setting_edit.php?id=".$row['default_setting_uuid']."'" : null;
			echo "<tr ".$tr_link.">\n";
			if (
				(permission_exists("domain_select") && permission_exists("domain_setting_add") && count($_SESSION['domains']) > 1) ||
				permission_exists("default_setting_delete")
				) {
				echo "	<td valign='top' class='".$row_style[$c]." tr_link_void' style='text-align: center; padding: 3px 0px 0px 0px;'><input type='checkbox' name='id[]' id='checkbox_".$row['default_setting_uuid']."' value='".$row['default_setting_uuid']."'></td>\n";
				$subcat_ids[strtolower($row['default_setting_category'])][] = 'checkbox_'.$row['default_setting_uuid'];
			}
			echo "	<td valign='top' class='".$row_style[$c]."'>";
			if (permission_exists('default_setting_edit')) {
				echo "<a href='default_setting_edit.php?id=".$row['default_setting_uuid']."'>".$row['default_setting_subcategory']."</a>";
			}
			else {
				echo $row['default_setting_subcategory'];
			}
			echo "	<td valign='top' class='".$row_style[$c]."'>".$row['default_setting_name']."</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>\n";

			$category = $row['default_setting_category'];
			$subcategory = $row['default_setting_subcategory'];
			$name = $row['default_setting_name'];
			if ($category == "domain" && $subcategory == "menu" && $name == "uuid" ) {
				$sql = "select * from v_menus ";
				$sql .= "where menu_uuid = '".$row['default_setting_value']."' ";
				$sub_prep_statement = $db->prepare(check_sql($sql));
				$sub_prep_statement->execute();
				$sub_result = $sub_prep_statement->fetchAll(PDO::FETCH_NAMED);
				foreach ($sub_result as &$sub_row) {
					echo $sub_row["menu_language"]." - ".$sub_row["menu_name"]."\n";
				}
			}
			elseif ($category == "domain" && $subcategory == "template" && $name == "name" ) {
				echo "		".ucwords($row['default_setting_value']);
			}
			elseif ($category == "email" && $subcategory == "smtp_password" && $name == "var" ) {
				echo "		******** &nbsp;\n";
			}
			elseif ($category == "provision" && $subcategory == "password" && $name == "var" ) {
				echo "		******** &nbsp;\n";
			} else {
				echo "		".substr($row['default_setting_value'],0,58);
			}
			echo "		&nbsp;\n";
			echo "	</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]." tr_link_void' style='text-align: center;'>\n";
			echo "		<a href='?id[]=".$row['default_setting_uuid']."&enabled=".(($row['default_setting_enabled'] == 'true') ? 'false' : 'true')."&category=".$category."'>".ucwords($row['default_setting_enabled'])."</a>\n";
			echo "	</td>\n";
			echo "	<td valign='top' class='row_stylebg'>".$row['default_setting_description']."&nbsp;</td>\n";
			echo "	<td class='list_control_icons'>";
			if (permission_exists('default_setting_edit')) {
				echo "<a href='default_setting_edit.php?id=".$row['default_setting_uuid']."' alt='".$text['button-edit']."'>$v_link_label_edit</a>";
			}
			if (permission_exists('default_setting_delete')) {
				echo "<a href='default_settings.php?id[]=".$row['default_setting_uuid']."&action=delete' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">$v_link_label_delete</a>";
			}
			echo "	</td>\n";
			echo "</tr>\n";
			$previous_category = $row['default_setting_category'];
			if ($c==0) { $c=1; } else { $c=0; }
		} //end foreach
		unset($sql, $result, $row_count);
	} //end if results

	echo "<tr>\n";
	if (
		(permission_exists("domain_select") && permission_exists("domain_setting_add") && count($_SESSION['domains']) > 1) ||
		permission_exists("domain_delete")
		) {
		$colspan = 7;
	}
	else {
		$colspan = 6;
	}
	echo "<td colspan='".$colspan."' class='list_control_icons'>\n";
	if (permission_exists('default_setting_add')) {
		echo "<a href='default_setting_edit.php?' alt='".$text['button-add']."'>$v_link_label_add</a>";
	}
	if (permission_exists('default_setting_delete')) {
		echo "<a href='javascript:void(0);' onclick=\"if (confirm('".$text['confirm-delete']."')) { document.getElementById('action').value = 'delete'; document.forms.frm.submit(); }\" alt='".$text['button-delete']."'>".$v_link_label_delete."</a>";
	}
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>";
	echo "<br />";
	echo $paging_controls;
	echo "<br /><br />";

	echo "</form>";

	echo "<br /><br />";

	// check or uncheck all category checkboxes
	if (sizeof($subcat_ids) > 0) {
		echo "<script>\n";
		echo "	function check(what, category) {\n";
		foreach ($subcat_ids as $default_setting_category => $checkbox_ids) {
			echo "if (category == '".$default_setting_category."') {\n";
			foreach ($checkbox_ids as $index => $checkbox_id) {
				echo "document.getElementById('".$checkbox_id."').checked = (what == 'all') ? true : false;\n";
			}
			echo "}\n";
		}
		echo "	}\n";
		echo "</script>\n";
	}

//include the footer
	require_once "resources/footer.php";
?>