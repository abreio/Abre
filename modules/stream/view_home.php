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
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');
	require_once(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

	//Display Stream and Widgets
	echo "<div class='row'>";

		//Stream
		echo "<div id='streamstream'>";

			//Display Stream Buttons
			require('view_navigation.php');

			//Display Stream
			echo "<div id='streamcards'>";

				require('view_stream_announcements.php');

			echo "</div>";

			//Loader
			echo "<div style='clear:both; height:60px;'><div id='showmorestream' style='display:none; position:relative; top:50%; left:50%; margin-left:-20px;'>";
				echo "<div class='mdl-spinner mdl-js-spinner is-active'></div>";
			echo "</div></div>";

			//Scroll to Top
			echo "<div class='scrollbutton pointer' style='display:none; position:fixed; width:90px; left:50%; bottom:10px; margin-left:-45px; text-align:center; z-index:1000;'><a href ='#' style='font-size:12px; color:#fff; padding:5px 10px; border-radius:3px; background-color: ".getSiteColor()."; white-space: nowrap;'>Scroll To Top</a></div>";

		echo "</div>";

		//Widget
		echo "<div id='streamwidgets'>";

			//Display Widgets
			require('view_widgets.php');

		echo "</div>";

		if(admin() || AdminCheck($_SESSION['useremail']) || isStreamHeadlineAdministrator()){
			require "view_stream_fab.php";
		}

	echo "</div>";

?>

<script>

	$(function(){

		//Variables
		var Page = "announcements";
		var StreamStart = 0;
		var StreamEnd = 24;
		var LoaderCheck = false;

		//Paging function
		function streamPaging(start,end){

			$.get('modules/stream/view_stream_'+Page+'.php?StreamStartResult='+start+'&StreamEndResult='+end, function(results){
				$('#showmorestream').hide();
			    $('#streamcards').append(results);
			});

		}

		//Detect when at bottom of page
		$('.mdl-layout__content').on('scroll', function(){

			if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight){

				LoaderCheck = $('#showmorestream').is(":visible");

				if(LoaderCheck === false){

				  $('#showmorestream').show();
				  StreamStart = StreamStart + 24;
					StreamEnd = 24;
					streamPaging(StreamStart,StreamEnd);

				}

			}

    });

		function pageChange(Page){

			StreamStart = 0;
			StreamEnd = 24;

			$('#news, #likes, #comments, #announcements').prop("disabled", false);
			$('#streamnavigationloader').show();
			$.get('modules/stream/view_stream_'+Page+'.php?StreamStartResult='+StreamStart+'&StreamEndResult='+StreamEnd, function(results){
				$('#streamnavigationloader').hide();
			    $('#streamcards').html(results);
			});

		}

		//View News
		$( "#news" ).unbind().click(function(){
			pageChange("news");
			Page = "news";
			$("#news").attr( "disabled", "disabled" );
		});

		//View Likes
		$("#likes").unbind().click(function(){
			pageChange("likes");
			Page = "likes";
			$("#likes").attr( "disabled", "disabled" );
		});

		//View Comments
		$("#comments").unbind().click(function(){
			pageChange("comments");
			Page = "comments";
			$("#comments").attr( "disabled", "disabled" );
		});

		//View Announcements
		$("#announcements").unbind().click(function(){
			pageChange("announcements");
			Page = "announcements";
			$("#announcements").attr( "disabled", "disabled" );
		});

		//Scroll to Top Functionality
		$('.mdl-layout__content').scroll(function(){
			if($(this).scrollTop() > 1000){
				$(".scrollbutton").fadeIn();
			}else{
				$(".scrollbutton").fadeOut();
			}
		});

		//Scroll to Top Button
		$('.scrollbutton').click(function(){
			$('.mdl-layout__content').animate({scrollTop : 0},500);
			return false;
		});

		//Add a Custom Post
		$(".streampost").unbind().click(function(event){
			event.preventDefault();
			$("#post_title").val('');
			$("#post_stream").val('');
			$("#post_content").html('');
			tinymce.get("post_content").setContent('');
			$("#customimage").val('');
			$('#post_image').hide();
			$("#postStudentRestrictions").val('No Restrictions');
			$("#postStaffRestrictions").val('No Restrictions');
			$("#postStudentRestrictionsDiv").hide();
			$("#postStaffRestrictionsDiv").hide();

			$('#streampost').openModal({
				in_duration: 0,
				out_duration: 0,
				ready: function(){
					$('.modal-content').animate({ scrollTop: 0}, 0);
					$('select').material_select();
				}
			});
		});


	});


</script>
