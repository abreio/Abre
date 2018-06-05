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
	require_once('permissions.php');
	
	$cloudsetting=constant("USE_GOOGLE_CLOUD");
	if ($cloudsetting=="true") 
		require(dirname(__FILE__). '/../../vendor/autoload.php');
	use Google\Cloud\Storage\StorageClient;

	if($pagerestrictions=="")
	{
		$UserEmail=$_SESSION['useremail'];

		if ($cloudsetting=="true") {
			$storage = new StorageClient([
				'projectId' => constant("GC_PROJECT")
			]);	
			$bucket = $storage->bucket(constant("GC_BUCKET"));	
			$cloud_assessment = "private_html/Abre-Assessments/Exports/$UserEmail.csv";

			if ($bucket->object($cloud_assessment)->exists()){

				$info = $bucket->object($cloud_assessment)->info();
				$filesize = $info['size'];

				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename=AssessmentDataExport.csv');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . $filesize);
				if(ob_get_length() > 0){ ob_end_clean(); }
				flush();

				$object = $bucket->object($cloud_assessment);
				$file = $object->downloadAsStream();
				echo($file);
			}
		}
		else {	
			$file = "../../../$portal_private_root/Abre-Assessments/Exports/$UserEmail.csv";
			
			if (file_exists($file)) {
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename=AssessmentDataExport.csv');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));
				if(ob_get_length() > 0){ ob_end_clean(); }
				flush();
				readfile($file);
				exit;
			}
		}

	}
	?>