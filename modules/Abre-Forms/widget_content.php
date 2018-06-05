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
	require_once(dirname(__FILE__) . '/../../core/abre_google_login.php');
  require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

	echo "<hr class='widget_hr'>";
	echo "<div class='widget_holder'>";
		echo "<div class='widget_container widget_body' style='color:#666;'>Recently Recommended Forms<i class='right material-icons widget_holder_refresh pointer' data-path='/modules/Abre-Forms/widget_content.php' data-reload='true'>refresh</i></div>";
	echo "</div>";

  $usertype = $_SESSION["usertype"];

  $sql = "SELECT ID, LastModified, Name, Settings FROM forms WHERE Public = '1' AND (Restrictions = '' OR Restrictions LIKE '%$usertype%') ORDER BY ID";
  $result = $db->query($sql);
  $displayCount = 0;
  while($returnrow = $result->fetch_assoc()){
    if($displayCount == 3){
      break;
    }
    $ID = $returnrow['ID'];
    $lastModified = $returnrow['LastModified'];
    $name = $returnrow['Name'];
    $settingsJSON = $returnrow['Settings'];
    $settings = json_decode($settingsJSON, true);

    $displayDate = date("m/d", strtotime($lastModified))." at ".date("g:i A", strtotime($lastModified));

    $limited = $settings['limit'];

    $sql = "SELECT COUNT(*) FROM forms_responses WHERE Submitter = '".$_SESSION['useremail']."' AND FormID = '$ID'";
    $query = $db->query($sql);
    $return = $query->fetch_assoc();
    $count = $return['COUNT(*)'];
    if($count == 0 || $limited == ""){
      echo "<hr class='widget_hr'>";
        echo "<div class='widget_holder widget_holder_link pointer' data-link='#forms/view/$ID' data-newtab='false' data-path='/modules/Abre-Forms/widget_content.php' data-reload='false'>";
        echo "<div class='widget_container widget_heading_h1 truncate'>$name</div>";
        echo "<div class='widget_container widget_body truncate'>Last updated on $displayDate</div>";
      echo "</div>";
      $displayCount++;
    }
  }
	$db->close();

?>