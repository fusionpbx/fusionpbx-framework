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
	Copyright (C) 2010
	All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/
include "root.php";

//define the follow me class
	class follow_me {
		public $domain_uuid;
		public $db_type;
		public $follow_me_uuid;
		public $follow_me_enabled;

		public $destination_data_1;
		public $destination_type_1;
		public $destination_delay_1;
		public $destination_timeout_1;

		public $destination_data_2;
		public $destination_type_2;
		public $destination_delay_2;
		public $destination_timeout_2;

		public $destination_data_3;
		public $destination_type_3;
		public $destination_delay_3;
		public $destination_timeout_3;

		public $destination_data_4;
		public $destination_type_4;
		public $destination_delay_4;
		public $destination_timeout_4;

		public $destination_data_5;
		public $destination_type_5;
		public $destination_delay_5;
		public $destination_timeout_5;

		public $destination_timeout = 0;
		public $destination_order = 1;

		public function follow_me_add() {
			global $db;

			$sql = "insert into v_follow_me ";
			$sql .= "(";
			$sql .= "domain_uuid, ";
			$sql .= "follow_me_uuid, ";
			$sql .= "follow_me_enabled ";
			$sql .= ")";
			$sql .= "values ";
			$sql .= "(";
			$sql .= "'$this->domain_uuid', ";
			$sql .= "'$this->follow_me_uuid', ";
			$sql .= "'$this->follow_me_enabled' ";
			$sql .= ")";
			if ($v_debug) {
				echo $sql."<br />";
			}
			$db->exec(check_sql($sql));
			unset($sql);
			$this->follow_me_destinations();
		} //end function

		public function follow_me_update() {
			global $db;

			$sql = "update follow_me set ";
			$sql .= "follow_me_enabled = '$this->follow_me_enabled' ";
			$sql .= "where domain_uuid = '$this->domain_uuid' ";
			$sql .= "and follow_me_uuid = '$this->follow_me_uuid' ";
			$db->exec(check_sql($sql));
			unset($sql);
			$this->follow_me_destinations();
		} //end function

		public function follow_me_destinations() {
			global $db;

			//delete related follow me destinations
				$sql = "delete from v_follow_me_destinations where follow_me_uuid = '$this->follow_me_uuid' ";
				$db->exec(check_sql($sql));

			//insert the follow me destinations
				if (strlen($this->destination_data_1) > 0) {
					$sql = "insert into v_follow_me_destinations ";
					$sql .= "(";
					$sql .= "follow_me_destination_uuid, ";
					$sql .= "domain_uuid, ";
					$sql .= "follow_me_uuid, ";
					$sql .= "follow_me_destination, ";
					$sql .= "follow_me_timeout, ";
					$sql .= "follow_me_delay, ";
					$sql .= "follow_me_order ";
					$sql .= ") ";
					$sql .= "values ";
					$sql .= "(";
					$sql .= "'".uuid()."', ";
					$sql .= "'$this->domain_uuid', ";
					$sql .= "'$this->follow_me_uuid', ";
					$sql .= "'$this->destination_data_1', ";
					$sql .= "'$this->destination_timeout_1', ";
					$sql .= "'$this->destination_delay_1', ";
					$sql .= "'1' ";
					$sql .= ")";
					$db->exec(check_sql($sql));
					$this->destination_order++;
					unset($sql);
				}
				if (strlen($this->destination_data_2) > 0) {
					$sql = "insert into v_follow_me_destinations ";
					$sql .= "(";
					$sql .= "follow_me_destination_uuid, ";
					$sql .= "domain_uuid, ";
					$sql .= "follow_me_uuid, ";
					$sql .= "follow_me_destination, ";
					$sql .= "follow_me_timeout, ";
					$sql .= "follow_me_delay, ";
					$sql .= "follow_me_order ";
					$sql .= ") ";
					$sql .= "values ";
					$sql .= "(";
					$sql .= "'".uuid()."', ";
					$sql .= "'$this->domain_uuid', ";
					$sql .= "'$this->follow_me_uuid', ";
					$sql .= "'$this->destination_data_2', ";
					$sql .= "'$this->destination_timeout_2', ";
					$sql .= "'$this->destination_delay_2', ";
					$sql .= "'2' ";
					$sql .= ")";
					$db->exec(check_sql($sql));
					$this->destination_order++;
					unset($sql);
				}
				if (strlen($this->destination_data_3) > 0) {
					$sql = "insert into v_follow_me_destinations ";
					$sql .= "(";
					$sql .= "follow_me_destination_uuid, ";
					$sql .= "domain_uuid, ";
					$sql .= "follow_me_uuid, ";
					$sql .= "follow_me_destination, ";
					$sql .= "follow_me_timeout, ";
					$sql .= "follow_me_delay, ";
					$sql .= "follow_me_order ";
					$sql .= ") ";
					$sql .= "values ";
					$sql .= "(";
					$sql .= "'".uuid()."', ";
					$sql .= "'$this->domain_uuid', ";
					$sql .= "'$this->follow_me_uuid', ";
					$sql .= "'$this->destination_data_3', ";
					$sql .= "'$this->destination_timeout_3', ";
					$sql .= "'$this->destination_delay_3', ";
					$sql .= "'3' ";
					$sql .= ")";
					$db->exec(check_sql($sql));
					$this->destination_order++;
					unset($sql);
				}
				if (strlen($this->destination_data_4) > 0) {
					$sql = "insert into v_follow_me_destinations ";
					$sql .= "(";
					$sql .= "follow_me_destination_uuid, ";
					$sql .= "domain_uuid, ";
					$sql .= "follow_me_uuid, ";
					$sql .= "follow_me_destination, ";
					$sql .= "follow_me_timeout, ";
					$sql .= "follow_me_delay, ";
					$sql .= "follow_me_order ";
					$sql .= ") ";
					$sql .= "values ";
					$sql .= "(";
					$sql .= "'".uuid()."', ";
					$sql .= "'$this->domain_uuid', ";
					$sql .= "'$this->follow_me_uuid', ";
					$sql .= "'$this->destination_data_4', ";
					$sql .= "'$this->destination_timeout_4', ";
					$sql .= "'$this->destination_delay_4', ";
					$sql .= "'4' ";
					$sql .= ")";
					$db->exec(check_sql($sql));
					$this->destination_order++;
					unset($sql);
				}
				if (strlen($this->destination_data_5) > 0) {
					$sql = "insert into v_follow_me_destinations ";
					$sql .= "(";
					$sql .= "follow_me_destination_uuid, ";
					$sql .= "domain_uuid, ";
					$sql .= "follow_me_uuid, ";
					$sql .= "follow_me_destination, ";
					$sql .= "follow_me_timeout, ";
					$sql .= "follow_me_delay, ";
					$sql .= "follow_me_order ";
					$sql .= ") ";
					$sql .= "values ";
					$sql .= "(";
					$sql .= "'".uuid()."', ";
					$sql .= "'$this->domain_uuid', ";
					$sql .= "'$this->follow_me_uuid', ";
					$sql .= "'$this->destination_data_5', ";
					$sql .= "'$this->destination_timeout_5', ";
					$sql .= "'$this->destination_delay_5', ";
					$sql .= "'5' ";
					$sql .= ")";
					$db->exec(check_sql($sql));
					$this->destination_order++;
					unset($sql);
				}
		} //function

		public function set() {
			global $db;
			//update the extension
				if ($this->follow_me_enabled == "true") {
					$sql = "select * from v_follow_me_destinations ";
					$sql .= "where follow_me_uuid = '$this->follow_me_uuid' ";
					$sql .= "order by follow_me_order asc ";
					$prep_statement_2 = $db->prepare(check_sql($sql));
					$prep_statement_2->execute();
					$result = $prep_statement_2->fetchAll(PDO::FETCH_NAMED);
					$dial_string = "{group_confirm_key=exec,group_confirm_file=lua confirm.lua,sip_invite_domain=".$_SESSION['domain_name']."}";
					foreach ($result as &$row) {
						$dial_string .= "[presence_id=".$row["follow_me_destination"]."@".$_SESSION['domain_name'].",";
						$dial_string .= "leg_delay_start=".$row["follow_me_delay"].",";
						$dial_string .= "leg_timeout=".$row["follow_me_timeout"]."]";
						if (extension_exists($row["follow_me_destination"])) {
							$dial_string .= "\${sofia_contact(".$row["follow_me_destination"]."@".$_SESSION['domain_name'].")},";
						}
						else {
							$dial_string .= "loopback/".$row["follow_me_destination"].",";
						}
					}
					$this->dial_string = trim($dial_string, ",");
				}
				else {
//					$this->dial_string = "";
				}
				$sql  = "update v_extensions set ";
				$sql .= "do_not_disturb = 'false', ";
				$sql .= "dial_string = '".$this->dial_string."', ";
				$sql .= "dial_domain = '".$_SESSION['domain_name']."' ";
				$sql .= "where domain_uuid = '".$this->domain_uuid."' ";
				$sql .= "and follow_me_uuid = '".$this->follow_me_uuid."' ";
				if ($this->debug) {
					echo $sql."<br />";
				}
				$db->exec($sql);
				unset($sql);

			//syncrhonize configuration
				save_extension_xml();
		}

	} //class

?>