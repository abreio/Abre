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

	if($booksadmin==1)
	{

		$id=mysqli_real_escape_string($db, $_GET["id"]);

		function Delete($path)
		{
		    if (is_dir($path) === true)
		    {
		        $files = array_diff(scandir($path), array('.', '..'));

		        foreach ($files as $file)
		        {
		            Delete(realpath($path) . '/' . $file);
		        }

		        return rmdir($path);
		    }

		    else if (is_file($path) === true)
		    {
		        return unlink($path);
		    }

		    return false;
		}

		//Delete Book
		include "../../core/abre_dbconnect.php";
		$sql = "SELECT Slug FROM books WHERE ID=$id";
		$result = $db->query($sql);
		while($row = $result->fetch_assoc())
		{
			$Slug=htmlspecialchars($row["Slug"], ENT_QUOTES);
			if($Slug!="")
			{
				if ($cloudsetting=="true") {
					$storage = new StorageClient([
						'projectId' => constant("GC_PROJECT")
					]);	
					$bucket = $storage->bucket(constant("GC_BUCKET"));			
					//Delete epub
					$cloud_book = "private_html/books/books/" . $Slug . '.epub';
					$object = $bucket->object($cloud_book);
					$object->delete();

					//Delete png
					$cloud_book = "private_html/books/books/" . $Slug . '.png';
					$object = $bucket->object($cloud_book);
					$object->delete();
				}
				else {			
					//Delete epub
					$oldfile = dirname(__FILE__) . "/../../../$portal_private_root/books/" . $Slug . '.epub';
					unlink($oldfile);

					//Delete png
					$oldfile = dirname(__FILE__) . "/../../../$portal_private_root/books/" . $Slug . '.png';
					unlink($oldfile);

					//Delete extracted epub folder
					$oldfile = dirname(__FILE__) . "/../../../$portal_private_root/books/" . $Slug;
					Delete($oldfile );

					//Delete extracted epub folder public
					$oldfile = "$portal_path_root/modules/Abre-Books/books/$Slug";
					Delete($oldfile );
				}
			}
		}

		//Remove from Libraries
		include "../../core/abre_dbconnect.php";
		$stmt = $db->stmt_init();
		$sql = "Delete from books_libraries where Book_ID='$id'";
		$stmt->prepare($sql);
		$stmt->execute();
		$stmt->close();
		$db->close();

		//Remove from Inventory
		include "../../core/abre_dbconnect.php";
		$stmt = $db->stmt_init();
		$sql = "Delete from books where id='$id' LIMIT 1";
		$stmt->prepare($sql);
		$stmt->execute();
		$stmt->close();
		$db->close();

		echo "The book has been removed from inventory.";
	}

?>