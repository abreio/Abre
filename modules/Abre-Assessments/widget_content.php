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

	if($_SESSION['usertype'] == 'staff'){

		echo "<hr class='widget_hr'>";
		echo "<div class='widget_holder'>";
			echo "<div class='widget_container widget_body' style='color:#666;'>Your Recent Assessments<i class='right material-icons widget_holder_refresh pointer' data-path='/modules/Abre-Assessments/widget_content.php' data-reload='true'>refresh</i></div>";
		echo "</div>";

    $sql = "SELECT ID, Title, Subject, Level FROM assessments WHERE Owner = '".$_SESSION['useremail']."' ORDER BY ID DESC LIMIT 3";
    $result = $db->query($sql);
    while($returnrow = $result->fetch_assoc()){
      $ID = $returnrow['ID'];
      $title = $returnrow['Title'];
      $subject = $returnrow['Subject'];
      $level = $returnrow['Level'];

      $description = $subject." - ".$level;

      echo "<hr class='widget_hr'>";
        echo "<div class='widget_holder widget_holder_link pointer' data-link='#assessments/$ID' data-newtab='false' data-path='/modules/Abre-Assessments/widget_content.php' data-reload='false'>";
        echo "<div class='widget_container widget_heading_h1 truncate'>$title</div>";
        echo "<div class='widget_container widget_body truncate'>$description</div>";
      echo "</div>";
			
    }
		$db->close();
	}

?>