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
	require_once('../../core/abre_functions.php');
	require_once('functions.php');
	require_once('permissions.php');

	if($pagerestrictions=="")
	{

		$token=getCerticaToken();

		?>
		<script src='https://cdn.certicasolutions.com/sdk/js/sdk.itemconnect.min.js?x-ic-credential=<?php echo $token; ?>'></script>
		<script src='https://cdn.certicasolutions.com/player/js/player.itemconnect.min.js'></script>
		<link rel="stylesheet" href='https://cdn.certicasolutions.com/player/css/player.itemconnect.min.css'>
		<?php

		//Include Fixed Table JS
		?><script src='modules/<?php echo basename(__DIR__); ?>/js/tableHeadFixer.js'></script><?php

		//Get Passed Variables
		$Assessment_ID=htmlspecialchars($_GET["assessmentid"], ENT_QUOTES);

		//Look up StaffID and Semester
		if(isset($_GET['staffidpass'])){ $StaffId=$_GET['staffidpass']; }else{ $StaffId=GetStaffID($_SESSION['useremail']); }
		$CurrentSememester=GetCurrentSemester();

		//Check if verified assessment
		$sql = "SELECT COUNT(*) FROM assessments WHERE ID='$Assessment_ID' AND Owner='".$_SESSION['useremail']."'";
		$result = $db->query($sql);
		$returnrow = $result->fetch_assoc();
		$owner = $returnrow["COUNT(*)"];

		//If admin or Admin Make an Owner
		if(admin() || AdminCheck($_SESSION['useremail']) || isAssessmentAdministrator()){ $owner=1; }

		if(isset($_GET["staffid"]))
		{
			$staffcode=$_GET["staffid"];
			$sql = "SELECT COUNT(*) FROM (SELECT * FROM Abre_StudentSchedules WHERE StaffId='$staffcode' AND (TermCode='$CurrentSememester' OR TermCode='Year') GROUP BY StudentID ORDER BY LastName) AS Result";
		}
		$result = $db->query($sql);
		$resultrow = $result->fetch_assoc();
		$rowcount = $resultrow["COUNT(*)"];

		if($rowcount!=0)
		{
			?>
			<div class='mdl-shadow--4dp'>
			<div class='page' style='padding:30px;'>
			<div id='searchresults'>
			<div class='row'><div class='tableholder'>
			<table id='myTableteacherview' class='tablesorter bordered thintable'>
			<thead>
			<tr class='pointer'>
				<th><div style='width:180px;'>Student</div></th>
				<th><div style='width:140px;'>Status</div></th>

				<?php

					$sqlheader = "SELECT Standard, Difficulty, Bank_ID, Type FROM assessments_questions WHERE Assessment_ID='$Assessment_ID'";
					$resultheader = $db->query($sqlheader);
					$questioncount=0;
					$QuestionDetails = array();
					while($row = $resultheader->fetch_assoc())
					{
						$questioncount++;
						$Standard=htmlspecialchars($row["Standard"], ENT_QUOTES);
						$Standard_Text = str_replace("CCSS.Math.Content.","",$Standard);
						$Standard_Text = str_replace("CCSS.ELA-Literacy.","",$Standard_Text);
						$Difficulty=htmlspecialchars($row["Difficulty"], ENT_QUOTES);
						$Bank_ID=htmlspecialchars($row["Bank_ID"], ENT_QUOTES);
						$Type=htmlspecialchars($row["Type"], ENT_QUOTES);
						$QuestionDetails[$Bank_ID] = $Type;

						echo "<th style='min-width:60px;'><div class='center-align' id='standard_$questioncount'>$questioncount</div><div class='mdl-tooltip mdl-tooltip--large' for='standard_$questioncount'>$Standard_Text<br><br>$Difficulty</div></th>";
					}
				?>

				<th style='min-width:50px;'><div class='center-align'>IEP</div></th>
				<th style='min-width:50px;'><div class='center-align'>ELL</div></th>
				<th style='min-width:50px;'><div class='center-align'>Gifted</div></th>
				<th style='min-width:100px;'><div class='center-align'>Auto Points</div></th>
				<th style='min-width:100px;'><div class='center-align'>Rubric Points</div></th>
				<th style='min-width:100px;'><div class='center-align'>Score</div></th>
				<th style='min-width:100px;'><div class='center-align'>Percentage</div></th>
				<th style='max-width:30px;'></th>
			</tr>
			</thead>
			<tbody>

				<?php

					//View By Teacher
					if(isset($staffcode))
					{
						$sql = "SELECT StudentID, StudentIEPStatus, StudentGiftedStatus, StudentELLStatus FROM Abre_StudentSchedules WHERE StaffId='$staffcode' AND (TermCode='$CurrentSememester' OR TermCode='Year') GROUP BY StudentID ORDER BY LastName";
						$result = $db->query($sql);
						$totalstudents=mysqli_num_rows($result);
						$studentcounter=0;
						$StudentsInClass = array();
						$totalresultsbystudentarray = array();
						while($row = $result->fetch_assoc())
						{
							$studentcounter++;
							$StudentID=htmlspecialchars($row["StudentID"], ENT_QUOTES);
							$IEP=htmlspecialchars($row["StudentIEPStatus"], ENT_QUOTES);
							$Gifted=htmlspecialchars($row["StudentGiftedStatus"], ENT_QUOTES);
							$ELL=htmlspecialchars($row["StudentELLStatus"], ENT_QUOTES);
							$ResultName=getStudentNameGivenStudentID($StudentID);
							$User=getEmailGivenStudentID($StudentID);
							array_push($StudentsInClass,$User);

							if($studentcounter==1)
							{
								$StudentScoresArray = GetCorrectResponsesforAssessment($Assessment_ID);
								$StudentStatusArray = GetAssessmentStatus($Assessment_ID);
							}
							ShowAssessmentResults($Assessment_ID,$User,$StudentID,$ResultName,$IEP,$ELL,$Gifted,$questioncount,$owner,$totalstudents,$studentcounter,$totalresultsbystudentarray,$StudentScoresArray,$StudentStatusArray,$StudentsInClass,$QuestionDetails);
						}
					}

			echo "</table>";
			echo "</div>";
			echo "</div>";
			echo "</div>";
			echo "</div>";
		}
		else
		{
			echo "<div class='row center-align'><div class='col s12'><h6>There are no results for this assessment</h6></div></div>";
		}

		$db->close();
	}

