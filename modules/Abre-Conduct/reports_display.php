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
	require_once('functions.php');

	if(admin() or conductAdminCheck($_SESSION['useremail']) or conductMonitor($_SESSION['useremail'])){

		if(admin()){
			$schoolCodes = getAllSchoolCodes();
			$buildingfilter = "";
			foreach($schoolCodes as $code){
				$buildingfilter = $buildingfilter."SchoolCode = '$code' OR ";
			}
			$buildingfilter = rtrim($buildingfilter, " OR ");
		}else{
			//Filter admin results by access buildings
			$query = "SELECT SchoolCode FROM Abre_Staff where EMail1 = '".$_SESSION['useremail']."'";
			$dbreturn = databasequery($query);
			$buildingfilter = "SchoolCode = ''";
			foreach ($dbreturn as $value){
				$AdminSchoolCode = htmlspecialchars($value["SchoolCode"], ENT_QUOTES);
				$buildingfilter = "$buildingfilter OR SchoolCode = '$AdminSchoolCode'";
			}
		}

		if(isset($_POST["conductsearch"])){ $ConductSearch = mysqli_real_escape_string($db, $_POST["conductsearch"]); }else{ $ConductSearch = ""; }
		if(isset($_POST["reportdate"])){ $ReportDate = mysqli_real_escape_string($db, $_POST["reportdate"]); }else{ $ReportDate = ""; }
		if(isset($_POST["page"])){ $PageNumber = $_POST["page"]; if($PageNumber == ""){ $PageNumber = 1; } }else{ $PageNumber = 1; }
		if(isset($_POST["sort"]) && $_POST["sort"] != ""){ $PageSort = $_POST["sort"]; }else{ $PageSort = "Submission_Time"; }
		if(isset($_POST["method"])){ $SortMethod = $_POST["method"]; }else{ $SortMethod = "ASC"; }
		if($SortMethod == "DESC"){ $SortMethod = "ASC"; }else{ $SortMethod = "DESC"; }

		$PerPage = 20;

		$LowerBound = $PerPage * ($PageNumber - 1);
		$UpperBound = $PerPage * $PageNumber;

		//Separate Date Picker
		if($ReportDate != ""){
			$ReportDateQuery = "AND ('$ReportDate' >= conduct_discipline_consequences.Serve_Date AND '$ReportDate' <= conduct_discipline_consequences.Thru_Date)";
		}
		if($ReportDate != ""){

			$querycount = "SELECT count(*) FROM conduct_discipline LEFT JOIN conduct_discipline_consequences ON conduct_discipline.ID = conduct_discipline_consequences.Incident_ID WHERE (conduct_discipline.Type = 'Office' AND ($buildingfilter) AND (conduct_discipline_consequences.Consequence_Name LIKE '%$ConductSearch%') $ReportDateQuery AND conduct_discipline.Archived = '0')";

			$possibleresult = mysqli_query($db, $querycount);
			$count = mysqli_fetch_array($possibleresult);
			$totalpossibleresults = $count[0];

			$query = "SELECT * FROM conduct_discipline LEFT JOIN conduct_discipline_consequences ON conduct_discipline.ID = conduct_discipline_consequences.Incident_ID WHERE (conduct_discipline.Type = 'Office' AND ($buildingfilter)) AND (conduct_discipline_consequences.Consequence_Name LIKE '%$ConductSearch%') $ReportDateQuery AND conduct_discipline.Archived = '0' ORDER BY conduct_discipline.Student_LastName, conduct_discipline.Student_FirstName LIMIT $LowerBound, $PerPage";
			$reportQuery = "SELECT * FROM conduct_discipline LEFT JOIN conduct_discipline_consequences ON conduct_discipline.ID = conduct_discipline_consequences.Incident_ID WHERE (conduct_discipline.Type = 'Office' AND ($buildingfilter)) AND (conduct_discipline_consequences.Consequence_Name LIKE '%$ConductSearch%') $ReportDateQuery AND conduct_discipline.Archived = '0' ORDER BY conduct_discipline.Student_LastName, conduct_discipline.Student_FirstName";
		}
		if($ReportDate == "")
		{
			$querycount = "SELECT count(*) FROM conduct_discipline LEFT JOIN conduct_discipline_consequences ON conduct_discipline.ID = conduct_discipline_consequences.Incident_ID WHERE (conduct_discipline.Type = 'Office' AND ($buildingfilter)) AND (conduct_discipline_consequences.Consequence_Name LIKE '%$ConductSearch%') AND conduct_discipline.Archived = '0'";

			$possibleresult = mysqli_query($db, $querycount);
			$count = mysqli_fetch_array($possibleresult);
			$totalpossibleresults = $count[0];

			$query = "SELECT * FROM conduct_discipline LEFT JOIN conduct_discipline_consequences ON conduct_discipline.ID = conduct_discipline_consequences.Incident_ID WHERE (conduct_discipline.Type = 'Office' AND ($buildingfilter)) AND (conduct_discipline_consequences.Consequence_Name LIKE '%$ConductSearch%') AND conduct_discipline.Archived = '0' ORDER BY conduct_discipline.Student_LastName, conduct_discipline.Student_FirstName LIMIT $LowerBound, $PerPage";
			$reportQuery = "SELECT * FROM conduct_discipline LEFT JOIN conduct_discipline_consequences ON conduct_discipline.ID = conduct_discipline_consequences.Incident_ID WHERE (conduct_discipline.Type = 'Office' AND ($buildingfilter)) AND (conduct_discipline_consequences.Consequence_Name LIKE '%$ConductSearch%') AND conduct_discipline.Archived = '0' ORDER BY conduct_discipline.Student_LastName, conduct_discipline.Student_FirstName";
		}

		$dbreturn = databasequery($query);
		$totalresults=count($dbreturn);
		$resultcounter=0;
		foreach ($dbreturn as $value){

			if($resultcounter==0){
				echo "<div class='page_container mdl-shadow--4dp'>";
				echo "<div class='page'>";
				echo "<div class='row'><div class='col s12'>";

				echo "<table id='myTable' class='tablesorter striped'>";
				echo "<thead>";
				echo "<tr>";
				echo "<th>Student</th>";
				echo "<th>Start Date</th>";
				echo "<th>End Date</th>";
				echo "<th style='text-align:center'>Served</th>";
				echo "</tr>";
				echo "</thead>";
				echo "<tbody>";
			}

			$SubmissionID = htmlspecialchars($value["ID"], ENT_QUOTES);
			$Consequence_ID  =  htmlspecialchars($value["Consequence_ID"], ENT_QUOTES);
			$Submission_Time = htmlspecialchars($value["Submission_Time"], ENT_QUOTES);
			$Student_FirstName = stripslashes($value["Student_FirstName"]);
			$Student_LastName = stripslashes($value["Student_LastName"]);
			$Incident_Date = htmlspecialchars($value["Incident_Date"], ENT_QUOTES);
			$Incident_Time = htmlspecialchars($value["Incident_Time"], ENT_QUOTES);
			$Type = htmlspecialchars($value["Type"], ENT_QUOTES);
			$StudentID = htmlspecialchars($value["StudentID"], ENT_QUOTES);
			$StudentIEP = htmlspecialchars($value["Student_IEP"], ENT_QUOTES);
			$Building = htmlspecialchars($value["Building"], ENT_QUOTES);
			$SchoolCode = htmlspecialchars($value["SchoolCode"], ENT_QUOTES);
			$Served = htmlspecialchars($value["Consequence_Served"], ENT_QUOTES);
			$Type = htmlspecialchars($value["Type"], ENT_QUOTES);
			$Offence = htmlspecialchars($value["Offence"], ENT_QUOTES);
			$Location = htmlspecialchars($value["Location"], ENT_QUOTES);
			$Description = htmlspecialchars($value["Description"], ENT_QUOTES);
			$Information = htmlspecialchars($value["Information"], ENT_QUOTES);
			$Owner_Name = stripslashes($value["Owner_Name"]);
			$Serve_Date = htmlspecialchars($value["Serve_Date"], ENT_QUOTES);
			$Thru_Date = htmlspecialchars($value["Thru_Date"], ENT_QUOTES);
			$Total_Days = htmlspecialchars($value["Total_Days"], ENT_QUOTES);
			$Consequence_Name = htmlspecialchars($value["Consequence_Name"], ENT_QUOTES);
			$Submission_Time_Date = date( "M jS, Y", strtotime($Submission_Time));
			$Submission_Time_Time = date( "g:iA", strtotime($Submission_Time));
			$Submission_Time = "$Submission_Time_Date at $Submission_Time_Time";

			echo "<tr class='incidentdetails' data-reload='closed'>";
			echo "<td><b>$Student_LastName, $Student_FirstName</b><br>$Consequence_Name</td>";
			echo "<td><input type='date' data-submissionid='$SubmissionID' data-consequenceid='$Consequence_ID' data-column='Serve_Date' data-type='input' placeholder='Start Date' class='datepickerformatted savecell' name='ServeDate' id='ServeDate' value='$Serve_Date'></td>";
			echo "<td><input type='date' data-submissionid='$SubmissionID' data-consequenceid='$Consequence_ID' data-column='Thru_Date' data-type='input' placeholder='End Date' class='datepickerformatted savecell' name='ThruDate' id='ThruDate' value='$Thru_Date'></td>";
			echo "<td style='text-align:center'><input type='checkbox' data-submissionid='$SubmissionID' data-consequenceid='$Consequence_ID' data-column='Consequence_Served' data-type='checkbox' class='filled-in savecell' id='conductserved_$Consequence_ID' name='conductserved_$Consequence_ID' value='$Served' ";
				if($Served == 1)echo "checked='checked'";
			echo " /><label for='conductserved_$Consequence_ID'></label></td>";
			echo "</tr>";

			$resultcounter++;
			if($totalresults == $resultcounter){
				echo "</tbody>";
				echo "</table>";
				echo "</div>";
				echo "</div>";

				//Paging
				$totalpages = ceil($totalpossibleresults / $PerPage);

				if($totalpossibleresults>$PerPage){
					$previouspage = $PageNumber-1;
					$nextpage = $PageNumber+1;

					if($PageNumber > 5){
						if($totalpages > $PageNumber + 5){
							$pagingstart = $PageNumber - 5;
							$pagingend = $PageNumber + 5;
						}else{
							$pagingstart = $PageNumber - 5;
							$pagingend = $totalpages;
						}
					}else{
						if($totalpages >= 10){ $pagingstart = 1; $pagingend = 10; }else{ $pagingstart = 1; $pagingend = $totalpages; }
					}

					echo "<div class='row'><br>";
					echo "<ul class='pagination center-align'>";
						if($PageNumber != 1){ echo "<li class='pagebuttonreports' data-page='$previouspage' data-sort='$PageSort' data-sortmethod='$SortMethod'><a href='#'><i class='material-icons'>chevron_left</i></a></li>"; }
						for ($x = $pagingstart; $x <= $pagingend; $x++){
							if($PageNumber == $x){
								echo "<li class='active pagebuttonreports' style='background-color: ".getSiteColor().";' data-page='$x'><a href='#'>$x</a></li>";
							}else{
								echo "<li class='waves-effect pagebuttonreports' data-page='$x' data-sort='$PageSort' data-sortmethod='$SortMethod'><a href='#'>$x</a></li>";
							}
						}
						if($PageNumber != $totalpages){ echo "<li class='waves-effect pagebuttonreports' data-page='$nextpage' data-sort='$PageSort' data-sortmethod='$SortMethod'><a href='#'><i class='material-icons'>chevron_right</i></a></li>"; }
					echo "</ul>";
					echo "</div>";
				}
				echo "</div>";
			}
		}
		if($totalresults == 0){
			echo "<div style='padding:56px; text-align:center; width:100%;'><span style='font-size: 22px; font-weight:700'>No Students Found</span><br><p style='font-size:16px; margin:20px 0 0 0;'>Click the '+' in the bottom right to create an incident.</p></div>";
		}

		require "button_export.php";
}
?>

<script>

	$(function(){
		//Update Material
		$('select').material_select();
		Materialize.updateTextFields();

		//Start Date Picker
		$('.datepickerformatted').pickadate({ container: 'body', format: 'yyyy-mm-dd', selectMonths: true, selectYears: 15 });

		//Detect Change to Start and End Dates and autosave
		$(".savecell").change(function(){
			var SubmissionID = $(this).data('submissionid');
			var ConsequenceID = $(this).data('consequenceid');
			var Column = $(this).data('column');
			var Type = $(this).data('type');
			var Value = $(this).val();
			if(Type == "checkbox"){ if ($(this).is(':checked')) { Value = "1"; }else{ Value = "0"; } }

			//Make a POST Request
			$.post( "/modules/Abre-Conduct/reports_savechanges.php",
			{ SubmissionID: SubmissionID, ConsequenceID: ConsequenceID, Column: Column, Value: Value })
			.done(function(data) {
				mdlregister();
				var notification = document.querySelector('.mdl-js-snackbar');
				var data = { message: "Changes Saved" };
				notification.MaterialSnackbar.showSnackbar(data);
			});
		});
	});

</script>