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

	//Google API PHP library files
	require_once 'google/vendor/autoload.php';

  //Create Client request to access Google API
  $client = new Google_Client();
  $client->setApplicationName("Abre");
  $client_id = getSiteGoogleClientId();
  $client->setClientId($client_id);
  $client_secret = getSiteGoogleClientSecret();
  $client->setClientSecret($client_secret);
  $redirect_uri = $portal_root.'/core/abre_google_login_helper.php';
  $client->setRedirectUri($redirect_uri);
  $simple_api_key = constant("GOOGLE_API_KEY");
  $client->setDeveloperKey($simple_api_key);
  $client->setAccessType("offline");
  $client->setApprovalPrompt("force");
  $scopes='https://www.googleapis.com/auth/userinfo.email';
  $client->setScopes($scopes);
	$client->setIncludeGrantedScopes(true);

  $Service_Oauth2 = new Google_Service_Oauth2($client);
?>
