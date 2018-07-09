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
	require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');

	$widget = $_POST['widget'];
	$status = $_POST['status'];

	//Check to see if profile record exists
	include "../../core/abre_dbconnect.php";
	$query = "SELECT COUNT(*) FROM profiles WHERE email = '".$_SESSION['useremail']."'";
	$results = $db->query($query);
	$resultrow = $results->fetch_assoc();
	$records = $resultrow["COUNT(*)"];

	if($records==0){
		$stmt = $db->stmt_init();
		$sql = "INSERT INTO profiles (email, startup) VALUES (?, '0')";
		$stmt->prepare($sql);
		$stmt->bind_param("s", $_SESSION['useremail']);
		$stmt->execute();
		$stmt->close();
	}
	else
	{
		$sql = "SELECT widgets_open FROM profiles WHERE email = '".$_SESSION['useremail']."'";
		$result = $db->query($sql);
		while($row = $result->fetch_assoc()) {
			$widgets_open = htmlspecialchars($row["widgets_open"], ENT_QUOTES);
		}
	}

	//Check if widget already saved as open
	if($status == "false"){
		$OpenWidgets = explode(',',$widgets_open);
		if(!in_array($widget, $OpenWidgets)){

			if($widgets_open!=""){
				$widgets_open = $widgets_open . ",$widget";
			}
			else
			{
				$widgets_open = $widget;
			}
		}
	}
	else
	{
		$OpenWidgets = explode(',',$widgets_open);
	    while(($i = array_search($widget, $OpenWidgets)) !== false) {
	        unset($OpenWidgets[$i]);
	    }

	    $widgets_open = implode(',', $OpenWidgets);
	}

	$stmt = $db->stmt_init();
	$sql = "UPDATE profiles SET widgets_open = ? WHERE email = ?";
	$stmt->prepare($sql);
	$stmt->bind_param("ss", $widgets_open, $_SESSION['useremail']);
	$stmt->execute();
	$stmt->close();
	$db->close();

?>