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
	Portions created by the Initial Developer are Copyright (C) 2008-2016
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//add multi-lingual support
	$language = new text;
	$text = $language->get(null,'core/user_settings');

//get action, if any
	if (isset($_REQUEST['action'])) {
		$action = check_str($_REQUEST['action']);
	}

//retrieve parse reset key
	if ($action == 'define') {
		$key = $_GET['key'];
		$key_part = explode('|', decrypt($_SESSION['login']['password_reset_key']['text'], $key));
		$username = $key_part[0];
		$domain_uuid = $key_part[1];
		$password_submitted = $key_part[2];
		//get current salt, see if same as submitted salt
		$sql = "select password from v_users where domain_uuid = :domain_uuid and username = :username ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->bindParam(':domain_uuid', $domain_uuid);
		$prep_statement->bindParam(':username', $username);
		$prep_statement->execute();
		$result = $prep_statement->fetch(PDO::FETCH_NAMED);
		$password_current = $result['password'];
		unset($prep_statement, $result);

		//set flag
		$password_reset = ($username != '' && $domain_uuid == $_SESSION['domain_uuid'] && $password_submitted == $password_current) ? true : false;
	}

//send password reset link
	if ($action == 'request') {
		if (valid_email($_REQUEST['email'])) {
			$email = check_str($_REQUEST['email']);
			//see if email exists
			$sql = "select ";
			$sql .= "u.username, ";
			$sql .= "u.password ";
			$sql .= "from ";
			$sql .= "v_users as u, ";
			$sql .= "v_contact_emails as e ";
			$sql .= "where ";
			$sql .= "e.domain_uuid = u.domain_uuid ";
			$sql .= "and e.contact_uuid = u.contact_uuid ";
			$sql .= "and e.email_address = :email ";
			$sql .= "and e.domain_uuid = '".$_SESSION['domain_uuid']."' ";
			$prep_statement = $db->prepare(check_sql($sql));
			$prep_statement->bindParam(':email', $email);
			$prep_statement->execute();
			$result = $prep_statement->fetch(PDO::FETCH_NAMED);
			unset($prep_statement);

			if ($result['username'] != '') {
				//generate reset link
				$key = encrypt($_SESSION['login']['password_reset_key']['text'], $result['username'].'|'.$_SESSION['domain_uuid'].'|'.$result['password']);
				$reset_link = "https://".$_SESSION['domain_name'].PROJECT_PATH."/login.php?action=define&key=".urlencode($key);
				$eml_body = "<a href='".$reset_link."' class='login_link'>".$reset_link."</a>";
				//send reset link
				if (send_email($email, $text['label-reset_link'], $eml_body)) {
					//email sent
					message::add($text['message-reset_link_sent'], 'positive', 2500);
				}
				else {
					//email failed
					message::add($eml_error, 'negative', 5000);
				}
			}
			else {
				//not found
				message::add($text['message-invalid_email'], 'negative', 5000);
			}
		}
		else {
			//not found
			message::add($text['message-invalid_email'], 'negative', 5000);
		}
	}

//reset password
	if ($action == 'reset') {
		$authorized_username = check_str($_REQUEST['au']);
		$username = check_str($_REQUEST['username']);
		$password_new = check_str($_REQUEST['password_new']);
		$password_repeat = check_str($_REQUEST['password_repeat']);

		if ($username != '' &&
			$authorized_username == md5($_SESSION['login']['password_reset_key']['text'].$username) &&
			$password_new != '' &&
			$password_repeat != '' &&
			$password_new == $password_repeat
			) {

			if (!check_password_strength($password_new, $text)) {
				$password_reset = true;
			}
			else {
				$salt = generate_password('20', '4');
				$sql  = "update v_users set ";
				$sql .= "password = :password, ";
				$sql .= "salt = :salt ";
				$sql .= "where domain_uuid = '".$_SESSION['domain_uuid']."' ";
				$sql .= "and username = :username ";
				$prep_statement = $db->prepare(check_sql($sql));
				$prep_statement->bindParam(':password', md5($salt.$password_new));
				$prep_statement->bindParam(':salt', $salt);
				$prep_statement->bindParam(':username', $username);
				$prep_statement->execute();
				unset($prep_statement);

				message::add($text['message-password_reset'], 'positive', 2500);
				$password_reset = false;
			}
		}
		else {
			//not found
			message::add($text['message-invalid_username_mismatch_passwords'], 'negative', 5000);
			$password_reset = true;
		}
	}

