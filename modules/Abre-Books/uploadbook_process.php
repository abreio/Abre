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
	require_once(dirname(__FILE__) . '/../../core/abre_verification.php');
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');
	require_once(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

	$cloudsetting=constant("USE_GOOGLE_CLOUD");
	if ($cloudsetting=="true") 
		require(dirname(__FILE__). '/../../vendor/autoload.php');
	use Google\Cloud\Storage\StorageClient;

	//Add the book to user library
	$booktitle=mysqli_real_escape_string($db, $_POST["booktitle"]);
	$bookauthor=mysqli_real_escape_string($db, $_POST["bookauthor"]);
	$booklicencelimit=mysqli_real_escape_string($db, $_POST["booklicencelimit"]);
	if($booklicencelimit==""){ $booklicencelimit=NULL; }
	if(isset($_POST["bookstaffrequired"])){ $bookstaffrequired=mysqli_real_escape_string($db, $_POST["bookstaffrequired"]); }else{ $bookstaffrequired=""; }
	if(isset($_POST["bookstudentrequired"])){ $bookstudentrequired=mysqli_real_escape_string($db, $_POST["bookstudentrequired"]); }else{ $bookstudentrequired=""; }

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

	if($_FILES['bookfile']['name'] && $booktitle!="" && $bookauthor!="")
	{
		//Upload the ePUB
		$validExtensions = array('.epub', '.ePub', '.EPUB');
		$fileExtension = strrchr($_FILES['bookfile']['name'], ".");
		if (in_array($fileExtension, $validExtensions))
		{
				//Get file information
				$file=$_FILES['bookfile']['name'];
				$fileextention=pathinfo($file, PATHINFO_EXTENSION);
				$cleanfilename=basename($file);
				$uniquetime=time();
				$sitelogofilename = $uniquetime . ".epub";

				//Upload new image
				if ($cloudsetting=="true") {
					$storage = new StorageClient([
						'projectId' => constant("GC_PROJECT")
					]);	
					$bucket = $storage->bucket(constant("GC_BUCKET"));
			
					$uploaddir = "private_html/books/" . $image_file_name;
					$sitelogo = $uploaddir . $sitelogofilename;

					$options = [
						'resumable' => true,
						'name' => $sitelogo,
						'metadata' => [
							'contentLanguage' => 'en'
						]
					];
					$upload = $bucket->upload(
						fopen($_FILES['bookfile']['tmp_name'], 'r'),
						$options
					);
				}
				else {
					$uploaddir = $portal_path_root."/../$portal_private_root/books/";

					$sitelogo = $uploaddir . $sitelogofilename;
					move_uploaded_file($_FILES['bookfile']['tmp_name'], $sitelogo);
				}
		}

		if ($cloudsetting!="true") {
			//Make a Copy of ePUB,extract,delete
			copy("$portal_path_root/../$portal_private_root/books/$sitelogofilename", "$portal_path_root/content/books/$sitelogofilename");
			$zip = new ZipArchive;
			$res = $zip->open("$portal_path_root/content/books/$sitelogofilename");
			if($res === TRUE) {
				$zip->extractTo("$portal_path_root/content/books/$uniquetime");
				$zip->close();
			}
			unlink("$portal_path_root/content/books/$sitelogofilename");

			//Unzip the file
			$zip = new ZipArchive;
			$res = $zip->open($portal_path_root."/../$portal_private_root/books/".$uniquetime.'.epub');
			if($res === TRUE) {
				$zip->extractTo($portal_path_root."/../$portal_private_root/books/".$uniquetime.'/');
				$zip->close();
			}
		}

		//Create coupon code
		$code=substr(time(), -5);

		//PNG File
		$coverimage=$uniquetime.".png";

		mysqli_query($db, "INSERT INTO books (Code,Code_Limit,Title,Author,Slug,Cover,File,Students_Required,Staff_Required) VALUES ('$code','$booklicencelimit','$booktitle','$bookauthor','$uniquetime','$coverimage','$sitelogofilename','$bookstudentrequired','$bookstaffrequired');") or die (mysqli_error($db));
		echo "The book has been uploaded.";

		//Upload the PNG
		$validExtensions = array('.png', '.PNG', '.Png');
		$fileExtension = strrchr($_FILES['bookcover']['name'], ".");
		if(in_array($fileExtension, $validExtensions))
		{
				//Get file information
				$file=$_FILES['bookcover']['name'];
				$fileextention=pathinfo($file, PATHINFO_EXTENSION);
				$cleanfilename=basename($file);
				$sitelogofilename = $uniquetime . ".png";
				if ($cloudsetting=="true") {
					$storage = new StorageClient([
						'projectId' => constant("GC_PROJECT")
					]);	
					$bucket = $storage->bucket(constant("GC_BUCKET"));
				}
				else {
					$uploaddir = $portal_path_root."/../$portal_private_root/books/";

					//Upload new image
					$sitelogo = $uploaddir . $sitelogofilename;
					move_uploaded_file($_FILES['bookcover']['tmp_name'], $sitelogo);

				}
		}    
	}
	else
	{
		echo "Whoops, there was a problem. Please try again.";
	}

?>