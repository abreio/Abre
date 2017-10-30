<?php

	/*
	* Copyright (C) 2016-2017 Abre.io LLC
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
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');

    if(superadmin()){

		echo "
			'settings': function(name) {
		    	$( '#navigation_top' ).hide();
				$( '#content_holder' ).hide();
				$( '#loader' ).show();
				$( '#titletext' ).text('Settings');
				document.title = 'Settings';
				$( '#content_holder' ).load( 'modules/settings/settings.php', function() { init_page(); });
	    	},
	    	'settings/integrations': function(name) {
		    	$( '#navigation_top' ).hide();
				$( '#content_holder' ).hide();
				$( '#loader' ).show();
				$( '#titletext' ).text('Settings');
				document.title = 'Settings';
				$( '#content_holder' ).load( 'modules/settings/integrations.php', function() { init_page(); });
	    	},
	    	'settings/parentaccess': function(name) {
		    	$( '#navigation_top' ).hide();
				$( '#content_holder' ).hide();
				$( '#loader' ).show();
				$( '#titletext' ).text('Settings');
				document.title = 'Settings';
				$( '#content_holder' ).load( 'modules/settings/parentaccess.php', function() { init_page(); });
	    	},";
	}
?>