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

?>

<script>

		//Give Assessment
		$('.modal-giveassessment').leanModal({
	    	in_duration: 0,
			out_duration: 0
    	});

    	//Copy the link
    	$(document).off().on("click", ".modal-giveassessment", function ()
    	{
	    	var GiveLink = $(this).data('givelink');
			$(".modal-content #GiveLink").val(GiveLink);
			var GiveTitle = $(this).data('givetitle');
			$(".modal-content #GiveLinkTitle").text(GiveTitle);
			var GiveGCTitle = $(this).data('givegctitle');
			$(".modal-content #GiveLink").select();

			if(GiveTitle!="Guided Learning Code")
			{
				$(".modal-content #gccontent_title").show();
				$(".modal-content #gccontent").show();
				gapi.sharetoclassroom.render("gccontent",{"url": GiveLink, "title": GiveGCTitle, "body": "Please take the linked assessment"} );
			}
			else
			{
				$(".modal-content #gccontent_title").hide();
				$(".modal-content #gccontent").hide();
			}

	    });

		//Make Explore clickable
		$(".clicklink").unbind().click(function() {
			 window.open($(this).find("a").attr("href"), '_self');
		});

		$(".buildassessment").unbind().click(function() {
			window.open($(this).data('href'), '_self');
		})

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


		//Duplicate Assessment
		$(".duplicateassessment").unbind().click(function(event)
		{
			event.preventDefault();
			var AssessmentIDDuplicate = $(this).data('assessmentid');
			$.ajax({
				type: 'POST',
				url: 'modules/<?php echo basename(__DIR__); ?>/assessment_duplicate.php',
				data: { assessmentIDduplicateid : AssessmentIDDuplicate }
			})
			.done(function(response) {
				$("#content_holder").load( "modules/<?php echo basename(__DIR__); ?>/assessments_display_all.php", function(){
					mdlregister();
					var notification = document.querySelector('.mdl-js-snackbar');
					var data = { message: response };
					notification.MaterialSnackbar.showSnackbar(data);
				});
			})
		});

		//Give Assessment
		$(".giveassessment").unbind().click(function(event)
		{
			event.preventDefault();
			var AssessmentIDDuplicate = $(this).data('assessmentid');
			$.ajax({
				type: 'POST',
				url: 'modules/<?php echo basename(__DIR__); ?>/assessment_give.php',
				data: { assessmentIDduplicateid : AssessmentIDDuplicate }
			})
			.done(function(response) {
				$(location).attr('href', '#assessments/sessions');
			})
		});

		$("#myTable").tablesorter();

</script>