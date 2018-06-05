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
	require(dirname(__FILE__) . '/../../configuration.php');
	require_once('permissions.php');

	if(CONSTANT('SITE_MODE') == "DEMO"){
		echo "<div style='padding:30px; text-align:center; width:100%;'>";
			echo "<div class='row'>";
				echo "<span style='font-size: 22px; font-weight:700'>Learn more about the Assessments App!</span>";
			echo "</div>";
			echo "<div class='row center-align'>";
				echo "<iframe id='assessmentsDemoVideo' src='https://player.vimeo.com/video/261906328' width='640' height='400' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";
			echo "</div>";
			echo "<div class='row'>";
				echo "<p style='font-size:16px; margin:20px 0 0 0;'>For more information about the Abre Platform visit <a href='https://www.abre.io/' style='color:".getSiteColor().";' target='_blank'>our website</p>";
			echo "</div>";
		echo "</div>";
	}else{
		if($pagerestrictions=="")
		{

			//Layout
			echo "<div style='position: absolute; top:0; bottom:0; left:0; right:0; overflow-y: hidden;'>";

				//List Questions
				echo "<div id='assessmentquestions' style='position: absolute; top:0; bottom:0; width:300px; overflow-y: scroll; background-color:"; echo getSiteColor(); echo ";'>";

					//My Assessments
					echo "<div class='location pointer' style='padding:2px 30px 2px 30px;' data-location='assessments_myassessments'>";
						echo "<div style='float:left; padding:23px 20px 0 0;'>";
							echo "<i class='material-icons' style='color:#fff;'>person</i>";
						echo "</div>";
						echo "<div style='width:220px; color:#fff;'><h6 class='truncate'>My Assessments</h6></div>";
					echo "</div>";

					//Shared Assessments
					echo "<div class='location pointer' style='padding:2px 30px 2px 30px;' data-location='assessments_sharedwithme'>";
						echo "<div style='float:left; padding:23px 20px 0 0;'>";
							echo "<i class='material-icons' style='color:#fff;'>people</i>";
						echo "</div>";
						echo "<div style='width:220px; color:#fff;'><h6 class='truncate'>Shared with me</h6></div>";
					echo "</div>";

					//Recommended
					echo "<div class='location pointer' style='padding:2px 30px 2px 30px;' data-location='assessments_recommended'>";
						echo "<div style='float:left; padding:23px 20px 0 0;'>";
							echo "<i class='material-icons' style='color:#fff;'>thumb_up</i>";
						echo "</div>";
						echo "<div style='width:220px; color:#fff;'><h6 class='truncate'>Recommended</h6></div>";
					echo "</div>";

					//District Created
					echo "<div class='location pointer' style='padding:2px 30px 2px 30px;' data-location='assessments_district'>";
						echo "<div style='float:left; padding:23px 20px 0 0;'>";
							echo "<i class='material-icons' style='color:#fff;'>verified_user</i>";
						echo "</div>";
						echo "<div style='width:220px; color:#fff;'><h6 class='truncate'>District</h6></div>";
					echo "</div>";

					//Public
					echo "<div class='location pointer' style='padding:2px 30px 2px 30px;' data-location='assessments_public'>";
						echo "<div style='float:left; padding:23px 20px 0 0;'>";
							echo "<i class='material-icons' style='color:#fff;'>public</i>";
						echo "</div>";
						echo "<div style='width:220px; color:#fff;'><h6 class='truncate'>Public</h6></div>";
					echo "</div>";

					//Settings
					if(superadmin())
					{
						echo "<div class='location pointer' style='padding:2px 30px 2px 30px;' data-location='settings'>";
							echo "<div style='float:left; padding:23px 20px 0 0;'>";
								echo "<i class='material-icons' style='color:#fff;'>settings</i>";
							echo "</div>";
							echo "<div style='width:220px; color:#fff;'><h6 class='truncate'>Settings</h6></div>";
						echo "</div>";
					}

				echo "</div>";

				//Dashboard Data
				echo "<div id='overview' style='position:absolute; width: calc(100% - 305px); left:305px; top:0; bottom:0; right:0; overflow-y: scroll; padding:20px;'>";
					echo "<div id='p2' class='mdl-progress mdl-js-progress mdl-progress__indeterminate landingloader' style='width:100%;'></div>";
					echo "<div class='dashboard'></div>";
				echo "</div>";

			echo "</div>";

		}
	}

?>

<script>

	$(function()
	{

		//Load the first question
		$(".dashboard").load('modules/<?php echo basename(__DIR__); ?>/assessments_myassessments.php', function()
		{
			mdlregister();
			$(".landingloader").hide();
			$(".location:first").css("background-color", "#000");
			$(".location:first").css("color", "#fff");
		});

		//Load Question in Window
		$(".location").unbind().click(function()
		{
			$(".dashboard").fadeTo(0,0);
			$(".landingloader").show();
			$(".location, #overviewpage").css("background-color", "");
			$(".location, #overviewpage").css("color", "#fff");
			$(this).css("background-color", "#000");
			$(this).css("color", "#fff");
			var Location= $(this).data('location');

			$(".dashboard").load('modules/<?php echo basename(__DIR__); ?>/'+Location+'.php', function()
			{
				mdlregister();
				$(".landingloader").hide();
				$('.mdl-layout__content, .dashboard').animate({scrollTop:0}, 0);
				$(".dashboard").fadeTo(0,1);
			});
		});

	});

	//Check Window Width
	if ($(window).width() < 600){ smallView(); }
	$(window).resize(function(){ if ($(window).width() < 600){ smallView(); } if ($(window).width() >= 600){ largeView(); } });
	function smallView()
	{
		$("#assessmentquestions").css("display", "none");
		$("#overview").css("width", "100%");
		$("#overview").css("left", "0");
	}
	function largeView()
	{
		$("#assessmentquestions").css("display", "block");
		$("#overview").css("width", "calc(100% - 305px)");
		$("#overview").css("left", "305px");
	}

</script>