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
	require_once(dirname(__FILE__) . '/../../api/profile-api.php');
	require_once('functions.php');

  //Find what streams to display
  if (useAPI()) {
    $apiValue = apiProfile::getUserProfile();
    $dbreturn = $apiValue['result'];
  }
  else {
    $query = "SELECT streams FROM profiles WHERE email = '".$_SESSION['useremail']."'";
    $dbreturn = databasequery($query);
  }

  foreach($dbreturn as $value) {
    $userstreams = htmlspecialchars($value['streams'], ENT_QUOTES);
  }

  //Create the Feed Array & Count
  $feeds = array();
  $totalcount = 0;

  //Get Feeds
  if(!empty($userstreams) != NULL){
    $sql = "SELECT title, url, `group`, color, staff_building_restrictions, student_building_restrictions FROM streams WHERE required = 1 OR id IN ($userstreams)";
  }else{
    $sql = "SELECT title, url, `group`, color, staff_building_restrictions, student_building_restrictions FROM streams WHERE `required` = 1";
  }

  //Look for all streams that apply to user
  $enrolledStreams = array();
  $dbreturn = databasequery($sql);
  foreach($dbreturn as $value){
    if(strpos($value["group"], $_SESSION["usertype"]) !== false){

      $fburl = htmlspecialchars($value['url'], ENT_QUOTES);

      if($_SESSION['usertype'] == "staff"){
        $restrictions = $value['staff_building_restrictions'];
        $restrictionsArray = explode(",", $restrictions);
      }
      if($_SESSION['usertype'] == "student"){
        $restrictions = $value['student_building_restrictions'];
        $restrictionsArray = explode(",", $restrictions);
      }

      if($restrictions == NULL || in_array("No Restrictions", $restrictionsArray)){
        array_push($enrolledStreams, $value["title"]);
      }else{
        if($codeArraySize >= 1){
          foreach($schoolCodeArray as $code){
            if(in_array($code, $restrictionsArray)){
                array_push($enrolledStreams, $value["title"]);
                break;
            }
          }
        }
      }
    }
  }

  $customPostArray = array();
  $scrolling = false;
  $StreamStartResult = 0;
  $StreamEndResult = 24;
  if(isset($_GET["StreamStartResult"])){
    if($_GET["StreamStartResult"] > 0){
      $StreamStartResult = $_GET["StreamStartResult"];
      $scrolling = true;
    }else{
      $StreamStartResult = $_GET["StreamStartResult"];
    }
  }

  if($scrolling){ $StreamEndResult = $StreamStartResult + 24; }

  $sql = "SELECT id, submission_time, post_author, post_title, post_stream, post_content, post_groups, post_image, color, staff_building_restrictions, student_building_restrictions FROM stream_posts ORDER BY submission_time ASC LIMIT $StreamStartResult, $StreamEndResult";
  $result = $db->query($sql);
  while($value = $result->fetch_assoc()){
    if(strpos($value["post_groups"], $_SESSION["usertype"]) !== false && in_array($value['post_stream'], $enrolledStreams)){
      array_push($customPostArray, $value);
    }
  }

  while(!empty($customPostArray)){
    $element = array_pop($customPostArray);
    $postDate = $element['submission_time'];
    $postDate = strtotime($postDate);
    $title = $element['post_title'];
    $excerpt = $element['post_content'];
    $feedtitle = $element['post_stream'];
    $feedimage = $element['post_image'];
    $color = $element['color'];
    $id = $element['id'];
    $owner = $element['post_author'];

    array_push($feeds, array("date" => "$postDate", "title" => "$title", "excerpt" => "$excerpt", "link" => "$id", "image" => "$feedimage", "feedtitle" => "$feedtitle", "feedlink" => "", "color" => "$color", "type" => "custom", "id" => "$id", "owner" => "$owner"));
    $totalcount++;
  }

  $cardcount = 0;
  for($cardcountloop = 0; $cardcountloop < $totalcount; $cardcountloop++){
    $date = $feeds[$cardcountloop]['date'];

    //Title
    $title = $feeds[$cardcountloop]['title'];
    $title = str_replace("<p>", " ", $title);
    $title = strip_tags(html_entity_decode($title));
    $title = preg_replace('/(\.)([[:alpha:]]{2,})/', '$1 $2', $title);
    $title = str_replace("'",'"',$title);
    $title = str_replace('"',"'",$title);
    $title = str_replace('’',"'",$title);
    $title = str_replace('—',"-",$title);

    //Excerpt
    $rawexcerpt = htmlspecialchars($feeds[$cardcountloop]['excerpt'], ENT_QUOTES);
		$excerpt = $feeds[$cardcountloop]['excerpt'];
		$excerpt = str_replace("<p>", " ", $excerpt);
		$excerpt = strip_tags(html_entity_decode($excerpt));
		$excerpt = preg_replace('/(\.)([[:alpha:]]{2,})/', '$1 $2', $excerpt);
		$excerpt = str_replace("'",'"',$excerpt);
		$excerpt = str_replace('"',"'",$excerpt);
		$excerpt = str_replace('’',"'",$excerpt);
		$excerpt = str_replace('—',"-",$excerpt);
		$excerpt = filter_var($excerpt, FILTER_SANITIZE_STRING);
		if($excerpt == ""){ $excerpt = $title; }

    $linkraw = $feeds[$cardcountloop]['link'];
    $image = $feeds[$cardcountloop]['image'];
    $feedtitle = $feeds[$cardcountloop]['feedtitle'];
    $feedlink = $feeds[$cardcountloop]['feedlink'];
    $color = "";
    $color = $feeds[$cardcountloop]['color'];
    $type = "";
    $type = $feeds[$cardcountloop]['type'];
    $id = "";
    $id = $feeds[$cardcountloop]['id'];
    $owner = "";
    $owner = $feeds[$cardcountloop]['owner'];

    //Add images to server to securely store and reference
    $cloudsetting=constant("USE_GOOGLE_CLOUD");
    if ($cloudsetting=="true")
      include "action_save_image_gc.php";
    else
      include "action_save_image.php";

    $link = mysqli_real_escape_string($db, $linkraw);
    if (useAPI()) {
      $apiValue = apiStreams::getStreamContentsByUrl(json_encode(array("url"=>$link)));
      $result = $apiValue['result'];

      //Counts
      $num_rows_comment = $result['counts']['comments'];
      $num_rows_like = $result['counts']['likes'];
    }
    else {
      //Comment count
      $query = "SELECT COUNT(*) FROM streams_comments WHERE url = '$link' and comment != ''";
      $dbreturn = $db->query($query);
      $resultrow = $dbreturn->fetch_assoc();
      $num_rows_comment = $resultrow["COUNT(*)"];

      //Like count
      $query = "SELECT COUNT(*) FROM streams_comments WHERE url = '$link' AND comment = '' AND liked = '1'";
      $dbreturn = $db->query($query);
      $resultrow = $dbreturn->fetch_assoc();
      $num_rows_like = $resultrow["COUNT(*)"];
    }

    if($title != "" && $excerpt != ""){

			DisplayCard($id,$type,$color,$owner,$feedtitle,$title,$image,$date,$rawexcerpt,$excerpt,$linkraw,$link,$num_rows_like,$num_rows_comment,$cardcountloop);
      $cardcount++;
    }
  }

	if($cardcount == 0 && $StreamStartResult == 0){
		echo "<div class='row center-align'>";
			echo "<div class='widget' style='padding:30px; text-align:center; width:100%;'><span style='font-size: 22px; font-weight:700'>Welcome to Your Announcements</span><br><p style='font-size:16px; margin:20px 0 0 0;'>Announcements related to you will be displayed here.<br></p></div>";
			echo "<a class='mdl-button mdl-js-button mdl-js-ripple-effect' style='background-color:".getSiteColor()."; color:#fff;' href='#profile'>View Available Streams</a>";
		echo "</div>";
	}

