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

	$schoolCodeArray = getRestrictions();
	$codeArraySize = sizeof($schoolCodeArray);

	//Display customized apps for staff
	if($_SESSION['usertype'] == "staff"){

		//Display Staff Apps
		echo "<div class='row'><p style='text-align:center; font-weight:600;'>My Staff Apps</p><hr style='margin-bottom:20px;'>";
			echo "<ul class='appssort'>";

			$required = array();

			//Get App preference settings (if they exist)
			$sql2 = "SELECT apps_order FROM profiles WHERE email = '".$_SESSION['useremail']."'";
			$result2 = $db->query($sql2);
			$apps_order = NULL;
			while($row2 = $result2->fetch_assoc()) {
				$apps_order = htmlspecialchars($row2["apps_order"], ENT_QUOTES);
			}

			//Build Array of Required Apps
			$sql = "SELECT id, staff_building_restrictions FROM apps WHERE staff = 1 AND required = 1";
			$result = $db->query($sql);
			while($row = $result->fetch_assoc()){
				$id = htmlspecialchars($row["id"], ENT_QUOTES);
				$restrictions = $row['staff_building_restrictions'];
				$restrictionsArray = explode(",", $restrictions);
				if($restrictions == NULL || in_array("No Restrictions", $restrictionsArray)){
					array_push($required, $id);
				}else{
					if($codeArraySize >= 1){
						foreach($schoolCodeArray as $code){
							if(in_array($code, $restrictionsArray)){
								array_push($required, $id);
								break;
							}
						}
					}
				}
			}

			//Display default order, unless they have saved prefrences
			if($apps_order != NULL){
				$order = explode(',', $apps_order);
			}else{
				$order = array();
			}

			//Compare
			foreach($required as $requiredvalue){
				$hit = NULL;
				foreach($order as $ordervalue){
					if($requiredvalue == $ordervalue){
						$hit = "yes";
					}
				}
				if($hit == NULL)
				{
					array_push($order, $requiredvalue);
				}
			}
			if($apps_order != NULL){
				foreach($order as $value){
					$sql = "SELECT id, title, image, link, staff_building_restrictions FROM apps WHERE id = '$value' AND staff = 1";
					$result = $db->query($sql);
					while($row = $result->fetch_assoc()){
						$id = htmlspecialchars($row["id"], ENT_QUOTES);
						$title = htmlspecialchars($row["title"], ENT_QUOTES);
						$image = htmlspecialchars($row["image"], ENT_QUOTES);
						$link = htmlspecialchars($row["link"], ENT_QUOTES);
						$restrictions = $row["staff_building_restrictions"];
						$restrictionsArray = explode(",", $restrictions);
						if($restrictions == NULL || in_array("No Restrictions", $restrictionsArray)){
							echo "<li id='item_$id' class='col s4 app' style='display:block; height:110px; overflow:hidden; word-wrap: break-word; margin:0 0 10px 0 !important;'>";
								echo "<img src='$portal_root/core/images/apps/$image' class='appicon_modal'>";
								echo "<span><a href='$link' class='applink truncate' style='display:block;'>$title</a></span>";
							echo "</li>";
						}
						else{
							if($codeArraySize >= 1){
								foreach($schoolCodeArray as $code){
									if(in_array($code, $restrictionsArray)){
										echo "<li id='item_$id' class='col s4 app' style='display:block; height:110px; overflow:hidden; word-wrap: break-word; margin:0 0 10px 0 !important;'>";
											echo "<img src='$portal_root/core/images/apps/$image' class='appicon_modal'>";
											echo "<span><a href='$link' class='applink truncate' style='display:block;'>$title</a></span>";
										echo "</li>";
										break;
									}
								}
							}
						}
					}
				}
			}else{
				$sql = "SELECT id, title, image, link, staff_building_restrictions FROM apps WHERE staff = 1 ORDER BY sort";
				$result = $db->query($sql);
				while($row = $result->fetch_assoc()){
					$id = htmlspecialchars($row["id"], ENT_QUOTES);
					$title = htmlspecialchars($row["title"], ENT_QUOTES);
					$image = htmlspecialchars($row["image"], ENT_QUOTES);
					$link = htmlspecialchars($row["link"], ENT_QUOTES);
					$restrictions = $row["staff_building_restrictions"];
					$restrictionsArray = explode(",", $restrictions);
					if($restrictions == NULL || in_array("No Restrictions", $restrictionsArray)){
						echo "<li id='item_$id' class='col s4 app' style='display:block; height:110px; overflow:hidden; word-wrap: break-word; margin:0 0 10px 0 !important;'>";
							echo "<img src='$portal_root/core/images/apps/$image' class='appicon_modal'>";
							echo "<span><a href='$link' class='applink truncate' style='display:block;'>$title</a></span>";
						echo "</li>";
					}else{
						if($codeArraySize >= 1){
							foreach($schoolCodeArray as $code){
								if(in_array($code, $restrictionsArray)){
									echo "<li id='item_$id' class='col s4 app' style='display:block; height:110px; overflow:hidden; word-wrap: break-word; margin:0 0 10px 0 !important;'>";
										echo "<img src='$portal_root/core/images/apps/$image' class='appicon_modal'>";
										echo "<span><a href='$link' class='applink truncate' style='display:block;'>$title</a></span>";
									echo "</li>";
									break;
								}
							}
						}
					}
				}
			}
			echo "</ul>";
	  echo "</div>";
	}

	//Display parent and student apps
	if($_SESSION['usertype'] == 'parent'){
		echo "<div class='row'><p style='text-align:center; font-weight:600;'>Parent Apps</p><hr style='margin-bottom:20px;'>";
		$sql2 = "SELECT id, title, image, link FROM apps WHERE parent = 1 AND required = 1 ORDER BY sort";
		$result2 = $db->query($sql2);
		while($row2 = $result2->fetch_assoc()){
			$id = htmlspecialchars($row2["id"], ENT_QUOTES);
			$title = htmlspecialchars($row2["title"], ENT_QUOTES);
			$image = htmlspecialchars($row2["image"], ENT_QUOTES);
			$link = htmlspecialchars($row2["link"], ENT_QUOTES);
			echo "<div class='col s4 app' style='display:block; height:110px; overflow:hidden; word-wrap: break-word; margin:0 0 10px 0 !important;'><div><img src='$portal_root/core/images/apps/$image' class='appicon_modal'></div><span><a href='$link' class='applink truncate' style='display:block;'>$title</a></span></div>";
		}
		echo "</div>";
	}else{
		if($_SESSION['usertype'] == "staff"){
			//Display uneditable student apps
			echo "<div class='row'><p style='text-align:center; font-weight:600;'>Student Apps</p><hr style='margin-bottom:20px;'>";
			$sql2 = "SELECT id, title, image, link, student_building_restrictions FROM apps WHERE student = 1 AND required = 1 ORDER BY sort";
			$result2 = $db->query($sql2);
			while($row2 = $result2->fetch_assoc()){
				$id=htmlspecialchars($row2["id"], ENT_QUOTES);
				$title=htmlspecialchars($row2["title"], ENT_QUOTES);
				$image=htmlspecialchars($row2["image"], ENT_QUOTES);
				$link=htmlspecialchars($row2["link"], ENT_QUOTES);
				$restrictions = $row["student_building_restrictions"];
				$restrictionsArray = explode(",", $restrictions);
				if($restrictions == NULL || in_array("No Restrictions", $restrictionsArray)){
					echo "<div class='col s4 app' style='display:block; height:110px; overflow:hidden; word-wrap: break-word; margin:0 0 10px 0 !important;'><div><img src='$portal_root/core/images/apps/$image' class='appicon_modal'></div><span><a href='$link' class='applink truncate' style='display:block;'>$title</a></span></div>";
				}else{
					if($codeArraySize >= 1){
						foreach($schoolCodeArray as $code){
							if(in_array($code, $restrictionsArray)){
								echo "<div class='col s4 app' style='display:block; height:110px; overflow:hidden; word-wrap: break-word; margin:0 0 10px 0 !important;'><div><img src='$portal_root/core/images/apps/$image' class='appicon_modal'></div><span><a href='$link' class='applink truncate' style='display:block;'>$title</a></span></div>";
								break;
							}
						}
					}
				}
			}
			echo "</div>";
		}else{
			//Display editable student apps
			echo "<div class='row'><p style='text-align:center; font-weight:600;'>Student Apps</p><hr style='margin-bottom:20px;'>";
				echo "<ul class='appssort'>";

				$required = array();

				//Get App preference settings (if they exist)
				$sql2 = "SELECT apps_order FROM profiles WHERE email = '".$_SESSION['useremail']."'";
				$result2 = $db->query($sql2);
				$apps_order = NULL;
				while($row2 = $result2->fetch_assoc()) {
					$apps_order = htmlspecialchars($row2["apps_order"], ENT_QUOTES);
				}

				//Build Array of Required Apps
				$sql = "SELECT id, student_building_restrictions FROM apps WHERE student = 1 AND required = 1";
				$result = $db->query($sql);
				while($row = $result->fetch_assoc()){
					$id = htmlspecialchars($row["id"], ENT_QUOTES);
					$restrictions = $row['student_building_restrictions'];
					$restrictionsArray = explode(",", $restrictions);
					if($restrictions == NULL || in_array("No Restrictions", $restrictionsArray)){
						array_push($required, $id);
					}else{
						if($codeArraySize >= 1){
							foreach($schoolCodeArray as $code){
								if(in_array($code, $restrictionsArray)){
									array_push($required, $id);
									break;
								}
							}
						}
					}
				}

				//Display default order, unless they have saved prefrences
				if($apps_order != NULL){
					$order = explode(',', $apps_order);
				}else{
					$order = array();
				}

				//Compare
				foreach($required as $requiredvalue){
					$hit = NULL;
					foreach($order as $ordervalue){
						if($requiredvalue == $ordervalue){
							$hit = "yes";
						}
					}
					if($hit == NULL){
						array_push($order, $requiredvalue);
					}
				}
				if($apps_order != NULL){
					foreach($order as $value){
						$sql = "SELECT id, title, image, link, student_building_restrictions FROM apps WHERE id= '$value' AND student = 1";
						$result = $db->query($sql);
						while($row = $result->fetch_assoc()){
							$id = htmlspecialchars($row["id"], ENT_QUOTES);
							$title = htmlspecialchars($row["title"], ENT_QUOTES);
							$image = htmlspecialchars($row["image"], ENT_QUOTES);
							$link = htmlspecialchars($row["link"], ENT_QUOTES);
							$restrictions = $row['student_building_restrictions'];
							$restrictionsArray = explode(",", $restrictions);
							if($restrictions == NULL || in_array("No Restrictions", $restrictionsArray)){
								echo "<li id='item_$id' class='col s4 app' style='display:block; height:110px; overflow:hidden; word-wrap: break-word; margin:0 0 10px 0 !important;'>";
									echo "<img src='$portal_root/core/images/apps/$image' class='appicon_modal'>";
									echo "<span><a href='$link' class='applink truncate' style='display:block;'>$title</a></span>";
								echo "</li>";
							}else{
								if($codeArraySize >= 1){
									foreach($schoolCodeArray as $code){
										if(in_array($code, $restrictionsArray)){
											echo "<li id='item_$id' class='col s4 app' style='display:block; height:110px; overflow:hidden; word-wrap: break-word; margin:0 0 10px 0 !important;'>";
												echo "<img src='$portal_root/core/images/apps/$image' class='appicon_modal'>";
												echo "<span><a href='$link' class='applink truncate' style='display:block;'>$title</a></span>";
											echo "</li>";
											break;
										}
									}
								}
							}
						}
					}
				}else{
					$sql = "SELECT id, title, image, link, student_building_restrictions FROM apps WHERE student = 1 ORDER BY sort";
					$result = $db->query($sql);
					while($row = $result->fetch_assoc()){
						$id = htmlspecialchars($row["id"], ENT_QUOTES);
						$title = htmlspecialchars($row["title"], ENT_QUOTES);
						$image = htmlspecialchars($row["image"], ENT_QUOTES);
						$link = htmlspecialchars($row["link"], ENT_QUOTES);
						$restrictions = $row['student_building_restrictions'];
						$restrictionsArray = explode(",", $restrictions);
						if($restrictions == NULL || in_array("No Restrictions", $restrictionsArray)){
							echo "<li id='item_$id' class='col s4 app' style='display:block; height:110px; overflow:hidden; word-wrap: break-word; margin:0 0 10px 0 !important;'>";
								echo "<img src='$portal_root/core/images/apps/$image' class='appicon_modal'>";
								echo "<span><a href='$link' class='applink truncate' style='display:block;'>$title</a></span>";
							echo "</li>";
						}else{
							if($codeArraySize >= 1){
								foreach($schoolCodeArray as $code){
									if(in_array($code, $restrictionsArray)){
										echo "<li id='item_$id' class='col s4 app' style='display:block; height:110px; overflow:hidden; word-wrap: break-word; margin:0 0 10px 0 !important;'>";
											echo "<img src='$portal_root/core/images/apps/$image' class='appicon_modal'>";
											echo "<span><a href='$link' class='applink truncate' style='display:block;'>$title</a></span>";
										echo "</li>";
										break;
									}
								}
							}
						}
					}
				}
				echo "</ul>";
			echo "</div>";
		}
	}
	$db->close();

	//Display Apps Editor if admin
	if(admin() && $_SESSION['usertype'] == 'staff'){
		echo "<div class='row center-align'><a href='#appeditor' class='modal-editapps waves-effect btn-flat white-text' style='background-color: "; echo getSiteColor(); echo "'>Manage</a></div>";
	}
