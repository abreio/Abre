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

		if($_SESSION['auth_service'] == "google"){
			try{

				//Set Access Token
				if(isset($_SESSION['access_token']) && $_SESSION['access_token']){ $client->setAccessToken($_SESSION['access_token']); }

				//Set Drive Parameters
				$parameters['pageSize'] = 3;
				$parameters['orderBy'] = "modifiedByMeTime desc";
				$parameters['fields'] = "files(modifiedTime,name,owners/displayName,webViewLink)";
				$parameters['q'] = "trashed=false and '".$_SESSION['useremail']."' in writers";

				//Request Drive Files
				$Drive_Results = $Service_Drive->files->listFiles($parameters);

				//Display Drive Files
				$counter=0;
				foreach($Drive_Results as $file){

					$counter++;
					$drivetitle = $file['name'];
					$drivelink = $file['webViewLink'];
					$drivemodifydate = $file['modifiedTime'];
					$drivemodifydate = date("m/d g:i A", strtotime($drivemodifydate));

					if($counter==1){
						echo "<hr class='widget_hr'>";
						echo "<div class='widget_holder'>";
							echo "<div class='widget_container widget_body' style='color:#666;'>Your Recent Files <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/drive/widget_content.php' data-reload='true'>refresh</i></div>";
						echo "</div>";
					}

					echo "<hr class='widget_hr'>";
						echo "<div class='widget_holder widget_holder_link pointer' data-link='$drivelink' data-newtab='true' data-path='/modules/drive/widget_content.php' data-reload='false'>";
						echo "<div class='widget_container widget_heading_h1 truncate'>$drivetitle</div>";
						echo "<div class='widget_container widget_body truncate'>$drivemodifydate</div>";
					echo "</div>";


				}

				//If no file, tell the user
				if(empty($Drive_Results))
				{
					echo "<hr class='widget_hr'>";
					echo "<div class='widget_holder pointer'>";
						echo "<div class='widget_container widget_body truncate' style='color:#666;'>No Drive files <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/drive/widget_content.php' data-reload='true'>refresh</i></div>";
					echo "</div>";
				}

			}catch(Exception $e){ }
		}
		if($_SESSION['auth_service'] == "microsoft"){
			try{
				//Set Access Token
				if(isset($_SESSION['access_token']) && $_SESSION['access_token']){
					getActiveMicrosoftAccessToken();
				}

				$accessToken = $_SESSION['access_token']['access_token'];

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://graph.microsoft.com/v1.0/me/drive/recent?\$top=3&\$select=lastModifiedDateTime,remoteItem,webUrl");
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $accessToken"));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);

				$driveItems = json_decode($result, true);

				//Display Drive Files
				$counter = 0;
				foreach($driveItems['value'] as $file){

					$counter++;
					$drivetitle = $file['remoteItem']['name'];
					$drivelink = $file['webUrl'];
					$drivemodifydate = $file['lastModifiedDateTime'];
					$drivemodifydate = date("m/d g:i A", strtotime($drivemodifydate));

					if($counter == 1){
						echo "<hr class='widget_hr'>";
						echo "<div class='widget_holder'>";
							echo "<div class='widget_container widget_body' style='color:#666;'>Your Recent Files <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/drive/widget_content.php' data-reload='true'>refresh</i></div>";
						echo "</div>";
					}

					echo "<hr class='widget_hr'>";
						echo "<div class='widget_holder widget_holder_link pointer' data-link='$drivelink' data-newtab='true' data-path='/modules/drive/widget_content.php' data-reload='false'>";
						echo "<div class='widget_container widget_heading_h1 truncate'>$drivetitle</div>";
						echo "<div class='widget_container widget_body truncate'>$drivemodifydate</div>";
					echo "</div>";
				}

				//If no file, tell the user
				if(empty($driveItems['value'])){
					echo "<hr class='widget_hr'>";
					echo "<div class='widget_holder pointer'>";
						echo "<div class='widget_container widget_body truncate' style='color:#666;'>No Drive files <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/drive/widget_content.php' data-reload='true'>refresh</i></div>";
					echo "</div>";
				}
			}catch(Exception $e){ }
		}


	}

?>