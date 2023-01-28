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
	Portions created by the Initial Developer are Copyright (C) 2008-2023
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J. Crane <markjcrane@fusionpbx.com>
*/

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//start the session
	ini_set("session.cookie_httponly", True);
	if (!isset($_SESSION)) { session_start(); }

//if config.conf file does not exist then redirect to the install page
	if (file_exists("/usr/local/etc/fusionpbx/config.conf")) {
		//BSD
	} elseif (file_exists("/etc/fusionpbx/config.conf")) {
		//Linux
	} else {
		header("Location: /core/install/install.php");
		exit;
	}

//adds multiple includes
	require_once "resources/require.php";

//if logged in, redirect to login destination
	if (isset($_SESSION["username"])) {
		if (isset($_SESSION['login']['destination']['text'])) {
			header("Location: ".$_SESSION['login']['destination']['text']);
		} elseif (file_exists($_SERVER["PROJECT_ROOT"]."/core/dashboard/app_config.php")) {
			header("Location: ".PROJECT_PATH."/core/dashboard/");
		}
		else {
			require_once "resources/header.php";
			require_once "resources/footer.php";
		}
	}
	else {
		//use custom index, if present, otherwise use custom login, if present, otherwise use default login
		if (file_exists($_SERVER["PROJECT_ROOT"]."/themes/".$_SESSION['domain']['template']['name']."/index.php")) {
			require_once "themes/".$_SESSION['domain']['template']['name']."/index.php";
		} else if (file_exists($_SERVER["PROJECT_ROOT"]."/themes/".$_SESSION['domain']['template']['name']."/login.php")) {
			require_once "themes/".$_SESSION['domain']['template']['name']."/login.php";
		}
		else {
			require_once "resources/login.php";
		}
	}

?>
