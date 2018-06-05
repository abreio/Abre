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
	require_once('permissions.php');

	$cloudsetting=constant("USE_GOOGLE_CLOUD");
	if ($cloudsetting=="true") 
		require(dirname(__FILE__). '/../../vendor/autoload.php');
	use Google\Cloud\Storage\StorageClient;


	if($pageaccess == 1){
		$filename = $_GET["file"];

		if ($cloudsetting=="true") {
			$storage = new StorageClient([
				'projectId' => constant("GC_PROJECT")
			]);	
			$bucket = $storage->bucket(constant("GC_BUCKET"));	
			$cloud_directory = "private_html/directory/discipline/" . $filename;

			if ($bucket->object($cloud_directory)->exists()){

				$info = $bucket->object($cloud_directory)->info();
				$filesize = $info['size'];

				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="'.basename('DisciplineReport.'.$fileext).'"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . $filesize);

				$object = $bucket->object($cloud_directory);
				$file = $object->downloadAsStream();
				echo($file);
			}
		}
		else {
			$file = $portal_path_root."/../$portal_private_root/directory/discipline/" . $filename;
			$fileext = pathinfo($filename, PATHINFO_EXTENSION);
	
			if(file_exists($file)){
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="'.basename('DisciplineReport.'.$fileext).'"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));
				readfile($file);
				exit;
			}	
		}
	}
?>