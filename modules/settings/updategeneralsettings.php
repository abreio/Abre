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
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');
	require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

	$cloudsetting=constant("USE_GOOGLE_CLOUD");
	if ($cloudsetting=="true") 
		require(dirname(__FILE__). '/../../vendor/autoload.php');
	use Google\Cloud\Storage\StorageClient;

	//Update system settings
	if(admin()){

		//Retrieve settings and group as json
		$sitetitle = $_POST["sitetitle"];
		$sitecolor = $_POST["sitecolor"];
		$sitedescription = $_POST["sitedescription"];
		$sitelogintext = $_POST["sitelogintext"];
		$siteanalytics = $_POST["siteanalytics"];
		$siteadminemail = $_POST["siteadminemail"];
		$studentdomain = $_POST["studentdomain"];
		$studentdomainrequired = $_POST["studentdomainrequired"];
		if(isset($_POST['staffandstudentdomainssame'])){ $abre_staffstudentdomainsame=$_POST['staffandstudentdomainssame']; }else{ $abre_staffstudentdomainsame=""; }
		if(isset($_POST['abre_community'])){ $abre_community=$_POST['abre_community']; }else{ $abre_community=""; }
		$community_first_name = $_POST['community_first_name'];
		$community_last_name = $_POST['community_last_name'];
		$community_email = $_POST['community_email'];
		$community_users = $_POST['community_users'];
		$analyticsViewId = $_POST['analyticsViewId'];

		if($_FILES['sitelogo']['name']){
			//Get file information
			$file = $_FILES['sitelogo']['name'];
			$fileextention = pathinfo($file, PATHINFO_EXTENSION);
			$cleanfilename = basename($file);
			$sitelogofilename = time() . "siteicon." . $fileextention;

			if ($cloudsetting=="true") {
				$storage = new StorageClient([
					'projectId' => constant("GC_PROJECT")
				]);	
				$bucket = $storage->bucket(constant("GC_BUCKET"));		

				$cloud_uploaddir = "content/";
				//Delete previous image
				$sitelogoexisting = getSiteLogo();
				if($sitelogoexisting != '/core/images/abre/abre_glyph.png'){ 
					$logo = getSettingsDbValue('sitelogo');
					$oldimagelocation = $cloud_uploaddir.$logo;

					if ($bucket->object($oldimagelocation)->exists()){
						$object = $bucket->object($oldimagelocation);
						$object->delete();
					}
				}

				//Upload new image
				$sitelogo = $cloud_uploaddir . $sitelogofilename;
				$options = [
					'resumable' => true,
					'name' => $sitelogo,
					'metadata' => [
						'contentLanguage' => 'en'
					]
				];
				$upload = $bucket->upload(
					fopen($_FILES['sitelogo']['tmp_name'], 'r'),
					$options
				);
				$acl = $upload->acl();
				$acl->add('allUsers', 'READER');
			}
			else {
				$uploaddir = $portal_path_root.'/content/';

				//Delete previous image
				$sitelogoexisting = getSiteLogo();
				$oldimagelocation = $portal_path_root.$sitelogoexisting;
				if($sitelogoexisting != '/core/images/abre/abre_glyph.png'){ unlink($oldimagelocation); }
	
				//Upload new image
				if (!file_exists("$portal_path_root/content/")){ mkdir("$portal_path_root/content/"); }
				$sitelogo = $uploaddir . $sitelogofilename;
				move_uploaded_file($_FILES['sitelogo']['tmp_name'], $sitelogo);	
			}
		}else{
			$sitelogoexisting = getSiteLogo();
			if(strpos($sitelogoexisting, '/content/') !== false){
				$sitelogoexisting = ltrim($sitelogoexisting,"/content/");
			}
			$sitelogofilename=$sitelogoexisting;
		}

		$array = [
					"sitetitle" => "$sitetitle",
					"sitecolor" => "$sitecolor",
					"sitedescription" => "$sitedescription",
					"sitelogintext" => "$sitelogintext",
					"siteanalytics" => "$siteanalytics",
					"analyticsViewId" => "$analyticsViewId",
					"siteadminemail" => "$siteadminemail",
					"sitelogo" => "$sitelogofilename",
					"studentdomain" => "$studentdomain",
					"studentdomainrequired" => "$studentdomainrequired",
					"staffstudentdomainsame" => "$abre_staffstudentdomainsame",
					"abre_community" => "$abre_community",
					"community_first_name" => "$community_first_name",
					"community_last_name" => "$community_last_name",
					"community_email" => "$community_email",
					"community_users" => "$community_users"
				];
		$json = json_encode($array);

		$stmt = $db->stmt_init();
		$sql = "UPDATE settings SET options=?";
		$stmt->prepare($sql);
		$stmt->bind_param("s", $json);
		$stmt->execute();
		$stmt->close();
		$db->close();

		//Notification message
		echo "Settings have been updated.";
	}

?>