?>

<script>

	//Responsive fixed table header
	$(function()
	{
		$("#myTableteacherview").tableHeadFixer({ 'head' : true, 'left' : 1, 'foot' : true });
		$("#myTableteacherview").tablesorter({ sortList: [[1,0]] });

		//Check Window Width
		tableContainer();
		$(window).resize(function(){ tableContainer(); });
		function tableContainer()
		{
			var height=$(".mdl-layout__content").height();
			height=height-210;
			height=height+'px';
			$(".tableholder").css("max-height", height);
		}

		//Remove Student Result
		$( ".removeresult" ).unbind().click(function()
		{
			event.preventDefault();
			var result = confirm("Delete this student assessment?");
			if (result) {
				$(this).closest("tr").hide();
				var address = $(this).attr("href");
				$.ajax({
					type: 'POST',
					url: address,
					data: '',
				})
			}
		});

		//Question Viewer
		$(".questionviewerreponse").unbind().click(function()
		{
			event.preventDefault();
			var Question = $(this).data('question');
			var QuestionTitle = $(this).data('questiontitle');
			$("#questionresponse_title").html(QuestionTitle);
			var QuestionScore = $(this).data('questionscore');
			if(QuestionScore=="1")
			{
				questionverbage="<div class='card white-text' style='background-color:#4CAF50; padding:20px;'>The response was correct</div>";
			}
			if(QuestionScore=="0")
			{
				questionverbage="<div class='card white-text' style='background-color:#F44336; padding:20px;'>The response was incorrect</div>";
			}
			if(QuestionScore=="t")
			{
				questionverbage="<div class='card white-text' style='background-color:#2196F3; padding:20px;'>This question is teacher graded</div>";
			}
			$("#questionresponse_score").html(questionverbage);
			var AssessmentID = $(this).data('assessmentid');
			var User = $(this).data('user');
			$("#questionholderresponse").hide();

			$(".modal-content #questionholderresponse").load( "modules/<?php echo basename(__DIR__); ?>/response_viewer.php?id="+Question+"&assessmentid="+AssessmentID+"&user="+User, function(){
				$("#questionholderresponse").show();
			});

			$('#questionresponse').openModal({
				in_duration: 0,
				out_duration: 0,
			});
		});


	});


</script>