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

	//The Domain/Location of Abre
	$portal_root = "https://yourdomain.org";

	//The Title of Your Site
	if (!defined('SITE_TITLE')){ define('SITE_TITLE','Abre'); }

	//The Description of Your Site
	if (!defined('SITE_DESCRIPTION')){ define('SITE_DESCRIPTION','Our Abre Platform'); }

	//Your Google Apps Domain Name
	if (!defined('SITE_GAFE_DOMAIN')){ define('SITE_GAFE_DOMAIN','@yourdomain.org'); }

	//The Help Text on the Login Page
	if (!defined('SITE_LOGIN_TEXT')){ define('SITE_LOGIN_TEXT','Your District Portal'); }

	//MySQL Host
	if (!defined('DB_HOST')){ define('DB_HOST','localhost'); }

	//MySQL Socket
	if (!defined('DB_SOCKET')){ define('DB_SOCKET','/cloudsql/socket'); }

	//MySQL Username
	if (!defined('DB_USER')){ define('DB_USER','username'); }

	//MySQL Password
	if (!defined('DB_PASSWORD')){ define('DB_PASSWORD','password'); }

	//MySQL Database Name
	if (!defined('DB_NAME')){ define('DB_NAME','nameofdatabase'); }

	//MySQL (The Encryption Key Used to Store Encrypted Data. Must be a 32 Randomly Generated Key)
	if (!defined('DB_KEY')){ define('DB_KEY','XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'); }

	//Google Cloud Project ID
	if (!defined('GC_PROJECT')){ define('GC_PROJECT','project_id'); }

	//Google Cloud Storage Bucket
	if (!defined('GC_BUCKET')){ define('GC_BUCKET','bucket_name'); }

	//Google Console Client ID
	if (!defined('GOOGLE_CLIENT_ID')){ define('GOOGLE_CLIENT_ID','XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'); }

	//Google Console Client Secret
	if (!defined('GOOGLE_CLIENT_SECRET')){ define('GOOGLE_CLIENT_SECRET','XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'); }

	//Google Console API Key
	if (!defined('GOOGLE_API_KEY')){ define('GOOGLE_API_KEY','XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'); }

	//Cookie (The Encryption Key Used to Store Encrypted Cookie Data. Must be a 10 Digit Randomly Generated Key)
	if (!defined('PORTAL_COOKIE_KEY')){ define('PORTAL_COOKIE_KEY','XXXXXXXXXX'); }

	//Cookie (The Name Used to Store the Cookie)
	if (!defined('PORTAL_COOKIE_NAME')){ define('PORTAL_COOKIE_NAME','Cookie_Name'); }

	/* That's all, stop editing! */

	ini_set('display_errors','off');

	$portal_path_root = $_SERVER['DOCUMENT_ROOT'];

	if (!defined('GOOGLE_REDIRECT')){ define('GOOGLE_REDIRECT', $portal_root.'/index.php'); }

	if (!defined('GOOGLE_SCOPES')){ define('GOOGLE_SCOPES',serialize (array(
		'https://www.googleapis.com/auth/userinfo.email',
		'https://www.googleapis.com/auth/gmail.modify',
		'https://www.googleapis.com/auth/drive.readonly',
		'https://www.googleapis.com/auth/calendar.readonly',
		'https://www.googleapis.com/auth/classroom.courses.readonly',
		'https://www.googleapis.com/auth/classroom.rosters.readonly')));
	}

	if (!defined('STREAM_CACHE')){ define('STREAM_CACHE','true'); }

	//Site Mode - Can either be DEMO or PRODUCTION
	if(!defined('SITE_MODE')){ define('SITE_MODE', 'PRODUCTION'); }

	if (!defined('USE_GOOGLE_CLOUD')){ define('USE_GOOGLE_CLOUD','false'); }

?>