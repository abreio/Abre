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
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');

	if($_SESSION['usertype'] != 'parent'){

		if($_SESSION['auth_service'] == "google"){
			try{
				//Set Access Token
				if(isset($_SESSION['access_token']) && $_SESSION['access_token']){ $client->setAccessToken($_SESSION['access_token']); }

				//Request Mail Files
				$inbox = $Service_Gmail->users_labels->get('me', 'INBOX');
				$Gmail_Unread = $inbox->messagesUnread;

				//Number of Unread Emails
				if($Gmail_Unread != '1'){
					echo "<hr class='widget_hr'><div class='widget_holder'><div class='widget_container widget_body' style='color:#666;'>$Gmail_Unread Unread Messages <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/mail/widget_content.php' data-reload='true'>refresh</i></div></div>";
				}else{
					echo "<hr class='widget_hr'><div class='widget_holder'><div class='widget_container widget_body' style='color:#666;'>$Gmail_Unread Unread Message <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/mail/widget_content.php' data-reload='true'>refresh</i></div></div>";
				}

				//Show Unread Mail
				if($Gmail_Unread != 0){

					$list = $Service_Gmail->users_messages->listUsersMessages('me',['maxResults' => 3, 'q' => 'is:unread label:inbox']);
					$messageList = $list->getMessages();
					$inboxMessage = [];

					foreach($messageList as $mlist){

						$optParamsGet2['format'] = 'full';
						$single_message = $Service_Gmail->users_messages->get('me',$mlist->id, $optParamsGet2);
						$message_id = $mlist->id;
						$headers = $single_message->getPayload()->getHeaders();
						$snippet = $single_message->getSnippet();

						foreach($headers as $single){

							if($single->getName() == 'Subject'){
								$subjecttext = $single->getValue();
										}else if($single->getName() == 'From'){
								$sendertext = $single->getValue();
								$sendertext = str_replace('"', '', $sendertext);
							}
								}

						echo "<hr class='widget_hr'>";
						echo "<div class='widget_holder widget_holder_link pointer' data-link='https://mail.google.com/mail/u/0/#inbox/$message_id' data-newtab='true' data-path='/modules/mail/widget_content.php' data-reload='true'>";
							echo "<div class='widget_container widget_heading_h1 truncate'>$sendertext</div>";
							if($sendertext!="Google"){ echo "<div class='widget_container widget_heading_h2 truncate'>$subjecttext</div>"; }
							if($sendertext!=""){ echo "<div class='widget_container widget_body truncate'>$snippet</div>"; }
						echo "</div>";
						}

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
				curl_setopt($ch, CURLOPT_URL, "https://graph.microsoft.com/v1.0/me/mailFolders('Inbox')/messages?\$filter=isRead+eq+false&\$select=webLink,sender,subject,bodyPreview");
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $accessToken"));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);

				$unreadEmailArray = json_decode($result, true);
				$Outlook_Unread = sizeof($unreadEmailArray['value']);

				//Number of Unread Emails
				if($Outlook_Unread != 1){
					echo "<hr class='widget_hr'><div class='widget_holder'><div class='widget_container widget_body' style='color:#666;'>$Outlook_Unread Unread Messages <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/mail/widget_content.php' data-reload='true'>refresh</i></div></div>";
				}else{
					echo "<hr class='widget_hr'><div class='widget_holder'><div class='widget_container widget_body' style='color:#666;'>$Outlook_Unread Unread Message <i class='right material-icons widget_holder_refresh pointer' data-path='/modules/mail/widget_content.php' data-reload='true'>refresh</i></div></div>";
				}

				//Show Unread Mail
				if($Outlook_Unread != 0){

					if($Outlook_Unread < 3){
						$size = $Outlook_Unread;
					}else{
						$size = 3;
					}

					for($i = 0; $i < $size; $i++){
						$webLink = $unreadEmailArray['value'][$i]['webLink'];
						$sendertext = $unreadEmailArray['value'][$i]['sender']['emailAddress']['name'];
						$subjecttext = $unreadEmailArray['value'][$i]['subject'];
						$snippet = $unreadEmailArray['value'][$i]['bodyPreview'];

						echo "<hr class='widget_hr'>";
						echo "<div class='widget_holder widget_holder_link pointer' data-link='$webLink' data-newtab='true' data-path='/modules/mail/widget_content.php' data-reload='true'>";
							echo "<div class='widget_container widget_heading_h1 truncate'>$sendertext</div>";
							if($sendertext != "Google"){ echo "<div class='widget_container widget_heading_h2 truncate'>$subjecttext</div>"; }
							if($sendertext != ""){ echo "<div class='widget_container widget_body truncate'>$snippet</div>"; }
						echo "</div>";
					}
				}
			}catch(Exception $e){ }
		}

	}

?>