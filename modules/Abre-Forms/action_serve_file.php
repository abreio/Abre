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

  $cloudsetting=constant("USE_GOOGLE_CLOUD");
	if ($cloudsetting=="true") 
		require(dirname(__FILE__). '/../../vendor/autoload.php');
	use Google\Cloud\Storage\StorageClient;

	$filename = $_GET['filename'];
	$file = $_GET['hash'];
  $formid = $_GET['formid'];

  if ($cloudsetting=="true") {
    $storage = new StorageClient([
			'projectId' => constant("GC_PROJECT")
		]);	
		$bucket = $storage->bucket(constant("GC_BUCKET"));
    $cloud_form = "private_html/Abre-Forms/$formid/".$file;

    if ($bucket->object($cloud_form)->exists()){

      $info = $bucket->object($cloud_form)->info();
      $filesize = $info['size'];

      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename='.basename($filename));
      header('Content-Transfer-Encoding: binary');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . $filesize);
      flush();

      $object = $bucket->object($cloud_form);
      $file = $object->downloadAsStream();
      echo($file);
    }
    else {
      //need to create custom 404 page. echoing response for now
      echo "The file no longer exists on the server!";      
    }
  }
  else {
    $download_file = $portal_path_root."/../$portal_private_root/Abre-Forms/$formid/".$file;
    if(file_exists($download_file)){
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename='.basename($filename));
      header('Content-Transfer-Encoding: binary');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . filesize($download_file));
      flush();
      readfile($download_file);
      exit;
    }else{
      //need to create custom 404 page. echoing response for now
      echo "The file no longer exists on the server!";
    }

  }

