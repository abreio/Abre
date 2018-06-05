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

	//Check for variable in url
	if(isset($_GET['discussion'])){
		$discussionid = $_GET['discussion'];
		$discussionid = preg_replace("/[^0-9]/","",$discussionid);
	}

	if($db->query("SELECT * FROM Abre_Students LIMIT 1")){
		$sql = "SELECT SchoolCode, SchoolName FROM Abre_Students ORDER BY SchoolCode";
		$schoolResults = databasequery($sql);
	}
	if(!isset($schoolResults)){
		$schoolResults = array();
	}

?>

	<?php
	if($_SESSION['usertype'] == 'staff'){
	?>

		<!-- Social Sharing -->
		<div id="sharecard" class="modal modal-fixed-footer modal-mobile-full" style="max-width: 600px;">
			<form>
				<div class="modal-content" style="padding: 0px !important;">
					<div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
						<div class='col s11'><span class="truncate" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">Share</span></div>
						<div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
					</div>
					<div style='padding: 0px 24px 0px 24px;'>
						<div class="socialshare pointer" style="width:100%; background-color:#0084b4; padding:15px; text-align: center; color:#fff; font-size:16px;" data-link="http://twitter.com/share?url=">Twitter</div><br>
						<div class="socialshare pointer" style="width:100%; background-color:#3B5998; padding:15px; text-align: center; color:#fff; font-size:16px;" data-link="http://www.facebook.com/sharer.php?u=">Facebook</div><br>
						<div class="socialshare pointer" style="width:100%; background-color:#d34836; padding:15px; text-align: center; color:#fff; font-size:16px;" data-link="https://plus.google.com/share?url=">Google</div>
						<input type="hidden" name="share_url" id="share_url">
					</div>
				</div>
	   		</form>
	 	</div>

	 	<!-- Custom Stream Post -->
		<div id="streampost" class="modal modal-fixed-footer modal-mobile-full">
			<form id="form-streampost" method="post" enctype='multipart/form-data' action="modules/stream/save_post.php">
				<div class="modal-content" style="padding: 0px !important;">
					<div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
						<div class='col s11'><span class="truncate" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">New Post</span></div>
						<div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
					</div>
					<div style='padding: 0px 24px 0px 24px;'>

						<div class="row">
							<div class="input-field col s12">
								<label for="post_stream" class="active">Stream Category</label>
								<select id="post_stream" name="post_stream" required>
									<option value="" disabled selected>Choose a Stream</option>
									<?php
										$sql = "SELECT title FROM streams";
										$result = $db->query($sql);
										while($value = $result->fetch_assoc()){
											$streamTitle = $value['title'];
											echo "<option value='$streamTitle'>$streamTitle</option>";
										}
									?>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="input-field col s12">
								<input type="text" name="post_title" id="post_title" autocomplete="off" placeholder="Enter a Post Title" required>
								<label for="post_title" class="active">Title</label>
							</div>
						</div>
						<div class="row">
							<div class="input-field col s12">
								<textarea placeholder="Enter your Post Content" id="post_content" name="post_content" class="materialize-textarea" required></textarea>
								<label for="post_content" class="active">Content</label>
							</div>
						</div>

						<div class='row'>
							<div class='col s12'>
								<img id='post_image' style='max-width: 100%; display:none;' alt='Post Image' src=''>
								<div class='custompostimage pointer' style='width:100%; background-color:#E0E0E0; text-align:center; padding:50px;'>
									<i class="material-icons">crop_original</i><br><b>Click to choose image</b></div>
								<input type='file' name='customimage' id='customimage' style='display:none;'>
							</div>
						</div>

					</div>

			</div>
			<div class="modal-footer">
				<button class="btn waves-effect btn-flat white-text" id='custompostbutton' type="submit" name="action" style='background-color:<?php echo getSiteColor(); ?>'>Post</button>
				<p id="errorMessage" style="display:none; float:right; color:red; margin:6px 0; padding-right:10px;"></p>
			</div>
			</form>
		</div>

	<?php
	}
	?>

	<!-- Read and Comment Modal -->
	<div id="addstreamcomment" class="modal modal-fixed-footer modal-mobile-full">
		<div id="commentloader" class="mdl-progress mdl-js-progress mdl-progress__indeterminate" style="width:100%"></div>
		<form id="form-addstreamcomment" method="post" action="modules/stream/comment_add.php">
			<div class="modal-content" id="modal-content-section" style="padding: 0px !important;">
				<div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
					<div class='col s11'><span class="truncate" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">Read Post</span></div>
					<div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
				</div>
				<div style='padding: 0px 24px 0px 24px;'>

					<div class="row" style='margin-bottom:0;'>
						<div class='wrap-links input-field col s12' id="streamTitle" style="font-weight:700; font-size:24px; line-height:32px;"></div>
					</div>
					<div class="row" id='streamPhotoHolder'>
						<div class="input-field col s12">
							<div id="streamPhoto" class="center-align"></div>
						</div>
					</div>
					<div class="row">
						<div class='input-field col s12'>
							<p class='wrap-links' id="streamExcerptDisplay" name="streamExcerptDisplay" style="font-size:16px; line-height:1.8em"></p>
						</div>
						<div class='input-field col s12'>
							<a id="streamLink" href="" style="text-decoration: underline; color: <?php echo getSiteColor(); ?>;" target="_blank">View full article</a>
						</div>
					</div>
					<?php
					if($_SESSION['usertype'] == 'staff'){
					?>
						<div class="row">
							<div class="input-field col s12" style="padding-bottom: 5px;">
								<textarea placeholder="Add a comment..." id="streamComment" name="streamComment" class="materialize-textarea" required></textarea>
							</div>
						</div>

						<div class="row">
							<div class="input-field col s12" style="padding-bottom: 5px;">
								<button class="btn waves-effect btn-flat white-text" type="submit" name="action" style='margin-top:-20px; background-color:<?php echo getSiteColor(); ?>'>Post</button><br><br>
							</div>
						</div>

						<div name="streamComments" id="streamComments"></div>
					<?php
					}
					?>

					<input type="hidden" name="streamUrl" id="streamUrl">
					<input type="hidden" name="streamTitleValue" id="streamTitleValue">
					<input type="hidden" name="commentID" id="commentID">
					<input type="hidden" name="streamImage" id="streamImage">
					<input type="hidden" name="redirect" id="redirect">
					<input id="streamExcerpt" name="streamExcerpt" type="hidden">
				</div>
			</div>
			<div class="modal-footer">
				<button class="modal-action modal-close waves-effect btn-flat white-text" type="button" style='background-color: <?php echo getSiteColor(); ?>'>Close</button>
			</div>
		</form>
	</div>

 	<!-- Edit Widgets -->
	<div id="editwidgets" class="modal modal-mobile-full" style="max-width: 600px;">
		<form>
			<div class="modal-content" style="padding: 0px !important;">
				<div class="row" style='background-color: <?php echo getSiteColor(); ?>; padding: 24px;'>
					<div class='col s11'><span class="truncate" style="color: #fff; font-weight: 500; font-size: 24px; line-height: 26px;">Edit Widgets</span></div>
					<div class='col s1 right-align'><a class="modal-close"><i class='material-icons' style='color: #fff;'>clear</i></a></div>
				</div>
				<div style='padding: 0px 24px 0px 24px;'>
					<div class="row">
						<div class="col s12">
							<?php

								//Check to see if there is hidden widgets
								$sql = "SELECT widgets_hidden FROM profiles WHERE email = '".$_SESSION['useremail']."'";
								$result = $db->query($sql);
								$widgets_order = NULL;
								$widgets_hidden = NULL;
								while($row = $result->fetch_assoc()) {
									$widgets_hidden = htmlspecialchars($row["widgets_hidden"], ENT_QUOTES);
								}

								//Show All Widgets
								echo "<table class='bordered'>";
								$widgetsdirectory = dirname(__FILE__) . '/../';
								$widgetsfolders = scandir($widgetsdirectory);
								$widgetcounter=0;

								foreach($widgetsfolders as $result){

									$pagetitle = NULL;
									$restrictions = NULL;

									if(file_exists(dirname(__FILE__) . '/../'.$result.'/widget.php')){

										include(dirname(__FILE__) . '/../'.$result.'/widget_config.php');

										if(strpos($restrictions,$_SESSION['usertype']) === false && strpos($services, $_SESSION['auth_service']) !== false && isAppActive($result)){

											$widgetcounter++;

											echo "<tr>";
												echo "<td><b>$pagetitle</b><td>";
												echo "<td style='width:30px; text-align:right;'>";

													echo "<div class='switch'><label><input type='checkbox' class='widgetclick' name='$result' id='$result' value='1' ";

													//Check if a widget should be hidden
													if($widgets_hidden==NULL){

														echo "checked";

													}
													else
													{

														//Check to see if widget was hidden by use selection
														$HiddenWidgets = explode(',',$widgets_hidden);
														if(!in_array($result, $HiddenWidgets)){

															echo "checked";

														}

													}

													echo "/><span class='lever'></span></label></div>";

												echo "</td>";
											echo "</tr>";

										}

									}

								}

								if($widgetcounter==0){ echo "<tr><td colspan='2'>No widgets are currently available.</td></tr>"; }
								echo "</table>";

							?>
						</div>
					</div>
				</div>
			</div>
   		</form>
 	</div>

