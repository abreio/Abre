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
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');
	require_once('permissions.php');

	function isAssessmentAdministrator(){
		require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
		$email = $_SESSION["useremail"];
		//Check to see if they have the Assessment Administrator Role
		$sql = "SELECT role FROM directory WHERE email = '$email'";
		$result = $db->query($sql);
		while($row = $result->fetch_assoc()){
			$role = decrypt($row["role"], "");
			if(strpos($role, 'Assessment Administrator') !== false){
				$db->close();
				return true;
			}
		}
		$db->close();
		return false;
	}

		function getCerticaToken()
		{
			$ch = curl_init();
			$sql = "SELECT Certica_URL, Certica_AccessKey FROM assessments_settings";
			require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
			$result = $db->query($sql);
			while($row = $result->fetch_assoc())
			{
				$resturl=$row["Certica_URL"];
				$restkey=$row["Certica_AccessKey"];
			}
			curl_setopt($ch, CURLOPT_URL, "$resturl/tokens?unlimited=true");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: IC-TOKEN Credential=$restkey"));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$json = json_decode($result,true);
			$token=$json['token'];
			curl_close($ch);
			$db->close();
			return $token;
		}

		function getNameGivenEmail($emaillookup)
		{
			require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
			$firstname=""; $lastname=""; $found=0;

			//Look for Student Given Student Email
			$sql = "SELECT StudentID FROM Abre_AD WHERE Email='$emaillookup'";
			$result = $db->query($sql);
			while($row = $result->fetch_assoc())
			{
				$StudentID=$row["StudentID"];

				$sql2 = "SELECT FirstName, LastName FROM Abre_Students WHERE StudentId='$StudentID'";
				$result2 = $db->query($sql2);
				while($row2 = $result2->fetch_assoc())
				{
					$found==1;
					$firstname=$row2["FirstName"];
					$lastname=$row2["LastName"];
				}
			}

			//Look for Staff in Staff Directory Given Email
			if($found==0)
			{
				$sql = "SELECT firstname, lastname FROM directory WHERE email='$emaillookup'";
				$result = $db->query($sql);
				while($row = $result->fetch_assoc())
				{
					$found==1;
					$firstname = $row["firstname"];
					$lastname = $row["lastname"];
				}
			}

			//Look for Staff in SIS Given Email
			if($found==0)
			{
				$sql = "SELECT FirstName, LastName FROM Abre_Staff WHERE EMail1='$emaillookup'";
				$result = $db->query($sql);
				while($row = $result->fetch_assoc())
				{
					$found==1;
					$firstname=$row["FirstName"];
					$lastname=$row["LastName"];
				}
			}

			$db->close();
			if($firstname && $lastname){ return "$lastname, $firstname"; }else{ return "$emaillookup"; }

		}

		//Final Score the Assessment
		function AssessmentResultsScore($Assessment_ID,$User)
		{
			require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
			mysqli_query($db, "UPDATE assessments_status SET Is_Graded='0' WHERE Assessment_ID='$Assessment_ID' AND User='$User'") or die (mysqli_error($db));
			$db->close();
		}

		//Start CSV Export
		function CSVExport()
		{
			require(dirname(__FILE__) . '/../../configuration.php');
			if (!file_exists("../../../$portal_private_root/Abre-Assessments/Exports")){
				mkdir("../../../$portal_private_root/Abre-Assessments/Exports", 0777, true);
			}
		}

		function str_putcsv($input, $delimiter = ',', $enclosure = '"')
    	{
			// Open a memory "file" for read/write...
			$fp = fopen('php://temp', 'r+');
			// ... write the $input array to the "file" using fputcsv()...
			fputcsv($fp, $input, $delimiter, $enclosure);
			// ... rewind the "file" so we can read what we just wrote...
			rewind($fp);
			// ... read the entire line into a variable...
			$data = fread($fp, 1048576);
			// ... close the "file"...
			fclose($fp);
			// ... and return the $data to the caller, with the trailing newline from fgets() removed.
			return rtrim($data, "\n");
    	}

		//Get Staff Name Given StaffID
		function getStaffNameGivenStaffID($StaffID)
		{
			require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
			$sql = "SELECT FirstName, LastName FROM Abre_Staff WHERE StaffID='$StaffID' LIMIT 1";
			$result = $db->query($sql);
			while($row = $result->fetch_assoc())
			{
				$FirstName=$row["FirstName"];
				$LastName=$row["LastName"];
			}
			$db->close();
			if($FirstName && $LastName){ return "$FirstName $LastName"; }else{ return "$StaffID"; }

		}

		//Get Email Given StudentID
		function getEmailGivenStudentID($StudentID)
		{
			require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
			$sql = "SELECT Email FROM Abre_AD WHERE StudentID='$StudentID'";
			$result = $db->query($sql);
			while($row = $result->fetch_assoc())
			{
				$Email=$row["Email"];
			}
			$db->close();
			if(isset($Email)){ return $Email; }else{ return $StudentID; }

		}

		//Get Student Name Given StudentID
		function getStudentNameGivenStudentID($StudentID)
		{
			require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
			$sql = "SELECT FirstName, LastName FROM Abre_Students WHERE StudentId='$StudentID'";
			$result = $db->query($sql);
			while($row = $result->fetch_assoc())
			{
				$FirstName=$row["FirstName"];
				$LastName=$row["LastName"];
			}
			$db->close();
			if(isset($FirstName) && isset($LastName)){ return "$LastName, $FirstName"; }else{ return $StudentID; }

		}

		//Get StaffID Given Email
		function GetStaffID($email){
			$email = strtolower($email);
			$query = "SELECT StaffID FROM Abre_Staff WHERE EMail1 LIKE '$email' LIMIT 1";
			$dbreturn = databasequery($query);
			foreach ($dbreturn as $value)
			{
				$StaffId=htmlspecialchars($value["StaffID"], ENT_QUOTES);
				return $StaffId;
			}
		}

		//Get Current Semester
		function GetCurrentSemester(){
			$currentMonth = date("F");
			if(	$currentMonth=="January" 	or
				$currentMonth=="February" 	or
				$currentMonth=="March" 		or
				$currentMonth=="April" 		or
				$currentMonth=="May" 		or
				$currentMonth=="June" 		or
				$currentMonth=="July" 		or
				$currentMonth=="August"
			)
			{
				return "Sem2";
			}
			else
			{
				return "Sem1";
			}
		}

		//Return all Results for Assessment
		function GetCorrectResponsesforAssessment($Assessment_ID)
		{

			require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

			//Check what questions the student got correct
			$sqlquestionsanswer = "SELECT ItemID, Score, User FROM assessments_scores WHERE Assessment_ID='$Assessment_ID'";
			$resultquestionsanswer = $db->query($sqlquestionsanswer);
			$StudentScoresArray = array();
			while($rowquestionsanswer = $resultquestionsanswer->fetch_assoc())
			{
				$StudentItemID = htmlspecialchars($rowquestionsanswer["ItemID"], ENT_QUOTES);
				$StudentScore = htmlspecialchars($rowquestionsanswer["Score"], ENT_QUOTES);
				$StudentUser = htmlspecialchars($rowquestionsanswer["User"], ENT_QUOTES);
				$StudentScoresArray[$StudentItemID][$StudentUser] = $StudentScore;
			}

			$db->close();
			return $StudentScoresArray;

		}

		//Return all Status for Assessment
		function GetAssessmentStatus($Assessment_ID)
		{

			require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

			$sqlcomplete = "SELECT User, Start_Time, End_Time FROM assessments_status WHERE Assessment_ID='$Assessment_ID'";
			$resultcomplete = $db->query($sqlcomplete);
			$completestatus=0;
			$StudentStatusArray = array();
			while($rowcomplete = $resultcomplete->fetch_assoc())
			{
				$completestatus=1;
				$User=htmlspecialchars($rowcomplete["User"], ENT_QUOTES);
				$Start_Time=htmlspecialchars($rowcomplete["Start_Time"], ENT_QUOTES);
				$End_Time=htmlspecialchars($rowcomplete["End_Time"], ENT_QUOTES);
				$Start_Time=date("F j, Y, g:i A", strtotime($Start_Time));
				if($End_Time=="0000-00-00 00:00:00"){ $End_Time="In Progress"; }else{ $End_Time=date("F j, Y, g:i A", strtotime($End_Time)); }
				$StudentStatusArray[$User] = array('StartTime' => $Start_Time, 'EndTime' => $End_Time);
			}
			$db->close();
			return $StudentStatusArray;

		}

		//Get Email Given StudentID
		function getTeacherRoster($StaffID)
		{

			require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
			$Students=array();
			$CurrentSemester=GetCurrentSemester();
			$sql = "SELECT StudentID, FirstName, LastName FROM Abre_StudentSchedules WHERE StaffId='$StaffID' AND (TermCode='$CurrentSemester' OR TermCode='Year') GROUP BY StudentID ORDER BY LastName";
			$result = $db->query($sql);
			while($row = $result->fetch_assoc())
			{
				$StudentID=$row["StudentID"];
				$FirstName=$row["FirstName"];
				$LastName=$row["LastName"];
				$Students[] = array("StudentID" => $StudentID, "FirstName" => $FirstName, "LastName" => $LastName);
			}
			$db->close();
			return $Students;

		}

		//Get all scores by teacher
		function getTeacherRosterScoreBreakdown($StaffID,$AssessmentID)
		{

			require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
			$Students=array();
			$CurrentSemester=GetCurrentSemester();
			$sql = "SELECT StudentID, FirstName, LastName FROM Abre_StudentSchedules WHERE StaffId='$StaffID' AND (TermCode='$CurrentSemester' OR TermCode='Year') GROUP BY StudentID ORDER BY LastName";
			$result = $db->query($sql);
			while($row = $result->fetch_assoc())
			{
				$StudentID=$row["StudentID"];
				$FirstName=$row["FirstName"];
				$LastName=$row["LastName"];

				//Find how they did on assessment
				$sql2 = "SELECT IEP, ELL, Gifted, Score, Possible_Points FROM assessments_results WHERE Student_ID='$StudentID' AND Assessment_ID='$AssessmentID'";
				$result2 = $db->query($sql2);
				while($row2 = $result2->fetch_assoc())
				{
					$IEP=$row2["IEP"];
					$ELL=$row2["ELL"];
					$Gifted=$row2["Gifted"];
					$Score=$row2["Score"];
					$Possible_Points=$row2["Possible_Points"];

					$Students[] = array("StudentID" => $StudentID, "FirstName" => $FirstName, "LastName" => $LastName, "IEP" => $IEP, "ELL" => $ELL, "Gifted" => $Gifted, "Score" => $Score, "PossiblePoints" => $Possible_Points);
				}

			}
			$db->close();
			return $Students;

		}

		//Get all Scores by Assessment
		function getAllScoresByAssessment($AssessmentID)
		{

			require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
			$Students=array();
			$sql = "SELECT Student_ID, IEP, ELL, Gifted, Score, Possible_Points FROM assessments_results WHERE Assessment_ID='$AssessmentID'";
			$result = $db->query($sql);
			while($row = $result->fetch_assoc())
			{
				$StudentID=$row["Student_ID"];
				$IEP=$row["IEP"];
				$ELL=$row["ELL"];
				$Gifted=$row["Gifted"];
				$Score=$row["Score"];
				$Possible_Points=$row["Possible_Points"];

				$Students[] = array("StudentID" => $StudentID, "IEP" => $IEP, "ELL" => $ELL, "Gifted" => $Gifted, "Score" => $Score, "PossiblePoints" => $Possible_Points);
			}
			$db->close();
			return $Students;

		}

		//Show Results of Assessment
		function ShowAssessmentResults($Assessment_ID,$User,$Student_ID,$ResultName,$IEP,$ELL,$Gifted,$questioncount,$owner,$totalstudents,$studentcounter,$correctarray,$StudentScoresArray,$StudentStatusArray,$StudentsInClass,$QuestionDetails,$CSVExportArray)
		{

			require(dirname(__FILE__) . '/../../configuration.php');
			require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

			$Username=str_replace("@","",$User);
			$Username=str_replace(".","",$Username);

			echo "<tr class='assessmentrow'>";
				echo "<td><b>$ResultName</b></td>";

				//CSV Student
				array_push($CSVExportArray,$ResultName);

				if(!isset($Student_ID)){
					$Student_ID = "";
				}
				array_push($CSVExportArray, $Student_ID);

				if (!empty($StudentStatusArray[$User]))
				{
					$StudentStatusVerb=$StudentStatusArray[$User];
					if($StudentStatusVerb['EndTime']!="In Progress"){

						$TimeDifference = (strtotime($StudentStatusVerb['EndTime']) - strtotime($StudentStatusVerb['StartTime']))/60;
						$TimeDifference = sprintf("%02d", $TimeDifference);
						if($TimeDifference==1){ $TimeDifferenceText="$TimeDifference Minute"; }else{ $TimeDifferenceText="$TimeDifference Minutes"; }
						echo "<td>";
							echo "<div id='status_$User' class='pointer'>Completed</div>";
							echo "<div class='mdl-tooltip mdl-tooltip--large' for='status_$User'><b>Completed:<br>"; echo $StudentStatusVerb['EndTime']; echo "</b><br><br>Total Time:<br>$TimeDifferenceText</div>";
						echo "</td>";

						//CSV Status
						array_push($CSVExportArray,"Completed");

					}else{
						echo "<td>";
							echo "<div id='status_$User' class='pointer'>In Progress</div>";
							echo "<div class='mdl-tooltip mdl-tooltip--large' for='status_$User'><b>Start Time:<br>"; echo $StudentStatusVerb['StartTime']; echo "</b></div>";
						echo "</td>";

						//CSV Status
						array_push($CSVExportArray,"In Progress");

					}

				}
				else
				{
					echo "<td>Has Not Started</td>";

					//CSV Status
					array_push($CSVExportArray,"Has Not Started");
				}

				//Loop through each question on assessment
				$sqlquestions = "SELECT Bank_ID, Points, Type FROM assessments_questions WHERE Assessment_ID='$Assessment_ID' ORDER BY Question_Order";
				$resultquestions = $db->query($sqlquestions);
				$allquestionitemsArray = array();
				$totalquestions=mysqli_num_rows($resultquestions);
				$totalcorrect=0;
				$totalcorrectrubric=0;
				$questioncounter=1;
				$totalpossibleassessmentpoints=NULL;
				while($rowquestions = $resultquestions->fetch_assoc())
				{
					$Bank_ID=htmlspecialchars($rowquestions["Bank_ID"], ENT_QUOTES);
					$PointsPossible=htmlspecialchars($rowquestions["Points"], ENT_QUOTES);
					$QuestionType=htmlspecialchars($rowquestions["Type"], ENT_QUOTES);
					if($PointsPossible==""){ $PointsPossible=1; }

					$totalpossibleassessmentpoints=$totalpossibleassessmentpoints+$PointsPossible;

					$allquestionitemsArray[$questioncounter] = $Bank_ID;

					if (isset($StudentScoresArray[$Bank_ID][$User]))
					{
						$Score = $StudentScoresArray[$Bank_ID][$User];

						if($Score=="0" && $QuestionType!="Open Response")
						{
							$icon="<i class='material-icons' style='color:#B71C1C'>cancel</i>";
							echo "<td class='center-align pointer questionviewerreponse' data-question='$Bank_ID' data-questiontitle='$ResultName - Question $questioncounter' data-questionscore='0' data-assessmentid='$Assessment_ID' data-user='$User' style='background-color:#F44336'>$icon</td>";

							//Export to CSV
							array_push($CSVExportArray,$Score);
						}
						if($Score=="1" && $QuestionType!="Open Response")
						{
							$icon="<i class='material-icons' style='color:#1B5E20'>check_circle</i>";
							$totalcorrect=$totalcorrect+$PointsPossible;
							echo "<td class='center-align pointer questionviewerreponse' data-question='$Bank_ID' data-questiontitle='$ResultName - Question $questioncounter' data-questionscore='1' data-assessmentid='$Assessment_ID' data-user='$User' style='background-color:#4CAF50'>$icon</td>";

							//Export to CSV
							array_push($CSVExportArray,$Score);
						}
						if($Score=="" && $QuestionType=="Open Response")
						{
							$icon="<i class='material-icons' style='color:#0D47A1'>star_border</i>";
							echo "<td class='center-align pointer questionviewerreponse' id='rubric-question-$Username-$Bank_ID' data-question='$Bank_ID' data-questiontitle='$ResultName - Question $questioncounter' data-questionscore='t' data-assessmentid='$Assessment_ID' data-user='$User' style='background-color:#2196F3'>$icon</td>";

							//Export to CSV
							array_push($CSVExportArray,"0");
						}
						if($Score!="" && $QuestionType=="Open Response")
						{
							$icon="<i class='material-icons' style='color:#0D47A1'>star</i>";
							$totalcorrectrubric=$totalcorrectrubric+$Score;
							echo "<td class='center-align pointer questionviewerreponse' data-question='$Bank_ID' data-questiontitle='$ResultName - Question $questioncounter' data-questionscore='t' data-assessmentid='$Assessment_ID' data-user='$User' style='background-color:#1565C0'>$icon</td>";

							//Export to CSV
							array_push($CSVExportArray,$Score);
						}


					}
					else
					{
						echo "<td class='center-align' style='background-color:#FFC107'><i class='material-icons' style='color:#FF6F00;'>remove_circle</i></td>";

						//Export to CSV
						array_push($CSVExportArray,"0");

					}

					$questioncounter++;

				}

				//IEP,ELL,Gifted
				if($IEP==""){ $IEP="N"; }
				if($ELL==""){ $ELL="N"; }
				if($Gifted==""){ $Gifted="N"; }
				echo "<td class='center-align'><b>$IEP</b></td>";

				//Export to CSV
				array_push($CSVExportArray,$IEP);

				echo "<td class='center-align'><b>$ELL</b></td>";

				//Export to CSV
				array_push($CSVExportArray,$ELL);

				echo "<td class='center-align'><b>$Gifted</b></td>";

				//Export to CSV
				array_push($CSVExportArray,$Gifted);

				//Auto Points
				$totalcorrectdouble=sprintf("%02d", $totalcorrect);
				if($totalcorrectdouble!="00"){ $totalcorrectdouble = ltrim($totalcorrectdouble, '0'); }
				if($totalcorrectdouble=="00"){ $totalcorrectdouble="0"; }
				echo "<td class='center-align'>$totalcorrectdouble</td>";
				array_push($CSVExportArray,$totalcorrectdouble);

				//Rubric Points
				echo "<td class='center-align' id='rubric-total-$Username'>$totalcorrectrubric</td>";
				array_push($CSVExportArray,$totalcorrectrubric);

				//Score
				$rubricandtotalscored=$totalcorrectdouble+$totalcorrectrubric;
				echo "<td class='center-align' id='score-total-$Username'>$rubricandtotalscored/$totalpossibleassessmentpoints</td>";
				array_push($CSVExportArray,"$rubricandtotalscored/$totalpossibleassessmentpoints");

				//Percentage
				$studentfinalpercentage=round((($rubricandtotalscored)/$totalpossibleassessmentpoints)*100);
				echo "<td class='center-align' id='percentage-total-$Username'>$studentfinalpercentage%</td>";
				array_push($CSVExportArray,"$studentfinalpercentage");

				//Delete Assessment Button
				if($owner==1 || admin() || isAssessmentAdministrator())
				{
					echo "<td class='center-align'><a href='modules/".basename(__DIR__)."/openstudentresult.php?assessmentid=".$Assessment_ID."&student=".$User."' class='mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect mdl-color-text--grey-600 refreshresult'><i class='material-icons'>refresh</i></a></td>";
					echo "<td class='center-align'><a href='modules/".basename(__DIR__)."/removestudentresult.php?assessmentid=".$Assessment_ID."&student=".$User."' class='mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect mdl-color-text--grey-600 removeresult'><i class='material-icons'>delete</i></a></td>";
				}
				else
				{
					echo "<td class='center-align'></td>";
					echo "<td class='center-align'></td>";
				}


			echo "</tr>";

			if($totalstudents==$studentcounter)
			{

				//How many district students took assessment
				$sqlquestions = "SELECT COUNT(*) FROM (SELECT * FROM assessments_scores WHERE Assessment_ID='$Assessment_ID' GROUP BY User) AS Result";
				$resultquestions = $db->query($sqlquestions);
				$resultrow = $resultquestions->fetch_assoc();
				$totalassessedstudents = $resultrow["COUNT(*)"];

				//Score Breakdown
				$sqlquestions = "SELECT ItemID, Count(*) FROM `assessments_scores` WHERE `Assessment_ID` LIKE '$Assessment_ID' AND Score=1 GROUP BY ItemID";
				$resultquestions = $db->query($sqlquestions);
				$questionscoreArray = array();
				while($rowquestions = $resultquestions->fetch_assoc())
				{
					$ItemIDScore=htmlspecialchars($rowquestions["ItemID"], ENT_QUOTES);
					$ItemIDCount=htmlspecialchars($rowquestions["Count(*)"], ENT_QUOTES);
					$questionscoreArray[$ItemIDScore] = $ItemIDCount;
				}




				echo "</tbody>";

				echo "<tfoot>";

					//Class Average
					if(!empty($StudentsInClass)){

						echo "<tr style='background-color:".getSiteColor().";'>";
						echo "<td colspan='2' style='color:#fff;' class='center-align'><b>Class Average</b></td>";

						foreach ($allquestionitemsArray as $Bank_ID)
						{

							$QuestionType=$QuestionDetails[$Bank_ID];
							$AnswersCorrect=0;

							//Find out how many students got question correct
							if($QuestionType!="Open Response"){
								$StudentsWhoAnswered=0;
								foreach ($StudentsInClass as $Email)
								{
									if(isset($StudentScoresArray[$Bank_ID][$Email])){ $StudentsWhoAnswered++; }

									if(isset($StudentScoresArray[$Bank_ID][$Email])){
										$AnswersCorrect=$AnswersCorrect+$StudentScoresArray[$Bank_ID][$Email];
									}
								}
								$correctpercent=round(($AnswersCorrect/$StudentsWhoAnswered)*100);
								echo "<td class='center-align' style='color:#fff;'><b>$correctpercent%</b></td>";
							}
							else
							{
								echo "<td class='center-align' style='color:#fff;'><b>NA</b></td>";
							}
						}

						echo "<td></td>";
						echo "<td></td>";
						echo "<td></td>";
						echo "<td></td>";
						echo "<td></td>";
						echo "<td></td>";
						echo "<td></td>";
						echo "</tr>";
					}

					//District Average
					echo "<tr style='background-color:".getSiteColor().";'>";
					echo "<td colspan='2' style='color:#fff;' class='center-align'><b>District Average</b></td>";

					foreach ($allquestionitemsArray as $Bank_ID)
					{
						$QuestionType=$QuestionDetails[$Bank_ID];

						if($QuestionType!="Open Response"){
							if (isset($questionscoreArray[$Bank_ID]))
							{
								$correctcount=$questionscoreArray[$Bank_ID];
								$correctpercent=round(($correctcount/$totalassessedstudents)*100);
								echo "<td class='center-align' style='color:#fff;'><b>$correctpercent%</b></td>";
							}
							else
							{
								echo "<td class='center-align' style='color:#fff;'><b>0%</b></td>";
							}
						}
						else
						{
							echo "<td class='center-align' style='color:#fff;'><b>NA</b></td>";
						}

					}

					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "</tr>";

				echo "</tfoot>";


			}

			$db->close();
			return $CSVExportArray;

		}

?>