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
	Portions created by the Initial Developer are Copyright (C) 2018-2020
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//includes
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('group_permission_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//action add or update
	if (is_uuid($_REQUEST["group_uuid"])) {
		$group_uuid = $_REQUEST["group_uuid"];
	}

//get the group_name
	if (is_uuid($group_uuid)) {
		$sql = "select group_name from v_groups ";
		$sql .= "where group_uuid = :group_uuid ";
		$parameters['group_uuid'] = $group_uuid;
		$database = new database;
		$group_name = $database->select($sql, $parameters, 'column');
		unset($sql, $parameters);
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//process permission reload
	if ($_GET['action'] == 'reload' && is_uuid($_GET['group_uuid'])) {
		if (is_array($_SESSION["groups"]) && @sizeof($_SESSION["groups"]) != 0) {
			//clear current permissions
				unset($_SESSION['permissions'], $_SESSION['user']['permissions']);
			//get the permissions assigned to the groups that the current user is a member of, set the permissions in session variables
				$x = 0;
				$sql = "select distinct(permission_name) from v_group_permissions ";
				$sql .= "where (domain_uuid = :domain_uuid or domain_uuid is null) ";
				foreach ($_SESSION["groups"] as $field) {
					if (strlen($field['group_name']) > 0) {
						$sql_where_or[] = "group_name = :group_name_".$x;
						$parameters['group_name_'.$x] = $field['group_name'];
						$x++;
					}
				}
				if (is_array($sql_where_or) && @sizeof($sql_where_or) != 0) {
					$sql .= "and (".implode(' or ', $sql_where_or).") ";
				}
				$parameters['domain_uuid'] = $_SESSION["domain_uuid"];
				$database = new database;
				$result = $database->select($sql, $parameters, 'all');
				if (is_array($result) && @sizeof($result) != 0) {
					foreach ($result as $row) {
						$_SESSION['permissions'][$row["permission_name"]] = true;
						$_SESSION["user"]["permissions"][$row["permission_name"]] = true;
					}
				}
				unset($sql, $parameters, $result, $row);
			//set message and redirect
				message::add($text['message-permissions_reloaded'],'positive');
				header('Location: group_permissions.php?group_uuid='.urlencode($_GET['group_uuid']));
				exit;
		}
	}

//get the http post data
	if (is_array($_POST['group_permissions'])) {
		$action = $_POST['action'];
		$search = $_POST['search'];
		$group_permissions = $_POST['group_permissions'];
	}

//process the user data and save it to the database
	if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {
		//get the list
			$sql = "select p.*, ";
			$sql .= "exists(select from v_group_permissions where permission_name = p.permission_name and group_name = :group_name) as permission_assigned ";
			$sql .= "from v_permissions as p ";
			$parameters['group_name'] = $group_name;
			//$sql = "select * from v_group_permissions ";
			//$sql .= "where group_uuid = :group_uuid ";
			//$parameters['group_uuid'] = $group_uuid;
			$database = new database;
			$group_permissions = $database->select($sql, $parameters, 'all');

		//add or remove permissions from the group
			$x = 0;
			if (is_array($_POST['group_permissions'])) {
				foreach($_POST['group_permissions'] as $row) {
					//check to see if the group has been assigned the permission
					$in_database = false;
					foreach($group_permissions as $field) {
						if ($field['permission_name'] === $row['permission_name'] && $field['permission_assigned'] === true) {
							$in_database = true;
							break;
						}
					}

					//add - checked on html form and not in the database
					if ($row['checked'] === 'true') {
						if (!$in_database) {
							if (isset($row['permission_name']) && strlen($row['permission_name']) > 0) {
								$array['add']['group_permissions'][$x]['group_permission_uuid'] = uuid();
								$array['add']['group_permissions'][$x]['permission_name'] = $row['permission_name'];
								$array['add']['group_permissions'][$x]['group_uuid'] = $group_uuid;
								$array['add']['group_permissions'][$x]['group_name'] = $group_name;
								//$array['add']['group_permissions'][$x]['permission_uuid'] = $row['uuid'];
								$x++;
							}
						}
					}

					//delete - unchecked on the form and in the database
					if ($row['checked'] !== 'true') {
						if ($in_database) {
							if (isset($row['permission_name']) && strlen($row['permission_name']) > 0) {
								$array['delete']['group_permissions'][$x]['permission_name'] = $row['permission_name'];
								$array['delete']['group_permissions'][$x]['group_uuid'] = $group_uuid;
								$array['delete']['group_permissions'][$x]['group_name'] = $group_name;
								//$array['delete'][$x]['permission_uuid'] = $row['uuid'];
							}
							$x++;
						}
					}
				}
			}

		//validate the token
			$token = new token;
			if (!$token->validate($_SERVER['PHP_SELF'])) {
				message::add($text['message-invalid_token'],'negative');
				header('Location: group_permissions.php?group_uuid='.urlencode($group_uuid).'&search='.urlencode($search));
				exit;
			}

		//save to the data
			if (is_array($array['add']) && @sizeof($array['add']) != 0) {
				$database = new database;
				$database->app_name = 'groups';
				$database->app_uuid = '2caf27b0-540a-43d5-bb9b-c9871a1e4f84';
				$database->save($array['add']);
				$message = $database->message;
			}

		//delete the permissions
			if (is_array($array['delete']) && @sizeof($array['delete']) != 0) {
				if (permission_exists('group_permission_delete')) {
					$database = new database;
					$database->app_name = 'groups';
					$database->app_uuid = '2caf27b0-540a-43d5-bb9b-c9871a1e4f84';
					$database->delete($array['delete']);
				}
			}

		//set the message
			message::add($text['message-update']);

		//redirect
			header('Location: group_permissions.php?group_uuid='.urlencode($group_uuid));
			exit;
	}

//get order and order by
	//$order_by = $_GET["order_by"];
	//$order = $_GET["order"];

//add the search string
	if (isset($_REQUEST["search"])) {
		$search =  strtolower($_REQUEST["search"]);
		$sql_search = " (";
		$sql_search .= "	lower(p.permission_name) like :search ";
		//$sql_search .= "	or lower(p.group_name) like :search ";
		$sql_search .= ") ";
		$parameters['search'] = '%'.$search.'%';
	}

//get the count
	/*
	$sql = "select count(group_permission_uuid) from v_group_permissions ";
	$sql .= "where group_uuid = :group_uuid ";
	$parameters['group_uuid'] = $group_uuid;
	if (isset($sql_search)) {
		$sql .= "where ".$sql_search;
	}
	$database = new database;
	$num_rows = $database->select($sql, $parameters, 'column');
	*/

//get the list
	$sql = "select p.*, ";
	$sql .= "exists(select from v_group_permissions where permission_name = p.permission_name and group_name = :group_name) as permission_assigned ";
	$sql .= "from v_permissions as p ";
	$parameters['group_name'] = $group_name;
	if (isset($sql_search)) {
		$sql .= "where ".$sql_search;
	}
	$sql .= "order by application_name asc, permission_name asc ";
	$database = new database;
	$group_permissions = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//include the header
	$document['title'] = $text['title-group_permissions'];
	require_once "resources/header.php";

//show the content
	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>".$text['title-group_permissions']." (".escape($group_name).")</b></div>\n";
	echo "	<div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-back'],'icon'=>$_SESSION['theme']['button_icon_back'],'id'=>'btn_back','style'=>'margin-right: 15px;','collapse'=>'hide-sm-dn','link'=>'groups.php']);
	echo button::create(['type'=>'button','label'=>$text['button-reload'],'icon'=>'key','link'=>'?group_uuid='.urlencode($group_uuid).'&action=reload']);
	if (permission_exists('group_member_view')) {
		echo button::create(['type'=>'button','label'=>$text['button-members'],'icon'=>'users','link'=>'group_members.php?group_uuid='.urlencode($group_uuid)]);
	}
	echo 		"<form id='form_search' class='inline' method='get'>\n";
	echo 		"<input type='hidden' name='group_uuid' value='".escape($group_uuid)."'>\n";
	echo 		"<input type='text' class='txt list-search' name='search' id='search' value=\"".escape($search)."\" placeholder=\"".$text['label-search']."\" onkeydown='list_search_reset();'>";
	echo button::create(['label'=>$text['button-search'],'icon'=>$_SESSION['theme']['button_icon_search'],'type'=>'submit','id'=>'btn_search','collapse'=>'hide-sm-dn','style'=>($search != '' ? 'display: none;' : null)]);
	echo button::create(['label'=>$text['button-reset'],'icon'=>$_SESSION['theme']['button_icon_reset'],'type'=>'button','id'=>'btn_reset','collapse'=>'hide-sm-dn','link'=>'group_permissions.php?group_uuid='.urlencode($group_uuid),'style'=>($search == '' ? 'display: none;' : null)]);
	if (permission_exists('group_permission_edit')) {
		echo button::create(['type'=>'button','label'=>$text['button-save'],'icon'=>$_SESSION['theme']['button_icon_save'],'id'=>'btn_save','collapse'=>'hide-sm-dn','style'=>'margin-left: 15px;','onclick'=>"document.getElementById('form_list').submit();"]);
	}
	echo "		</form>\n";
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	echo $text['description-group_permissions']."\n";
	echo "<br /><br />\n";

	echo "<form id='form_list' method='post'>\n";
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";
	echo "<input type='hidden' name='group_uuid' value='".escape($group_uuid)."'>\n";
	echo "<input type='hidden' name='search' value=\"".escape($search)."\">\n";
	echo "<table class='list' style='margin-bottom: 25px;'>\n";
	if (is_array($group_permissions) && @sizeof($group_permissions) != 0) {
		$x = 0;
		foreach ($group_permissions as $row) {
			$checked = ($row['permission_assigned'] === true) ? " checked=\"checked\"" : $checked = '';
			$application_name = strtolower($row['application_name']);
			$label_application_name = ucwords(str_replace(['_','-'], " ", $row['application_name']));

			$label_application_name = ucwords($label_application_name);

			if ($previous_application_name !== $row['application_name']) {
				echo "		<tr>";
				echo "			<td align='left' colspan='999'>&nbsp;</td>\n";
				echo "		</tr>";
				echo "		<tr>";
				echo "			<td align='left' colspan='999' nowrap='nowrap'><b>".escape($label_application_name)."</b></td>\n";
				echo "		</tr>";
				echo "<tr class='list-header'>\n";
				if (permission_exists('group_permission_add') || permission_exists('group_permission_edit') || permission_exists('group_permission_delete')) {
					echo "	<th class='checkbox'>\n";
					echo "		<input type='checkbox' id='checkbox_all_".$application_name."' name='checkbox_all' onclick=\"list_all_toggle('".$application_name."');\">\n";
					echo "	</th>\n";
				}
				echo th_order_by('group_name', $text['label-group_name'], $order_by, $order);
				if (permission_exists('group_permission_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
					echo "	<td class='action-button'>&nbsp;</td>\n";
				}
				echo "</tr>\n";

			}
			echo "<tr class='list-row'>\n";
			if (permission_exists('group_permission_add') || permission_exists('group_permission_edit') || permission_exists('group_permission_delete')) {
				echo "	<td class='checkbox'>\n";
				echo "		<input type='checkbox' name='group_permissions[$x][checked]' id='checkbox_".$x."' class='checkbox_".$application_name."' value='true' ".$checked." onclick=\"if (!this.checked) { document.getElementById('checkbox_all_".$application_name."').checked = false; }\">\n";
				echo "		<input type='hidden' name='group_permissions[$x][permission_uuid]' value='".escape($row['permission_uuid'])."' />\n";
				echo "		<input type='hidden' name='group_permissions[$x][permission_name]' value='".escape($row['permission_name'])."' />\n";
				echo "	</td>\n";
			}
			echo "	<td  class='no-wrap' onclick=\"if (document.getElementById('checkbox_".$x."').checked) { document.getElementById('checkbox_".$x."').checked = false; document.getElementById('checkbox_all_".$application_name."').checked = false; } else { document.getElementById('checkbox_".$x."').checked = true; }\">".escape($row['permission_name'])."</td>\n";
			echo "</tr>\n";

			//set the previous category
			$previous_application_name = $row['application_name'];
			$x++;
		}
		unset($group_permissions);
	}

	echo "</table>\n";
	echo "</form>\n";

//include the footer
	require_once "resources/footer.php";

?>
