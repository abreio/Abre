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

		//Include clipboard javascript file
		echo "<script src='$portal_root/modules/".basename(__DIR__)."/js/clipboard.min.js'></script>";

		$sql = "SELECT COUNT(*) FROM assessments WHERE (Owner='".$_SESSION['useremail']."' OR Editors LIKE '%".$_SESSION['useremail']."%') ORDER BY Title";
		$result = $db->query($sql);
		$returnrow = $result->fetch_assoc();
		$rowcount = $returnrow["COUNT(*)"];
		if($rowcount!=0)
		{
			?>
			<div class='page_container mdl-shadow--4dp'>
			<div class='page'>
				<div id='searchresults'>
					<div class='row'><div class='col s12'>
						<table id='myTable' class='tablesorter'>
							<thead>
								<tr class='pointer'>
									<th>Title</th>
									<th class='hide-on-med-and-down'>Level</th>
									<th style='width:30px'></th>
								</tr>
							</thead>
							<tbody>

								<?php
								$sql = "SELECT ID, Title, Description, Subject, Level, Grade, Locked, Shared, Verified, Owner, Editors, Session_ID FROM assessments WHERE (Owner='".$_SESSION['useremail']."' OR Editors LIKE '%".$_SESSION['useremail']."%') ORDER BY Title";
								$result = $db->query($sql);
								while($row = $result->fetch_assoc())
								{
									$Assessment_ID=htmlspecialchars($row["ID"], ENT_QUOTES);
									$Title=htmlspecialchars($row["Title"], ENT_QUOTES);
									$Description=htmlspecialchars($row["Description"], ENT_QUOTES);
									$Subject=htmlspecialchars($row["Subject"], ENT_QUOTES);
									$Level=htmlspecialchars($row["Level"], ENT_QUOTES);
									$Grade=htmlspecialchars($row["Grade"], ENT_QUOTES);
									$Locked=htmlspecialchars($row["Locked"], ENT_QUOTES);
									$Shared=htmlspecialchars($row["Shared"], ENT_QUOTES);
									$Verified=htmlspecialchars($row["Verified"], ENT_QUOTES);
									$Owner=htmlspecialchars($row["Owner"], ENT_QUOTES);
									$Editors=htmlspecialchars($row["Editors"], ENT_QUOTES);
									$Session_ID=htmlspecialchars($row["Session_ID"], ENT_QUOTES);

									if (strpos($Editors, $_SESSION['useremail']) !== false) { $SharedEditable=1; }else{ $SharedEditable=0; }

									$Student_Link="$portal_root/?url=assessments/session/$Assessment_ID/$Session_ID";

									echo "<tr class='assessmentrow'>";
										echo "<td>$Title</td>";
										echo "<td class='hide-on-med-and-down'>$Level</td>";
										echo "<td width=30px>";

											echo "<div class='morebutton' style='position:absolute; margin-top:-15px;'>";
												echo "<button id='demo-menu-bottom-left-$Assessment_ID' class='mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect mdl-color-text--grey-600'><i class='material-icons'>more_vert</i></button>";
												echo "<ul class='mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect' for='demo-menu-bottom-left-$Assessment_ID'>";
													if($Session_ID!="" && $Owner==$_SESSION['useremail'])
													{
														//mdl-menu__item--full-bleed-divider
														echo "<li class='mdl-menu__item copystudentlink' data-clipboard-text='$Student_Link'><a href='#' class='mdl-color-text--black' style='font-weight:400'>Give</a></li>";
														echo "<li class='mdl-menu__item mdl-menu__item--full-bleed-divider'><a href='#assessments/results/$Assessment_ID' class='mdl-color-text--black' style='font-weight:400'>Results</a></li>";
													}
													if(($SharedEditable==1 || admin() || isAssessmentAdministrator()) || $Verified==0)
													{
														echo "<li class='mdl-menu__item exploreassessment'><a href='#assessments/$Assessment_ID' class='mdl-color-text--black' style='font-weight:400'>Edit</a></li>";
													}
													if($Verified==0 || admin() || isAssessmentAdministrator())
													{
														echo "<li class='mdl-menu__item duplicateassessment' data-assessmentid='$Assessment_ID'>Make a Copy</a></li>";
													}
													if($Owner==$_SESSION['useremail'])
													{
														echo "<li class='mdl-menu__item modal-createassessment' href='#createassessment' data-title='$Title' data-description='$Description' data-subject='$Subject' data-level='$Level' data-assessmentid='$Assessment_ID' data-grade='$Grade' data-editors='$Editors' data-locked='$Locked' data-shared='$Shared' data-verified='$Verified' data-sessionid='$Session_ID' style='font-weight:400'>Settings</a></li>";

														//Check to make sure no assessment data exists if it's a district assesssment
														$sqlquestion = "SELECT COUNT(*) FROM assessments_scores WHERE Assessment_ID=$Assessment_ID";
														$resultquestioncount = $db->query($sqlquestion);
														$returnrow = $resultquestioncount->fetch_assoc();
														$rowcountresultquestioncount = $returnrow["COUNT(*)"];
														if($Verified==0 or ($Verified!=0 && $rowcountresultquestioncount==0) || admin() || isAssessmentAdministrator())
														{
															echo "<li class='mdl-menu__item deleteassessment'><a href='modules/".basename(__DIR__)."/assessment_delete.php?assessmentid=".$Assessment_ID."' class='mdl-color-text--black' style='font-weight:400'>Delete</a></li>";
														}

													}
												echo "</ul>";
											echo "</div>";

										echo "</td>";
									echo "</tr>";
								}
								echo "</tbody>";
							echo "</table>";
						echo "</div>";

					echo "</div>";

			echo "</div>";
			echo "</div>";
		}
		else
		{
			echo "<div class='row center-align'><div class='col s12'><h6>You haven't created any assessments</h6></div><div class='col s12'>Click the '+' in the bottom right to create a new assessment.</div></div>";
		}

		include "assessment_button.php";

		$db->close();
	}