?>
<script>

  $(function(){

    //Like a Stream Post
    $(".likeicon").unbind().click(function(){

      event.preventDefault();

      var Stream_Title = $(this).data('title');
      var Stream_Url = $(this).data('url');
      var Stream_Image = $(this).data('image');
      var excerpt = $(this).data('excerpt');

      var elementCount = $(this).next();
      var elementIcon = $(this);

      $.post("modules/stream/view_stream_like.php", { url: Stream_Url, title: Stream_Title, image: Stream_Image, excerpt: excerpt })
      .done(function(data) {
        $.post( "modules/<?php echo basename(__DIR__); ?>/action_update_card.php", {url: Stream_Url, type: "like"})
        .done(function(data) {
          if(data.count == 0){
            elementIcon.addClass("mdl-color-text--grey-600");
            elementCount.removeClass("mdl-color-text--red");
            elementCount.addClass("mdl-color-text--grey-600");
            elementCount.html(data.count);
          }else{
            if(data.currentusercount == 0){
              elementIcon.addClass("mdl-color-text--grey-600");
              elementIcon.removeClass("mdl-color-text--red");
              elementCount.addClass("mdl-color-text--grey-600");
              elementCount.removeClass("mdl-color-text--red");
              elementCount.html(data.count);
            }else{
              elementIcon.removeClass("mdl-color-text--grey-600");
              elementIcon.addClass("mdl-color-text--red");
              elementCount.removeClass("mdl-color-text--grey-600");
              elementCount.addClass("mdl-color-text--red");
              elementCount.html(data.count);
            }
          }
        });
      });
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
			if(type == "custom"){
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
			if(type == "custom"){
				$(".modal-content #streamLink").attr("href", "");
				$(".modal-content #streamLink").hide();
			}else{
				$(".modal-content #streamLink").show();
				$(".modal-content #streamLink").attr("href", atob(Stream_Url));
			}

			<?php
			if($_SESSION['usertype'] == 'staff'){
			?>
			$( "#streamComments" ).load( "modules/stream/view_comment_list.php?url="+Stream_Url, function() {
				$("#commentloader").hide();
			});
			<?php
			}else{
			?>
			$("#commentloader").hide();
			<?php
			}
			?>

			$('#addstreamcomment').openModal({
				in_duration: 0,
				out_duration: 0,
				ready: function(){}
			});

			$('.modal-content').animate({
				scrollTop: 0},
				0);
		});

		//remove custom post
		$(".removepost").unbind().click(function (event){
			event.preventDefault();
			var id = $(this).data('id');
			var result = confirm("Are you sure you want to remove this post?");
			if(result){
				//Make the post request
				$.ajax({
					type: 'POST',
					url: 'modules/stream/action_remove_announcement.php',
					data: { id: id }
				})
				.done(function(response){
					$.get('modules/stream/view_stream_announcements.php?StreamStartResult=0&StreamEndResult=24', function(results){
						$('#showmorestream').hide();
						$('#streamcards').html(results);
						var notification = document.querySelector('.mdl-js-snackbar');
						var data = { message: response.message };
						notification.MaterialSnackbar.showSnackbar(data);
					});
				});
			}
		});
  });






</script>
