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

	$schoolCodeArray = getRestrictions();
	$codeArraySize = sizeof($schoolCodeArray);


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
	$preliminaryFeeds = array();
	$feeds = array();
	$totalcount = 0;

	//Get Feeds
	require_once('simplepie/autoloader.php');
	$feed_flipboard = new SimplePie();
	if(!empty($userstreams) != NULL){
		$sql = "SELECT title, url, `group`, color, staff_building_restrictions, student_building_restrictions FROM streams WHERE required = 1 OR id IN ($userstreams)";
	}else{
		$sql = "SELECT title, url, `group`, color, staff_building_restrictions, student_building_restrictions FROM streams WHERE `required` = 1";
	}

	//Look for all streams that apply to user
	$flipboardarray = array();
	$infoArray = array();
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

			$protocols = array("https://", "http://");
			$fbArrayValue = str_replace($protocols, "", $fburl);
			$infoArray[$fbArrayValue] = array("color" => $value['color'], "title" => $value['title']);

			if($restrictions == NULL || in_array("No Restrictions", $restrictionsArray)){
				array_push($flipboardarray, $fburl);
				array_push($enrolledStreams, $value["title"]);
			}else{
				if($codeArraySize >= 1){
					foreach($schoolCodeArray as $code){
						if(in_array($code, $restrictionsArray)){
								array_push($flipboardarray, $fburl);
								array_push($enrolledStreams, $value["title"]);
								break;
						}
					}
				}
			}
		}
	}

	$feed_flipboard->set_cache_duration(1800);
	$feed_flipboard->set_stupidly_fast(true);
	$feed_flipboard->set_feed_url($flipboardarray);
	$streamcachesetting=constant("STREAM_CACHE");
	$location = 'mysql://'.$db_user.':'.$db_password.'@hostname:port/database?prefix=sp_';
	$feed_flipboard->set_cache_location($location);
	$feed_flipboard->enable_cache($streamcachesetting);
	$feed_flipboard->init();
	$feed_flipboard->handle_content_type();

	$StreamStartResult = 0;
	$StreamEndResult = 24;
	if(isset($_GET["StreamStartResult"])){
		if($_GET["StreamStartResult"] > 0){
			$StreamStartResult = $_GET["StreamStartResult"];
		}else{
			$StreamStartResult = $_GET["StreamStartResult"];
		}
	}
	if(isset($_GET["StreamEndResult"])){ $StreamEndResult = $_GET["StreamEndResult"]; }
	date_default_timezone_set("EST");
	foreach($feed_flipboard->get_items($StreamStartResult,$StreamEndResult) as $item){
		$date = $item->get_date();
		$title = $item->get_title();
		$link = $item->get_link();
		$feedtitle = $item->get_feed()->get_title();
		$feedlink = $item->get_feed()->get_link();

		$date = strtotime($date);
		$excerpt = $item->get_description();
		if($enclosure = $item->get_enclosure()){
			$image=$enclosure->get_link();
		}

		if($image == ""){
				preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $excerpt, $embededimage);
				if(isset($embededimage['src'])){ $image=$embededimage['src']; }
		}

		$indexLink = $item->get_feed()->get_link(0, 'self');
		$protocols = array("https://", "http://");
		$indexLinkValue = str_replace($protocols, "", $indexLink);

		$color = $infoArray[$indexLinkValue]['color'];
		$feedtitle = "";
		$feedtitle = $infoArray[$indexLinkValue]['title'];
		if($feedtitle == ""){
			$feedtitle = "Stream Name";
		}
		array_push($feeds, array("date" => "$date", "title" => "$title", "excerpt" => "$excerpt", "link" => "$link", "image" => "$image", "feedtitle" => "$feedtitle", "feedlink" => "$feedlink", "color" => "$color", "type" => "stream", "id" => "", "owner" => ""));
		$totalcount++;
	}

	//Display the Feeds
	sort($feeds, SORT_DESC);
	$feeds = array_reverse($feeds);
	$cardcount = 0;
	for($cardcountloop = 0; $cardcountloop < $totalcount; $cardcountloop++){
		$date = $feeds[$cardcountloop]['date'];
		$title = $feeds[$cardcountloop]['title'];
		$title = str_replace("<p>", " ", $title);
		$title = strip_tags(html_entity_decode($title));
		$title = preg_replace('/(\.)([[:alpha:]]{2,})/', '$1 $2', $title);
		$title = str_replace("'",'"',$title);
		$title = str_replace('"',"'",$title);
		$title = str_replace('’',"'",$title);
		$title = str_replace('—',"-",$title);

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
		$rawexcerpt = $excerpt;

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
			echo "<div class='widget' style='padding:30px; text-align:center; width:100%;'><span style='font-size: 22px; font-weight:700'>Welcome to Your Stream</span><br><p style='font-size:16px; margin:20px 0 0 0;'>Get started by following a few streams.<br>You'll see the latest posts from the streams you follow here.</p></div>";
			echo "<a class='mdl-button mdl-js-button mdl-js-ripple-effect' style='background-color:".getSiteColor()."; color:#fff;' href='#profile'>View Available Streams</a>";
		echo "</div>";
	}


?>

<script>

	$(function(){

	  	//Make Streams Feeds Clickable
		$( ".cardclick" ).unbind().click(function(){
			window.open($(this).data('link'), '_blank');
		});

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

	});

</script>