?>

<script>

	//Process the profile form
	$(function()
	{

		//Start clipboard
		new Clipboard('.copystudentlink');

		//Make Explore clickable
		$(".copystudentlink").unbind().click(function(event) {
			 event.preventDefault();
			 var notification = document.querySelector('.mdl-js-snackbar');
			 var data = { message: "Student Link Copied!" };
			 notification.MaterialSnackbar.showSnackbar(data);
		});

		//Make Explore clickable
		$(".exploreassessment").unbind().click(function() {
			 window.open($(this).find("a").attr("href"), '_self');
		});

		$(document).on("click", ".modal-createassessment", function () {
			var Assessment_ID = $(this).data('assessmentid');
			$(".modal-content #assessment_id").val(Assessment_ID);
			var Session_ID = $(this).data('sessionid');
			$(".modal-content #assessment_sessionid").val(Session_ID);
			var Assessment_Title = $(this).data('title');
			$(".modal-content #assessment_title").val(Assessment_Title);
			var Assessment_Description = $(this).data('description');
			$(".modal-content #assessment_description").val(Assessment_Description);
			var Assessment_Editors = $(this).data('editors');
			$(".modal-content #assessment_editors").val(Assessment_Editors);
			var Assessment_Grade = $(this).data('grade');
			var Assessment_Verified = $(this).data('verified');
			if(Assessment_Verified=='1')
			{
				$(".modal-content #assessment_verified").prop('checked',true);
				<?php if(!admin() && !isAssessmentAdministrator()){ ?> $(".advancedsettings").css("display", "none"); $(".modal-content #assessment_verified").val(Assessment_Verified); <?php } ?>
			}
			else
			{
				$(".modal-content #assessment_verified").prop('checked',false);
				<?php if(!admin() && !isAssessmentAdministrator()){ ?> $(".advancedsettings").css("display", "block"); $(".modal-content #assessment_verified").val(Assessment_Verified); <?php } ?>
			}
			var Assessment_shared = $(this).data('shared');
			if(Assessment_shared=='1')
			{
				$(".modal-content #assessment_share").prop('checked',true);
			}
			else
			{
				$(".modal-content #assessment_share").prop('checked',false);
			}
			var Assessment_Locked = $(this).data('locked');
			if(Assessment_Locked=='1')
			{
				$(".modal-content #assessment_lock").prop('checked',true);
			}
			else
			{
				$(".modal-content #assessment_lock").prop('checked',false);
			}
			if(Assessment_Grade!="blank")
			{
				var Assessment_Grade_String=String(Assessment_Grade);
				if( Assessment_Grade_String.indexOf(',') >= 0)
				{
					var dataarrayassessment=Assessment_Grade.split(", ");
					$("#assessment_grade").val(dataarrayassessment);
				}
				else
				{
					$("#assessment_grade").val(Assessment_Grade_String);
				}
			}
			else
			{
				$("#assessment_grade").val('');
			}
			var Assessment_Subject = $(this).data('subject');
			if(Assessment_Subject!="blank")
			{
				$("#assessment_subject option[value='"+Assessment_Subject+"']").prop('selected',true);
			}
			else
			{
				$("#assessment_subject option[value='']").prop('selected',true);
			}
			var Assessment_Level = $(this).data('level');
			if(Assessment_Level!="blank")
			{
				$("#assessment_level option[value='"+Assessment_Level+"']").prop('selected',true);
			}
			else
			{
				$("#assessment_level option[value='']").prop('selected',true);
			}
		});

		//Delete assessment
		$( ".deleteassessment" ).unbind().click(function() {
			event.preventDefault();
			var result = confirm("Are you sure you want to delete this assessment?");
			if (result) {

				//Make the post request
				var address = $(this).find("a").attr("href");
				$.ajax({
					type: 'POST',
					url: address,
					data: '',
				})

				//Show the notification
				.done(function(response){

					mdlregister();
					var notification = document.querySelector('.mdl-js-snackbar');
					var data = { message: response };
					notification.MaterialSnackbar.showSnackbar(data);

					$('#content_holder').load('modules/<?php echo basename(__DIR__); ?>/assessments_display_all.php', function() { init_page(); });

				})
			}
		});

		$("#myTable").tablesorter();

	});


</script>