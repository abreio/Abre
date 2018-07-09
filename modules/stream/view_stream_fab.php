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
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');

?>

	<div class='fixed-action-btn buttonpin pointer'>
		<a class='btn-floating btn-large waves-effect waves-light streampost' id="streamFab" style='background-color: <?php echo getSiteColor(); ?>' href='#streampost'><i class='large material-icons'>add</i></a>
		<div class="mdl-tooltip mdl-tooltip--left" for="streamFab">New Announcement</div>
	</div>