//get the http values and set as variables
	$msg = isset($_GET["msg"]) ? check_str($_GET["msg"]) : null;

//set variable if not set
	if (!isset($_SESSION['login']['domain_name_visible']['boolean'])) { $_SESSION['login']['domain_name_visible']['boolean'] = null; }

//set a default login destination
	if (strlen($_SESSION['login']['destination']['url']) == 0) {
		$_SESSION['login']['destination']['url'] = PROJECT_PATH."/core/user_settings/user_dashboard.php";
	}

//add the header
	include "resources/header.php";

//show the message
	if (strlen($msg) > 0) {
		echo "<br><br>";
		echo "<div align='center'>\n";
		echo "<table width='50%'>\n";
		echo "<tr>\n";
		echo "<th align='left'>Message</th>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td class='row_style1'>\n";
		switch ($msg) {
			case "username required":
				echo "<strong>Please provide a username.</strong>";
				break;
			case "incorrect account information":
			   echo "<strong>The username or password was incorrect. Please try again.</strong>";
				break;
			case "install complete":
				echo "<br />\n";
				echo "Installation is complete. <br />";
				echo "<br /> ";
				echo  "<strong>Getting Started:</strong><br /> ";
				echo "<ul><li>There are two levels of admins 1. superadmin 2. admin.<br />";
				echo "<br />\n";
				echo "username: <strong>superadmin</strong> <br />password: <strong>fusionpbx</strong> <br />\n";
				echo "<br />\n";
				echo "username: <strong>admin</strong> <br />password: <strong>fusionpbx</strong> <br/><br/>\n";
				echo "</li>\n";
				echo "<li>\n";
				echo "The database connection settings have been saved to ".$_SERVER["DOCUMENT_ROOT"].PROJECT_PATH."/resources/config.php.<br />\n";
				echo "</li>\n";
				echo "</ul>\n";
				echo "<strong>\n";
				break;
		}
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</div>\n";
		echo "<br /><br />\n\n";
	}

