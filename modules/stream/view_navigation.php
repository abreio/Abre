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

	echo "<div class='mdl-card mdl-shadow--2dp' id='streamnavigation' style='border-top: solid 3px"; echo getSiteColor(); echo "'>";
		echo "<div class='row' style='padding:0; margin:0;'>";
			echo "<div class='col l9 m9 s12'>";
				echo "<div class='streamnavholder'>";
					echo "<button class='mdl-button mdl-js-button mdl-js-ripple-effect' id='announcements' style='padding: 0px 9px;' disabled>";
					echo "<i class='material-icons hide-on-large-only' style='font-size:16px; padding:0 4px 2px 0;'>announcement</i><span class='hide-on-med-and-down'>Announcements</span></button>";
				echo "</div>";
				echo "<div class='streamnavholder'>";
					echo "<button class='mdl-button mdl-js-button mdl-js-ripple-effect' id='news' style='padding: 0px 9px;'>";
					echo "<i class='material-icons hide-on-large-only' style='font-size:16px; padding:0 4px 2px 0;'>dashboard</i><span class='hide-on-med-and-down'>News</span></button>";
				echo "</div>";
				if($_SESSION['usertype'] == "staff"){
					echo "<div class='streamnavholder'>";
						echo "<button class='mdl-button mdl-js-button mdl-js-ripple-effect' id='likes' style='padding: 0px 9px;'>";
						echo "<i class='material-icons hide-on-large-only' style='font-size:16px; padding:0 4px 2px 0;'>favorite</i><span class='hide-on-med-and-down'>Likes</span></button>";
					echo "</div>";
					echo "<div class='streamnavholder'>";
						echo "<button class='mdl-button mdl-js-button mdl-js-ripple-effect' id='comments' style='padding: 0px 9px;'>";
						echo "<i class='material-icons hide-on-large-only' style='font-size:16px; padding:0 4px 2px 0;'>comment</i><span class='hide-on-med-and-down'>Comments</span></button>";
					echo "</div>";
				}
				echo "<div class='streamnavholder'>";
					echo "<a href='#profile' class='mdl-button mdl-js-button mdl-js-ripple-effect hide-on-med-and-up' style='padding: 0px 9px;'>";
					echo "<i class='material-icons' style='font-size:16px; padding:0 4px 2px 0;'>person</i></a>";
				echo "</div>";
			echo "</div>";
			echo "<div class='col l3 m3 hide-on-small-only' style='text-align:right;'>";
				echo "<a class='mdl-button mdl-js-button mdl-js-ripple-effect' href='#profile'>Streams</a>";
			echo "</div>";
		echo "</div>";
	echo "</div>";
	echo "<div id='streamnavigationloader' style='margin:8px; display:none;'><div class='progress'><div class='indeterminate'></div></div></div>";

?>
