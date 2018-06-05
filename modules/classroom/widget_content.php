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

	if($_SESSION['usertype'] != 'parent'){

		try{

			//Set Access Token
			if(isset($_SESSION['access_token']) && $_SESSION['access_token']){ $client->setAccessToken($_SESSION['access_token']); }

			//Set Classroom Parameters
			if($_SESSION['usertype'] == "staff"){
				$optParams = array('pageSize' => 7, 'teacherId' => 'me');
			}
			if($_SESSION['usertype'] == "student"){
				$optParams = array('pageSize' => 7, 'studentId' => 'me');
			}

			//Request Classroom Files
			$results = $Service_Classroom->courses->listCourses($optParams);

			if (count($results->getCourses()) != 0){

				$counter=0;
				foreach ($results->getCourses() as $course){

					$counter++;
					$courseName = $course->getName();
					$courseSection = $course->getSection();
					$courseId = $course->getId();
					$courseEnrollmentCode = $course->getenrollmentCode();
					$courseAlternateLink = $course->getalternateLink();
					$courseCourseState = $course->getcourseState();
					if($courseCourseState == "ACTIVE"){

						if($counter==1){
							echo "<hr class='widget_hr'>";
							echo "<div class='widget_holder'>";
								echo "<div class='widget_container widget_body' style='color:#666;'>Your Google Classrooms <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/classroom/widget_content.php' data-reload='true'>refresh</i></div>";
							echo "</div>";
						}

						echo "<hr class='widget_hr'>";
						echo "<div class='widget_holder widget_holder_link pointer' data-link='$courseAlternateLink' data-newtab='true' data-path='/modules/classroom/widget_content.php' data-reload='false'>";
							echo "<div class='widget_container widget_heading_h1 truncate'>$courseName</div>";
							if($courseSection != ""){ echo "<div class='widget_container widget_heading_h2 truncate'>$courseSection</div>"; }
							echo "<div class='widget_container widget_body truncate'>$courseEnrollmentCode</div>";
						echo "</div>";

					}

				}
			}


		}catch(Exception $e){

			echo "<hr class='widget_hr'><div class='widget_holder'><div class='widget_container widget_body' style='color:#666;'>No Google Classroom Courses <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/classroom/widget_content.php' data-reload='true'>refresh</i></div></div>";

		}

	}

?>