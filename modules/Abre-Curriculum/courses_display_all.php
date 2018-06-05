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
	require_once('../../core/abre_functions.php');
	require_once('permissions.php');

	if($pagerestrictions == ""){

		//get the current page the user is on
		if(isset($_POST["page"])){
			if($_POST["page"] == ""){
				$PageNumber = 1;
			}else{
				$PageNumber = $_POST["page"];
			}
		}else{
			$PageNumber = 1;
		}

		$PerPage = 10;

		//set bounds for pagination
		$LowerBound = $PerPage * ($PageNumber - 1);
		$UpperBound = $PerPage * $PageNumber;

		if(isset($_POST["searchquery"])){
			$searchquery = strtolower(mysqli_real_escape_string($db, $_POST["searchquery"]));
		}else{
			$searchquery = "";
		}

		if($searchquery == ""){
			if($pagerestrictionsedit == ""){
				$querycount = "SELECT COUNT(*) FROM curriculum_course";
				$sql = "SELECT ID, Hidden, Title, Subject, Grade, Image, Editors FROM curriculum_course ORDER BY Title LIMIT $LowerBound, $PerPage";
			}else{
				$querycount = "SELECT COUNT(*) FROM curriculum_course WHERE Hidden = '0'";
				$sql = "SELECT ID, Hidden, Title, Subject, Grade, Image, Editors FROM curriculum_course WHERE Hidden = '0' ORDER BY Title LIMIT $LowerBound, $PerPage";
			}
		}else{
			if($pagerestrictionsedit == ""){
				$querycount = "SELECT COUNT(*) FROM curriculum_course WHERE (LOWER(Title) LIKE '%$searchquery%' OR LOWER(Subject) = '$searchquery')";
				$sql = "SELECT ID, Hidden, Title, Subject, Grade, Image, Editors FROM curriculum_course WHERE (LOWER(Title) LIKE '%$searchquery%' OR LOWER(Subject) = '$searchquery') ORDER BY Title LIMIT $LowerBound, $PerPage";
			}else{
				$querycount = "SELECT COUNT(*) FROM curriculum_course WHERE Hidden = '0' AND (LOWER(Title) LIKE '%$searchquery%' OR LOWER(Subject) = '$searchquery')";
				$sql = "SELECT ID, Hidden, Title, Subject, Grade, Image, Editors FROM curriculum_course WHERE Hidden = '0'AND (LOWER(Title) LIKE '%$searchquery%' OR LOWER(Subject) = '$searchquery') ORDER BY Title LIMIT $LowerBound, $PerPage";
			}
		}

		$result = $db->query($sql);
		$totalcoursecount=mysqli_num_rows($result);
		$coursecounter=0;
		while($row = $result->fetch_assoc()){

			$coursecounter++;

			if($coursecounter == 1){
			?>
				<div class='page_container mdl-shadow--4dp'>
				<div class='page'>
				<div id='searchresults'>
				<div class='row'><div class='col s12'>
				<table id='myTable' class='highlight'>
				<thead>
				<tr>
				<th class='hide-on-med-and-down'></th>
				<th>Title</th>
				<th class='hide-on-med-and-down'>Subject</th>
				<th>Grade Level</th>
				<th style='width:30px'></th>
				</tr>
				</thead>
				<tbody>
			<?php
			}

			$Course_ID = htmlspecialchars($row["ID"], ENT_QUOTES);
			$Course_Hidden = htmlspecialchars($row["Hidden"], ENT_QUOTES);
			$Title = htmlspecialchars($row["Title"], ENT_QUOTES);
			$Subject = htmlspecialchars($row["Subject"], ENT_QUOTES);
			$Grade = htmlspecialchars($row["Grade"], ENT_QUOTES);
			$Image = htmlspecialchars($row["Image"], ENT_QUOTES);
			$Editors = htmlspecialchars($row["Editors"], ENT_QUOTES);
			if($Image == ""){ $Image = "course.jpg"; }

			echo "<tr class='courserow pointer'>";
				echo "<td class='hide-on-med-and-down explorecourse' data-href='#curriculum/0/$Course_ID'>";
					echo "<img src='$portal_root/modules/".basename(__DIR__)."/images/$Image' class='profile-avatar-small' style='object-fit:cover;'>";
					echo "</td>";
					echo "<td class='explorecourse' data-href='#curriculum/0/$Course_ID'>$Title</td>";
					echo "<td class='hide-on-med-and-down explorecourse' data-href='#curriculum/0/$Course_ID'>$Subject</td>";
					echo "<td class='explorecourse' data-href='#curriculum/0/$Course_ID'>$Grade</td>";
					echo "<td style='width:30px;'>";

					include "../../core/abre_dbconnect.php";
					$userid = finduseridcore($_SESSION['useremail']);
					$sqllookup = "SELECT COUNT(*) FROM curriculum_libraries WHERE User_ID = '$userid' AND Course_ID = '$Course_ID'";
					$result2 = $db->query($sqllookup);
					$resultrow = $result2->fetch_assoc();
					$foundcourse = $resultrow["COUNT(*)"];

					echo "<div class='morebutton' style='position:relative;'>";
						echo "<button id='demo-menu-bottom-left-$Course_ID' class='mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect mdl-color-text--grey-600'><i class='material-icons'>more_vert</i></button>";
						echo "<ul class='mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect' for='demo-menu-bottom-left-$Course_ID'>";
						if($foundcourse == 0){
							echo "<li class='mdl-menu__item addcourse'><a href='modules/".basename(__DIR__)."/course_addlibrary.php?librarycourseid=".$Course_ID."' class='mdl-color-text--black' style='font-weight:400'>Add to My Courses</a></li>";
						}

						if($pagerestrictionsedit == ""){
							echo "<li class='mdl-menu__item modal-addcourse' href='#curriculumcourse' data-title='$Title' data-grade='$Grade' data-subject='$Subject' data-courseid='$Course_ID' data-editors='$Editors' data-coursehidden='$Course_Hidden' style='font-weight:400'>Edit</a></li>";
							echo "<li class='mdl-menu__item duplicatecourse' data-courseid='$Course_ID'>Duplicate</li>";
							echo "<li class='mdl-menu__item deletecourse' data-courseid='$Course_ID'>Delete</li>";
						}
						echo "</ul>";
					echo "</div>";
				echo "</td>";
			echo "</tr>";
		}

		if($totalcoursecount == $coursecounter){
			echo "</tbody>";
			echo "</table>";
			echo "</div>";
			echo "</div>";

			//getting count for pagination
			$result = $db->query($querycount);
			$dbreturnpossible = $result->fetch_assoc();
			$totalpossibleresults = $dbreturnpossible["COUNT(*)"];

			//Paging
			$totalpages = ceil($totalpossibleresults / $PerPage);
			if($totalpossibleresults > $PerPage){
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
						if($PageNumber != 1){ echo "<li class='pagebutton' data-page='$previouspage'><a href='#'><i class='material-icons'>chevron_left</i></a></li>"; }
						for($x = $pagingstart; $x <= $pagingend; $x++){
							if($PageNumber == $x){
								echo "<li class='active pagebutton' style='background-color: ".getSiteColor().";' data-page='$x'><a href='#'>$x</a></li>";
							}else{
								echo "<li class='waves-effect pagebutton' data-page='$x'><a href='#'>$x</a></li>";
							}
						}
						if($PageNumber != $totalpages){ echo "<li class='waves-effect pagebutton' data-page='$nextpage'><a href='#'><i class='material-icons'>chevron_right</i></a></li>"; }
					echo "</ul>";
				echo "</div>";
			}
			echo "</div>";
			echo "</div>";
		}

		if($totalcoursecount == 0){
			echo "<div class='row' style='padding:56px; text-align:center; width:100%;'><span style='font-size: 22px; font-weight:700'>No Courses Found</span><br><p style='font-size:16px; margin:20px 0 0 0;'>Click the '+' in the bottom right to create a course.</p></div>";
		}

	}

