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

				//Set Calendar Parameters
				$rightNow = date('c');
				$inOneDay = date('c', strtotime('+1 days'));
				$inTwoDays = date('c', strtotime('+2 days'));
				$params = array('singleEvents' => 'true', 'orderBy' => 'startTime', 'timeMin' => $rightNow, 'timeMax' => $inOneDay);

				//Request Calendar Files
				$events = $Service_Calendar->events->listEvents('primary', $params);

				$counter=0;
				foreach($events->getItems() as $event){

					$counter++;
					$eventitle = $event->getSummary();
					$eventlink = $event->getHtmlLink();
					$eventlocation = $event->getLocation();
					if($eventlocation == ""){
						$eventlocation = "Location: None Listed";
					}else{
						$eventlocation = "Location: $eventlocation";
					}
					$eventdescription = $event->getDescription();
					if($eventdescription == ""){
						$eventdescription = "No Description";
					}else{
						$eventdescription = "Summary: $eventdescription";
					}

					$AllDayCheck = $event->getStart()->getDate();
					if($AllDayCheck == NULL){
						$starttime = $event->getStart()->getDateTime();
						$starttime = date("m/d g:i A", strtotime($starttime));
						$endtime = $event->getEnd()->getDateTime();
						$endtime = date("m/d g:i A", strtotime($endtime));
						$timeofevent="$starttime to $endtime";
					}else{
						$timeofevent = substr($AllDayCheck, 5, 2)."/".substr($AllDayCheck, 8, 2);
						$timeofevent = "$timeofevent - All Day Event";
					}

					if($counter==1){
						echo "<hr class='widget_hr'><div class='widget_holder'><div class='widget_container widget_body' style='color:#666;'>Your Next 24 Hours <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/calendar/widget_content.php' data-reload='true'>refresh</i></div></div>";
					}

					echo "<hr class='widget_hr'>";
					echo "<div class='widget_holder widget_holder_link pointer' data-link='$eventlink' data-newtab='true' data-path='/modules/calendar/widget_content.php' data-reload='false'>";
						echo "<div class='widget_container widget_heading_h1 truncate'>$eventitle</div>";
						echo "<div class='widget_container widget_body truncate'>$timeofevent</div>";
					echo "</div>";

				}

				if(empty($events->getItems()))
				{
					echo "<hr class='widget_hr'>";
					echo "<div class='widget_holder pointer'>";
						echo "<div class='widget_container widget_body truncate' style='color:#666;'>No Upcoming Events <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/calendar/widget_content.php' data-reload='true'>refresh</i></div>";
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

				//Set Calendar Parameters
				$rightNow = urlencode(date('c'));
				$inOneDay = urlencode(date('c', strtotime('+1 days')));

				$accessToken = $_SESSION['access_token']['access_token'];

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://graph.microsoft.com/v1.0/me/events?\$filter=start/dateTime+ge+'$rightNow'+and+end/dateTime+le+'$inOneDay'&\$select=webLink,subject,isAllDay,start,end&\$orderby=start/dateTime");
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $accessToken"));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);

				$eventArray = json_decode($result, true);

				$counter = 0;
				foreach($eventArray['value'] as $event){

					$counter++;
					$eventitle = $event['subject'];
					$eventlink = $event['webLink'];

					$allDayCheck = $event['isAllDay'];

					if(!$allDayCheck){
						$starttime = $event['start']['dateTime'];
						$starttime = date("m/d g:i A", strtotime($starttime));

						$endtime = $event['end']['dateTime'];
						$endtime = date("m/d g:i A", strtotime($endtime));

						$timeofevent = "$starttime to $endtime";
					}else{
						$startDay = $event['start']['dateTime'];

						$timeofevent = date("m/d", strtotime($starttime));
						$timeofevent = "$timeofevent - All Day Event";
					}

					if($counter == 1){
						echo "<hr class='widget_hr'><div class='widget_holder'><div class='widget_container widget_body' style='color:#666;'>Your Next 24 Hours <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/calendar/widget_content.php' data-reload='true'>refresh</i></div></div>";
					}

					echo "<hr class='widget_hr'>";
					echo "<div class='widget_holder widget_holder_link pointer' data-link='$eventlink' data-path='/modules/calendar/widget_content.php' data-reload='false'>";
						echo "<div class='widget_container widget_heading_h1 truncate'>$eventitle</div>";
						echo "<div class='widget_container widget_body truncate'>$timeofevent</div>";
					echo "</div>";

				}

				if(empty($eventArray['value'])){
					echo "<hr class='widget_hr'>";
					echo "<div class='widget_holder pointer'>";
						echo "<div class='widget_container widget_body truncate' style='color:#666;'>No Upcoming Events <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/calendar/widget_content.php' data-reload='true'>refresh</i></div>";
					echo "</div>";
				}


			}catch(Exception $e){ }
		}

	}

?>