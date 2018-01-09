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

		try{
	
			//Set Access Token
			if(isset($_SESSION['access_token']) && $_SESSION['access_token']){ $client->setAccessToken($_SESSION['access_token']); }
	
			//Get Gmail content
			if($client->getAccessToken()){
	
				$_SESSION['access_token'] = $client->getAccessToken();
	
				$label = $Service_Gmail->users_labels->get('me', 'INBOX');
				$Gmail_Unread = $label->messagesUnread;

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
						echo "<div class='widget_holder widget_holder_link pointer' data-link='https://mail.google.com/mail/u/0/#inbox/$message_id' data-path='/modules/mail/widget_content.php' data-reload='true'>";
							echo "<div class='widget_container widget_heading_h1 truncate'>$sendertext</div>";
							if($sendertext!="Google"){ echo "<div class='widget_container widget_heading_h2 truncate'>$subjecttext</div>"; }
							if($sendertext!=""){ echo "<div class='widget_container widget_body truncate'>$snippet</div>"; }
						echo "</div>";
		    		}	
	
				}
			}

		}catch(Exception $e){ }
	
	}
	
?>