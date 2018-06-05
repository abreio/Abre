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

	$img = $_GET['file'];
	$fileextention = substr($img, -4);

	header('Pragma: public');
	header('Cache-Control: max-age=31536000');
	header("Expires: Mon, 1 Jan 2099 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	if($fileextention == '.jpg' or $fileextention == '.JPG'){ header('Content-Type: image/jpeg'); }
	if($fileextention == '.png' or $fileextention == '.PNG'){ header('Content-Type: image/png'); }
	if($fileextention == '.gif' or $fileextention == '.GIF'){ header('Content-Type: image/gif'); }
	if($fileextention == '.tif' or $fileextention == '.TIF'){ header('Content-Type: image/tif'); }
	if($fileextention == '.bmp' or $fileextention == '.BMP'){ header('Content-Type: image/bmp'); }
	if ($cloudsetting=="true") {
		$storage = new StorageClient([
			'projectId' => constant("GC_PROJECT")
		]);	
		$bucket = $storage->bucket(constant("GC_BUCKET"));
		$cloud_book = "private_html/directory/images/employees/$img";
		$object = $bucket->object($cloud_book);
		$stream = $object->downloadAsStream();
		$img = $stream->getContents(); 
	}
	else {
		$img = $portal_path_root."/../$portal_private_root/directory/images/employees/".$img;
		$img = file_get_contents($img);
	}
	echo($img);
	exit();

?>