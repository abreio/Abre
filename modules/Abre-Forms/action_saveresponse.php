<?php

	/*
	* Copyright (C) 2016-2017 Abre.io LLC
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
	require_once(dirname(__FILE__) . '/../../core/abre_verification.php');
	require_once(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');

	$cloudsetting=constant("USE_GOOGLE_CLOUD");
	if ($cloudsetting=="true") 
		require(dirname(__FILE__). '/../../vendor/autoload.php');
	use Google\Cloud\Storage\StorageClient;

		//Get POST Data
		if(isset($_POST['json'])){
			$formDataArray = $_POST['json'];
			$formDataArray = json_decode($_POST['json'], true);
		}else{
			$formDataArray = "";
		}

		foreach($_FILES as $key=>$value){
			if($value['size'] > 0){
				if($value['size'] < 1000000){
					$fileextension = pathinfo($value['name'], PATHINFO_EXTENSION);
					$hashedFileName = sha1($value['name']);
					$file_name = $hashedFileName.".".$fileextension;

					if ($cloudsetting=="true") {
						$storage = new StorageClient([
							'projectId' => constant("GC_PROJECT")
						]);	
						$bucket = $storage->bucket(constant("GC_BUCKET"));				
						$uploaddir = "private_html/Abre-Forms/".$formDataArray['formid']."/" . $file_name;
						$upload = "private_html/Abre-Forms/".$formDataArray['formid']."/";
						if (!$bucket->object($upload)->exists()){
						}

						if (!$bucket->object($uploaddir)->exists()){

							$options = [
								'resumable' => true,
								'name' => $uploaddir,
								'metadata' => [
									'contentLanguage' => 'en'
								]
							];
							$upload = $bucket->upload(
								fopen($value['tmp_name'], "r"),
								$options
							);
							$filename = $value['name'];
						}
						else {
							$i = 1;
							while(file_exists($uploaddir)){
								$filename = $fileNameNoExtension." ($i).".$fileextension;
								$hashedFileName = sha1($filename);
								$file_name = $hashedFileName.".".$fileextension;
								$uploaddir = "private_html/Abre-Forms/".$formDataArray['formid']."/" . $file_name;
								$i++;
							}

							$options = [
								'resumable' => true,
								'name' => $uploaddir,
								'metadata' => [
									'contentLanguage' => 'en'
								]
							];
							$upload = $bucket->upload(
								fopen($value['tmp_name'], "r"),
								$options
							);
						}
					}
					else {
						$uploaddir = $portal_path_root . "/../$portal_private_root/Abre-Forms/".$formDataArray['formid']."/" . $file_name;
						if(!file_exists($portal_path_root . "/../$portal_private_root/Abre-Forms/".$formDataArray['formid']."/")){
							mkdir($portal_path_root . "/../$portal_private_root/Abre-Forms/".$formDataArray['formid']."/", 0775);
						}
						if(!file_exists($uploaddir)){
							move_uploaded_file($value['tmp_name'], $uploaddir);
							$filename = $value['name'];
						}else{
							$i = 1;
							while(file_exists($uploaddir)){
								$filename = $fileNameNoExtension." ($i).".$fileextension;
								$hashedFileName = sha1($filename);
								$file_name = $hashedFileName.".".$fileextension;
								$uploaddir = $portal_path_root . "/../$portal_private_root/Abre-Forms/".$formDataArray['formid']."/" . $file_name;
								$i++;
							}
							move_uploaded_file($value['tmp_name'], $uploaddir);
						}	
					}

					$fileFieldName = str_replace("_", " ", $key);

					$formDataArray[$fileFieldName] = array();
					$storeName = htmlspecialchars($filename, ENT_QUOTES);
					$formDataArray[$fileFieldName][$storeName] = $file_name;
				}
			}
		}
		if(isset($formDataArray['formid'])){
			$formid = $formDataArray['formid'];
			unset($formDataArray["formid"]);
		}else{
			$formid = '';
		}
		if(isset($formDataArray['formsubmitter'])){
			$formsubmitter = $formDataArray['formsubmitter'];
			unset($formDataArray['formsubmitter']);
		}else{
			$formsubmitter = '';
		}
		if(isset($formDataArray['formowner'])){
			$formOwner = $formDataArray['formowner'];
			unset($formDataArray['formowner']);
		}else{
			$formOwner = '';
		}
		if(isset($formDataArray['formEmailNotifications'])){
			$formEmailNotifications = $formDataArray['formEmailNotifications'];
			unset($formDataArray['formEmailNotifications']);
		}else{
			$formEmailNotifications = '';
		}
		if(isset($formDataArray['formName'])){
			$formName = $formDataArray['formName'];
			unset($formDataArray['formName']);
		}else{
			$formName = '';
		}

		$response = json_encode($formDataArray);

		//User Information
		$usertype = $_SESSION['usertype'];
		$firstname = "";
		$lastname = "";
		$uniqueid = "";

		//Get Staff Information
		if($usertype == 'staff'){
			$firstname = GetStaffFirstName($formsubmitter);
			$lastname = GetStaffLastName($formsubmitter);
			$uniqueid = GetStaffUniqueID($formsubmitter);
		}

		//Get Student Information
		if($usertype == 'student'){
			$firstname = GetStudentFirstName($formsubmitter);
			$lastname = GetStudentLastName($formsubmitter);
			$uniqueid = GetStudentUniqueID($formsubmitter);
		}

		//Get Staff Information
		if($usertype == 'parent' or ($firstname=="" && $lastname=="")){
			$name = $_SESSION['displayName'];
			$name = explode(" ", $name);
			$firstname = $name[0];
			$lastname = $name[1];
		}

		//Add entry to responses
		$stmt = $db->stmt_init();
		$sql = "INSERT INTO forms_responses (FormID, Submitter, FirstName, LastName, UniqueID, UserType, Response) VALUES (?, ?, ?, ?, ?, ?, ?);";
		$stmt->prepare($sql);
		$stmt->bind_param("issssss", $formid, $formsubmitter, $firstname, $lastname, $uniqueid, $usertype, $response);
		$stmt->execute();
		$stmt->close();
		$db->close();

		if($formEmailNotifications == "checked" && $formOwner != ""){
			sendFormEmailNotification($formOwner, $formName, $portal_root."/#forms/responses/$formid");
		}


?>