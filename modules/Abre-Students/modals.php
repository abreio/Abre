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

	if($pagerestrictions == "" || $isParent)
	{

?>

		<!-- Add Group Modal -->
		<div id="studentgroup" class="fullmodal modal modal-fixed-footer modal-mobile-full">
			<?php if(GetStaffID($_SESSION['useremail']) != "ABREDEMO"){ ?>
				<form class="col s12" id="form-studentgroup" method="post" action="modules/<?php echo basename(__DIR__); ?>/group_process.php">
			<?php } ?>
				<div class="modal-content" style="padding: 0px !important;">
					<div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
						<div class='col s11'><span class="truncate" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">Student Group</span></div>
						<div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
					</div>
					<div style='padding: 0px 24px 0px 24px;'>
						<?php if(GetStaffID($_SESSION['useremail']) != "ABREDEMO"){ ?>
							<div class="row">
								<div class="input-field col s12"><input id="group_name" name="group_name" placeholder="Name of the Group" type="text" required></div>
							</div>

							<!--Tabs-->
							<div class="row">
							<ul class="tabs" style='background-color: <?php echo getSiteColor(); ?>'>
						    	<li class="tab col s3" id='searchtab'><a class="active" href="#search">Search</a></li>
						        <li class="tab col s3"><a href="#roster">Roster</a></li>
						    </ul>
							</div>

							<div class="row" id="search">
								<div class="row">
									<div class="input-field col s12">
									    <input placeholder="Enter a Student" name='group_search' id="group_search" type="text" style='width:100%;'>
										<label for="group_search" class="active">Search</label>
									</div>
								</div>
								<div class="row">
								<div class="input-field col s12">
									<div id="topicLoader"><div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate" style="width:100%;"></div></div>
									<div id="topicFiles"></div>
								</div>
								</div>
							</div>

							<div class="row" id="roster">
								<div class="row">
								<div class="col s12">
									<div id="currentRoster"></div>
								</div>
								</div>
							</div>

							<input type="hidden" name="group_id" id="group_id">
						</div>
			    </div>
				 	<div class="modal-footer">
						<button type="submit" class="modal-action waves-effect btn-flat white-text" style='margin-left:5px; background-color: <?php echo getSiteColor(); ?>'>Save</button>
					  <a class="modal-close waves-effect btn-flat white-text" style='margin-left:5px; background-color: <?php echo getSiteColor(); ?>'>Close</a>
					  <div id="deletebutton" style='display:none;'><a href="#" class="waves-effect btn-flat white-text deletegroup pointer" style='background-color: <?php echo getSiteColor(); ?>'>Delete</a></div>
					</div>
				<?php }else{ ?>
						<div class='row' style='padding:56px; text-align:center; width:100%;'><span style='font-size: 22px; font-weight:700'>No Staff ID Found</span><br><p style='font-size:16px; margin:20px 0 0 0;'>You need to have be a registered staff member in the SIS to create groups!</p></div>
						</div>
					</div>
				<?php } ?>
			</form>
		</div>


		<!-- Assessment Details Modal -->
		<div id="assessmentlook" class="fullmodal modal modal-fixed-footer modal-mobile-full">
			<div class="modal-content" style="padding: 0px !important;">
				<div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
					<div class='col s11'><span class="truncate" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">Assessment Details</span></div>
					<div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
				</div>
				<div style='padding: 0px 24px 0px 24px;'>
					<div class="row">
						<div class="input-field col s12">
							<div id="assessmentdetailsloader"><div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate" style="width:100%;"></div></div>
							<div id="assessmentdetails"></div>
						</div>
					</div>
				</div>
		  </div>
			<div class="modal-footer">
				<a class="modal-close waves-effect btn-flat white-text" style='margin-left:5px; background-color: <?php echo getSiteColor(); ?>'>Close</a>
			</div>
		</div>

		<!-- Conduct Details Modal -->
		<div id="conductlook" class="fullmodal modal modal-fixed-footer modal-mobile-full">
			<div class="modal-content" style="padding: 0px !important;">
				<div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
					<div class='col s11'><span class="truncate" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">Conduct Details</span></div>
					<div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
				</div>
				<div style='padding: 0px 24px 0px 24px;'>
					<div class="row">
						<div class="input-field col s12">
							<div id="conductdetailsloader"><div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate" style="width:100%;"></div></div>
							<div id="conductdetails"></div>
						</div>
					</div>
				</div>
		  </div>
			<div class="modal-footer">
				<a class="modal-close waves-effect btn-flat white-text" style='margin-left:5px; background-color: <?php echo getSiteColor(); ?>'>Close</a>
			</div>
		</div>

		<!-- Student Quick View Modal -->
		<div id="studentlook" class="fullmodal modal modal-fixed-footer modal-mobile-full">
			<div class="modal-content" style="padding: 0px !important;">
				<div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
					<div class='col s11'><span class="truncate" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">Student Quick Look</span></div>
					<div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
				</div>
				<div style='padding: 0px 24px 0px 24px;'>
					<div class="row">
						<div class="input-field col s12">
							<div id="studentdetailsloader"><div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate" style="width:100%;"></div></div>
							<div id="studentdetails"></div>
						</div>
					</div>
				</div>
		  </div>
			<div class="modal-footer">
				<a class="modal-close waves-effect btn-flat white-text" style='margin-left:5px; background-color: <?php echo getSiteColor(); ?>'>Close</a>
			</div>
		</div>


<?php

	}

