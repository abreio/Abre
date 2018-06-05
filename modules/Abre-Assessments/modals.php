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
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');
	require_once('permissions.php');
	require_once('functions.php');

	if($pagerestrictions=="")
	{

?>

	<script src="https://apis.google.com/js/platform.js" async defer></script>

	<!-- Create Assessment -->
	<div id="createassessment" class="modal modal-fixed-footer modal-mobile-full">
		<form class="col s12" id="form-addassessment" method="post" action="modules/<?php echo basename(__DIR__); ?>/assessment_process.php">
		<div class="modal-content" style="padding: 0px !important;">
      <div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
        <div class='col s11'><span class="truncate" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">Assessment</span></div>
        <div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
      </div>
      <div style='padding: 0px 24px 0px 24px;'>
  			<div class="row">
  				<div class="input-field col s12">
            <input id="assessment_title" name="assessment_title" placeholder="Title of the Assessment" type="text" autocomplete="off" required>
            <label for="assessment_title" class="active">Title of the Assessment</label>
          </div>
  			</div>
  			<div class="row">
  				<div class="input-field col s12">
            <textarea id="assessment_description" name="assessment_description" class="materialize-textarea" placeholder="Description of the Assessment"></textarea>
            <label for="assessment_description" class="active">Description of the Assessment</label>
          </div>
  			</div>
  			<div class="row">
  			<div class="input-field col s4">
  				<p class='black-text' style="font-weight: 500;">Grade Level</p>
  				<select name='assessment_grade[]' id='assessment_grade' class="browser-default" style='height: 100px;' required='required' multiple>
  					<option value='K'>K</option>
  				    <option value='1'>1</option>
  				    <option value='2'>2</option>
  				    <option value='3'>3</option>
  				    <option value='4'>4</option>
  					  <option value='5'>5</option>
  				    <option value='6'>6</option>
  				    <option value='7'>7</option>
  				    <option value='8'>8</option>
  				    <option value='9'>9</option>
  				    <option value='10'>10</option>
  				    <option value='11'>11</option>
  				    <option value='12'>12</option>
  			    </select>
  			</div>
  			<div class="input-field col s4">
  				<p class='black-text' style="font-weight: 500;">Subject</p>
  				<select name='assessment_subject' id='assessment_subject' class="browser-default" required>
  					<option value=''></option>
  					<option value='English Language Arts'>English Language Arts</option>
  					<option value='Mathematics'>Mathematics</option>
  				    <option value='Science'>Science</option>
  				    <option value='Social Studies'>Social Studies</option>
  				    <option value='Miscellaneous'>Miscellaneous</option>
  			    </select>
  			</div>
  			<div class="input-field col s4">
  				<p class='black-text' style="font-weight: 500;">Level</p>
  				<select name='assessment_level' id='assessment_level' class="browser-default" required>
  					<option value=''></option>
  					<option value='Core'>Core</option>
  					<option value='College Preparatory'>College Preparatory</option>
  				    <option value='Honors'>Honors</option>
  			    </select>
  			</div>
  			</div>

  			<div class="row">
  				<div class="input-field col s12">
            <input id="assessment_editors" name="assessment_editors" placeholder="Assessment Editors (Emails Separated by Commas)" type="text" autocomplete="off">
            <label for="assessment_editors" class="active">Assessment Editors</label>
          </div>
  			</div>

        <div class="row">
    			<div class="col s12">
    				<input type="checkbox" class="filled-in" id="assessment_lock" name="assessment_lock" value="1" />
    				<label for="assessment_lock">Lock Assessment - This removes all editing access from this assessment.</label>
    			</div>
        </div>

        <div class="row">
    			<div class="col s12">
    				<input type="checkbox" class="filled-in" id="assessment_share" name="assessment_share" value="1" />
    				<label for="assessment_share">Public Assessment - Allows other teachers to find and use this assessment.</label>
    			</div>
        </div>

  			<?php
  			if(admin() || isAssessmentAdministrator())
  			{
          echo "<div class='row'>";
    				echo "<div class='col s12'>";
    					echo "<input type='checkbox' class='filled-in' id='assessment_verified' name='assessment_verified' value='1' />";
    					echo "<label for='assessment_verified'>District Assessment - Marks the assessment as a district created assessment.</label>";
    				echo "</div>";
          echo "</div>";
  			}
  			?>

  			<input type="hidden" name="assessment_id" id="assessment_id">
      </div>
    </div>
	  <div class="modal-footer">
      <button type="submit" class="modal-action waves-effect btn-flat white-text" style='margin-left:5px; background-color: <?php echo getSiteColor(); ?>'>Save</button>
			<a class="modal-close waves-effect btn-flat white-text" style='background-color: <?php echo getSiteColor(); ?>'>Cancel</a>
		</div>
		</form>
	</div>

	<!-- Add Question -->
	<div id="assessmentquestion" class="fullmodal modal modal-fixed-footer modal-mobile-full">
		<form class="col s12" id="form-addquestion" method="post" action="">
		<div class="modal-content" style="padding: 0px !important;">
      <div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
        <div class='col s11'><span class="truncate" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">Question Bank</span></div>
        <div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
      </div>

      <div style='padding: 0px 24px 0px 24px;'>
  			<!--Tabs-->
  			<div class="row">
  			<ul class="tabs" style='background-color: <?php echo getSiteColor(); ?>'>
  		    	<li class="tab col s3"><a class="active" href="#filter">Filter</a></li>
  		        <li class="tab col s3"><a href="#search">Search</a></li>
  		    </ul>
  			</div>

  			<div class="row" id="filter">
  				<div class="input-field col l3 m6">
  					<select name='question_subject' id='question_subject' required>
  						<option value='' selected='selected' disabled>Select a Subject</option>
  						<option value='Language Arts'>Language Arts</option>
  						<option value='Math'>Math</option>
  						<option value='Science'>Science</option>
  						<option value='History/Social Studies'>Social Studies</option>
  				    </select>
  				    <label>Subject</label>
  				</div>
  				<div class="input-field col l3 m6">
  					<div id="choosestandard">
  						<?php include "standard_choices.php"; ?>
  					</div>
  				</div>
  				<div class="input-field col l3 m6">
  					<select name='question_grade' id='question_grade' required>
  						<option value='' selected='selected'></option>
  						<option value='Grade K'>K</option>
  						<option value='Grade 01'>1</option>
  						<option value='Grade 02'>2</option>
  						<option value='Grade 03'>3</option>
  						<option value='Grade 04'>4</option>
  						<option value='Grade 05'>5</option>
  						<option value='Grade 06'>6</option>
  						<option value='Grade 07'>7</option>
  						<option value='Grade 08'>8</option>
  						<option value='Grade 09'>9</option>
  						<option value='Grade 10'>10</option>
  						<option value='Grade 11'>11</option>
  						<option value='Grade 12'>12</option>
  						<option value='Grades 09-12'>9-12</option>
  				    </select>
  				    <label>Grade</label>
  				</div>
  				<div class="input-field col l3 m6">
  					<select name='question_difficulty' id='question_difficulty' required>
  						<option value='' selected='selected'></option>
  						<option value='Low'>Low</option>
  						<option value='Medium'>Medium</option>
  						<option value='High'>High</option>
  				    </select>
  				    <label>Difficulty</label>
  				</div>
  				<div class="input-field col l3 m6">
  					<select name='question_itemtype' id='question_itemtype' required>
  						<option value='' selected='selected'></option>
  						<option value='MC'>Multiple Choice</option>
  						<option value='CM'>Choice Multiple</option>
  						<option value='GM'>Gap Match</option>
  						<option value='GR'>Graphic Gap Match</option>
  						<option value='HS'>Hot Spot</option>
  						<option value='HT'>Hot Text</option>
  						<option value='IC'>Inline Choice</option>
  						<option value='OD'>Order</option>
  						<option value='OR'>Open Response</option>
  						<option value='TE'>Text Entry</option>
  				    </select>
  				    <label>Question Type</label>
  				</div>
  				<div class="input-field col l3 m6">
  					<select name='question_blooms' id='question_blooms' required>
  						<option value='' selected='selected'></option>
  						<option value='Remembering'>Remembering</option>
  						<option value='Understanding'>Understanding</option>
  						<option value='Applying'>Applying</option>
  						<option value='Analyzing'>Analyzing</option>
  						<option value='Evaluating'>Evaluating</option>
  						<option value='Creating'>Creating</option>
  				    </select>
  				    <label>Blooms</label>
  				</div>
  				<div class="input-field col l3 m6">
  					<select name='question_dok' id='question_dok' required>
  						<option value='' selected='selected'></option>
  						<option value='I'>Recall</option>
  						<option value='II'>Skill/Concept</option>
  						<option value='III'>Strategic Thinking</option>
  						<option value='IV'>Extended Thinking</option>
  				    </select>
  				    <label>Depth of Knowledge</label>
  				</div>
  				<div class="input-field col l3 m6">
  					<select name='question_language' id='question_language' required>
  						<option value='' selected='selected'></option>
  						<option value='English'>English</option>
  						<option value='Spanish'>Spanish</option>
  				    </select>
  				    <label>Language</label>
  				</div>
  			</div>

  			<div class="row" id="search">
  				<div class="input-field col s12">
  					<input id="searchquery" type="text" autocomplete="off">
  					<label for="searchquery" class="active">Search</label>
  				</div>
  			</div>

  			<div class="row">
  			<div class="input-field col s12">
  				<div id="topicLoader"><div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate" style="width:100%;"></div></div>
  				<div id="topicFiles"></div>
  			</div>
  			</div>
  			<input type="hidden" name="AssessmentID" id="AssessmentID">
      </div>
    </div>
	  <div class="modal-footer">
      <a class="modal-close waves-effect btn-flat white-text" style='background-color: <?php echo getSiteColor(); ?>'>Close</a>
		</div>
		</form>
	</div>

	<!-- Question Preview -->
	<div id="linktotopic" class="fullmodal modal modal-fixed-footer modal-mobile-full" style="width: 80%">
		<form class="col s12" id="form-addlinktotopic" method="post" action="">
		<div class="modal-content" style="padding: 0px !important;">
      <div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
        <div class='col s11'><span class="truncate" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">Question Preview</span></div>
        <div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
      </div>
      <div style='padding: 0px 24px 0px 24px;'>
				<div class='row' id='previewmeta'>
					<div class='col l2 m4 s6'><span class='grey-text'>ID</span><div style='margin-top:5px; color: <?php echo getSiteColor(); ?>' id='preview_questionid'></div></div>
					<div class='col l2 m4 s6'><span class='grey-text'>Subject</span><div style='margin-top:5px; color: <?php echo getSiteColor(); ?>' id='preview_subject'></div></div>
					<div class='col l2 m4 s6'><span class='grey-text'>Type</span><div style='margin-top:5px; color: <?php echo getSiteColor(); ?>' id='preview_questiontype'></div></div>
					<div class='col l2 m4 s6'><span class='grey-text'>Grade</span><div style='margin-top:5px; color: <?php echo getSiteColor(); ?>' id='preview_grade'></div></div>
					<div class='col l2 m4 s6'><span class='grey-text'>Difficulty</span><div style='margin-top:5px; color: <?php echo getSiteColor(); ?>' id='preview_difficulty'></div></div>
					<div class='col l2 m4 s6'><span class='grey-text'>Blooms</span><div style='margin-top:5px; color: <?php echo getSiteColor(); ?>' id='preview_blooms'></div></div>
				</div>
				<hr>
				<div id='questionholder'></div>
				<input type="hidden" name="AssessmentID" id="AssessmentID">
				<input type="hidden" name="QuestionID" id="QuestionID">
				<input type="hidden" name="VendorID" id="VendorID">
				<input type="hidden" name="Type" id="Type">
				<input type="hidden" name="Difficulty" id="Difficulty">
				<input type="hidden" name="StandardCode" id="StandardCode">
      </div>
    </div>
	  <div class="modal-footer">
      <a class="modal-close waves-effect btn-flat white-text" style='background-color: <?php echo getSiteColor(); ?>'>Close</a>
		  <a class="modal-close waves-effect btn-flat white-text addquestiontoassessmentpreview" style='margin-right:5px; background-color: <?php echo getSiteColor(); ?>'>Add</a>
		</div>
		</form>
	</div>

	<!-- Question Response View -->
	<div id="questionresponse" class="fullmodal modal modal-fixed-footer modal-mobile-full" style="width: 80%">
		<form class="col s12" id="form-addlinktotopic" method="post" action="">
		<div class="modal-content" style="padding: 0px !important;">
      <div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
        <div class='col s11'><span class="truncate" id="questionresponse_title" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">/span></div>
        <div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
      </div>
      <div style='padding: 0px 24px 0px 24px;'>
  			<div id='questionholderresponse'></div>
  			<div id='questionresponse_score'></div>
    	</div>
    </div>
	</div>

	<!-- Give Assessment -->
	<div id="giveassessment" class="modal">
		<div class="modal-content" style="padding: 0px !important;">
      <div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
        <div class='col s11'><span class="truncate" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">Give Assessment</span></div>
        <div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
      </div>
      <div style='padding: 0px 24px 0px 24px;'>
  			<div class='input-field'>
  				<h6 style='margin-bottom:0;' name="GiveLinkTitle" id="GiveLinkTitle"></h6>
  				<input type="text" autocomplete="off" name="GiveLink" id="GiveLink">
  				<h6 id="gccontent_title">Share via Google Classroom</h6>
  				<div id="gccontent">
  					<div class="g-sharetoclassroom" data-size="32" data-url="..." data-title="..."></div>
      			</div>
  			</div>
      </div>
    </div>
	  <div class="modal-footer">
      <a class="modal-close waves-effect btn-flat white-text" style='margin-right:5px; background-color: <?php echo getSiteColor(); ?>'>Close</a>
		</div>
	</div>

	<!-- Teacher Details View -->
	<div id="teacherdetails" class="fullmodal modal modal-fixed-footer modal-mobile-full" style="width: 95%">
		<div class="modal-content" style="padding: 0px !important;">
      <div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
        <div class='col s11'><span class="truncate" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">Assessment Details</span></div>
        <div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
      </div>
      <div style='padding: 0px 24px 0px 24px;'>
  			<div id="teacherdetailsloader"><div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate" style="width:100%;"></div></div>
  			<div id='teacherdetailsfilldiv'></div>
      </div>
    </div>
	</div>

<?php
	}
