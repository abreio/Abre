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
  require_once(dirname(__FILE__) . '/../../modules/Abre-Conduct/functions.php');
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');
  require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

	if($_SESSION['usertype'] == 'staff'){
    if(admin() || conductAdminCheck($_SESSION['useremail'])){
			if(admin()){
				$schoolCodes = getAllSchoolCodes();
				$buildingfilter = "";
				foreach($schoolCodes as $code){
					$buildingfilter = $buildingfilter."SchoolCode = '$code' OR ";
				}
				$buildingfilter = rtrim($buildingfilter, " OR ");
			}else{
				//Filter admin results by access buildings
				$query = "SELECT SchoolCode FROM Abre_Staff where EMail1='".$_SESSION['useremail']."'";
				$dbreturn = databasequery($query);
				$buildingfilter = "SchoolCode = ''";
				foreach ($dbreturn as $value){
					$AdminSchoolCode = htmlspecialchars($value["SchoolCode"], ENT_QUOTES);
					$buildingfilter = "$buildingfilter OR SchoolCode = '$AdminSchoolCode'";
				}
			}

			$querycount = "SELECT * FROM conduct_discipline LEFT JOIN conduct_discipline_consequences ON conduct_discipline.ID = conduct_discipline_consequences.Incident_ID WHERE (conduct_discipline.Served = '0' AND conduct_discipline_consequences.Incident_ID IS NULL AND conduct_discipline.type != 'Personal' AND ($buildingfilter)) AND conduct_discipline.Archived = '0' GROUP BY conduct_discipline.ID";
			$query = "SELECT Submission_Time, Student_FirstName, Student_LastName, Offence, Location FROM conduct_discipline LEFT JOIN conduct_discipline_consequences ON conduct_discipline.ID = conduct_discipline_consequences.Incident_ID WHERE (conduct_discipline.Served = '0' AND conduct_discipline_consequences.Incident_ID IS NULL AND conduct_discipline.type != 'Personal' AND ($buildingfilter)) AND conduct_discipline.Archived = '0' GROUP BY conduct_discipline.ID ORDER BY Submission_Time DESC LIMIT 3";

			$dbreturn = databasequery($query);
			$dbreturnpossible = databasequery($querycount);
			$totalpossibleresults = count($dbreturnpossible);

			echo "<hr class='widget_hr'>";
			echo "<div class='widget_holder'>";
				echo "<div class='widget_container widget_body' style='color:#666;'>$totalpossibleresults Queued Incidents<i class='right material-icons widget_holder_refresh pointer' data-path='/modules/Abre-Conduct/widget_content.php' data-reload='true'>refresh</i></div>";
			echo "</div>";

			foreach($dbreturn as $value){
				$studentName = $value['Student_FirstName']." ".$value["Student_LastName"];
				$submissionTime = $value['Submission_Time'];
				$offence = str_replace('"', "", $value['Offence']);
				$location = $value['Location'];

				$displayTime = date("m/d", strtotime($submissionTime))." at ".date("g:i A", strtotime($submissionTime));
				$description = "Submitted on $displayTime";

				echo "<hr class='widget_hr'>";
				echo "<div class='widget_holder widget_holder_link pointer' data-link='#conduct/discipline/queue' data-newtab='false' data-path='/modules/Abre-Conduct/widget_content.php' data-reload='false'>";
					echo "<div class='widget_container widget_heading_h1 truncate'>$studentName - $offence</div>";
					echo "<div class='widget_container widget_body truncate'>$description</div>";
				echo "</div>";
			}

    }else{
			$query = "SELECT * FROM conduct_discipline LEFT JOIN conduct_discipline_consequences ON conduct_discipline.ID = conduct_discipline_consequences.Incident_ID WHERE (conduct_discipline.Type = 'Personal' AND conduct_discipline.Owner = '".$_SESSION['useremail']."' AND conduct_discipline.Served = '0') AND conduct_discipline.Archived = '0' GROUP BY conduct_discipline.ID ORDER BY Submission_Time DESC LIMIT 3";

			$dbreturn = databasequery($query);

			echo "<hr class='widget_hr'>";
			echo "<div class='widget_holder'>";
				echo "<div class='widget_container widget_body' style='color:#666;'>Recent Personal Incidents<i class='right material-icons widget_holder_refresh pointer' data-path='/modules/Abre-Conduct/widget_content.php' data-reload='true'>refresh</i></div>";
			echo "</div>";

			foreach($dbreturn as $value){
				$studentName = $value['Student_FirstName']." ".$value["Student_LastName"];
				$submissionTime = $value['Submission_Time'];
				$offence = str_replace('"', "", $value['Offence']);
				$location = $value['Location'];

				$displayTime = date("m/d", strtotime($submissionTime))." at ".date("g:i A", strtotime($submissionTime));
				$description = "Submitted on $displayTime";

				echo "<hr class='widget_hr'>";
				echo "<div class='widget_holder widget_holder_link pointer' data-link='#conduct/discipline/open' data-newtab='false' data-path='/modules/Abre-Conduct/widget_content.php' data-reload='false'>";
					echo "<div class='widget_container widget_heading_h1 truncate'>$studentName - $offence</div>";
					echo "<div class='widget_container widget_body truncate'>$description</div>";
				echo "</div>";
			}
		}
	}

?>