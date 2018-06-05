<?php

	/*
	* Copyright (C) 2016-2018 Abre.io Inc.
	*
	* This program is free software: you can redistribute it and/or modify
    * it under the terms of the Affero General Public License version 3
    * as published by the Free Software Foundation.
	*
    * This program is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    * GNU Affero General Public License for more details.
	*
    * You should have received a copy of the Affero General Public License
    * version 3 along with this program.  If not, see https://www.gnu.org/licenses/agpl-3.0.en.html.
    */

	//Include required files
	require_once(dirname(__FILE__) . '/../configuration.php');

	$cloudsetting=constant("USE_GOOGLE_CLOUD");
	if ($cloudsetting=="true") 
		require(dirname(__FILE__). '/../vendor/autoload.php');
	use Google\Cloud\Storage\StorageClient;


	function useApi() {

		if(isset($_SESSION['api_url'])){
			$url = $_SESSION['api_url'];
			return true;
		}

		return false;
	}

	//Encryption function
	function encrypt($string, $encryption_key){
		$encryption_key = constant("DB_KEY");
		$string = rtrim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $encryption_key, $string, MCRYPT_MODE_ECB)));

		return $string;
	}

	//Decryption function
	function decrypt($string, $encryption_key){
		$encryption_key = constant("DB_KEY");
		$string = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $encryption_key, base64_decode($string), MCRYPT_MODE_ECB));

		return $string;
	}

	//Find user ID in directory module given an email
	function finduserid($email){
		$sql = "SELECT id FROM directory WHERE email = '$email'";
		$result = $db->query($sql);
		while($row = $result->fetch_assoc()){
			$id = $row["id"];
			return $id;
		}
	}

	//Determine if users is superadmin or district admin
	function admin(){
		include "abre_dbconnect.php";
		$sql = "SELECT COUNT(*) FROM users WHERE email = '".$_SESSION['useremail']."' AND superadmin = 1";
		$result = $db->query($sql);
		$resultrow = $result->fetch_assoc();
		$count = $resultrow["COUNT(*)"];
		if($count > 0){
			return true;
		}

		$sql = "SELECT COUNT(*) FROM users WHERE email = '".$_SESSION['useremail']."' AND admin = 1";
		$result = $db->query($sql);
		$resultrow = $result->fetch_assoc();
		$count = $resultrow["COUNT(*)"];
		if($count > 0){
			return true;
		}

		return false;
	}

	//Determine if a user is a superadmin
	function superadmin(){
		include "abre_dbconnect.php";
		$sql = "SELECT COUNT(*) FROM users WHERE email = '".$_SESSION['useremail']."' AND superadmin = 1";
		$result = $db->query($sql);
		$resultrow = $result->fetch_assoc();
		$count = $resultrow["COUNT(*)"];
		if($count > 0){
			return true;
		}

		return false;
	}

	//Find user ID given an email
	function finduseridcore($email){
		include "abre_dbconnect.php";
		$sql = "SELECT id FROM users WHERE email = '".$_SESSION['useremail']."'";
		$result = $db->query($sql);
		while($row = $result->fetch_assoc()){
			$id = $row["id"];
			return $id;
		}
	}

	//determine if user is stream and headline administrator
	function isStreamHeadlineAdministrator(){
		$email = $_SESSION['useremail'];
		include "abre_dbconnect.php";
		$sql = "SELECT role FROM directory WHERE email = '$email'";
		$result = $db->query($sql);
		while($row = $result->fetch_assoc()){
			$role = decrypt($row["role"], "");
			if(strpos($role, 'Stream and Headline Administrator') !== false) {
				return true;
			}
		}
		return false;
	}

	//Determine the grades that students do not have email access
	function studentaccess(){
		$email = $_SESSION['useremail'];
		if(preg_replace('/[^0-9]+/', '', $email)){
			$gradyear = intval(preg_replace('/[^0-9]+/', '', $email), 10);
			$currentyear = date("y");
			$difference = $gradyear - $currentyear;
			if($difference < 6){
				return true;
			}else{
				return false;
			}
		}else{
			return true;
		}
	}

	//returns an array containing all school codes and school names for a given
	//district
	function getAllSchoolCodesAndNames(){
		require('abre_dbconnect.php');
		$schoolResults = array();
		if($db->query("SELECT * FROM Abre_Students LIMIT 1")){
			$sql = "SELECT SchoolCode, SchoolName FROM Abre_Students ORDER BY SchoolCode";
			$query = $db->query($sql);
			while($results = $query->fetch_assoc()){
				if($results['SchoolCode'] == ''){
					continue;
				}else{
					$schoolResults[$results['SchoolCode']] = $results['SchoolName'];
				}
			}
		}
		$db->close();
		return $schoolResults;
	}

	//returns an array containing all school codes for a district
	function getAllSchoolCodes(){
		require('abre_dbconnect.php');
		$schoolCodes = array();
		if($db->query("SELECT * FROM Abre_Students LIMIT 1")){
			$sql = "SELECT SchoolCode FROM Abre_Students ORDER BY SchoolCode";
			$query = $db->query($sql);
			while($results = $query->fetch_assoc()){
					array_push($schoolCodes, $results['SchoolCode']);
				}
			}
		if(sizeof($schoolCodes) != 0){
			$schoolCodes = array_unique($schoolCodes, SORT_REGULAR);
		}
		$db->close();
		return $schoolCodes;
	}

	//set verified session data for parent
	/* the first part of this function finds students who have a registered
	parent in the abre parent contact table, but not claimed in the
	users_parents table. We wanted to make parents who are registered as a contact
	for the school to be able to "fastpass" registering a student token */
	function isVerified(){
		include "abre_dbconnect.php";

    if($_SESSION['usertype'] == 'parent'){
			$sql = "SELECT id FROM users_parent WHERE email LIKE '".$_SESSION['useremail']."';";
      $result = $db->query($sql);
      $row = $result->fetch_assoc();
      $parent_id = $row["id"];

      if($db->query("SELECT * FROM student_tokens LIMIT 1") && $db->query("SELECT * FROM users_parent LIMIT 1")
          && $db->query("SELECT * FROM Abre_Students LIMIT 1") && $db->query("SELECT * FROM Abre_ParentContacts LIMIT 1")){
					//see if email matches any records
	        $sql = "SELECT StudentID FROM Abre_ParentContacts WHERE Email1 LIKE '".$_SESSION['useremail']."'";
	        $result = $db->query($sql);
	        while($row = $result->fetch_assoc()){
						//for records that match find kids associated with that email
	          $sql2 = "SELECT token, studentId FROM student_tokens WHERE studentId = '".$row['StudentID']."'";
	          $result2 = $db->query($sql2);
	          while($row2 = $result2->fetch_assoc()){
							$studenttokenencrypted = $row2['token'];
	            $studentId = $row2['studentId'];

	            //Check to see if student has already been claimed by parent
	            $sqlcheck = "SELECT COUNT(*) FROM parent_students WHERE student_token = '$studenttokenencrypted' AND parent_id = $parent_id AND studentId = '$studentId'";
	            $resultcheck = $db->query($sqlcheck);
							$resultrow = $resultcheck->fetch_assoc();
	            $numrows2 = $resultrow["COUNT(*)"];

	            //this parent does not have access
	            if($numrows2 == 0 && $_SESSION['useremail'] != ''){
								$stmt = $db->stmt_init();
	              $sql = "INSERT INTO parent_students (parent_id, student_token, studentId) VALUES (?, ?, ?)";
	              $stmt->prepare($sql);
	              $stmt->bind_param("iss", $parent_id, $studenttokenencrypted, $row2['studentId']);
	              $stmt->execute();
	              $stmt->close();
						}
					}
				}
			}
      $db->close();

      include "abre_dbconnect.php";
      if($db->query("SELECT * FROM student_tokens LIMIT 1") && $db->query("SELECT * FROM users_parent LIMIT 1")){
				$sql = "SELECT student_token FROM parent_students WHERE parent_id = $parent_id";
        if($result = $db->query($sql)){
					$_SESSION['auth_students'] = '';
          while($row = $result->fetch_assoc()){
						$sql2 = "SELECT studentId FROM student_tokens WHERE token = '".$row['student_token']."'";
            $result2 = $db->query($sql2);
            $row2 = $result2->fetch_assoc();
            $_SESSION['auth_students'] .= $row2['studentId'].',';
          }
          $_SESSION['auth_students'] = rtrim($_SESSION['auth_students'], ", ");
				}
			}
		}
	}

	//Query the database
	function databasequery($query){
		include "abre_dbconnect.php";
		$result = $db->query($query);
		$rowarray = array();
		while($row = $result->fetch_assoc()){
			array_push($rowarray, $row);
		}
		$db->close();

		return $rowarray;
	}

	//Insert into the database
	function databaseexecute($query){
		include "abre_dbconnect.php";
		$stmt = $db->stmt_init();
		$stmt->prepare($query);
		$stmt->execute();
		$newcommentid = $stmt->insert_id;
		$stmt->close();
		$db->close();

		return $newcommentid;
	}

	//Insert into the database
	function pingupdate(){
		$url = 'https://status.abre.io/installation.php';
		$ch = curl_init($url);
		$jsonData = array(
		    'Domain' => "$portal_root"
		);
		$jsonDataEncoded = json_encode($jsonData);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		$result = curl_exec($ch);
	}

	//Save Screenshot to server
	function savescreenshot($website, $filename){
		//Get Image and Use Google Page Speed API
		$website = $website;
		$api = "https://www.googleapis.com/pagespeedonline/v2/runPagespeed?url=$website&screenshot=true";
		$string = file_get_contents($api);
		$json = json_decode($string, true);

		//Get Data from JSON
		$data = $json['screenshot']['data'];

		//Replace characters for correct decode
		$data = str_replace("_","/",$data);
		$data = str_replace("-","+",$data);

		//Decode base64
		$data = base64_decode($data);

		//Save image to server
		$im = imagecreatefromstring($data);

		if ($cloudsetting=="true") {

			$storage = new StorageClient([
				'projectId' => constant("GC_PROJECT")
			]);	
			$bucket = $storage->bucket(constant("GC_BUCKET"));	

			$cloud_dir = "private_html/guide/";

			ob_start (); 
			imagejpeg($im);
			$image_data = ob_get_contents (); 
			ob_end_clean ();

			$options = [
				'resumable' => true,
				'name' => $cloud_dir . $fileName,
				'metadata' => [
					'contentLanguage' => 'en'
				]
			];
			$upload = $bucket->upload(
				$image_data,
				$options
			);
		}
		else {
			if(!file_exists("../../../$portal_private_root/guide")){
				mkdir("../../../$portal_private_root/guide", 0777, true);
			}	
			imagejpeg($im, "../../../$portal_private_root/guide/$filename");
		}
	}

	//Retrieve Site Title
	//DEPRECIATED
	function sitesettings($value){
		include "abre_dbconnect.php";
		if(!$result = $db->query("SELECT * FROM settings LIMIT 1")){
	  		$sql = "CREATE TABLE `settings` (`id` int(11) NOT NULL,`options` text NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	  		$sql .= "INSERT INTO `settings` (`id`, `options`) VALUES (1, '');";
	  		$sql .= "ALTER TABLE `settings` ADD PRIMARY KEY (`id`);";
	  		$sql .= "ALTER TABLE `settings` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
	  		$db->multi_query($sql);
		}

		$sql2 = "SELECT options FROM settings LIMIT 1";
		$result2 = $db->query($sql2);
		if($result2){
			while($row = $result2->fetch_assoc()){
				$options = $row["options"];
				if($options != ""){
					$options = json_decode($options);
					if(isset($options->$value)){
						$valuereturn = $options->$value;
					}else{
						$valuereturn = "";
					}
				}else{
					$valuereturn = "";
				}
				if(($value == "googleclientsecret" && $valuereturn != "") || ($value == "facebookclientsecret" && $valuereturn != "")
				    || ($value == "microsoftclientsecret" && $valuereturn != "")){
					$valuereturn = decrypt($valuereturn, '');
				}
				if($value == "sitetitle" && $valuereturn == ""){ $valuereturn = "Abre"; }
				if($value == "sitecolor" && $valuereturn == ""){ $valuereturn = "#2B2E4A"; }
				if($value == "sitedescription" && $valuereturn == ""){ $valuereturn = "Abre Open Platform for Education"; }
				if($value == "sitelogintext" && $valuereturn == ""){ $valuereturn = "Open Platform for Education"; }
				if($value == "siteanalytics" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "siteadminemail" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "sitevendorlinkurl" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "sitevendorlinkidentifier" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "sitevendorlinkkey" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "certicabaseurl" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "certicaaccesskey" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "studentdomain" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "studentdomainrequired" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "sitelogo" && $valuereturn !=""){
					if($valuereturn != '/core/images/abre/abre_glyph.png'){
						$valuereturn = "/content/$valuereturn";
					}else{
						$valuereturn="/core/images/abre/abre_glyph.png";
					}
				}
				if($value == "sitelogo" && $valuereturn == ""){ $valuereturn = "/core/images/abre/abre_glyph.png"; }
				if($value == "googleclientid" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "parentaccess" && $valuereturn == ""){ $valuereturn = "unchecked"; }
				if($value == "googleclientsecret" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "facebookclientid" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "facebookclientsecret" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "microsoftclientid" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "microsoftclientsecret" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "abre_community" && $valuereturn == ""){ $valuereturn = "unchecked"; }
				if($value == "community_first_name" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "community_last_name" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "community_email" && $valuereturn == ""){ $valuereturn = ""; }
				if($value == "community_users" && $valuereturn == ""){ $valuereturn = ""; }
			}
		}
		$db->close();
		return $valuereturn;
	}

	function getSettingsDbValue($value){
		include "abre_dbconnect.php";
		$sql2 = "SELECT options FROM settings LIMIT 1";
		$result2 = $db->query($sql2);
		if($result2){
			$row = $result2->fetch_assoc();
			$options = $row["options"];
			if($options != ""){
				$options = json_decode($options);
				if(isset($options->$value)){
					$valuereturn = $options->$value;
				}else{
					$valuereturn = "";
				}
			}else{
				$valuereturn = "";
			}
		}else{
			$sql = "CREATE TABLE `settings` (`id` int(11) NOT NULL,`options` text NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
			$sql .= "INSERT INTO `settings` (`id`, `options`) VALUES (1, '');";
			$sql .= "ALTER TABLE `settings` ADD PRIMARY KEY (`id`);";
			$sql .= "ALTER TABLE `settings` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
			$db->multi_query($sql);

			$valuereturn = "";
		}
		$db->close();
		return $valuereturn;
	}

	function getSiteTitle(){
		$valuereturn = getSettingsDbValue('sitetitle');
		if($valuereturn == ""){ $valuereturn = "Abre"; }

		return $valuereturn;
	}

	function getSiteColor(){
		$valuereturn = getSettingsDbValue('sitecolor');
		if($valuereturn == ""){ $valuereturn = "#2B2E4A"; }

		return $valuereturn;
	}

	function getSiteDescription(){
		$valuereturn = getSettingsDbValue('sitedescription');
		if($valuereturn == ""){ $valuereturn = "Abre Open Platform for Education"; }

		return $valuereturn;
	}

	function getSiteLoginText(){
		$valuereturn = getSettingsDbValue('sitelogintext');
		if($valuereturn == ""){ $valuereturn = "Open Platform for Education"; }

		return $valuereturn;
	}

	function getSiteAnalytics(){
		$valuereturn = getSettingsDbValue('siteanalytics');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getSiteAnalyticsViewId(){
		$valuereturn = getSettingsDbValue('analyticsViewId');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getSiteAdminEmail(){
		$valuereturn = getSettingsDbValue('siteadminemail');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getSiteCerticaBaseUrl(){
		$valuereturn = getSettingsDbValue('certicabaseurl');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getSiteCerticaAccessKey(){
		$valuereturn = getSettingsDbValue('certicaaccesskey');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getSiteStudentDomain(){
		$valuereturn = getSettingsDbValue('studentdomain');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getSiteStudentDomainRequired(){
		$valuereturn = getSettingsDbValue('studentdomainrequired');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getStaffStudentMatch(){
		$valuereturn = getSettingsDbValue('staffstudentdomainsame');
		if($valuereturn == ""){ $valuereturn = "unchecked"; }

		return $valuereturn;
	}

	function getSiteLogo(){
		$valuereturn = getSettingsDbValue('sitelogo');
		if($valuereturn == ""){
			$valuereturn = "/core/images/abre/abre_glyph.png";
		}else{
			if($valuereturn != '/core/images/abre/abre_glyph.png'){

				$cloudsetting=constant("USE_GOOGLE_CLOUD");
				if ($cloudsetting=="true") {
					$bucket = constant("GC_BUCKET");
					$valuereturn = "https://storage.googleapis.com/$bucket/content/$valuereturn";
				}
				else {
					$valuereturn = "/content/$valuereturn";
				}			
			}else{
				$valuereturn="/core/images/abre/abre_glyph.png";
			}
		}

		return $valuereturn;
	}

	function getSiteAbreCommunity(){
		$valuereturn = getSettingsDbValue('abre_community');
		if($valuereturn == ""){ $valuereturn = "unchecked"; }

		return $valuereturn;
	}

	function getSiteCommunityFirstName(){
		$valuereturn = getSettingsDbValue('community_first_name');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getSiteCommunityLastName(){
		$valuereturn = getSettingsDbValue('community_last_name');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getSiteCommunityEmail(){
		$valuereturn = getSettingsDbValue('community_email');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getSiteCommunityUsers(){
		$valuereturn = getSettingsDbValue('community_users');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getIntegrationsDbValue($value){
		include "abre_dbconnect.php";
		$sql2 = "SELECT integrations FROM settings LIMIT 1";
		$result2 = $db->query($sql2);
		if($result2){
			$row = $result2->fetch_assoc();
			$options = $row["integrations"];
			if($options != ""){
				$options = json_decode($options);
				if(isset($options->$value)){
					$valuereturn = $options->$value;
				}else{
					$valuereturn = "";
				}
			}else{
				$valuereturn = "";
			}
		}else{
			$sql = "ALTER TABLE `settings` ADD `integrations` text NOT NULL;";
			$db->multi_query($sql);
			$valuereturn = "";
		}
		$db->close();
		return $valuereturn;
	}

	function getSoftwareAnswersURL(){
		$valuereturn = getIntegrationsDbValue('softwareanswersurl');
		if($valuereturn == ""){ $valuereturn = ""; }
		return $valuereturn;
	}

	function getSoftwareAnswersIdentifier(){
		$valuereturn = getIntegrationsDbValue('softwareanswersidentifier');
		if($valuereturn == ""){ $valuereturn = ""; }
		return $valuereturn;
	}

	function getSoftwareAnswersKey(){
		$valuereturn = getIntegrationsDbValue('softwareanswerskey');
		if($valuereturn == ""){ $valuereturn = ""; }
		return $valuereturn;
	}

	function getAuthenticationDbValue($value){
		include "abre_dbconnect.php";
		$sql2 = "SELECT authentication FROM settings LIMIT 1";
		$result2 = $db->query($sql2);
		if($result2){
			$row = $result2->fetch_assoc();
			$options = $row["authentication"];
			if($options != ""){
				$options = json_decode($options);
				if(isset($options->$value)){
					$valuereturn = $options->$value;
				}else{
					$valuereturn = "";
				}
			}else{
				$valuereturn = "";
			}
		}else{
			$sql = "ALTER TABLE `settings` ADD `authentication` text NOT NULL;";
			$db->multi_query($sql);

			$array = [
						"googleclientid" => constant("GOOGLE_CLIENT_ID"),
						"googleclientsecret" => encrypt(constant("GOOGLE_CLIENT_SECRET"), ''),
						"googlesigningroups" => ["staff", "students"]
							];
			$json = json_encode($array);

			$stmt = $db->stmt_init();
			$sql = "UPDATE settings SET authentication = ?";
			$stmt->prepare($sql);
			$stmt->bind_param("s", $json);
			$stmt->execute();
			$stmt->close();

			if($value == "googleclientid"){
				$valuereturn = constant("GOOGLE_CLIENT_ID");
			}elseif($value == "googleclientsecret"){
				$valuereturn = constant("GOOGLE_CLIENT_SECRET");
			}elseif($value == 'googlesigningroups'){
				$json = json_decode($json);
				$valuereturn = $json->googlesigningroups;
			}else{
				$valuereturn = "";
			}
		}
		$db->close();
		return $valuereturn;
	}

	function getSiteGoogleClientId(){
		$valuereturn = getAuthenticationDbValue('googleclientid');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getSiteGoogleClientSecret(){
		$valuereturn = getAuthenticationDbValue('googleclientsecret');
		if($valuereturn == ""){
			$valuereturn = "";
		}else{
			$valuereturn = decrypt($valuereturn, '');
		}

		return $valuereturn;
	}

	function getSiteGoogleSignInGroups($group){
		$valuereturn = getAuthenticationDbValue('googlesigningroups');
		if($valuereturn == ""){
			$valuereturn = "";
		}else{
			if(in_array($group, $valuereturn)){
				$valuereturn = "checked";
			}else{
				$valuereturn = "";
			}
		}
		return $valuereturn;
	}

	function getSiteFacebookClientId(){
		$valuereturn = getAuthenticationDbValue('facebookclientid');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getSiteFacebookClientSecret(){
		$valuereturn = getAuthenticationDbValue('facebookclientsecret');
		if($valuereturn == ""){
			$valuereturn = "";
		}else{
			$valuereturn = decrypt($valuereturn, '');
		}

		return $valuereturn;
	}

	function getSiteFacebookSignInGroups($group){
		$valuereturn = getAuthenticationDbValue('facebooksigningroups');
		if($valuereturn == ""){
			$valuereturn = "";
		}else{
			if(in_array($group, $valuereturn)){
				$valuereturn = "checked";
			}else{
				$valuereturn = "";
			}
		}
		return $valuereturn;
	}

	function getSiteMicrosoftClientId(){
		$valuereturn = getAuthenticationDbValue('microsoftclientid');
		if($valuereturn == ""){ $valuereturn = ""; }

		return $valuereturn;
	}

	function getSiteMicrosoftClientSecret(){
		$valuereturn = getAuthenticationDbValue('microsoftclientsecret');
		if($valuereturn == ""){
			$valuereturn = "";
		}else{
			$valuereturn = decrypt($valuereturn, '');
		}

		return $valuereturn;
	}

	function getSiteMicrosoftSignInGroups($group){
		$valuereturn = getAuthenticationDbValue('microsoftsigningroups');
		if($valuereturn == ""){
			$valuereturn = "";
		}else{
			if(in_array($group, $valuereturn)){
				$valuereturn = "checked";
			}else{
				$valuereturn = "";
			}
		}
		return $valuereturn;
	}

	function getUpdateRequiredDbValue(){
		include "abre_dbconnect.php";
		$sql2 = "SELECT `update_required` FROM settings LIMIT 1";
		$result2 = $db->query($sql2);
		if($result2){
			$row = $result2->fetch_assoc();
			if($row["update_required"] == 0){
				return false;
			}else{
				return true;
			}
		}else{
			$sql = "ALTER TABLE `settings` ADD `update_required` int(11) NOT NULL DEFAULT '1';";
			$db->multi_query($sql);
			return true;
		}
	}

	function isAppInstalled($appName){
		include "abre_dbconnect.php";
		$sql = "SELECT COUNT(*) FROM apps_abre WHERE app='$appName' AND installed = 1";
		$query = $db->query($sql);
		$result = $query->fetch_assoc();
		$count = $result["COUNT(*)"];
		if($count == 1){
			return true;
		}
		return false;
	}

	function isAppActive($appName){
		//return true if any of these apps are checked. This is primarily used for the edit widgets view.
		if($appName == "calendar" || $appName == "classroom" || $appName == "drive" || $appName == "mail"){
			return true;
		}
		include "abre_dbconnect.php";
		$sql = "SELECT COUNT(*) FROM apps_abre WHERE app='$appName' AND active = 1";
		$query = $db->query($sql);
		$result = $query->fetch_assoc();
		$count = $result["COUNT(*)"];
		if($count == 1){
			return true;
		}
		return false;
	}

	function activateApp($appName){
		include "abre_dbconnect.php";
		$active = 1;
		$installed = 0;

		$stmt = $db->stmt_init();
		$insertSql = "INSERT INTO apps_abre (app, active, installed) VALUES (?, ?, ?)";
		$stmt->prepare($insertSql);

		$sql = "SELECT COUNT(*) FROM apps_abre WHERE app = '$appName'";
		$query = $db->query($sql);
		$result = $query->fetch_assoc();
		$count = $result["COUNT(*)"];
		if($count == 0){
			$stmt->bind_param("sii", $appName, $active, $installed);
			$stmt->execute();
		}
		$stmt->close();
		$db->close();
	}

	function linkify($value, $protocols = array('http', 'mail'), array $attributes = array()){
		// Link attributes
		$attr = '';
    foreach($attributes as $key => $val){
        $attr = ' ' . $key . '="' . htmlentities($val) . '"';
    }
    $links = array();

    // Extract existing links and tags
    $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links){ return '<' . array_push($links, $match[1]) . '>'; }, $value);

    // Extract text links for each protocol
    foreach ((array)$protocols as $protocol){
        switch ($protocol){
            case 'http':
            case 'https':   $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { if ($match[1]) $protocol = $match[1]; $link = $match[2] ?: $match[3]; return '<' . array_push($links, "<a $attr href=\"$protocol://$link\" target=\"_blank\" style='color: ".getSiteColor()."'>$link</a>") . '>'; }, $value); break;
            case 'mail':    $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\" target=\"_blank\" style='color: ".getSiteColor()."'>{$match[1]}</a>") . '>'; }, $value); break;
            case 'twitter': $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . "\" target=\"_blank\" style='color: ".getSiteColor()."'>{$match[0]}</a>") . '>'; }, $value); break;
            default:        $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\" target=\"_blank\" style='color: ".getSiteColor()."'>{$match[1]}</a>") . '>'; }, $value); break;
        }
    }
    // Insert all link
    return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links){ return $links[$match[1] - 1]; }, $value);
  }

	//Insert into the database
	function vendorLinkGet($call){
		$VendorLinkURL = getSoftwareAnswersURL();
		$vendorIdentifier = getSoftwareAnswersIdentifier();
		$vendorKey = getSoftwareAnswersKey();
		$userID = "";
		$requestDate = gmdate('D, d M Y H:i:s').' GMT';
		$userName = $vendorIdentifier."|".$userID."|".$requestDate;
		$password = $vendorIdentifier.$userID.$requestDate.$vendorKey;
		$hmacData = $vendorIdentifier.$userID.$requestDate.$vendorKey;
		$hmacSignature = base64_encode(pack("H*", sha1($hmacData)));
		$vlauthheader = $vendorIdentifier."||".$hmacSignature;

		$url = $call;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('VL-Authorization: '.$vlauthheader, 'Date: '.$requestDate));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$json = json_decode($result,true);

		return $json;
	}

	//Insert into the database
	function vendorLinkPost($call, $fields){
		$VendorLinkURL = getSoftwareAnswersURL();
		$vendorIdentifier = getSoftwareAnswersIdentifier();
		$vendorKey = getSoftwareAnswersKey();
		$userID = "";
		$requestDate = gmdate('D, d M Y H:i:s').' GMT';
		$userName = $vendorIdentifier."|".$userID."|".$requestDate;
		$password = $vendorIdentifier.$userID.$requestDate.$vendorKey;
		$hmacData = $vendorIdentifier.$userID.$requestDate.$vendorKey;
		$hmacSignature = base64_encode(pack("H*", sha1($hmacData)));
		$vlauthheader = $vendorIdentifier."||".$hmacSignature;

		$url = $call;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('VL-Authorization: '.$vlauthheader, 'Date: '.$requestDate, 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$json = json_decode($result,true);

		return $json;
	}

	//Insert into the database
	function vendorLinkPut($call, $fields){
		$VendorLinkURL = getSoftwareAnswersURL();
		$vendorIdentifier = getSoftwareAnswersIdentifier();
		$vendorKey = getSoftwareAnswersKey();
		$userID = "";
		$requestDate = gmdate('D, d M Y H:i:s').' GMT';
		$userName = $vendorIdentifier."|".$userID."|".$requestDate;
		$password = $vendorIdentifier.$userID.$requestDate.$vendorKey;
		$hmacData = $vendorIdentifier.$userID.$requestDate.$vendorKey;
		$hmacSignature = base64_encode(pack("H*", sha1($hmacData)));
		$vlauthheader = $vendorIdentifier."||".$hmacSignature;

		$url = $call;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('VL-Authorization: '.$vlauthheader, 'Date: '.$requestDate, 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$json = json_decode($result,true);

		return $json;
	}

	//returns a string listing all scopes the user has access too.
	function getCurrentGoogleScopes($access_token){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=".$access_token);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$result = curl_exec($ch);
		$json = json_decode($result, true);
		return $json["scope"];
	}

	//calls the microsoft graph api to get a active token and stores it in the session
	function getActiveMicrosoftAccessToken(){
		//token has expired, we need to get a new one
		$fields = array(
			'client_id' => urlencode(getSiteMicrosoftClientId()),
			'redirect_uri' => urlencode(''),
			'grant_type' => 'refresh_token',
			'client_secret' => urlencode(getSiteMicrosoftClientSecret()),
			'refresh_token' => $_SESSION['access_token']['refresh_token'],
			'scope' => urlencode('openid profile user.read mail.read')
		);

		//url-ify the data for the POST
		$fields_string = "";
		foreach($fields as $key=>$value){
			$fields_string .= $key.'='.$value.'&';
		}
		rtrim($fields_string, '&');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://login.microsoftonline.com/common/oauth2/v2.0/token");
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);

		$accessTokenArray = json_decode($result, true);
		$_SESSION['access_token']['access_token'] = $accessTokenArray['access_token'];
	}

	function getRestrictions(){
		include "abre_dbconnect.php";
		//get schools codes for the staff member
		$schoolCodeArray = array();
		if($_SESSION['usertype'] == "staff"){
			if($db->query("SELECT * FROM Abre_Staff LIMIT 1")){
				$sql = "SELECT SchoolCode FROM Abre_Staff WHERE EMail1 = '".$_SESSION['useremail']."'";
				$resultrow = $db->query($sql);
				$count = mysqli_num_rows($resultrow);
				while($result = $resultrow->fetch_assoc()){
						array_push($schoolCodeArray, $result['SchoolCode']);
				}
			}
		}

		if($_SESSION['usertype'] == "student"){
			$sql = "SELECT StudentID FROM Abre_AD WHERE Email = '".$_SESSION['useremail']."'";
			$query = $db->query($sql);
			$result = $query->fetch_assoc();
			$studentID = $result["StudentID"];
			if(isset($studentID)){
				if($db->query("SELECT * FROM Abre_Students LIMIT 1")){
					$sql = "SELECT SchoolCode FROM Abre_Students WHERE StudentId = '$studentID' LIMIT 1";
					$resultrow = $db->query($sql);
					$result = $resultrow->fetch_assoc();
					array_push($schoolCodeArray, $result['SchoolCode']);
				}
			}
		}

		return $schoolCodeArray;
	}

	//Admin Check
	function AdminCheck($email){
		include "abre_dbconnect.php";
		$contract = encrypt('Administrator', "");
		$sql = "SELECT COUNT(*) FROM directory WHERE email = '$email' AND contract = '$contract'";
		$result = $db->query($sql);
		$returnrow = $result->fetch_assoc();
		$count = $returnrow["COUNT(*)"];
		$db->close();
		if($count >= 1){
			return true;
		}
		else{
			return false;
		}
	}

	//Resize Image
	function ResizeImage($image, $maxsize, $quality){
		$mime = getimagesize($image);
		if($mime['mime'] == 'image/jpeg'){ $imagecreated = imagecreatefromjpeg($image); }
		if($mime['mime'] == 'image/jpg'){ $imagecreated = imagecreatefromjpeg($image); }
		if($mime['mime'] == 'image/png'){ $imagecreated = imagecreatefrompng($image); }
		if($mime['mime'] == 'image/gif'){ $imagecreated = imagecreatefromgif($image); }
		$imageScaled = imagescale($imagecreated, $maxsize);
		if($mime['mime'] == 'image/jpeg'){ imagejpeg($imageScaled, $image, $quality); }
		if($mime['mime'] == 'image/jpg'){ imagejpeg($imageScaled, $image, $quality); }
		if($mime['mime'] == 'image/png'){ imagepng($imageScaled, $image, "8"); }
		if($mime['mime'] == 'image/gif'){ imagegif($imageScaled, $image, $quality); }
		imagedestroy($imagecreated);
	}

	//Get Staff Name Given Email
	function GetStaffName($email){
		include "abre_dbconnect.php";
		if($email == $_SESSION['useremail']){
			return "Me";
		}else{
			$email = strtolower($email);
			if($db->query("SELECT * FROM Abre_Staff LIMIT 1")){
				$query = "SELECT FirstName, LastName FROM Abre_Staff WHERE EMail1 LIKE '$email' LIMIT 1";
				$dbreturn = databasequery($query);
				foreach ($dbreturn as $value){
					$FirstName = htmlspecialchars($value["FirstName"], ENT_QUOTES);
					$LastName = htmlspecialchars($value["LastName"], ENT_QUOTES);
					return "$FirstName $LastName";
				}
			}
			$db->close();
			return $email;
		}
	}

	//Get Staff FirstName Given Email
	function GetStaffFirstName($email){
		include "abre_dbconnect.php";
		$email = strtolower($email);
		if($db->query("SELECT * FROM Abre_Staff LIMIT 1")){
			$query = "SELECT FirstName FROM Abre_Staff WHERE EMail1 LIKE '$email' LIMIT 1";
			$dbreturn = databasequery($query);
			foreach ($dbreturn as $value){
				$FirstName = htmlspecialchars($value["FirstName"], ENT_QUOTES);
				return $FirstName;
			}
		}

		$query = "SELECT firstname FROM directory WHERE email LIKE '$email' LIMIT 1";
		$dbreturn = databasequery($query);
		foreach ($dbreturn as $value){
			$FirstName = htmlspecialchars($value["firstname"], ENT_QUOTES);
			return $FirstName;
		}
		$db->close();
		return "";
	}

	//Get Staff LastName Given Email
	function GetStaffLastName($email){
		include "abre_dbconnect.php";
		$email = strtolower($email);
		if($db->query("SELECT * FROM Abre_Staff LIMIT 1")){
			$query = "SELECT LastName FROM Abre_Staff WHERE EMail1 LIKE '$email' LIMIT 1";
			$dbreturn = databasequery($query);
			foreach ($dbreturn as $value){
				$LastName = htmlspecialchars($value["LastName"], ENT_QUOTES);
				return $LastName;
			}
		}

		$query = "SELECT lastname FROM directory WHERE email LIKE '$email' LIMIT 1";
		$dbreturn = databasequery($query);
		foreach ($dbreturn as $value){
			$LastName = htmlspecialchars($value["lastname"], ENT_QUOTES);
			return $LastName;
		}
		$db->close();
		return "";
	}

	//Get Staff UniqueID Given Email
	function GetStaffUniqueID($email){
		include "abre_dbconnect.php";
		$email = strtolower($email);
		if($db->query("SELECT * FROM Abre_Staff LIMIT 1")){
			$query = "SELECT StaffID FROM Abre_Staff WHERE EMail1 LIKE '$email' LIMIT 1";
			$dbreturn = databasequery($query);
			foreach ($dbreturn as $value){
				$StaffID = htmlspecialchars($value["StaffID"], ENT_QUOTES);
				return $StaffID;
			}
		}
		$db->close();
		return "";
	}

	//Get Student FirstName Given Email
	function GetStudentFirstName($email){
		include "abre_dbconnect.php";
		$email = strtolower($email);
		if($db->query("SELECT * FROM Abre_AD LIMIT 1")){
			$query = "SELECT StudentID FROM Abre_AD WHERE Email LIKE '$email' LIMIT 1";
			$dbreturn = databasequery($query);
			if($db->query("SELECT * FROM Abre_Students LIMIT 1")){
				foreach ($dbreturn as $value){
					$StudentID = htmlspecialchars($value["StudentID"], ENT_QUOTES);
					$query2 = "SELECT FirstName FROM Abre_Students WHERE StudentId = '$StudentID' LIMIT 1";
					$dbreturn2 = databasequery($query2);
					foreach ($dbreturn2 as $value2){
						$FirstName = htmlspecialchars($value2["FirstName"], ENT_QUOTES);
					}
					return $FirstName;
				}
			}
		}
		$db->close();
		return "";
	}

	//Get Student LastName Given Email
	function GetStudentLastName($email){
		include "abre_dbconnect.php";
		$email = strtolower($email);
		if($db->query("SELECT * FROM Abre_AD LIMIT 1")){
		$query = "SELECT StudentID FROM Abre_AD WHERE Email LIKE '$email' LIMIT 1";
			$dbreturn = databasequery($query);
			if($db->query("SELECT * FROM Abre_Students LIMIT 1")){
				foreach ($dbreturn as $value){
					$StudentID = htmlspecialchars($value["StudentID"], ENT_QUOTES);
					$query2 = "SELECT LastName FROM Abre_Students WHERE StudentId = '$StudentID' LIMIT 1";
					$dbreturn2 = databasequery($query2);
					foreach ($dbreturn2 as $value2){
						$LastName = htmlspecialchars($value2["LastName"], ENT_QUOTES);
					}
					return $LastName;
				}
			}
		}
		$db->close();
		return "";
	}

	//Get Student UniqueID Given Email
	function GetStudentUniqueID($email){
		include "abre_dbconnect.php";
		$email = strtolower($email);
		if($db->query("SELECT * FROM Abre_AD LIMIT 1")){
			$query = "SELECT StudentID FROM Abre_AD WHERE Email LIKE '$email' LIMIT 1";
			$dbreturn = databasequery($query);
			foreach ($dbreturn as $value){
				$StudentID = htmlspecialchars($value["StudentID"], ENT_QUOTES);
				return $StudentID;
			}
		}
		$db->close();
		return "";
	}

	function sendFormEmailNotification($ownerEmail, $formName, $url){
		$to = $ownerEmail;
		$subject = "New Response";
		$message = 'Your form, '.$formName.', has a new response. <a href='.$url.'>View all responses now!</a>';
		$headers = "From: noreply@abre.io";
		$headers  .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		mail($to,$subject,$message,$headers);
	}

?>