?>

<script>

	$(function(){
		//Load Masonry
		function checkWidthApps(){
			if ($(window).width() > 600){
				//Sortable settings
				$( ".appssort" ).sortable({
					cursorAt: { top: 25, left: 45 },
					update: function(event, ui){
						var postData = $(this).sortable('serialize');
						<?php
							echo "$.post('$portal_root/modules/apps/apps_save_order.php', {list: postData})";
						?>
						.done(function(){
							if (typeof loadOtherCardsApps == 'function'){
								loadOtherCardsApps();
							}
						});
					}
				});
			}
		}
		checkWidthApps();

		//Make the Icons Clickable
		$(".app").unbind().click(function(event){

			//Open link
			event.preventDefault();
			window.open($(this).find("a").attr("href"), '_blank');

			//Close dock
		    $("#viewapps_arrow").hide();
		    $('#viewapps').closeModal({ in_duration: 0, out_duration: 0, });

	    //Track click
	    var linktitle = '/#apps/'+$(this).find("a").text()+'/';
	    ga('set', 'page', linktitle);
			ga('send', 'pageview');

		});

		//Hide truncate on hover
		$(".app").hover(function() {
			$(this).find('.applink').toggleClass("truncate");
		});

		//Apps Modal
		<?php
		if(admin()){
		?>
		    $('.modal-editapps').leanModal({
					in_duration: 0,
					out_duration: 0,
					ready: function() {
						$('.modal-content').scrollTop(0);
				    $("#viewapps_arrow").hide();
						$('#viewapps').closeModal({ in_duration: 0, out_duration: 0, });
					},
				});
		<?php
		}
		?>
	});

</script>