?>

<script>

	$(function()
	{

		//Load MDL
		mdlregister();

		//Tabs
    	$('ul.tabs').tabs();

		//Hide modal loader
		$("#topicLoader").hide();

		//Search Delay
		var delay = (function(){
		  var timer = 0;
		  return function(callback, ms){
		    clearTimeout (timer);
		    timer = setTimeout(callback, ms);
		  };
		})();

		//Material dropdown
		$('#question_subject, #question_grade, #question_difficulty, #question_itemtype, #question_blooms, #question_dok, #question_language').material_select();

		//Add or Edit a Assessment
		$('#form-addassessment').submit(function(event)
		{
			event.preventDefault();

			var form = $('#form-addassessment');
			var formMessages = $('#form-messages');

			$('#createassessment').closeModal({
				in_duration: 0,
				out_duration: 0,
			});
			var formData = $(form).serialize();
			$.ajax({
				type: 'POST',
				url: $(form).attr('action'),
				data: formData
			})

			//Show the notification
			.done(function(response) {
				//$("input").val('');
				$("#content_holder").load( "modules/<?php echo basename(__DIR__); ?>/assessments_display_all.php", function(){

					mdlregister();

					var notification = document.querySelector('.mdl-js-snackbar');
					var data = { message: response };
					notification.MaterialSnackbar.showSnackbar(data);

				});

			})
		});

		//Add question to assessment
		$( ".addquestiontoassessmentpreview" ).unbind().click(function()
		{

			event.preventDefault();
			$(this).hide();

			//Add to the assessment
			var AssessmentID = $('#AssessmentID').val();
			var QuestionID = $('#QuestionID').val();
			var VendorID = $('#VendorID').val();
			var Type = $('#Type').val();
			var Difficulty = $('#Difficulty').val();
			var StandardCode = $('#StandardCode').val();
			var address= "/modules/<?php echo basename(__DIR__); ?>/question_add_process.php?assessmentid="+AssessmentID+"&questionid="+QuestionID+"&vendorid="+VendorID+"&type="+Type+"&difficulty="+Difficulty+"&standard="+StandardCode;
			$.ajax({
				type: 'POST',
				url: address,
				data: '',
			})

			//Hide Add buttons
			$('#questionplus-'+QuestionID).hide();

		});

    	//Question Search/Filter
    	$('#question_subject').change(function()
    	{
	    	var question_subject = $('#question_subject').val();
	    	question_subject = btoa(question_subject);
	    	$('#question_standard').prop('selectedIndex', 0);
	    	//Update the available Standards
	    	$("#choosestandard").load( "modules/<?php echo basename(__DIR__); ?>/standard_choices.php?subject="+question_subject );

		});

    	//Question Search/Filter
    	$('#question_subject, #question_grade, #question_difficulty, #question_itemtype, #question_blooms, #question_dok, #question_language, #choosestandard').change(function()
    	{
	    	var question_subject = $('#question_subject').val();
	    	question_subject = btoa(question_subject);
	    	var question_grade = $('#question_grade').val();
	    	question_grade = btoa(question_grade);
	    	var question_difficulty = $('#question_difficulty').val();
	    	question_difficulty = btoa(question_difficulty);
	    	var question_itemtype = $('#question_itemtype').val();
	    	question_itemtype = btoa(question_itemtype);
	    	var question_blooms = $('#question_blooms').val();
	    	question_blooms = btoa(question_blooms);
	    	var question_dok = $('#question_dok').val();
	    	question_dok = btoa(question_dok);
	    	var question_language = $('#question_language').val();
	    	question_language = btoa(question_language);
	    	var question_standard = $('#question_standard').val();
	    	question_standard = btoa(question_standard);
	    	var AssessmentID = $('#AssessmentID').val();

			$("#topicFiles").hide();
			$("#topicLoader").show();
			$("#topicFiles").load('modules/<?php echo basename(__DIR__); ?>/questions_list_questions.php?assessmentid='+AssessmentID+'&subject='+question_subject+"&grade="+question_grade+"&difficulty="+question_difficulty+"&type="+question_itemtype+"&blooms="+question_blooms+"&dok="+question_dok+"&language="+question_language+"&standard="+question_standard, function() {
				$("#topicLoader").hide();
				$("#topicFiles").show();
			});
		});

		//Question Search
		$("#searchquery").keyup(function()
		{

			$("#topicFiles").hide();
			$("#topicLoader").show();

			delay(function()
	    	{
				var search_query = $('#searchquery').val();
				var AssessmentID = $('#AssessmentID').val();
		    	search_query = btoa(search_query);
				$("#topicFiles").load('modules/<?php echo basename(__DIR__); ?>/questions_list_questions.php?assessmentid='+AssessmentID+'&searchquery='+search_query, function() {
					$("#topicLoader").hide();
					$("#topicFiles").show();
				});
			}, 500 );
		});

		//Change Tabs
		$('ul.tabs').on('click', 'a', function(e) {
			//Hide Content
		    $("#topicFiles").hide();
		    //Clear Inputs
		    $('#searchquery').val('');
		    $('select').prop('selectedIndex', 0);
			$('select').material_select();
		});

		//Fill in Text Topic Data
		$(document).on("click", ".modal-addquestion", function ()
		{
			var Assessment_ID = $(this).data('assessmentid');
		    $(".modal-content #AssessmentID").val(Assessment_ID);
		});

		//Change pages
		$(document).on( "click", ".pagebutton", function(event)
		{
			event.preventDefault();

			//Move to top of modal and get page number
			$('.modal-content').scrollTop(0);
			var Page = $(this).data('page');

			//Reload div with new page update
			$("#topicFiles").hide();
			$("#topicLoader").show();

	    	var question_subject = $('#question_subject').val();
	    	question_subject = btoa(question_subject);
	    	var question_grade = $('#question_grade').val();
	    	question_grade = btoa(question_grade);
	    	var question_difficulty = $('#question_difficulty').val();
	    	question_difficulty = btoa(question_difficulty);
	    	var question_itemtype = $('#question_itemtype').val();
	    	question_itemtype = btoa(question_itemtype);
	    	var question_blooms = $('#question_blooms').val();
	    	question_blooms = btoa(question_blooms);
	    	var question_dok = $('#question_dok').val();
	    	question_dok = btoa(question_dok);
	    	var question_language = $('#question_language').val();
	    	question_language = btoa(question_language);
	    	var question_standard = $('#question_standard').val();
	    	question_standard = btoa(question_standard);
	    	var AssessmentID = $('#AssessmentID').val();

			$("#topicFiles").load('modules/<?php echo basename(__DIR__); ?>/questions_list_questions.php?assessmentid='+AssessmentID+'&subject='+question_subject+"&grade="+question_grade+"&difficulty="+question_difficulty+"&type="+question_itemtype+"&blooms="+question_blooms+"&dok="+question_dok+"&language="+question_language+"&standard="+question_standard+"&pagenumber="+Page, function() {
				$("#topicLoader").hide();
				$("#topicFiles").show();
			});

		});

	});

</script>