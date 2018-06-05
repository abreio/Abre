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

	if(admin()){

		$id = $_GET["id"];

		//Delete Picture
		include "../../core/abre_dbconnect.php";
		$sql = "SELECT picture FROM directory where id = $id";
		$result = $db->query($sql);
		while($row = $result->fetch_assoc()){
			$oldpicture = htmlspecialchars($row["picture"], ENT_QUOTES);
			if($oldpicture != ""){
				if ($cloudsetting=="true") {
					$storage = new StorageClient([
						'projectId' => constant("GC_PROJECT")
					]);	
					$bucket = $storage->bucket(constant("GC_BUCKET"));			

					$cloud_dir = "private_html/directory/images/employees/" . $oldpicture;
					$object = $bucket->object($cloud_dir);
					$object->delete();
				}
				else {
					$oldfile = dirname(__FILE__) . "/../../../$portal_private_root/directory/images/employees/" . $oldpicture;
					unlink($oldfile);	
				}
			}
		}
		$db->close();

		//Delete Discipline Files
		include "../../core/abre_dbconnect.php";
		$sql = "SELECT Filename FROM directory_discipline where UserID = $id";
		$result = $db->query($sql);
		while($row = $result->fetch_assoc()){
			$filename = htmlspecialchars($row["Filename"], ENT_QUOTES);
			if($filename != ""){
				if ($cloudsetting=="true") {
					$storage = new StorageClient([
						'projectId' => constant("GC_PROJECT")
					]);	
					$bucket = $storage->bucket(constant("GC_BUCKET"));			

					$cloud_dir = "private_html/directory/discipline/" . $filename;
					$object = $bucket->object($cloud_dir);
					$object->delete();
				}
				else {
					$filename = dirname(__FILE__) . "/../../../$portal_private_root/directory/discipline/" . $filename;
					unlink($filename);	
				}
			}
		}
		$db->close();

		//Delete the Records
		include "../../core/abre_dbconnect.php";
		$stmtrecord = $db->prepare("DELETE from directory_discipline where UserID = ?");
		$stmtrecord->bind_param("i",$id);
		$stmtrecord->execute();
		$stmtrecord->close();
		$db->close();

		//Remove from Database
		include "../../core/abre_dbconnect.php";
		$stmt = $db->stmt_init();
		$sql = "DELETE FROM directory WHERE id = ? LIMIT 1";
		$stmt->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->close();
		$db->close();

		echo "Employee Deleted!";
	}
?>