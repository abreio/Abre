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
	require_once(dirname(__FILE__) . '/../../core/abre_google_login.php');

	if($_SESSION['usertype'] != 'parent'){

		echo "<hr class='widget_hr'>";
		echo "<div class='widget_holder'>";
			echo "<div class='widget_container widget_body' style='color:#666;'>Your Top 6 Apps <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/apps/widget_content.php' data-reload='true'>refresh</i></div>";
		echo "</div>";


		$query = "SELECT apps_order FROM profiles where email = '".$_SESSION['useremail']."'";
		$gafecards = databasequery($query);
		foreach ($gafecards as $value){
			$apps_order = htmlspecialchars($value["apps_order"], ENT_QUOTES);
		}

		//Display default order, unless they have saved prefrences
		if($apps_order != NULL){
			$order = explode(',', $apps_order);
		}else{
			$order = array();
		}

		$appcount = 0;
		if (!empty($order)){
			//Display customized list of apps
			foreach($order as $value){
				if ($appcount++ < 6){
					include(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
					$sql = "SELECT id, title, image, link FROM apps WHERE id = '$value' AND ".$_SESSION['usertype']." = 1";
					$result = $db->query($sql);
					while($row = $result->fetch_assoc()){
						if($appcount == 1){
							echo "<hr class='widget_hr'>";
						}
						$id = htmlspecialchars($row["id"], ENT_QUOTES);
						$title = htmlspecialchars($row["title"], ENT_QUOTES);
						$image = htmlspecialchars($row["image"], ENT_QUOTES);
						$link = htmlspecialchars($row["link"], ENT_QUOTES);
						echo "<div class='topapps' style='width:33.333%; float:left; text-align:center; margin:10px 0 10px 0;'>";
							echo "<img src='$portal_root/core/images/apps/$image' class='appicon_modal'>";
							echo "<span><a href='$link' class='applink truncate'>$title</a></span>";
						echo "</div>";
					}
				}
			}
			$db->close();
		}else{

			include(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
			$sql = "SELECT id, title, image, link FROM apps WHERE ".$_SESSION['usertype']." = 1 AND required = 1 LIMIT 6";
			$result = $db->query($sql);
			while($row = $result->fetch_assoc()){
				if($appcount == 0){
					echo "<hr class='widget_hr'>";
				}
				$id = htmlspecialchars($row["id"], ENT_QUOTES);
				$title = htmlspecialchars($row["title"], ENT_QUOTES);
				$image = htmlspecialchars($row["image"], ENT_QUOTES);
				$link = htmlspecialchars($row["link"], ENT_QUOTES);

				echo "<div class='topapps' style='width:33.333%; float:left; text-align:center; margin:10px 0 10px 0;'>";
					echo "<img src='$portal_root/core/images/apps/$image' class='appicon_modal'>";
					echo "<span><a href='$link' class='applink truncate'>$title</a></span>";
				echo "</div>";
				$appcount++;
			}
			$db->close();
		}

	}

?>

<script>

	$(function(){

		//Make the Icons Clickable
		$(".topapps").unbind().click(function(event) {

			event.preventDefault();
			window.open($(this).find("a").attr("href"), '_blank');

			//Track click
			var linktitle = '/#apps/'+$(this).find("a").text()+'/';
			ga('set', 'page', linktitle);
			ga('send', 'pageview');
		});

	});

</script>