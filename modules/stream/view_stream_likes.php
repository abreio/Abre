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
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');
	require_once(dirname(__FILE__) . '/../../api/streams-api.php');
	require_once('functions.php');

	//Get Stream Limit
	$StreamStartResult = 0;
	if(isset($_GET["StreamStartResult"])){ $StreamStartResult = $_GET["StreamStartResult"]; }
	$StreamEndResult = 24;
	if(isset($_GET["StreamEndResult"])){ $StreamEndResult = $_GET["StreamEndResult"]; }

	if (useAPI()) {
		//Determine total number of likes
		$apiValue = apiStreams::getStreamCardsForLikes($StreamStartResult, $StreamEndResult);

		$result = $apiValue['result']['likes'];
		$totallikes =  $apiValue['result']['totalCards'];

		$counter = 0;
		foreach($result as $value){
			$link = mysqli_real_escape_string($db, $value['url']);
			$title = $value['title'];
			$titleencoded = htmlspecialchars($title, ENT_QUOTES);
			$titlewithoutlongwords = preg_replace('~\b\S{30,}\b~', '', $title);
			$image = htmlspecialchars($value ['image'], ENT_QUOTES);
			$imagebase = base64_encode($image);
			$linkbase = base64_encode($value['url']);
			$linkescaped = htmlspecialchars($value['url'], ENT_QUOTES);
			$creationtime = $value['creationtime'];
			$creationtime = date('F jS, Y',strtotime($creationtime));

			$rawexcerpt = htmlspecialchars($value['excerpt'], ENT_QUOTES);
			$excerpt = htmlspecialchars($value['excerpt'], ENT_QUOTES);
			$excerpt = str_replace("<p>", " ", $excerpt);
			$excerpt = strip_tags(html_entity_decode($excerpt));
			$excerpt = preg_replace('/(\.)([[:alpha:]]{2,})/', '$1 $2', $excerpt);
			$excerpt = str_replace("'",'"',$excerpt);
			$excerpt = str_replace('"',"'",$excerpt);
			$excerpt = str_replace('’',"'",$excerpt);
			$excerpt = str_replace('—',"-",$excerpt);
			$excerpt = filter_var($excerpt, FILTER_SANITIZE_STRING);
			if($excerpt == ""){ $excerpt = $title; }
			$counter++;

			$type = '';
			if(is_numeric($link)){
				$type = "custom";
			}else{
				$type = "stream";
			}

			$apiValue = apiStreams::getStreamContentsByUrl(json_encode(array("url"=>$link)));
			$result = $apiValue['result'];

			//Counts
			$num_rows_like_current_user = $result['counts']['userLikes'];
			$num_rows_like = $result['counts']['likes'];
			$num_rows_comment = $result['counts']['comments'];

			DisplayCard($id,$type,$color,$owner,$feedtitle,$title,$image,$date,$rawexcerpt,$excerpt,$linkraw,$link,$num_rows_like,$num_rows_comment,$cardcountloop);


		}
	}
	else {
		//Determine total number of likes
		$query = "SELECT COUNT(*) FROM streams_comments WHERE user = '".$_SESSION['useremail']."' AND liked = '1' GROUP BY url ORDER BY ID DESC";
		$result = $db->query($query);
		$totallikes = 0;
		while($resultrow = $result->fetch_assoc()){
			$totallikes += $resultrow["COUNT(*)"];
		}

		//$query = "SELECT COUNT(*) FROM streams_comments WHERE url = '$link' AND liked = '1' AND user = '".$_SESSION['useremail']."'";


		//Find what streams to display
		$query = "SELECT title, image, url, creationtime, excerpt FROM streams_comments WHERE user = '".$_SESSION['useremail']."' AND liked = '1' ORDER BY ID DESC LIMIT $StreamStartResult, $StreamEndResult";
		$dbreturn = databasequery($query);
		$counter = 0;
		$lastUrl = NULL;
		foreach($dbreturn as $value){
			$link = mysqli_real_escape_string($db, $value['url']);
			if($lastUrl == $link && $link != ""){
				continue;
			}
			$lastUrl = $link;
			$title = $value['title'];
			$titleencoded = htmlspecialchars($title, ENT_QUOTES);
			$titlewithoutlongwords = preg_replace('~\b\S{30,}\b~', '', $title);
			$image = htmlspecialchars($value ['image'], ENT_QUOTES);
			$imagebase = base64_encode($image);
			$linkbase = base64_encode($value['url']);
			$linkescaped = htmlspecialchars($value['url'], ENT_QUOTES);
			$creationtime = $value['creationtime'];
			$creationtime = date('F jS, Y',strtotime($creationtime));

			$rawexcerpt = htmlspecialchars($value['excerpt'], ENT_QUOTES);
			$excerpt = htmlspecialchars($value['excerpt'], ENT_QUOTES);
			$excerpt = str_replace("<p>", " ", $excerpt);
			$excerpt = strip_tags(html_entity_decode($excerpt));
			$excerpt = preg_replace('/(\.)([[:alpha:]]{2,})/', '$1 $2', $excerpt);
			$excerpt = str_replace("'",'"',$excerpt);
			$excerpt = str_replace('"',"'",$excerpt);
			$excerpt = str_replace('’',"'",$excerpt);
			$excerpt = str_replace('—',"-",$excerpt);
			$excerpt = filter_var($excerpt, FILTER_SANITIZE_STRING);
			if($excerpt == ""){ $excerpt = $title; }
			$counter++;

			$type = '';
			if(is_numeric($link)){
				$type = "custom";
			}else{
				$type = "stream";
			}

			//Comment count
			$query = "SELECT COUNT(*) FROM streams_comments WHERE url = '$link' AND comment != ''";
			$dbreturn = $db->query($query);
			$resultrow = $dbreturn->fetch_assoc();
			$num_rows_comment = $resultrow["COUNT(*)"];

			//Like count
			$query2 = "SELECT COUNT(*) FROM streams_comments WHERE url = '$link' AND comment = '' AND liked = '1'";
			$dbreturn2 = $db->query($query2);
			$resultrow = $dbreturn2->fetch_assoc();
			$num_rows_like = $resultrow["COUNT(*)"];

			//Display Card
			echo "<div class='mdl-card mdl-shadow--2dp card_stream hoverable' style='float:left;'>";

				//Feed
				echo "<div class='truncate' style='padding:16px 16px 0 16px; font-size: 12px; color: #999; font-weight: 500;'>You Liked This</div>";

				//Title
				echo "<div class='cardtitle' style='height:60px; padding:5px 16px 0 16px;'>";
					echo "<div class='mdl-card__title-text ellipsis-multiline modal-readstream pointer' data-excerpt='$rawexcerpt' data-image='$imagebase' data-redirect='likes' data-title='$titleencoded' data-url='$linkbase' data-type='$type' style='font-weight:700; font-size:20px; line-height:24px;'>$titlewithoutlongwords</div>";
				echo "</div>";

				//Date
				echo "<div class='truncate' style='padding:0 16px 10px 16px; font-size: 14px; color: #9E9E9E;'>$creationtime</div>";

				//Card Image
				if($image != ""){
					echo "<div class='mdl-card__media mdl-color--grey-100 mdl-card--expand modal-readstream pointer' data-excerpt='$rawexcerpt' data-image='$imagebase' data-redirect='likes' data-title='$titleencoded' data-url='$linkbase' data-type='$type' style='height:200px; background-image: url($image);'></div>";
				}
				else
				{
					if($excerpt == ""){
						$excerpt = $title;
					}
					if(strlen($excerpt) > 100){
						$body = substr($excerpt, 0, strrpos( substr($excerpt , 0, 100), ' ' ));
						$body = substr($excerpt, 0, 97) . ' ...';
					}
					else
					{
						$body = $excerpt;
					}

					echo "<div class='mdl-card__media mdl-color--grey-100 mdl-card--expand valign-wrapper modal-readstream pointer' data-excerpt='$rawexcerpt' data-image='$imagebase' data-redirect='likes' data-title='$titleencoded' data-url='$linkbase' data-type='$type' style='height:200px; background-image: url(/core/images/abre/abre_pattern.png); background-color: ".getSiteColor()." !important; overflow:hidden;'>";
						echo "<span class='wrap-links' style='width:100%; color:#fff; padding:32px; font-size:18px; line-height:normal; font-weight:700; text-align:center;'>$body</span>";
					echo "</div>";

				}

				//Card Actions
				echo "<div class='mdl-card__actions'>";

					//Read Button
					echo "<a class='mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect modal-readstream' data-commenticonid='comment_$counter' data-excerpt='$rawexcerpt' data-image='$imagebase' data-redirect='likes' data-title='$titleencoded' data-url='$linkbase' data-type='$type' style='color: ".getSiteColor()."'>Read</a>";

					//Share, Likes, Comments for Staff Only
					if($_SESSION['usertype'] == 'staff'){

						echo "<div class='mdl-layout-spacer'></div>";

						//Share
						if(!is_numeric($link)){
							echo "<a class='material-icons mdl-color-text--grey-600 modal-sharecard commenticon shareinfo' style='margin-right:30px;' data-url='$linkbase' title='Share' href='#sharecard'>share</a>";
						}

						//Likes
						$query = "SELECT COUNT(*) FROM streams_comments WHERE url = '$link' AND liked = '1' AND user = '".$_SESSION['useremail']."'";
						$dbreturn = $db->query($query);
						$resultrow = $dbreturn->fetch_assoc();
						$num_rows_like_current_user = $resultrow["COUNT(*)"];

						if($num_rows_like == 0){
							echo "<a class='material-icons mdl-color-text--grey-600 likeicon' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-image='$imagebase' data-type='$type' title='Like' href='#'>favorite</a> <span class='mdl-color-text--grey-600' style='font-size:12px; font-weight:600; width:30px; padding-left:5px;'>$num_rows_like</span>";
						}else{
							if($num_rows_like_current_user == 0){
								echo "<a class='material-icons mdl-color-text--grey-600 likeicon' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-image='$imagebase' data-type='$type' href='#'>favorite</a> <span class='mdl-color-text--grey-600' style='font-size:12px; font-weight:600; width:30px; padding-left:5px;'>$num_rows_like</span>";
							}else{
								echo "<a class='material-icons mdl-color-text--red likeicon' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-image='$imagebase' data-type='$type' href='#'>favorite</a> <span class='mdl-color-text--red' style='font-size:12px; font-weight:600; width:30px; padding-left:5px;'>$num_rows_like</span>";
							}
						}

						//Comments
						if($num_rows_comment == 0){
							echo "<a class='material-icons mdl-color-text--grey-600 modal-addstreamcomment commenticon' data-commenticonid='comment_$counter' data-excerpt='$rawexcerpt' data-image='$imagebase' data-redirect='likes' data-title='$titleencoded' data-url='$linkbase' data-type='$type' title='Add a comment' href='#addstreamcomment'>insert_comment</a><span id='comment_$counter' style='font-size:12px; font-weight:600; width:30px; padding-left:5px; color:grey'>$num_rows_comment</span>";
						}else{
							echo "<a class='material-icons modal-addstreamcomment commenticon' style='color: ".getSiteColor().";' data-commenticonid='comment_$counter' data-excerpt='$rawexcerpt' data-image='$imagebase' data-redirect='likes' data-title='$titleencoded' data-url='$linkbase' data-type='$type' title='Add a comment' href='#addstreamcomment'>insert_comment</a> <span id='comment_$counter' style='font-size:12px; font-weight:600; width:30px; padding-left:5px; color: ".getSiteColor()."'>$num_rows_comment</span>";
						}
					}

				echo "</div>";

			echo "</div>";

		}

	}

	echo "<div id='noLikesMessage' class='row center-align' style='display:none;'>";
		echo "<div class='widget' style='padding:30px; text-align:center; width:100%;'><span style='font-size: 22px; font-weight:700'>Stream Likes</span><br><p style='font-size:16px; margin:20px 0 0 0;'>Get started by liking a post from your stream.<br>You'll see the stream posts you have liked here.</p></div>";
	echo "</div>";