//show the content
	echo "<script>";
	echo "	var speed = 350;";
	echo "	function toggle_password_reset(hide_id, show_id, focus_id) {";
	echo "		if (focus_id == undefined) { focus_id = ''; }";
	echo "		$('#'+hide_id).slideToggle(speed, function() {";
	echo "			$('#'+show_id).slideToggle(speed, function() {";
	echo "				if (focus_id != '') {";
	echo "					$('#'+focus_id).focus();";
	echo "				}";
	echo "			});";
	echo "		});";
	echo "	}";
	echo "</script>";

	echo "<br />\n";

	if (!$password_reset) {

		echo "<div id='login_form'>\n";
		echo "<form name='login' method='post' action='".$_SESSION['login']['destination']['url']."'>\n";
		echo "<input type='text' class='txt login' style='text-align: center; min-width: 200px; width: 200px; margin-bottom: 8px;' name='username' id='username' placeholder=\"".$text['label-username']."\"><br />\n";
		echo "<input type='password' class='txt login' style='text-align: center; min-width: 200px; width: 200px; margin-bottom: 8px;' name='password' placeholder=\"".$text['label-password']."\"><br />\n";
		if ($_SESSION['login']['domain_name_visible']['boolean'] == "true") {
			if (count($_SESSION['login']['domain_name']) > 0) {
				$click_change_color = ($_SESSION['theme']['login_input_text_color']['text'] != '') ? $_SESSION['theme']['login_input_text_color']['text'] : (($_SESSION['theme']['input_text_color']['text'] != '') ? $_SESSION['theme']['input_text_color']['text'] : '#000000');
				$placeholder_color = ($_SESSION['theme']['login_input_text_placeholder_color']['text'] != '') ? 'color: '.$_SESSION['theme']['login_input_text_placeholder_color']['text'].';' : 'color: #999999;';
				echo "<select name='domain_name' class='txt login' style='".$placeholder_color." width: 200px; text-align: center; text-align-last: center; margin-bottom: 8px;' onclick=\"this.style.color='".$click_change_color."';\" onchange=\"this.style.color='".$click_change_color."';\">\n";
				echo "	<option value='' disabled selected hidden>".$text['label-domain']."</option>\n";
				sort($_SESSION['login']['domain_name']);
				foreach ($_SESSION['login']['domain_name'] as &$row) {
					echo "	<option value='$row'>$row</option>\n";
				}
				echo "</select><br />\n";
			}
			else {
				echo "<input type='text' name='domain_name' class='txt login' style='text-align: center; min-width: 200px; width: 200px; margin-bottom: 8px;' placeholder=\"".$text['label-domain']."\"><br />\n";
			}
		}
		echo "<input type='submit' id='btn_login' class='btn' style='width: 100px; margin-top: 15px;' value='".$text['button-login']."'>\n";
		if (
			function_exists('openssl_encrypt') &&
			$_SESSION['login']['password_reset_key']['text'] != '' &&
			$_SESSION['email']['smtp_host']['text'] != ''
			) {
			echo "<br><br><a class='login_link' onclick=\"toggle_password_reset('login_form','request_form','email');\">".$text['label-reset_password']."</a>";
		}
		echo "</form>";
		echo "<script>document.getElementById('username').focus();</script>";
		echo "</div>";

		echo "<div id='request_form' style='display: none;'>\n";
		echo "<form name='request' method='post' action=''>\n";
		echo "<input type='hidden' name='action' value='request'>\n";
		echo "<input type='text' class='txt login' style='text-align: center; min-width: 200px; width: 200px; margin-bottom: 8px;' name='email' id='email' placeholder=\"".$text['label-email_address']."\"><br />\n";
		echo "<input type='submit' id='btn_reset' class='btn' style='width: 100px; margin-top: 15px;' value='".$text['button-reset']."'>\n";
		echo "<br><br><a class='login_link' onclick=\"toggle_password_reset('request_form','login_form','username');\">".$text['label-cancel']."</a>";
		echo "</form>";
		echo "</div>";

	}
	else {

		echo "<script>\n";
		echo "	function compare_passwords() {\n";
		echo "		if (document.getElementById('password') === document.activeElement || document.getElementById('password_confirm') === document.activeElement) {\n";
		echo "			if ($('#password').val() != '' || $('#password_confirm').val() != '') {\n";
		echo "				if ($('#password').val() != $('#password_confirm').val()) {\n";
		echo "					$('#password').removeClass('formfld_highlight_good');\n";
		echo "					$('#password_confirm').removeClass('formfld_highlight_good');\n";
		echo "					$('#password').addClass('formfld_highlight_bad');\n";
		echo "					$('#password_confirm').addClass('formfld_highlight_bad');\n";
		echo "				}\n";
		echo "				else {\n";
		echo "					$('#password').removeClass('formfld_highlight_bad');\n";
		echo "					$('#password_confirm').removeClass('formfld_highlight_bad');\n";
		echo "					$('#password').addClass('formfld_highlight_good');\n";
		echo "					$('#password_confirm').addClass('formfld_highlight_good');\n";
		echo "				}\n";
		echo "			}\n";
		echo "		}\n";
		echo "		else {\n";
		echo "			$('#password').removeClass('formfld_highlight_bad');\n";
		echo "			$('#password_confirm').removeClass('formfld_highlight_bad');\n";
		echo "			$('#password').removeClass('formfld_highlight_good');\n";
		echo "			$('#password_confirm').removeClass('formfld_highlight_good');\n";
		echo "		}\n";
		echo "	}\n";

		$req['length'] = $_SESSION['security']['password_length']['numeric'];
		$req['number'] = ($_SESSION['security']['password_number']['boolean'] == 'true') ? true : false;
		$req['lowercase'] = ($_SESSION['security']['password_lowercase']['boolean'] == 'true') ? true : false;
		$req['uppercase'] = ($_SESSION['security']['password_uppercase']['boolean'] == 'true') ? true : false;
		$req['special'] = ($_SESSION['security']['password_special']['boolean'] == 'true') ? true : false;

		echo "	function check_password_strength(pwd) {\n";
		echo "		if ($('#password').val() != '' || $('#password_confirm').val() != '') {\n";
		echo "			var msg_errors = [];\n";
		if (is_numeric($req['length']) && $req['length'] != 0) {
			echo "		var re = /.{".$req['length'].",}/;\n"; //length
			echo "		if (!re.test(pwd)) { msg_errors.push('".$req['length']."+ ".$text['label-characters']."'); }\n";
		}
		if ($req['number']) {
			echo "		var re = /(?=.*[\d])/;\n";  //number
			echo "		if (!re.test(pwd)) { msg_errors.push('1+ ".$text['label-numbers']."'); }\n";
		}
		if ($req['lowercase']) {
			echo "		var re = /(?=.*[a-z])/;\n";  //lowercase
			echo "		if (!re.test(pwd)) { msg_errors.push('1+ ".$text['label-lowercase_letters']."'); }\n";
		}
		if ($req['uppercase']) {
			echo "		var re = /(?=.*[A-Z])/;\n";  //uppercase
			echo "		if (!re.test(pwd)) { msg_errors.push('1+ ".$text['label-uppercase_letters']."'); }\n";
		}
		if ($req['special']) {
			echo "		var re = /(?=.*[\W])/;\n";  //special
			echo "		if (!re.test(pwd)) { msg_errors.push('1+ ".$text['label-special_characters']."'); }\n";
		}
		echo "			if (msg_errors.length > 0) {\n";
		echo "				var msg = '".$text['message-password_requirements'].": ' + msg_errors.join(', ');\n";
		echo "				display_message(msg, 'negative', '6000');\n";
		echo "				return false;\n";
		echo "			}\n";
		echo "			else {\n";
		echo "				return true;\n";
		echo "			}\n";
		echo "		}\n";
		echo "		else {\n";
		echo "			return true;\n";
		echo "		}\n";
		echo "	}\n";

		echo "	function show_strenth_meter() {\n";
		echo "		$('#pwstrength_progress').slideDown();\n";
		echo "	}\n";
		echo "</script>\n";

		echo "<span id='reset_form'>\n";
		echo "<form name='reset' id='frm' method='post' action=''>\n";
		echo "<input type='hidden' name='action' value='reset'>\n";
		echo "<input type='hidden' name='au' value='".md5($_SESSION['login']['password_reset_key']['text'].$username)."'>\n";
		echo "<input type='text' class='txt login' style='text-align: center; min-width: 200px; width: 200px; margin-bottom: 8px;' name='username' id='username' placeholder=\"".$text['label-username']."\"><br />\n";
		echo "<input type='password' class='txt login' style='text-align: center; min-width: 200px; width: 200px; margin-bottom: 4px;' name='password_new' id='password' autocomplete='off' placeholder=\"".$text['label-new_password']."\" onkeypress='show_strenth_meter();' onfocus='compare_passwords();' onkeyup='compare_passwords();' onblur='compare_passwords();'><br />\n";
		echo "<div id='pwstrength_progress' class='pwstrength_progress pwstrength_progress_password_reset'></div>";
		echo "<input type='password' class='txt login' style='text-align: center; min-width: 200px; width: 200px; margin-top: 4px; margin-bottom: 8px;' name='password_repeat' id='password_confirm' autocomplete='off' placeholder=\"".$text['label-repeat_password']."\" onfocus='compare_passwords();' onkeyup='compare_passwords();' onblur='compare_passwords();'><br />\n";
		echo "<input type='button' class='btn' style='width: 100px; margin-top: 15px;' value='".$text['button-save']."' onclick=\"if (check_password_strength(document.getElementById('password').value)) { submit_form(); }\">\n";
		echo "<br><br><a class='login_link' onclick=\"document.location.href='login.php';\">".$text['label-cancel']."</a>";
		echo "</form>";

		echo "<script>\n";
		echo "	document.getElementById('username').focus();\n";
		// convert password fields to text
			echo "	function submit_form() {\n";
			echo "		$('input:password').css('visibility','hidden');\n";
			echo "		$('input:password').attr({type:'text'});\n";
			echo "		$('form#frm').submit();\n";
			echo "	}\n";
		echo "</script>\n";
		echo "</span>";

	}

//add the footer
	$default_login = true;
	include "resources/footer.php";

?>
