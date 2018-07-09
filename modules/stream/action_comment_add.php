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

	if($_SESSION['usertype'] == 'staff'){
		$streamUrl = $_POST["streamUrl"];
		$streamUrldecoded = base64_decode($streamUrl);
		$streamComment = $_POST["streamComment"];
		$streamTitleValue = $_POST["streamTitleValue"];
		$streamComment = htmlspecialchars($streamComment, ENT_QUOTES);
		$streamImage = $_POST["streamImage"];
		$streamImagedecoded = base64_decode($streamImage);
		$streamExcerpt = $_POST["streamExcerpt"];

		$portal_root_path = $portal_root.'/';
		$trimmedimageurl = str_replace($portal_root_path, '', $streamImagedecoded);

		$userposter = $_SESSION['useremail'];

		if($streamComment != "" && $streamTitleValue != ""){
			$stmt = $db->stmt_init();
			$sql = "INSERT INTO streams_comments (url, title, image, user, comment, excerpt) VALUES (?, ?, ?, ?, ?, ?);";
			$stmt->prepare($sql);
			$stmt->bind_param("ssssss", $streamUrldecoded, $streamTitleValue, $trimmedimageurl, $userposter, $streamComment, $streamExcerpt);
			$stmt->execute();
			$stmt->close();
			$db->close();
		}
		$streamUrldecoded = base64_encode($streamUrldecoded);
		echo $streamUrldecoded;
	}
?>