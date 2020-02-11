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
	Portions created by the Initial Developer are Copyright (C) 2008 - 2019
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//includes
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('menu_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get the http post data
	if (is_array($_POST['menus'])) {
		$action = $_POST['action'];
		$search = $_POST['search'];
		$menus = $_POST['menus'];
	}

//process the http post data by action
	if ($action != '' && is_array($menus) && @sizeof($menus) != 0) {
		switch ($action) {
			case 'delete':
				if (permission_exists('menu_delete')) {
					$obj = new menu;
					$obj->delete($menus);
				}
				break;
		}

		header('Location: menu.php'.($search != '' ? '?search='.urlencode($search) : null));
		exit;
	}

//get order and order by
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

//add the search string
	if (isset($_GET["search"])) {
		$search =  strtolower($_GET["search"]);
		$sql_search = " (";
		$sql_search .= "	lower(menu_name) like :search ";
		$sql_search .= "	or lower(menu_language) like :search ";
		$sql_search .= "	or lower(menu_description) like :search ";
		$sql_search .= ") ";
		$parameters['search'] = '%'.$search.'%';
	}

//get the count
	$sql = "select count(menu_uuid) from v_menus ";
	if (isset($sql_search)) {
		$sql .= "where ".$sql_search;
	}
	$database = new database;
	$num_rows = $database->select($sql, $parameters, 'column');

//get the list
	$sql = str_replace('count(menu_uuid)', '*', $sql);
	$sql .= order_by($order_by, $order, 'menu_name', 'asc');
	$sql .= limit_offset($rows_per_page, $offset);
	$database = new database;
	$menus = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//include the header
	$document['title'] = $text['title-menus'];
	require_once "resources/header.php";

//show the content
	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>".$text['title-menus']." (".$num_rows.")</b></div>\n";
	echo "	<div class='actions'>\n";
	if (permission_exists('menu_add')) {
		echo button::create(['type'=>'button','label'=>$text['button-add'],'icon'=>$_SESSION['theme']['button_icon_add'],'link'=>'menu_edit.php']);
	}
	//if (permission_exists('menu_add') && $menus) {
	//	echo button::create(['type'=>'button','label'=>$text['button-copy'],'icon'=>$_SESSION['theme']['button_icon_copy'],'onclick'=>"if (confirm('".$text['confirm-copy']."')) { list_action_set('copy'); list_form_submit('form_list'); } else { this.blur(); return false; }"]);
	//}
	//if (permission_exists('menu_edit') && $menus) {
	//	echo button::create(['type'=>'button','label'=>$text['button-toggle'],'icon'=>$_SESSION['theme']['button_icon_toggle'],'onclick'=>"if (confirm('".$text['confirm-toggle']."')) { list_action_set('toggle'); list_form_submit('form_list'); } else { this.blur(); return false; }"]);
	//}
	if (permission_exists('menu_delete') && $menus) {
		echo button::create(['type'=>'button','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'onclick'=>"if (confirm('".$text['confirm-delete']."')) { list_action_set('delete'); list_form_submit('form_list'); } else { this.blur(); return false; }"]);
	}
	echo 		"<form id='form_search' class='inline' method='get'>\n";
	echo 		"<input type='text' class='txt list-search' name='search' id='search' value=\"".escape($search)."\" placeholder=\"".$text['label-search']."\" onkeydown='list_search_reset();'>";
	echo button::create(['label'=>$text['button-search'],'icon'=>$_SESSION['theme']['button_icon_search'],'type'=>'submit','id'=>'btn_search','style'=>($search != '' ? 'display: none;' : null)]);
	echo button::create(['label'=>$text['button-reset'],'icon'=>$_SESSION['theme']['button_icon_reset'],'type'=>'button','id'=>'btn_reset','link'=>'menu.php','style'=>($search == '' ? 'display: none;' : null)]);
	if ($paging_controls_mini != '') {
		echo 	"<span style='margin-left: 15px;'>".$paging_controls_mini."</span>\n";
	}
	echo "		</form>\n";
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	echo $text['description-menus']."\n";
	echo "<br /><br />\n";

	echo "<form id='form_list' method='post'>\n";
	echo "<input type='hidden' id='action' name='action' value=''>\n";
	echo "<input type='hidden' name='search' value=\"".escape($search)."\">\n";

	echo "<table class='list'>\n";
	echo "<tr class='list-header'>\n";
	if (permission_exists('menu_add') || permission_exists('menu_edit') || permission_exists('menu_delete')) {
		echo "	<th class='checkbox'>\n";
		echo "		<input type='checkbox' id='checkbox_all' name='checkbox_all' onclick='list_all_toggle();' ".($menus ?: "style='visibility: hidden;'").">\n";
		echo "	</th>\n";
	}
	echo th_order_by('menu_name', $text['label-menu_name'], $order_by, $order);
	echo th_order_by('menu_language', $text['label-menu_language'], $order_by, $order);
	echo "	<th class='hide-sm-dn'>".$text['label-menu_description']."</th>\n";
	if (permission_exists('menu_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
		echo "	<td class='action-button'>&nbsp;</td>\n";
	}
	echo "</tr>\n";

	if (is_array($menus) && @sizeof($menus) != 0) {
		$x = 0;
		foreach ($menus as $row) {
			if (permission_exists('menu_edit')) {
				$list_row_url = "menu_edit.php?id=".urlencode($row['menu_uuid']);
			}
			echo "<tr class='list-row' href='".$list_row_url."'>\n";
			if (permission_exists('menu_add') || permission_exists('menu_edit') || permission_exists('menu_delete')) {
				echo "	<td class='checkbox'>\n";
				echo "		<input type='checkbox' name='menus[$x][checked]' id='checkbox_".$x."' value='true' onclick=\"if (!this.checked) { document.getElementById('checkbox_all').checked = false; }\">\n";
				echo "		<input type='hidden' name='menus[$x][uuid]' value='".escape($row['menu_uuid'])."' />\n";
				echo "	</td>\n";
			}
			echo "	<td>\n";
			if (permission_exists('menu_edit')) {
				echo "	<a href='".$list_row_url."' title=\"".$text['button-edit']."\">".escape($row['menu_name'])."</a>\n";
			}
			else {
				echo "	".escape($row['menu_name']);
			}
			echo "	</td>\n";
			echo "	<td>".escape($row['menu_language'])."</td>\n";
			echo "	<td class='description overflow hide-sm-dn'>".escape($row['menu_description'])."</td>\n";
			if (permission_exists('menu_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
				echo "	<td class='action-button'>\n";
				echo button::create(['type'=>'button','title'=>$text['button-edit'],'icon'=>$_SESSION['theme']['button_icon_edit'],'link'=>$list_row_url]);
				echo "	</td>\n";
			}
			echo "</tr>\n";
			$x++;
		}
		unset($menus);
	}

	echo "</table>\n";
	echo "<br />\n";
	echo "<div align='center'>".$paging_controls."</div>\n";
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";
	echo "</form>\n";

//include the footer
	require_once "resources/footer.php";

?>