?>

<script>

	//Process the profile form
	$(function(){

		//Make Explore clickable
		$(".explorecourse").unbind().click(function() {
			 window.open($(this).data('href'), '_self');
		});

		//Duplicate course
		$(".duplicatecourse").unbind().click(function(event){
			event.preventDefault();
			var CourseIDDuplicate = $(this).data('courseid');
			$.ajax({
				type: 'POST',
				url: 'modules/<?php echo basename(__DIR__); ?>/course_duplicate_process.php',
				data: { courseIDduplicateid : CourseIDDuplicate }
			})
			.done(function(response) {
				$("#displaycourses").load( "modules/<?php echo basename(__DIR__); ?>/courses_display_all.php", {page: '<?php echo $PageNumber ?>', searchquery: '<?php echo $searchquery ?>'}, function(){
					mdlregister();
					var notification = document.querySelector('.mdl-js-snackbar');
					var data = { message: response };
					notification.MaterialSnackbar.showSnackbar(data);
				});
			})
		});

		//Delete course
		$(".deletecourse").unbind().click(function(event){
			event.preventDefault();
			var Course_ID = $(this).data('courseid');
			var result = confirm("Delete the entire course?");
			if (result) {

				var address = $(this).find("a").attr("href");
				$.ajax({
					type: 'POST',
					url: 'modules/<?php echo basename(__DIR__); ?>/course_delete.php?librarycourseid='+Course_ID,
					data: '',
				})

				//Show the notification
				.done(function(response) {

					$( "#displaycourses" ).load( "modules/<?php echo basename(__DIR__); ?>/courses_display_all.php", {page: '<?php echo $PageNumber ?>', searchquery: '<?php echo $searchquery ?>'}, function(){
						//Register MDL Components
						mdlregister();
						var notification = document.querySelector('.mdl-js-snackbar');
						var data = { message: response };
						notification.MaterialSnackbar.showSnackbar(data);
					});
				})
			}
		});

		$(".modal-addcourse").off().on("click", function () {
			var Course_Hidden = $(this).data('coursehidden');
			if(Course_Hidden == '1'){
				$(".modal-content #course_hidden").prop('checked',true);
			}else{
				$(".modal-content #course_hidden").prop('checked',false);
			}
			var Course_ID = $(this).data('courseid');
			$(".modal-content #course_id").val(Course_ID);
			var Course_Title = $(this).data('title');
			$(".modal-content #course_title").val(Course_Title);
			var Course_Grade = $(this).data('grade');
			var Course_Editors = $(this).data('editors');
			$(".modal-content #course_editors").val(Course_Editors);
			if(Course_Grade != "blank"){
				var Course_Grade_String=String(Course_Grade);
				if( Course_Grade_String.indexOf(',') >= 0){
					var dataarraycourse=Course_Grade.split(", ");
					$("#course_grade").val(dataarraycourse);
				}else{
					$("#course_grade").val(Course_Grade_String);
				}
			}else{
				$("#course_grade").val('');
			}
			var Course_Subject = $(this).data('subject');
			if(Course_Subject != "blank"){
				$("#course_subject option[value='"+Course_Subject+"']").prop('selected',true);
			}else{
				$("#course_subject option[value='']").prop('selected',true);
			}

			$('#curriculumcourse').openModal({
				in_duration: 0,
				out_duration: 0,
				ready: function() { $("#course_title").focus(); }
			});

		});

		//Add course
		$( ".addcourse" ).unbind().click(function(event) {
			event.preventDefault();
			$(this).hide();
			var address = $(this).find("a").attr("href");
			$.ajax({
				type: 'POST',
				url: address,
				data: '',
			})

			//Show the notification
			.done(function(response) {
				mdlregister();
				var notification = document.querySelector('.mdl-js-snackbar');
				var data = { message: response };
				notification.MaterialSnackbar.showSnackbar(data);
			})

		});

	});


</script>
