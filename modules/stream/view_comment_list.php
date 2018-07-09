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
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');
	require_once(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

	if($_SESSION['usertype'] == 'staff'){

		$articletitle = "";
		if(isset($_GET['url'])){
			$url = base64_decode($_GET['url']);
			$url = mysqli_real_escape_string($db, $url);
		}else{
			$url = "";
		}
		if($url != ""){
			$sql = "(SELECT user, comment, title, id, creationtime FROM streams_comments WHERE url = '$url' AND comment != '' ORDER BY id DESC) ORDER BY id DESC LIMIT 100";
		}

		//Display comments
		echo "<div class='row' id='commentthreadbox'>";
			$dbreturn = databasequery($sql);
			$commentcount=count($dbreturn);
			$counter=0;
			foreach($dbreturn as $row){

				$counter++;
				$User = htmlspecialchars($row["user"], ENT_QUOTES);
				$Comment = htmlspecialchars($row["comment"], ENT_QUOTES);
				$Comment = nl2br(strip_tags(html_entity_decode($Comment)));
				$Comment = linkify($Comment);
				$articletitle = html_entity_decode($row["title"]);
				$CommentID = htmlspecialchars($row["id"], ENT_QUOTES);
				$CommentCreationTime = htmlspecialchars($row["creationtime"], ENT_QUOTES);

				//Display comment creation in correct format
				if(strtotime($CommentCreationTime) < strtotime('-7 days')){
					$CommentCreationTime = date( "F j", strtotime($CommentCreationTime))." at ".date( "g:i A", strtotime($CommentCreationTime));
			 	}else{
					$CommentCreationTime = date( "l", strtotime($CommentCreationTime))." at ".date( "g:i A", strtotime($CommentCreationTime));
				}

				//Look up name given email from directory
				$picture = "";
				$sql = "SELECT firstname, lastname, picture FROM directory WHERE email = '$User'";
				$dbreturn = databasequery($sql);
				$firstname = "";
				$lastname = "";
				$picture = "";
				foreach($dbreturn as $row){
					$firstname = htmlspecialchars($row["firstname"], ENT_QUOTES);
					$firstname = stripslashes($firstname);
					$lastname = htmlspecialchars($row["lastname"], ENT_QUOTES);
					$lastname = stripslashes($lastname);
					$picture = htmlspecialchars($row["picture"], ENT_QUOTES);
				}

				if (strpos($picture, 'http') === false) {

					if(empty($picture)){
						$picture = $portal_root."/modules/directory/images/user.png";
					}else{
						$picture = $portal_root."/modules/directory/serveimage.php?file=$picture";
					}

				}

					//Display Each Comment
					echo "<div class='commentwrapper col s12' style='overflow:hidden;'>";
						echo "<div style='padding:10px 0 10px 0;'>";

							echo "<div style='float:left; width:50px;'>";
								echo "<img src='$picture' class='profile-avatar-small'>";
							echo "</div>";

							echo "<div style='float:right; width:50px; text-align:right;'>";
								if($User == $_SESSION['useremail']){
									echo "<a href='#' data-commentid='$CommentID' class='mdl-color-text--grey commentdelete pointer'><i class='material-icons'>clear</i></a>";
								}
							echo "</div>";

							echo "<div style='margin:0 50px;'>";

								if(!empty($firstname)){
									echo "<span style='font-weight:500; font-size:15px;'>$firstname $lastname</span><br>";
								}else{
									echo "<span style='font-weight:500; font-size:15px;'>$User</span><br>";
								}
								echo "<span style='color:#999; font-size:13px;'>$CommentCreationTime</span>";
								echo "<p style=''>$Comment</p>";

							echo "</div>";


						echo "</div>";
						if($commentcount!=$counter){ echo "<hr>"; }
					echo "</div>";
			}

		echo "</div>";

?>

<script>

		$(function(){

			//Fill modal with content
			<?php if($articletitle != ""){ ?>
				$(".modal-content #streamTitle").text("<?php echo $articletitle; ?>");
				$(".modal-content #streamUrl").val("<?php echo base64_encode($url); ?>");
				$(".modal-content #streamTitleValue").val("<?php echo $articletitle; ?>");
			<?php } ?>

			//Delete a comment
			$(".commentdelete").unbind().click(function(event){
				event.preventDefault();
				var result = confirm("Remove this comment?");
				if(result){
					var url = $("#streamUrl").val();
					var id = $("#commentID").val();
					var redirect = $("#redirect").val();
					$(this).closest(".commentwrapper").hide();
					var CommentID = $(this).data('commentid');
					$.ajax({
						type: 'POST',
						url: 'modules/stream/action_comment_remove.php?commentid='+CommentID,
						data: '',
					})
					.done(function() {
						$.post( "modules/<?php echo basename(__DIR__); ?>/action_update_card.php", {url: url, redirect: redirect, type: "comment"})
						.done(function(data) {
							if(data.count == 0){
								$("#"+id).prev().addClass("mdl-color-text--grey-600");
								$("#"+id).prev().css("color", "");
								$("#"+id).css("color", "grey");
								$("#"+id).html(data.count);
							}else{
								$("#"+id).prev().removeClass("mdl-color-text--grey-600");
								$("#"+id).prev().css("color", "<?php echo getSiteColor(); ?>");
								$("#"+id).css("color", "<?php echo getSiteColor(); ?>");
								$("#"+id).html(data.count);
							}
							if(redirect == "comments"){
								if(data.currentusercount == 0){
									$("#"+id).closest('.card_stream').hide();
								}
								if(data.streamcardsleft == 0){
									$("#noCommentsMessage").show();
								}
							}
						});
					});
				}
			});

		});

</script>

<?php } ?>
