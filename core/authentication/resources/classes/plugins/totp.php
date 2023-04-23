<?php

/**
 * plugin_totp
 *
 * @method totp time based one time password authenticate the user
 */
class plugin_totp {

	/**
	 * Define variables and their scope
	 */
	public $debug;
	public $domain_name;
	public $username;
	public $password;
	public $user_uuid;
	public $user_email;
	public $contact_uuid;
	private $user_totp_secret;

	/**
	 * time based one time password aka totp
	 * @return array [authorized] => true or false
	 */
	function totp() {

		//request the username
			if (!isset($_POST['username']) && !isset($_POST['authentication_code'])) {

				//set a default template
				$_SESSION['domain']['template']['name'] = 'default';
				$_SESSION['theme']['menu_brand_image']['text'] = PROJECT_PATH.'/themes/default/images/logo.png';
				$_SESSION['theme']['menu_brand_type']['text'] = 'image';

				//get the domain
				$domain_array = explode(":", $_SERVER["HTTP_HOST"]);
				$domain_name = $domain_array[0];

				//temp directory
				$_SESSION['server']['temp']['dir'] = '/tmp';

				//create token
				//$object = new token;
				//$token = $object->create('login');

				//add multi-lingual support
				$language = new text;
				$text = $language->get(null, '/core/authentication');

				//initialize a template object
				$view = new template();
				$view->engine = 'smarty';
				$view->template_dir = $_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'/core/authentication/resources/views/';
				$view->cache_dir = $_SESSION['server']['temp']['dir'];
				$view->init();

				//assign default values to the template
				$view->assign("login_title", $text['label-username']);
				$view->assign("login_username", $text['label-username']);
				$view->assign("login_logo_width", $login_logo_width);
				$view->assign("login_logo_height", $login_logo_height);
				$view->assign("login_logo_source", $login_logo_source);
				$view->assign("button_login", $text['button-login']);
					
				//show the views
				$content = $view->render('username.htm');
				echo $content;
				exit;
			}

		//show the authentication code view
			if (!isset($_POST['authentication_code'])) {

				//get the username
				if (!isset($this->username) && isset($_REQUEST['username'])) {
					$this->username = $_REQUEST['username'];
				}

				//get the user details
				$sql = "select user_uuid, username, user_email, contact_uuid, user_totp_secret\n";
				$sql .= "from v_users\n";
				$sql .= "where username = :username\n";
				if ($_SESSION["users"]["unique"]["text"] != "global") {
					//unique username per domain (not globally unique across system - example: email address)
					$sql .= "and domain_uuid = :domain_uuid ";
					$parameters['domain_uuid'] = $this->domain_uuid;
				}
				$parameters['username'] = $this->username;
				$database = new database;
				$row = $database->select($sql, $parameters, 'row');
				unset($parameters);

				//set class variables
				$this->user_uuid = $row['user_uuid'];
				$this->user_email = $row['user_email'];
				$this->contact_uuid = $row['contact_uuid'];
				$this->user_totp_secret = $row['user_totp_secret'];

				//set a few session variables
				$_SESSION["user_uuid"] = $row['user_uuid'];
				$_SESSION["username"] = $row['username'];
				$_SESSION["user_email"] = $row['user_email'];
				$_SESSION["contact_uuid"] = $row["contact_uuid"];

				//set a default template
				$_SESSION['domain']['template']['name'] = 'default';
				$_SESSION['theme']['menu_brand_image']['text'] = PROJECT_PATH.'/themes/default/images/logo.png';
				$_SESSION['theme']['menu_brand_type']['text'] = 'image';

				//get the domain
				$domain_array = explode(":", $_SERVER["HTTP_HOST"]);
				$domain_name = $domain_array[0];

				//temp directory
				$_SESSION['server']['temp']['dir'] = '/tmp';

				//create token
				//$object = new token;
				//$token = $object->create('login');

				//add multi-lingual support
				$language = new text;
				$text = $language->get(null, '/core/authentication');

				//initialize a template object
				$view = new template();
				$view->engine = 'smarty';
				$view->template_dir = $_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'/core/authentication/resources/views/';
				$view->cache_dir = $_SESSION['server']['temp']['dir'];
				$view->init();

				//assign default values to the template
				$view->assign("login_title", $text['label-verify']);
				$view->assign("login_authentication_code", $text['label-authentication_code']);
				$view->assign("login_logo_width", $login_logo_width);
				$view->assign("login_logo_height", $login_logo_height);
				$view->assign("login_logo_source", $login_logo_source);
				$view->assign("button_verify", $text['label-verify']);

				//show the views
				$content = $view->render('totp.htm');
				echo $content;
				exit;
			}

		//if authorized then verify
			if (isset($_POST['authentication_code'])) {

				//get the user details
				$sql = "select user_uuid, user_email, contact_uuid, user_totp_secret\n";
				$sql .= "from v_users\n";
				$sql .= "where username = :username\n";
				if ($_SESSION["users"]["unique"]["text"] != "global") {
					//unique username per domain (not globally unique across system - example: email address)
					$sql .= "and domain_uuid = :domain_uuid ";
					$parameters['domain_uuid'] = $_SESSION["domain_uuid"];
				}
				$parameters['username'] = $_SESSION["username"];
				$database = new database;
				$row = $database->select($sql, $parameters, 'row');
				$this->user_uuid = $row['user_uuid'];
				$this->user_email = $row['user_email'];
				$this->contact_uuid = $row['contact_uuid'];
				$this->user_totp_secret = $row['user_totp_secret'];
				unset($parameters);

				//create the authenticator object
				$totp = new google_authenticator;

				//validate the code
				if ($totp->checkCode($this->user_totp_secret, $_POST['authentication_code'])) {
					$auth_valid = true;
				}
				else {
					$auth_valid = false;
				}

				//get the user details
				if ($auth_valid) {
					//get user data from the database
					$sql = "select user_uuid, username, user_email, contact_uuid from v_users ";
					$sql .= "where user_uuid = :user_uuid ";
					if ($_SESSION["users"]["unique"]["text"] != "global") {
						//unique username per domain (not globally unique across system - example: email address)
						$sql .= "and domain_uuid = :domain_uuid ";
						$parameters['domain_uuid'] = $_SESSION["domain_uuid"];
					}
					$parameters['user_uuid'] = $_SESSION["user_uuid"];
					$database = new database;
					$row = $database->select($sql, $parameters, 'row');
					//view_array($row);
					unset($parameters);
				}
				else {
					//destroy session
					session_unset();
					session_destroy();
					//$_SESSION['authentication']['plugin']
					//send http 403
					header('HTTP/1.0 403 Forbidden', true, 403);

					//redirect to the root of the website
					header("Location: ".PROJECT_PATH."/");

					//exit the code
					exit();
				}

				/*
				//check if user successfully logged in during the interval
					//$sql = "select user_log_uuid, timestamp, user_name, user_agent, remote_address ";
					$sql = "select count(*) as count ";
					$sql .= "from v_user_logs ";
					$sql .= "where domain_uuid = :domain_uuid ";
					$sql .= "and user_uuid = :user_uuid ";
					$sql .= "and user_agent = :user_agent ";
					$sql .= "and type = 'login' ";
					$sql .= "and result = 'success' ";
					$sql .= "and floor(extract(epoch from now()) - extract(epoch from timestamp)) > 3 ";
					$sql .= "and floor(extract(epoch from now()) - extract(epoch from timestamp)) < 300 ";
					$parameters['domain_uuid'] = $this->domain_uuid;
					$parameters['user_uuid'] = $this->user_uuid;
					$parameters['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
					$database = new database;
					$user_log_count = $database->select($sql, $parameters, 'all');
					//view_array($user_log_count);
					unset($sql, $parameters);
				*/

				//build the result array
				$result["plugin"] = "totp";
				$result["domain_name"] = $_SESSION["domain_name"];
				$result["username"] = $_SESSION["username"];
				$result["user_uuid"] = $_SESSION["user_uuid"];
				$result["domain_uuid"] = $_SESSION["domain_uuid"];
				$result["contact_uuid"] = $_SESSION["contact_uuid"];
				$result["authorized"] = $auth_valid ? true : false;

				//add the failed login to user logs
				if (!$auth_valid) {
					user_logs::add($result);
				}

				//retun the array
				return $result;


				//$_SESSION['authentication']['plugin']['totp']['plugin'] = "totp";
				//$_SESSION['authentication']['plugin']['totp']['domain_name'] = $_SESSION["domain_name"];
				//$_SESSION['authentication']['plugin']['totp']['username'] = $row['username'];
				//$_SESSION['authentication']['plugin']['totp']['user_uuid'] = $_SESSION["user_uuid"];
				//$_SESSION['authentication']['plugin']['totp']['contact_uuid'] = $_SESSION["contact_uuid"];
				//$_SESSION['authentication']['plugin']['totp']['domain_uuid'] =  $_SESSION["domain_uuid"];
				//$_SESSION['authentication']['plugin']['totp']['authorized'] = $auth_valid ? true : false;
			}

	}
}

?>
