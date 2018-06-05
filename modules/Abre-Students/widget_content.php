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
  require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
	require_once(dirname(__FILE__) . '/../../modules/Abre-Students/functions.php');

	if($_SESSION['usertype'] == 'parent'){

    $authStudentArray = explode(",", $_SESSION['auth_students']);

    if(empty($authStudentArray)){
      echo "<hr class='widget_hr'>";
      echo "<div class='widget_holder pointer'>";
        echo "<div class='widget_container widget_body truncate' style='color:#666;'>No Authorized Students<i class='right material-icons widget_holder_refresh pointer' data-path='/modules/Abre-Students/widget_content.php' data-reload='true'>refresh</i></div>";
      echo "</div>";
    }else{
      echo "<hr class='widget_hr'>";
      echo "<div class='widget_holder'>";
        echo "<div class='widget_container widget_body' style='color:#666;'>Your Authorized Students<i class='right material-icons widget_holder_refresh pointer' data-path='/modules/Abre-Students/widget_content.php' data-reload='true'>refresh</i></div>";
      echo "</div>";

      foreach($authStudentArray as $studentID){
        $sql = "SELECT FirstName, LastName FROM Abre_Students WHERE StudentId = '$studentID' LIMIT 1";
        $query = $db->query($sql);
        $row = $query->fetch_assoc();

        $firstName = $row['FirstName'];
        $lastName = $row['LastName'];
        $link = '/#mystudents/'.$studentID;

        echo "<hr class='widget_hr'>";
          echo "<div class='widget_holder widget_holder_link pointer' data-link='$link' data-newtab='false' data-path='/modules/Abre-Students/widget_content.php' data-reload='false'>";
          echo "<div class='widget_container widget_heading_h1 truncate'>$firstName $lastName</div>";
        echo "</div>";
      }
    }
	}elseif($_SESSION['usertype'] == "staff"){

		$StaffId = GetStaffID($_SESSION['useremail']);
		$CurrentSememester = GetCurrentSemester();

		if($db->query("SELECT * FROM Abre_StaffSchedules LIMIT 1")){
			$sql = "SELECT CourseCode, SchoolCode, SectionCode, CourseName, Period FROM Abre_StaffSchedules WHERE StaffID = '$StaffId' AND (TermCode = '$CurrentSememester' OR TermCode = 'Year') ORDER BY Period";
			$query = $db->query($sql);
			$totalcourses = $query->num_rows;

			if($totalcourses == 0){
				echo "<hr class='widget_hr'>";
				echo "<div class='widget_holder pointer'>";
					echo "<div class='widget_container widget_body truncate' style='color:#666;'>You Have No Registered Classes<i class='right material-icons widget_holder_refresh pointer' data-path='/modules/Abre-Students/widget_content.php' data-reload='true'>refresh</i></div>";
				echo "</div>";
			}else{
				if($totalcourses == 1){
					echo "<hr class='widget_hr'>";
					echo "<div class='widget_holder pointer'>";
						echo "<div class='widget_container widget_body truncate' style='color:#666;'>You Teach $totalcourses Class<i class='right material-icons widget_holder_refresh pointer' data-path='/modules/Abre-Students/widget_content.php' data-reload='true'>refresh</i></div>";
					echo "</div>";
				}else{
					echo "<hr class='widget_hr'>";
					echo "<div class='widget_holder pointer'>";
						echo "<div class='widget_container widget_body truncate' style='color:#666;'>You Teach $totalcourses Classes<i class='right material-icons widget_holder_refresh pointer' data-path='/modules/Abre-Students/widget_content.php' data-reload='true'>refresh</i></div>";
					echo "</div>";
				}
			}

			while($result = $query->fetch_assoc()){
				$CourseCode = $result['CourseCode'];
				$SchoolCode = $result['SchoolCode'];
				$SectionCode = $result['SectionCode'];
				$CourseName = $result['CourseName'];
				$Period = $result['Period'];

				$description = "";
				if($db->query("SELECT * FROM Abre_StaffSchedules LIMIT 1")){
					//Get Total Students
					$sql = "SELECT COUNT(DISTINCT StudentID) as count FROM Abre_StudentSchedules WHERE StaffId = '$StaffId' AND CourseCode = '$CourseCode' AND SectionCode = '$SectionCode' AND (TermCode = '$CurrentSememester' OR TermCode = 'Year')";
					$dbreturn = $db->query($sql);
					$resultrow = $dbreturn->fetch_assoc();
					$StudentCount = $resultrow["count"];

					if($StudentCount == 0){
						$description = "0 students are enrolled in this class.";
					}
					if($StudentCount == 1){
						$description = "1 student is enrolled in this class.";
					}
					if($StudentCount > 1){
						$description = "$StudentCount students are enrolled in this class.";
					}
				}

					echo "<hr class='widget_hr'>";
					echo "<div class='widget_holder widget_holder_link pointer' data-link='#students/$CourseCode/$SectionCode' data-newtab='false' data-path='/modules/Abre-Students/widget_content.php' data-reload='false'>";
		        echo "<div style='float: left; padding-left: 10px; padding-top:4px;'>";
		        	echo "<div class='btn-floating btn-flat' style='text-align:center; background-color:".getSiteColor()."; color:#fff;'>$Period</div>";
		        echo "</div>";
		        echo "<div>";
		          echo "<div class='widget_container widget_heading_h1 truncate' style='padding-left: 11px;'>$CourseName</div>";
		          echo "<div class='widget_container widget_body truncate' style='padding-left: 11px;'>$description</div>";
		        echo "</div>";
		      echo "</div>";
			}
		}else{
			echo "<hr class='widget_hr'>";
			echo "<div class='widget_holder pointer'>";
				echo "<div class='widget_container widget_body truncate' style='color:#666;'>You Have No Registered Classes<i class='right material-icons widget_holder_refresh pointer' data-path='/modules/Abre-Students/widget_content.php' data-reload='true'>refresh</i></div>";
			echo "</div>";
		}
	}

	$db->close();

?>