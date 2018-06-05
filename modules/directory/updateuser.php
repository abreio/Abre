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

	//Required configuration files
	require(dirname(__FILE__) . '/../../configuration.php');
	require_once(dirname(__FILE__) . '/../../core/abre_verification.php');
	require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
	require('permissions.php');
	require_once('functions.php');
	require_once('../../core/abre_functions.php');

	$cloudsetting=constant("USE_GOOGLE_CLOUD");
	if ($cloudsetting=="true") 
		require(dirname(__FILE__). '/../../vendor/autoload.php');
	use Google\Cloud\Storage\StorageClient;

	//Update the User
	if($pageaccess == 1){

		$stack = array();
		$id = $_POST["id"];
		$email = $_POST["email"];
		$firstname = $_POST["firstname"];
		$lastname = $_POST["lastname"];
		if($email == "" && $id == "new"){
			$email = emailPrediction($firstname, $lastname);
		}else{
			$email = $email;
		}
		$middlename = $_POST["middlename"];
		$title = $_POST["title"];
		$contract = $_POST["contract"];
		$contract = encrypt($contract, "");
		$address = $_POST["address"];
		$address = encrypt($address, "");
		$city = $_POST["city"];
		$city = encrypt($city, "");
		$state = $_POST["state"];
		$state = encrypt($state, "");
		$zip = $_POST["zip"];
		$zip = encrypt($zip, "");
		$phone = $_POST["phone"];
		$phone = encrypt($phone, "");
		$extension = $_POST["extension"];
		$cellphone = $_POST["cellphone"];
		$cellphone = encrypt($cellphone, "");
		$ss = $_POST["ss"];
		$ss = encrypt($ss, "");
		$dob = $_POST["dob"];
		$dob = encrypt($dob, "");
		$gender = $_POST["gender"];
		$gender = encrypt($gender, "");
		$ethnicity = $_POST["ethnicity"];
		$ethnicity = encrypt($ethnicity, "");
		$classification = $_POST["classification"];
		$location = $_POST["location"];
		$grade = $_POST["grade"];
		$subject = $_POST["subject"];
		$doh = $_POST["doh"];
		$doh = encrypt($doh, "");
		$sd = $_POST["sd"];
		$sd = encrypt($sd, "");
		$ed = $_POST["ed"];
		$ed = encrypt($ed, "");
		$rategroup = $_POST["rategroup"];
		$rategroup = encrypt($rategroup, "");
		$step = $_POST["step"];
		$step = encrypt($step, "");
		$educationlevel = $_POST["educationlevel"];
		$educationlevel = encrypt($educationlevel, "");
		$salary = $_POST["salary"];
		$salary = encrypt($salary, "");
		$hours = $_POST["hours"];
		$hours = encrypt($hours, "");
		$probationreportdate = $_POST["probationreportdate"];
		$probationreportdate = encrypt($probationreportdate, "");
		$statebackgroundcheck = $_POST["statebackgroundcheck"];
		$statebackgroundcheck = encrypt($statebackgroundcheck, "");
		$federalbackgroundcheck = $_POST["federalbackgroundcheck"];
		$federalbackgroundcheck = encrypt($federalbackgroundcheck, "");
		$stateeducatorid = $_POST["stateeducatorid"];
		$stateeducatorid = encrypt($stateeducatorid, "");
		$currentpicture = $_POST["currentpicture"];

		$licensetypeid1 = $_POST["licensetypeid1"];
		$licensetypeid1 = encrypt($licensetypeid1, "");
		$licenseissuedateid1 = $_POST["licenseissuedateid1"];
		$licenseissuedateid1 = encrypt($licenseissuedateid1, "");
		$licenseexpirationdateid1 = $_POST["licenseexpirationdateid1"];
		$licenseexpirationdateid1 = encrypt($licenseexpirationdateid1, "");
		$licensetermid1 = $_POST["licensetermid1"];
		$licensetermid1 = encrypt($licensetermid1, "");

		$licensetypeid2 = $_POST["licensetypeid2"];
		$licensetypeid2 = encrypt($licensetypeid2, "");
		$licenseissuedateid2 = $_POST["licenseissuedateid2"];
		$licenseissuedateid2 = encrypt($licenseissuedateid2, "");
		$licenseexpirationdateid2 = $_POST["licenseexpirationdateid2"];
		$licenseexpirationdateid2 = encrypt($licenseexpirationdateid2, "");
		$licensetermid2 = $_POST["licensetermid2"];
		$licensetermid2 = encrypt($licensetermid2, "");

		$licensetypeid3 = $_POST["licensetypeid3"];
		$licensetypeid3 = encrypt($licensetypeid3, "");
		$licenseissuedateid3 = $_POST["licenseissuedateid3"];
		$licenseissuedateid3 = encrypt($licenseissuedateid3, "");
		$licenseexpirationdateid3 = $_POST["licenseexpirationdateid3"];
		$licenseexpirationdateid3 = encrypt($licenseexpirationdateid3, "");
		$licensetermid3 = $_POST["licensetermid3"];
		$licensetermid3 = encrypt($licensetermid3, "");

		$licensetypeid4 = $_POST["licensetypeid4"];
		$licensetypeid4 = encrypt($licensetypeid4, "");
		$licenseissuedateid4 = $_POST["licenseissuedateid4"];
		$licenseissuedateid4 = encrypt($licenseissuedateid4, "");
		$licenseexpirationdateid4 = $_POST["licenseexpirationdateid4"];
		$licenseexpirationdateid4 = encrypt($licenseexpirationdateid4, "");
		$licensetermid4 = $_POST["licensetermid4"];
		$licensetermid4 = encrypt($licensetermid4, "");

		$licensetypeid5 = $_POST["licensetypeid5"];
		$licensetypeid5 = encrypt($licensetypeid5, "");
		$licenseissuedateid5 = $_POST["licenseissuedateid5"];
		$licenseissuedateid5 = encrypt($licenseissuedateid5, "");
		$licenseexpirationdateid5 = $_POST["licenseexpirationdateid5"];
		$licenseexpirationdateid5 = encrypt($licenseexpirationdateid5, "");
		$licensetermid5 = $_POST["licensetermid5"];
		$licensetermid5 = encrypt($licensetermid5, "");

		$licensetypeid6 = $_POST["licensetypeid6"];
		$licensetypeid6 = encrypt($licensetypeid6, "");
		$licenseissuedateid6 = $_POST["licenseissuedateid6"];
		$licenseissuedateid6 = encrypt($licenseissuedateid6, "");
		$licenseexpirationdateid6 = $_POST["licenseexpirationdateid6"];
		$licenseexpirationdateid6 = encrypt($licenseexpirationdateid6, "");
		$licensetermid6 = $_POST["licensetermid6"];
		$licensetermid6 = encrypt($licensetermid6, "");

		$permissions = $_POST["permissions"];
		$permissions = encrypt($permissions, "");
		isset($_POST["sysadmin"]) ? $sysadmin = $_POST["sysadmin"] : $sysadmin = 0;

		isset($_POST["role"]) ? $role = $_POST["role"] : $role = "";
		if($role != ""){
			$role = implode (", ", $_POST["role"]);
		}
		if(strpos($role, 'Directory Administrator') !== false){
			$superadmin = 1;
		}else{
			$superadmin = 0;
		}

		if(strpos($role, 'Directory Supervisor') !== false){
			$admin = 1;
		}elseif(strpos($role, 'Directory Advisor') !== false){
			$admin = 2;
		}else{
			$admin = 0;
		}
		$role = encrypt($role, "");

		$contractdays = $_POST["contractdays"];
		$contractdays = encrypt($contractdays, "");

		//Process New Profile Picture
		if($_FILES['picture']['name']){
			require_once("ImageManipulator.php");

			$validExtensions = array('.jpg', '.JPG', '.jpeg', '.JPEG', '.gif', '.GIF', '.png', '.PNG');
		    $fileExtension = strrchr($_FILES['picture']['name'], ".");
		    if(in_array($fileExtension, $validExtensions)){
					$newNamePrefix = time() . '_';
				$manipulator = new ImageManipulator($_FILES['picture']['tmp_name']);
				$manipulator->resample(500, 500);
				$width  = $manipulator->getWidth();
				$height = $manipulator->getHeight();
				$centreX = round($width / 2);
				$centreY = round($height / 2);
				$x1 = $centreX - 150;
				$y1 = $centreY - 150;

				$x2 = $centreX + 150;
				$y2 = $centreY + 150;

				$newImage = $manipulator->crop($x1, $y1, $x2, $y2);
				// saving file to uploads folder
				$picturefilename = $newNamePrefix . $_FILES['picture']['name'];
				if ($cloudsetting=="true") {
					$manipulator->saveGC("private_html/directory/images/employees/". $picturefilename);				
				}
				else {
					$manipulator->save($portal_path_root."/../$portal_private_root/directory/images/employees/" . $picturefilename);				
				}
			}	
		}else{
			$picturefilename = $currentpicture;
		}

		if($id != "new"){
			//Process the Discipline Report
			if($_FILES['discipline']['name']){
				//Upload File to Server
				$validExtensions = array('.doc', '.DOC', '.docx', '.DOCX', '.pdf', '.PDF');
		    	$fileExtension = strrchr($_FILES['discipline']['name'], ".");
		    	if(in_array($fileExtension, $validExtensions)){
					$newNamePrefix = time() . '$_$';
			    	$disfilename = $newNamePrefix . $_FILES['discipline']['name'];

					$cloud_directory = "private_html/directory/discipline/" . $disfilename;
					if ($cloudsetting=="true") {
						$storage = new StorageClient([
							'projectId' => constant("GC_PROJECT")
						]);	
						$bucket = $storage->bucket(constant("GC_BUCKET"));				
				
						$options = [
							'resumable' => true,
							'name' => $cloud_directory,
							'metadata' => [
								'contentLanguage' => 'en'
							]
						];
						$upload = $bucket->upload(
							fopen($_FILES['discipline']['tmp_name'], "r"),
							$options
						);					
					}
					else {
						if(!move_uploaded_file($_FILES['discipline']['tmp_name'], $portal_path_root . "/../$portal_private_root/directory/discipline/" . $disfilename)){
							echo "The file was not uploaded";
						}	
					}

					//Add Record to Database
					include "../../core/abre_dbconnect.php";
					$stmtdiscipline = $db->stmt_init();
					$sqldiscipline = "INSERT INTO directory_discipline (UserID,Filename) VALUES (?, ?);";
					$stmtdiscipline->prepare($sqldiscipline);
					$stmtdiscipline->bind_param("is", $id, $disfilename);
					$stmtdiscipline->execute();
					$stmtdiscipline->store_result();
					$stmtdiscipline->close();
					$db->close();
		    	}
			}
		}

		if($id != ""){
			include "../../core/abre_dbconnect.php";
			$stmt = $db->stmt_init();
			$sql = "UPDATE directory SET updatedtime = now(), superadmin = ?, admin = ?, picture = ?, firstname = ?, middlename = ?, lastname = ?, address = ?, city = ?, state = ?, zip = ?, phone = ?, extension = ?, cellphone = ?, email = ?, ss = ?, dob = ?, gender = ?, ethnicity = ?, title = ?, contract = ?, classification = ?, location = ?, grade = ?, subject = ?, doh = ?, senioritydate = ?, effectivedate = ?, rategroup = ?, step = ?, educationlevel = ?, salary = ?, hours = ?, probationreportdate = ?, statebackgroundcheck = ?, federalbackgroundcheck = ?, stateeducatorid = ?, licensetype1 = ?, licenseissuedate1 = ?, licenseexpirationdate1 = ?, licenseterm1 = ?, licensetype2 = ?, licenseissuedate2 = ?, licenseexpirationdate2 = ?, licenseterm2 = ?, licensetype3 = ?, licenseissuedate3 = ?, licenseexpirationdate3 = ?, licenseterm3 = ?, licensetype4 = ?, licenseissuedate4 = ?, licenseexpirationdate4 = ?, licenseterm4 = ?, licensetype5 = ?, licenseissuedate5 = ?, licenseexpirationdate5 = ?, licenseterm5 = ?, licensetype6 = ?, licenseissuedate6 = ?, licenseexpirationdate6 = ?, licenseterm6 = ?, permissions = ?, role = ?, contractdays = ? WHERE id = ?";
			$stmt->prepare($sql);
			$stmt->bind_param("iisssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssi", $superadmin, $admin, $picturefilename, $firstname, $middlename, $lastname, $address, $city, $state, $zip, $phone, $extension, $cellphone, $email, $ss, $dob, $gender, $ethnicity, $title, $contract, $classification, $location, $grade, $subject, $doh, $sd, $ed, $rategroup, $step, $educationlevel, $salary, $hours, $probationreportdate, $statebackgroundcheck, $federalbackgroundcheck, $stateeducatorid, $licensetypeid1, $licenseissuedateid1, $licenseexpirationdateid1, $licensetermid1, $licensetypeid2, $licenseissuedateid2, $licenseexpirationdateid2, $licensetermid2, $licensetypeid3, $licenseissuedateid3, $licenseexpirationdateid3, $licensetermid3, $licensetypeid4, $licenseissuedateid4, $licenseexpirationdateid4, $licensetermid4, $licensetypeid5, $licenseissuedateid5, $licenseexpirationdateid5, $licensetermid5, $licensetypeid6, $licenseissuedateid6, $licenseexpirationdateid6, $licensetermid6, $permissions, $role, $contractdays, $id);
			$stmt->execute();
			$stmt->close();
			$db->close();
		}
		if($id == "new"){
			include "../../core/abre_dbconnect.php";
			$stmt = $db->stmt_init();
			$sql = "INSERT INTO directory (updatedtime, superadmin, admin, picture, firstname, lastname, middlename, address, city, state, zip, email, phone, extension, cellphone, ss, dob, gender, ethnicity, title, contract, classification, location, grade, subject, doh, senioritydate, effectivedate, rategroup, step, educationlevel, salary, hours, probationreportdate, statebackgroundcheck, federalbackgroundcheck, stateeducatorid, licensetype1, licenseissuedate1, licenseexpirationdate1, licenseterm1, licensetype2, licenseissuedate2, licenseexpirationdate2, licenseterm2, licensetype3, licenseissuedate3, licenseexpirationdate3, licenseterm3, licensetype4, licenseissuedate4, licenseexpirationdate4, licenseterm4, licensetype5, licenseissuedate5, licenseexpirationdate5, licenseterm5, licensetype6, licenseissuedate6, licenseexpirationdate6, licenseterm6, permissions, role, contractdays) VALUES (CURRENT_TIMESTAMP, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
			$stmt->prepare($sql);
			$stmt->bind_param("iisssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss", $superadmin, $admin, $picturefilename, $firstname, $lastname, $middlename, $address, $city, $state, $zip, $email, $phone, $extension, $cellphone, $ss, $dob, $gender, $ethnicity, $title, $contract, $classification, $location, $grade, $subject, $doh, $sd, $ed, $rategroup, $step, $educationlevel, $salary, $hours, $probationreportdate, $statebackgroundcheck, $federalbackgroundcheck, $stateeducatorid, $licensetypeid1, $licenseissuedateid1, $licenseexpirationdateid1, $licensetermid1, $licensetypeid2, $licenseissuedateid2, $licenseexpirationdateid2, $licensetermid2, $licensetypeid3, $licenseissuedateid3, $licenseexpirationdateid3, $licensetermid3, $licensetypeid4, $licenseissuedateid4, $licenseexpirationdateid4, $licensetermid4, $licensetypeid5, $licenseissuedateid5, $licenseexpirationdateid5, $licensetermid5, $licensetypeid6, $licenseissuedateid6, $licenseexpirationdateid6, $licensetermid6, $permissions, $role, $contractdays);
			$stmt->execute();
			$stmt->close();
			$db->close();
		}

		include "../../core/abre_dbconnect.php";
		$sql = "SELECT COUNT(*) FROM users WHERE email = '$email'";
		$result = $db->query($sql);
		$resultrow = $result->fetch_assoc();
		$rowcount = $resultrow["COUNT(*)"];

		if($rowcount == 0){
			include "../../core/abre_dbconnect.php";
			$stmt = $db->stmt_init();
			$sql = "INSERT INTO users (email, admin) VALUES (?, ?);";
			$stmt->prepare($sql);
			$stmt->bind_param("si", $email, $sysadmin);
			$stmt->execute();
			$stmt->close();
			$db->close();
		}else{
			include "../../core/abre_dbconnect.php";
			$stmt = $db->stmt_init();
			$sql = "UPDATE users SET admin = ? WHERE email = ?;";
			$stmt->prepare($sql);
			$stmt->bind_param("is", $sysadmin, $email);
			$stmt->execute();
			$stmt->close();
			$db->close();
		}

		echo "The user has been updated.";
	}

?>