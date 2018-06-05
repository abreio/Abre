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
      echo "<div class='widget_container widget_body' style='color:#666;'>Your Recent Courses<i class='right material-icons widget_holder_refresh pointer' data-path='/modules/Abre-Curriculum/widget_content.php' data-reload='true'>refresh</i></div>";
    echo "</div>";

    $id = finduseridcore($_SESSION['useremail']);
    $sql = "SELECT curriculum_course.ID, Title, Subject, Grade, Image FROM curriculum_libraries LEFT JOIN curriculum_course ON curriculum_libraries.Course_ID = curriculum_course.ID WHERE User_ID = '$id' AND Hidden = '0' ORDER BY curriculum_libraries.Course_ID DESC LIMIT 3";
    $query = $db->query($sql);
    while($result = $query->fetch_assoc()){
      $ID = $result['ID'];
      $title = $result['Title'];
      $subject = $result['Subject'];
      $grade = $result['Grade'];
      $image = $result['Image'];
      if($image == ""){ $image = "course.jpg"; }

      $description = $subject." - Grade ".$grade;


      echo "<hr class='widget_hr'>";
      echo "<div class='widget_holder widget_holder_link pointer' data-link='#curriculum/0/$ID' data-newtab='false' data-path='/modules/Abre-Curriculum/widget_content.php' data-reload='false'>";
        echo "<div style='float: left;'>";
        	echo "<image class='btn-floating btn-flat' src='modules/Abre-Curriculum/images/$image' style='object-fit: cover; cursor:default; left:10px; top:5px;'>";
        echo "</div>";
        echo "<div>";
          echo "<div class='widget_container widget_heading_h1 truncate'>$title</div>";
          echo "<div class='widget_container widget_body truncate'>$description</div>";
        echo "</div>";
      echo "</div>";
    }
    $db->close();
  }

?>