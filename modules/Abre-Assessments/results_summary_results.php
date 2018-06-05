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

	$cloudsetting=constant("USE_GOOGLE_CLOUD");
	if ($cloudsetting=="true") 
		require(dirname(__FILE__). '/../../vendor/autoload.php');
	use Google\Cloud\Storage\StorageClient;

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
		if(admin() || isAssessmentAdministrator() || AdminCheck($_SESSION['useremail'])){ $owner=1; }

		if(isset($_GET["course"]))
		{
			$course=$_GET["course"];
			list($CourseCode, $SectionCode) = explode(",",$course);
			$sql = "SELECT COUNT(*) FROM (SELECT * FROM Abre_StudentSchedules WHERE CourseCode='$CourseCode' AND SectionCode='$SectionCode' AND StaffId='$StaffId' AND (TermCode='$CurrentSememester' OR TermCode='Year') GROUP BY StudentID ORDER BY LastName) AS Result";
		}
		if(isset($_GET["groupid"]))
		{
			$groupid=$_GET["groupid"];
			$sql = "SELECT COUNT(*) FROM students_groups WHERE ID='$groupid'";
		}
		if(isset($_GET["staffid"]))
		{
			$staffcode=$_GET["staffid"];
			$sql = "SELECT COUNT(*) FROM (SELECT * FROM Abre_StudentSchedules WHERE StaffId='$staffcode' AND (TermCode='$CurrentSememester' OR TermCode='Year') GROUP BY StudentID ORDER BY LastName) AS Result";
		}
		if(!isset($course) && !isset($groupid) && !isset($staffcode))
		{
			$sql = "SELECT COUNT(*) FROM assessments_status WHERE Assessment_ID='$Assessment_ID'";
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

			<?php

				echo "<div class='row' id='reloadbutton' style='margin-left:-5px;'>";
						echo "<button class='modal-action waves-effect btn-flat white-text' id='reload' style='margin-left:5px; background-color:"; echo getSiteColor(); echo "'>Refresh Results</button>";
				echo "</div>";

			?>

			<div class='row'><div class='tableholder'>
			<table id='myTable' class='tablesorter bordered thintable'>
			<thead>
			<tr class='pointer'>
				<th><div style='width:180px;'>Student</div></th>
				<th><div style='width:140px;'>Status</div></th>

				<?php

					$sqlheader = "SELECT Standard, Difficulty, Bank_ID, Type FROM assessments_questions WHERE Assessment_ID='$Assessment_ID' ORDER BY Question_Order";
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
				<th style='max-width:30px;'></th>
			</tr>
			</thead>
			<tbody>

				<?php

					//View By Groups
					if(isset($groupid))
					{
						$sql = "SELECT Email, Student_ID FROM students_groups_students LEFT JOIN Abre_AD ON students_groups_students.Student_ID=Abre_AD.StudentID WHERE students_groups_students.Group_ID='$groupid'";
						$result = $db->query($sql);
						$totalstudents=mysqli_num_rows($result);
						$studentcounter=0;
						$StudentsInClass = array();
						$totalresultsbystudentarray = array();

						//CSV Export Prepare
						$CSVExportArray= array();
						if ($cloudsetting!="true") 
							CSVExport();
						$UserEmail=$_SESSION['useremail'];
						$CSVGC = "";
						if ($cloudsetting!="true") 
							$CSVExportFile = fopen(dirname(__FILE__) . "../../../../$portal_private_root/Abre-Assessments/Exports/$UserEmail.csv", 'w');

						$CSVHeaderArray = array();
						array_push($CSVHeaderArray, "Student", "StudentID", "Status");
						$sqlquestions = "SELECT COUNT(*) FROM assessments_questions WHERE Assessment_ID='$Assessment_ID' ORDER BY Question_Order";
						$resultquestions = $db->query($sqlquestions);
						$returnrow = $resultquestions->fetch_assoc();
						$questionNumber = $returnrow["COUNT(*)"];
						for($i = 1; $i <= $questionNumber; $i++){
							array_push($CSVHeaderArray, "Question ".$i);
						}
						array_push($CSVHeaderArray, "IEP", "ELL", "Gifted", "Auto Points", "Rubric Points", "Score", "Percentage");
						if ($cloudsetting=="true") 
							$CSVGC .= str_putcsv($CSVHeaderArray);
						else
							fputcsv($CSVExportFile, $CSVHeaderArray);

						while($row = $result->fetch_assoc())
						{
							$studentcounter++;
							$User=htmlspecialchars($row["Email"], ENT_QUOTES);
							if($User!=NULL)
							{
								$ResultName=getNameGivenEmail($User);
								$StudentID=htmlspecialchars($row["Student_ID"], ENT_QUOTES);
								$IEP = "";
								$Gifted = "";
								$ELL = "";
							}
							else
							{
								$StudentID=htmlspecialchars($row["Student_ID"], ENT_QUOTES);
								$IEP = "";
								$Gifted = "";
								$ELL = "";
								$ResultName=getStudentNameGivenStudentID($StudentID);
							}

							if($studentcounter==1)
							{
								$StudentScoresArray = GetCorrectResponsesforAssessment($Assessment_ID);
								$StudentStatusArray = GetAssessmentStatus($Assessment_ID);
							}

							$CSVExportArrayreturn = ShowAssessmentResults($Assessment_ID,$User,$StudentID,$ResultName,$IEP,$ELL,$Gifted,$questioncount,$owner,$totalstudents,$studentcounter,$totalresultsbystudentarray,$StudentScoresArray,$StudentStatusArray,$StudentsInClass,$QuestionDetails,$CSVExportArray);
							if ($cloudsetting=="true") 
								$CSVGC .= str_putcsv($CSVExportArrayreturn);
							else
								fputcsv($CSVExportFile, $CSVExportArrayreturn);
						}

						if ($cloudsetting=="true") {
							// Write to GC storage
							$storage = new StorageClient([
								'projectId' => constant("GC_PROJECT")
							]);	
							$bucket = $storage->bucket(constant("GC_BUCKET"));
					
							$uploaddir = "private_html/Abre-Assessments/Exports/" . $UserEmail.csv;
							$options = [
								'resumable' => true,
								'name' => $uploaddir,
								'metadata' => [
									'contentLanguage' => 'en'
								]
							];
							$upload = $bucket->upload(
								$CSVGC,
								$options
							);
						}
						else {
							//Close CSV Export
							fclose($CSVExportFile);
						}
					}

					//View By Courses
					if(isset($course))
					{
						$sql = "SELECT StudentID, StudentIEPStatus, StudentGiftedStatus, StudentELLStatus FROM Abre_StudentSchedules WHERE CourseCode='$CourseCode' AND SectionCode='$SectionCode' AND StaffId='$StaffId' AND (TermCode='$CurrentSememester' OR TermCode='Year') GROUP BY StudentID ORDER BY LastName";
						$result = $db->query($sql);
						$totalstudents=mysqli_num_rows($result);
						$studentcounter=0;
						$StudentsInClass = array();
						$totalresultsbystudentarray = array();

						//CSV Export Prepare
						$CSVExportArray= array();
						if ($cloudsetting!="true") 
							CSVExport();
						$UserEmail=$_SESSION['useremail'];
						$CSVGC = "";
						if ($cloudsetting!="true") 
							$CSVExportFile = fopen(dirname(__FILE__) . "../../../../$portal_private_root/Abre-Assessments/Exports/$UserEmail.csv", 'w');

						$CSVHeaderArray = array();
						array_push($CSVHeaderArray, "Student", "StudentID", "Status");
						$sqlquestions = "SELECT COUNT(*) FROM assessments_questions WHERE Assessment_ID='$Assessment_ID' ORDER BY Question_Order";
						$resultquestions = $db->query($sqlquestions);
						$returnrow = $resultquestions->fetch_assoc();
						$questionNumber = $returnrow["COUNT(*)"];
						for($i = 1; $i <= $questionNumber; $i++){
							array_push($CSVHeaderArray, "Question ".$i);
						}
						array_push($CSVHeaderArray, "IEP", "ELL", "Gifted", "Auto Points", "Rubric Points", "Score", "Percentage");
						if ($cloudsetting=="true") 
							$CSVGC .= str_putcsv($CSVHeaderArray);
						else
							fputcsv($CSVExportFile, $CSVHeaderArray);

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
							$CSVExportArrayreturn = ShowAssessmentResults($Assessment_ID,$User,$StudentID,$ResultName,$IEP,$ELL,$Gifted,$questioncount,$owner,$totalstudents,$studentcounter,$totalresultsbystudentarray,$StudentScoresArray,$StudentStatusArray,$StudentsInClass,$QuestionDetails,$CSVExportArray);
							if ($cloudsetting=="true") 
								$CSVGC .= str_putcsv($CSVExportArrayreturn);
							else
								fputcsv($CSVExportFile, $CSVExportArrayreturn);
						}

						if ($cloudsetting=="true") {
							// Write to GC storage
							$storage = new StorageClient([
								'projectId' => constant("GC_PROJECT")
							]);	
							$bucket = $storage->bucket(constant("GC_BUCKET"));
					
							$uploaddir = "private_html/Abre-Assessments/Exports/" . $UserEmail.csv;
							$options = [
								'resumable' => true,
								'name' => $uploaddir,
								'metadata' => [
									'contentLanguage' => 'en'
								]
							];
							$upload = $bucket->upload(
								$CSVGC,
								$options
							);
						}
						else {
							//Close CSV Export
							fclose($CSVExportFile);
						}
					}

					//View By Teacher
					if(isset($staffcode))
					{
						$sql = "SELECT StudentID, StudentIEPStatus, StudentGiftedStatus, StudentELLStatus FROM Abre_StudentSchedules WHERE StaffId='$staffcode' AND (TermCode='$CurrentSememester' OR TermCode='Year') GROUP BY StudentID ORDER BY LastName";
						$result = $db->query($sql);
						$totalstudents=mysqli_num_rows($result);
						$studentcounter=0;
						$StudentsInClass = array();
						$totalresultsbystudentarray = array();

						//CSV Export Prepare
						$CSVExportArray= array();
						if ($cloudsetting!="true") 
							CSVExport();
						$UserEmail=$_SESSION['useremail'];
						$CSVGC = "";
						if ($cloudsetting!="true") 
							$CSVExportFile = fopen(dirname(__FILE__) . "../../../../$portal_private_root/Abre-Assessments/Exports/$UserEmail.csv", 'w');

						$CSVHeaderArray = array();
						array_push($CSVHeaderArray, "Student", "StudentID", "Status");
						$sqlquestions = "SELECT COUNT(*) FROM assessments_questions WHERE Assessment_ID='$Assessment_ID' ORDER BY Question_Order";
						$resultquestions = $db->query($sqlquestions);
						$returnrow = $resultquestions->fetch_assoc();
						$questionNumber = $returnrow["COUNT(*)"];
						for($i = 1; $i <= $questionNumber; $i++){
							array_push($CSVHeaderArray, "Question ".$i);
						}
						array_push($CSVHeaderArray, "IEP", "ELL", "Gifted", "Auto Points", "Rubric Points", "Score", "Percentage");
						if ($cloudsetting=="true") 
							$CSVGC .= str_putcsv($CSVHeaderArray);
						else
							fputcsv($CSVExportFile, $CSVHeaderArray);

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
							$CSVExportArrayreturn = ShowAssessmentResults($Assessment_ID,$User,$StudentID,$ResultName,$IEP,$ELL,$Gifted,$questioncount,$owner,$totalstudents,$studentcounter,$totalresultsbystudentarray,$StudentScoresArray,$StudentStatusArray,$StudentsInClass,$QuestionDetails,$CSVExportArray);
							if ($cloudsetting=="true") 
								$CSVGC .= str_putcsv($CSVExportArrayreturn);
							else							
								fputcsv($CSVExportFile, $CSVExportArrayreturn);
						}

						if ($cloudsetting=="true") {
							// Write to GC storage
							$storage = new StorageClient([
								'projectId' => constant("GC_PROJECT")
							]);	
							$bucket = $storage->bucket(constant("GC_BUCKET"));
					
							$uploaddir = "private_html/Abre-Assessments/Exports/" . $UserEmail.csv;
							$options = [
								'resumable' => true,
								'name' => $uploaddir,
								'metadata' => [
									'contentLanguage' => 'en'
								]
							];
							$upload = $bucket->upload(
								$CSVGC,
								$options
							);
						}
						else {
							//Close CSV Export
							fclose($CSVExportFile);
						}
					}

					//View All
					if(!isset($course) && !isset($groupid) && !isset($staffcode))
					{
						$sql = "SELECT User, Student_ID, IEP, ELL, Gifted FROM assessments_results WHERE Assessment_ID='$Assessment_ID' ORDER BY User";
						$result = $db->query($sql);
						$totalstudents=mysqli_num_rows($result);
						$studentcounter=0;
						$StudentsInClass = array();
						$totalresultsbystudentarray = array();

						//CSV Export Prepare
						$CSVExportArray= array();
						if ($cloudsetting!="true") 
							CSVExport();
						$UserEmail=$_SESSION['useremail'];
						$CSVGC = "";
						if ($cloudsetting!="true") 
							$CSVExportFile = fopen(dirname(__FILE__) . "../../../../$portal_private_root/Abre-Assessments/Exports/$UserEmail.csv", 'w');

						$CSVHeaderArray = array();
						array_push($CSVHeaderArray, "Student", "StudentID", "Status");
						$sqlquestions = "SELECT COUNT(*) FROM assessments_questions WHERE Assessment_ID='$Assessment_ID' ORDER BY Question_Order";
						$resultquestions = $db->query($sqlquestions);
						$returnrow = $resultquestions->fetch_assoc();
						$questionNumber = $returnrow["COUNT(*)"];
						for($i = 1; $i <= $questionNumber; $i++){
							array_push($CSVHeaderArray, "Question ".$i);
						}
						array_push($CSVHeaderArray, "IEP", "ELL", "Gifted", "Auto Points", "Rubric Points", "Score", "Percentage");
						if ($cloudsetting=="true") 
							$CSVGC .= str_putcsv($CSVHeaderArray);
						else
							fputcsv($CSVExportFile, $CSVHeaderArray);

						while($row = $result->fetch_assoc())
						{
							$studentcounter++;
							$User=htmlspecialchars($row["User"], ENT_QUOTES);
							$StudentID = htmlspecialchars($row["Student_ID"], ENT_QUOTES);
							$IEP=htmlspecialchars($row["IEP"], ENT_QUOTES);
							$ELL=htmlspecialchars($row["ELL"], ENT_QUOTES);
							$Gifted=htmlspecialchars($row["Gifted"], ENT_QUOTES);
							$ResultName=getNameGivenEmail($User);

							if($studentcounter==1)
							{
								$StudentScoresArray = GetCorrectResponsesforAssessment($Assessment_ID);
								$StudentStatusArray = GetAssessmentStatus($Assessment_ID);
							}
							$CSVExportArrayreturn = ShowAssessmentResults($Assessment_ID,$User,$Student_ID,$ResultName,$IEP,$ELL,$Gifted,$questioncount,$owner,$totalstudents,$studentcounter,$totalresultsbystudentarray,$StudentScoresArray,$StudentStatusArray,$StudentsInClass,$QuestionDetails,$CSVExportArray);
							if ($cloudsetting=="true") 
								$CSVGC .= str_putcsv($CSVExportArrayreturn);
							else
								fputcsv($CSVExportFile, $CSVExportArrayreturn);
						}

						if ($cloudsetting=="true") {
							// Write to GC storage
							$storage = new StorageClient([
								'projectId' => constant("GC_PROJECT")
							]);	
							$bucket = $storage->bucket(constant("GC_BUCKET"));
					
							$uploaddir = "private_html/Abre-Assessments/Exports/" . $UserEmail.csv;
							$options = [
								'resumable' => true,
								'name' => $uploaddir,
								'metadata' => [
									'contentLanguage' => 'en'
								]
							];
							$upload = $bucket->upload(
								$CSVGC,
								$options
							);
						}
						else {
							//Close CSV Export
							fclose($CSVExportFile);
						}
					}

			echo "</table>";
			echo "</div>";
			echo "</div>";
			echo "</div>";
			echo "</div>";

			include "download_button.php";

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
		$("#myTable").tableHeadFixer({ 'head' : true, 'left' : 1, 'foot' : true });
		$("#myTable").tablesorter({ sortList: [[0,0]] });

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

		//Delete Student Result
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

		//Open Up Assessment for Student
		$( ".refreshresult" ).unbind().click(function()
		{
			event.preventDefault();
			var result = confirm("Open student assessment?");
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