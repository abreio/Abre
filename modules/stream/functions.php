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
	require_once(dirname(__FILE__) . '/../../api/streams-api.php');
	require_once(dirname(__FILE__) . '/../../api/profile-api.php');

	//Display Card
	function DisplayCard($id,$type,$color,$owner,$feedtitle,$title,$image,$date,$rawexcerpt,$excerpt,$linkraw,$link,$num_rows_like,$num_rows_comment,$cardcountloop){

		require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

		$linkbase = base64_encode($linkraw);
		$linkescaped = htmlspecialchars($linkraw, ENT_QUOTES);
		$imagebase = base64_encode($image);
		$displaydate = date("F jS, Y", $date);
		$titleencoded = htmlspecialchars($title, ENT_QUOTES);
		$titlewithoutlongwords = preg_replace('~\b\S{30,}\b~', '', $title);

		//Display Card
		echo "<div class='mdl-card mdl-shadow--2dp card_stream hoverable' style='float:left;'>";

			//Stream
			echo "<div class='truncate' style='padding:20px 20px 0 20px;'>";
				if($color != ""){
					echo "<a href='#addstreamcomment' class='chip modal-readstream'  data-commenticonid='comment_$cardcountloop' data-image='$imagebase' data-redirect='latest' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-type='$type' style='background-color: $color; color: #fff; height:20px; line-height:20px; margin-bottom: 0px; font-weight: 500;' target='_blank'>$feedtitle</a>";
				}else{
					echo "<a href='#addstreamcomment' class='chip modal-readstream' data-commenticonid='comment_$cardcountloop' data-image='$imagebase' data-redirect='latest' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-type='$type' style='background-color: #BDBDBD; color: #fff; height:20px; line-height:20px; margin-bottom: 0px; font-weight: 500;' target='_blank'>$feedtitle</a>";
				}
				if($type == "custom" && ($owner == $_SESSION['useremail'] || admin())){
					echo "<div class='right-align pointer' style='float:right; position:absolute; right:15px; top:18px; z-index:5;'><a class='removepost' data-id='$id'><i class='material-icons' style='font-size: 16px; color: #333;'>clear</i></a></div>";
				}
			echo "</div>";

			//Title
			echo "<div class='cardtitle' style='padding:10px 20px 0px 20px; height:65px;'>";
				echo "<div class='mdl-card__title-text ellipsis-multiline pointer modal-readstream' data-commenticonid='comment_$cardcountloop' data-image='$imagebase' data-redirect='latest' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-type='$type' style='font-weight:700; font-size:20px; line-height:24px;'>$titlewithoutlongwords</div>";
			echo "</div>";

			//Date
			echo "<div class='truncate' style='padding:0 20px 20px 20px;'>";
				echo "<a class='modal-readstream' href='#addstreamcomment' data-commenticonid='comment_$cardcountloop' data-image='$imagebase' data-redirect='latest' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-type='$type' style='color: #9E9E9E;  font-size: 14px;' target='_blank'>$displaydate</a>";
			echo "</div>";

			//Card Image
			if($image != ""){
				echo "<div class='mdl-card__media mdl-color--grey-100 mdl-card--expand pointer modal-readstream' data-commenticonid='comment_$cardcountloop' data-image='$imagebase' data-redirect='latest' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-type='$type' style='height:200px; background-image: url($image);'></div>";
			}
			else
			{

				if ($excerpt!=""){ $body = $excerpt; }else{ $body = $feedtitle; }
				if (strlen($body) > 100){
					$body = substr( $body, 0, strrpos( substr( $body, 0, 100), ' ' ));
					$body = substr($body, 0, 97) . ' ...';
				}

				echo "<div class='mdl-card__media mdl-color--grey-100 mdl-card--expand valign-wrapper pointer modal-readstream' data-image='$imagebase' data-redirect='latest' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-type='$type' style='height:200px; background-image: url(/core/images/abre/abre_pattern.png); background-color: ".getSiteColor()." !important; overflow:hidden;'>";
					echo "<span class='wrap-links' style='width:100%; color:#fff; padding:32px; font-size:18px; line-height:normal; font-weight:700; text-align:center;'>$body</span>";
				echo "</div>";

			}

			//Card Actions
			echo "<div class='mdl-card__actions'>";

				//Read Button
				echo "<a class='mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect modal-readstream' href='#addstreamcomment' data-commenticonid='comment_$cardcountloop' data-image='$imagebase' data-redirect='latest' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-type='$type' style='color: ".getSiteColor()."'>Read</a>";

				//Share, Likes, Comments for Staff Only
				if($_SESSION['usertype'] == 'staff'){

					echo "<div class='mdl-layout-spacer'></div>";

					//Share
					if($type != "custom"){
						echo "<a class='material-icons mdl-color-text--grey-600 modal-sharecard commenticon shareinfo' style='margin-right:30px;' data-url='$linkbase' title='Share' href='#sharecard'>share</a>";
					}

					//Likes
					if (useAPI()) {
						$apiValue = apiStreams::getStreamContentsByUrl(json_encode(array("url"=>$link)));
						$result = $apiValue['result'];
						$num_rows_like_current_user = $result['counts']['userLikes'];
					}
					else {
						$query = "SELECT COUNT(*) FROM streams_comments WHERE url = '$link' AND liked = '1' AND user = '".$_SESSION['useremail']."'";
						$dbreturn = $db->query($query);
						$resultrow = $dbreturn->fetch_assoc();
						$num_rows_like_current_user = $resultrow["COUNT(*)"];
					}

					if($num_rows_like == 0){
						echo "<a class='material-icons mdl-color-text--grey-600 likeicon' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-image='$imagebase' title='Like' href='#'>favorite</a> <span class='mdl-color-text--grey-600' style='font-size:12px; font-weight:600; width:30px; padding-left:5px;'>$num_rows_like</span>";
					}else{
						if($num_rows_like_current_user == 0){
							echo "<a class='material-icons mdl-color-text--grey-600 likeicon' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-image='$imagebase' href='#'>favorite</a> <span class='mdl-color-text--grey-600' style='font-size:12px; font-weight:600; width:30px; padding-left:5px;'>$num_rows_like</span>";
						}else{
							echo "<a class='material-icons mdl-color-text--red likeicon' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-image='$imagebase' href='#'>favorite</a> <span class='mdl-color-text--red' style='font-size:12px; font-weight:600; width:30px; padding-left:5px;'>$num_rows_like</span>";
						}
					}

					//Comments
					if($num_rows_comment == 0){
						echo "<a class='material-icons mdl-color-text--grey-600 modal-addstreamcomment commenticon' data-commenticonid='comment_$cardcountloop' data-image='$imagebase' data-redirect='latest' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-type='$type' title='Add a comment' href='#addstreamcomment'>insert_comment</a><span id='comment_$cardcountloop' style='font-size:12px; font-weight:600; width:30px; padding-left:5px; color:grey'>$num_rows_comment</span>";
					}else{
						echo "<a class='material-icons modal-addstreamcomment commenticon' style='color: ".getSiteColor().";' data-commenticonid='comment_$cardcountloop' data-image='$imagebase' data-redirect='latest' data-title='$titleencoded' data-excerpt='$rawexcerpt' data-url='$linkbase' data-type='$type' title='Add a comment' href='#addstreamcomment'>insert_comment</a> <span id='comment_$cardcountloop' style='font-size:12px; font-weight:600; width:30px; padding-left:5px; color: ".getSiteColor()."'>$num_rows_comment</span>";
					}
				}

			echo "</div>";

		echo "</div>";

	}

	//Display Widget
	function DisplayWidget($path,$icon,$title,$color,$url,$newtab){

		require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

		$URLPath = "/modules/$path/widget_content.php";

		//Check if widget is open for user
		$widgets_open = NULL;
		$active = "";
		$sql = "SELECT widgets_open FROM profiles WHERE email = '".$_SESSION['useremail']."'";
		$result = $db->query($sql);
		while($row = $result->fetch_assoc()) {
			$widgets_open = htmlspecialchars($row["widgets_open"], ENT_QUOTES);
		}

		$OpenWidgets = explode(',',$widgets_open);
		if(in_array($path, $OpenWidgets)){
			$active = "active";
		}

		echo "<ul class='widget mdl-card mdl-shadow--2dp hoverable' style='width:100%;' data-collapsible='accordion'>";
			echo "<li class='widgetli' data-path='$path'>";
				echo "<div class='collapsible-header $active' data-path='$URLPath' data-widget='$path' style='border-top: solid 3px $color;'>";
					echo "<span class='widgeticonlink' data-link='$url' data-newtab='$newtab'>";
						echo "<i class='material-icons' style='color: $color'>$icon</i>";
						echo "<span style='color:#000;'>$title</span>";
					echo "</span>";
					echo "<i class='right material-icons' style='color: #666; margin-right:2px;'>expand_more</i>";
				echo "</div>";
				echo "<div class='collapsible-body' id='widgetbody_$path'></div>";
			echo "</li>";
  		echo "</ul>";

	}

?>