?>

<script>

	$(function(){

		var likedPosts = <?php echo $totallikes; ?>;
		if(likedPosts == 0){
			$("#noLikesMessage").show();
		}else{
			$("#noLikesMessage").hide();
		}

		//Like a Stream Post
		$(".likeicon").unbind().click(function(){

			event.preventDefault();

			var result = confirm("Remove from your likes?");
			if(result){

				var Stream_Title = $(this).data('title');
				var Stream_Url = $(this).data('url');
				var Stream_Image = $(this).data('image');
				var excerpt = $(this).data('excerpt');

				var elementCount = $(this).next();
				var elementIcon = $(this);
				var card = $(this).closest('.card_stream');

				$.post("modules/stream/view_stream_like.php", { url: Stream_Url, title: Stream_Title, image: Stream_Image, excerpt: excerpt })
				.done(function(data) {
					$.post( "modules/<?php echo basename(__DIR__); ?>/action_update_card.php", {url: Stream_Url, type: "like"})
					.done(function(data) {
						if(data.count == 0){
							elementIcon.addClass("mdl-color-text--grey-600");
							elementCount.removeClass("mdl-color-text--red");
							elementCount.addClass("mdl-color-text--grey-600");
							card.hide();
							likedPosts--;
							elementCount.html(data.count);
						}else{
							if(data.currentusercount == 0){
								elementIcon.addClass("mdl-color-text--grey-600");
								elementIcon.removeClass("mdl-color-text--red");
								elementCount.addClass("mdl-color-text--grey-600");
								elementCount.removeClass("mdl-color-text--red");
								card.hide();
								likedPosts--;
								elementCount.html(data.count);
							}else{
								elementIcon.removeClass("mdl-color-text--grey-600");
								elementIcon.addClass("mdl-color-text--red");
								elementCount.removeClass("mdl-color-text--grey-600");
								elementCount.addClass("mdl-color-text--red");
								elementCount.html(data.count);
							}
						}
						if(likedPosts == 0){
							$("#noLikesMessage").show();
						}
					});
				});
			}
		});

		//Fill comment modal
		$(".shareinfo").unbind().click(function(){
			event.preventDefault();
			var Article_URL = $(this).data('url');
			Article_URL = atob(Article_URL);
			$(".modal-content #share_url").val(Article_URL);
			$('#sharecard').openModal({ in_duration: 0, out_duration: 0 });
		});

		//Comment Modal
		$('.modal-addstreamcomment').leanModal({
			in_duration: 0,
			out_duration: 0,
			ready: function()
			{
				$("#streamComment").focus();
			}
		});

		//Fill comment modal
		$(document).off().on("click", ".modal-addstreamcomment", function (event){
			event.preventDefault();

			$("#commentloader").show();
			$("#streamComments").empty();
			$(".modal-content #streamTitle").text('');
			$(".modal-content #streamTitle").val('');
			$(".modal-content #streamUrl").val('');
			$(".modal-content #commentID").val('');
			$(".modal-content #streamImage").val('');
			$(".modal-content #redirect").val('');
			$(".modal-content #streamExcerpt").val('');
			$(".modal-content #streamExcerptDisplay").html('');

			var type = $(this).data('type');
			if(type == "custom"){
				$("#readStreamTitle").text("Announcement");
			}else{
				$("#readStreamTitle").text("News");
			}
			var Stream_Title = $(this).data('title');
			$(".modal-content #streamTitle").text(Stream_Title);
			$(".modal-content #streamTitleValue").val(Stream_Title);
			var Stream_Url = $(this).data('url');
			$(".modal-content #streamUrl").val(Stream_Url);
			var commentID = $(this).data('commenticonid');
			$(".modal-content #commentID").val(commentID);
			var streamImage = $(this).data('image');
			$(".modal-content #streamImage").val(streamImage);
			var redirect = $(this).data('redirect');
			$(".modal-content #redirect").val(redirect);
			var excerpt = $(this).data('excerpt');
			$(".modal-content #streamExcerpt").val(excerpt);
			$(".modal-content #streamExcerptDisplay").html(excerpt);
			if(streamImage != ""){
				$(".modal-content #streamPhotoHolder").show();
				$(".modal-content #streamPhoto").addClass("mdl-card__media");
				$(".modal-content #streamPhoto").attr('style', 'height:300px;');
				$(".modal-content #streamPhoto").css("background-image", "url("+atob(streamImage)+")");
			}else{
				$(".modal-content #streamPhotoHolder").hide();
				$(".modal-content #streamPhoto").removeAttr('style');
				$(".modal-content #streamPhoto").removeClass("mdl-card__media");
			}
			if(!isNaN(parseInt(atob(Stream_Url)))){
				$(".modal-content #streamLink").attr("href", "");
				$(".modal-content #streamLink").hide();
			}else{
				$(".modal-content #streamLink").show();
				$(".modal-content #streamLink").attr("href", atob(Stream_Url));
			}

			$( "#streamComments" ).load( "modules/stream/view_comment_list.php?url="+Stream_Url, function() {
				$("#commentloader").hide();
			});

			$('.modal-content').animate({
				scrollTop: $("#streamComments").offset().top},
				0);
		});

		$(".modal-readstream").unbind().click(function(event){
			event.preventDefault();

			$("#commentloader").show();
			$("#streamComments").empty();
			$(".modal-content #streamTitle").text('');
			$(".modal-content #streamTitle").val('');
			$(".modal-content #streamUrl").val('');
			$(".modal-content #commentID").val('');
			$(".modal-content #streamImage").val('');
			$(".modal-content #redirect").val('');
			$(".modal-content #streamExcerpt").val('');
			$(".modal-content #streamExcerptDisplay").html('');

			var type = $(this).data('type');
			if(type == "custom"){
				$("#readStreamTitle").text("Announcement");
			}else{
				$("#readStreamTitle").text("News");
			}
			var Stream_Title = $(this).data('title');
			$(".modal-content #streamTitle").text(Stream_Title);
			$(".modal-content #streamTitleValue").val(Stream_Title);
			var Stream_Url = $(this).data('url');
			$(".modal-content #streamUrl").val(Stream_Url);
			var commentID = $(this).data('commenticonid');
			$(".modal-content #commentID").val(commentID);
			var streamImage = $(this).data('image');
			$(".modal-content #streamImage").val(streamImage);
			var redirect = $(this).data('redirect');
			$(".modal-content #redirect").val(redirect);
			var excerpt = $(this).data('excerpt');
			$(".modal-content #streamExcerpt").val(excerpt);
			$(".modal-content #streamExcerptDisplay").html(excerpt);
			if(streamImage != ""){
				$(".modal-content #streamPhotoHolder").show();
				$(".modal-content #streamPhoto").addClass("mdl-card__media");
				$(".modal-content #streamPhoto").attr('style', 'height:300px;');
				$(".modal-content #streamPhoto").css("background-image", "url("+atob(streamImage)+")");
			}else{
				$(".modal-content #streamPhotoHolder").hide();
				$(".modal-content #streamPhoto").removeAttr('style');
				$(".modal-content #streamPhoto").removeClass("mdl-card__media");
			}
			if(!isNaN(parseInt(atob(Stream_Url)))){
				$(".modal-content #streamLink").attr("href", "");
				$(".modal-content #streamLink").hide();
			}else{
				$(".modal-content #streamLink").show();
				$(".modal-content #streamLink").attr("href", atob(Stream_Url));
			}

			$( "#streamComments" ).load( "modules/stream/view_comment_list.php?url="+Stream_Url, function() {
				$("#commentloader").hide();
			});

			$('#addstreamcomment').openModal({
				in_duration: 0,
				out_duration: 0,
				ready: function(){}
			});

			$('.modal-content').animate({
				scrollTop: 0},
				0);
		});

	});

</script>