<script>

	$(function(){

		//Material Design Dropdown Selects
		$('select').material_select();

		//Provide image upload on icon click
		$(".custompostimage").unbind().click(function(event){
			event.preventDefault();
			$("#customimage").click();
	  	});

		//Submit form if image if changed
		$("#customimage").change(function (){
			if (this.files && this.files[0]){
				var reader = new FileReader();
				reader.onload = function (e) {
					$('#post_image').show();
					$('#post_image').attr('src', e.target.result);
				}
				reader.readAsDataURL(this.files[0]);
			}
	  	});

		//Social Share
		$(".socialshare").unbind().click(function(e){
			e.preventDefault();
			$('#sharecard').closeModal({ in_duration: 0, out_duration: 0 });
			var network = $(this).data('link');
			var link = $('#share_url').val();
			var finallink = network+link;
		    objWindow = window.open(finallink, 'Social Share', 'width=500,height=500,resizable=no').focus();
		});

		//Update Widget Setting
		$(".widgetclick").unbind().click(function(e){
			var widget = $(this).attr('id');
			var selectedWidgets = "";

			$("input:checkbox[class=widgetclick]:not(:checked)").each(function () {
           		selectedWidgets = $(this).attr("id") + "," + selectedWidgets;
        	});

        	selectedWidgets = selectedWidgets.replace(/^,|,$/g,'');

			$.post("modules/stream/save_widget_visibility.php", {widgets: selectedWidgets}, function() {
				$("#streamwidgets").load("modules/<?php echo basename(__DIR__); ?>/widgets.php");
			});

		});

		//Save comment
		var form = $('#form-addstreamcomment');
		$(form).submit(function(event) {
			event.preventDefault();
			$("#commentstatustext").text("Posting comment...");
			var url = $("#streamUrl").val();
			var id = $("#commentID").val();
			var redirect = $("#redirect").val();
			var formData = $(form).serialize();
			$.ajax({
				type: 'POST',
				url: $(form).attr('action'),
				data: formData
			})
			//Show the notification
			.done(function(response) {
				$( "#streamComments" ).load( "modules/stream/comment_list.php?url="+response, function() {
					$("textarea").val('');
					$("#commentstatustext").text("Write a comment");
					$(".modal-content #streamUrl").val(response);
					var element = document.getElementById("commentthreadbox");
					element.scrollTop = element.scrollHeight;
					$.post( "modules/<?php echo basename(__DIR__); ?>/update_card.php", {url: url, redirect: redirect, type: "comment"})
					.done(function(data) {
						$("#"+id).prev().removeClass("mdl-color-text--grey-600");
						$("#"+id).prev().css("color", "<?php echo getSiteColor(); ?>");
						$("#"+id).css("color", "<?php echo getSiteColor(); ?>");
						$("#"+id).html(data.count);
						if(redirect == "comments"){
							if(data.currentusercount > 0){
								$("#"+id).closest(".card_stream").show();
							}
							if(data.streamcardsleft > 0){
								$("#noCommentsMessage").hide();
							}
						}
					});
				});
			})
		});

		$("#post_staff").change(function(){
			if($(this).is(':checked')){
				$("#postStaffRestrictionsDiv").show();
			}else{
				$("#postStaffRestrictionsDiv").hide();
			}
		});

		$("#post_students").change(function(){
			if($(this).is(':checked')){
				$("#postStudentRestrictionsDiv").show();
			}else{
				$("#postStudentRestrictionsDiv").hide();
			}
		});

		//Submit the Custom Post
		$("#form-streampost").submit(function(event) {
			event.preventDefault();
			$("errorMessage").hide();
			$('#custompostbutton').html("Posting...");
			var title = $("#post_title").val();
			var stream = $("#post_stream").val();
			var content = $("#post_content").val();
			var data = new FormData($(this)[0]);

			$.ajax({ type: 'POST', url: $(this).attr('action'), data: data, contentType: false, processData: false })
			.done(function(response){
				$('#custompostbutton').html("Post");
				if(response.status == "Success"){
					$('#streampost').closeModal({ in_duration: 0, out_duration: 0, });
					$('#all').trigger('click');
					var notification = document.querySelector('.mdl-js-snackbar');
					var data = { message: response.message };
					notification.MaterialSnackbar.showSnackbar(data);
				}
				if(response.status == "Error"){
					$("#errorMessage").html(response.message);
					$("#errorMessage").show();
				}
			});
		});


	});

</script>