?>

<script>

	$(function()
	{

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

    	//Student Search/Filter
    	$("#group_search").keyup(function()
    	{
	    	$("#topicFiles").hide();
			  $("#topicLoader").show();

	    	delay(function()
	    	{
		    	var group_search = $('#group_search').val();
		    	group_search = btoa(group_search);
		    	var group_id = $('#group_id').val();

				$("#topicFiles").load('modules/<?php echo basename(__DIR__); ?>/students_search.php?group_search='+group_search+'&group_id='+group_id, function() {
					$("#topicLoader").hide();
					$("#topicFiles").show();
				});
			 }, 500 );
		});

    	//Student Search/Filter
    	$("#searchtab").unbind().click(function()
    	{
	    	$('#group_search').val('');
			$("#topicFiles").hide();
		});

		//Delete Group
		$(".deletegroup").unbind().click(function(event) {

			event.preventDefault();
			var result = confirm("Are you sure you want to permanently delete this group?");
			if (result) {
				var id = $("#group_id").val();
				$.post("modules/<?php echo basename(__DIR__); ?>/group_delete.php", { group_id: id })
				.done(function(response) {
					$('#studentgroup').closeModal({ in_duration: 0, out_duration: 0 });

					$("#content_holder").load( "modules/<?php echo basename(__DIR__); ?>/home.php", function(){

						mdlregister();

						var notification = document.querySelector('.mdl-js-snackbar');
						var data = { message: response };
						notification.MaterialSnackbar.showSnackbar(data);

					});
				});
			}

		});

		//Add/Edit a Group
		$('#form-studentgroup').submit(function(event)
		{
			event.preventDefault();

			var form = $('#form-studentgroup');
			var formMessages = $('#form-messages');

			$('#studentgroup').closeModal({
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
				$("#group_name").val('');
				$("#content_holder").load( "modules/<?php echo basename(__DIR__); ?>/home.php", function(){

					mdlregister();

					var notification = document.querySelector('.mdl-js-snackbar');
					var data = { message: response.message };
					notification.MaterialSnackbar.showSnackbar(data);

				});

			})
		});

		//Open up preview of Student app on click
		$(document).off().on('click', '.modal-studentlook', function(event)
		{
			event.preventDefault();
			var StudentID = $(this).data('studentid');
			$("#searchresults").html('');

				$('#studentlook').openModal({ in_duration: 0, out_duration: 0,
					ready: function()
					{
						$("#studentdetailsloader").show();
						$("#studentdetails").html('');
						$("#studentdetails").load('modules/Abre-Students/student.php?Student_ID='+StudentID, function(){ $("#studentdetailsloader").hide(); mdlregister(); });
				},
			});
		});


	//End Document Ready
	});


</script>
