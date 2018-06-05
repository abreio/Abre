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
	require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

	//Settings
	if(superadmin())
	{
		echo "<form id='form-assessments-settings' method='post' enctype='multipart/form-data' action='modules/".basename(__DIR__)."/updatesettings.php'>";
			echo "<div class='mdl-shadow--2dp' style='background-color:#fff; padding:20px 40px 40px 40px'>";

					$query = "SELECT Certica_URL, Certica_AccessKey FROM assessments_settings";
					$dbreturn = databasequery($query);
					foreach ($dbreturn as $value)
					{
						$Certica_URL=$value["Certica_URL"];
						$Certica_AccessKey=$value["Certica_AccessKey"];
					}

					echo "<div class='row'>";
						echo "<div class='input-field col s12' style='margin-top:0;'>";
							echo "<h4>Certica Solutions</h4>";
						echo "</div>";
					echo "</div>";
					//Settings
					echo "<div class='row'>";
						echo "<div class='input-field col s12'>";
							echo "<input placeholder='Enter Certica REST API Base URL' value='$Certica_URL' id='certicabaseurl' name='certicabaseurl' type='text'>";
							echo "<label class='active' for='certicabaseurl'>Certica REST API Base URL</label>";
						echo "</div>";
					echo "</div>";
					echo "<div class='row'>";
						echo "<div class='input-field col s12'>";
							echo "<input placeholder='Enter Certica Customer AccessKey' value='$Certica_AccessKey' id='certicaaccesskey' name='certicaaccesskey' type='text'>";
							echo "<label class='active' for='certicaaccesskey'>Certica Customer AccessKey</label>";
						echo "</div>";
					echo "</div>";

					//Save Button
					echo "<div class='row'>";
						echo "<div class='col s12'>";
							echo "<button type='submit' class='modal-action waves-effect btn-flat white-text' style='background-color: ".getSiteColor()."'>Save Changes</button>";
						echo "</div>";
					echo "</div>";

					//Help Video
					echo "<div class='row'>";
						echo "<div class='col s12'>";
							echo "Not connected to Certica? Check out the demo video <a href='https://vimeo.com/212144253' target='_blank' style='color: ".getSiteColor()."'>here</a>.";
						echo "</div>";
					echo "</div>";

			echo "</div>";
		echo "</form>";
	}

?>

<script>

	//Save Settings
	var form = $('#form-assessments-settings');
	$(form).submit(function(event)
	{
		event.preventDefault();
		var data = new FormData($(this)[0]);
		var url = $(form).attr('action');
		$.ajax({ type: 'POST', url: url, data: data, contentType: false, processData: false })

		//Show the notification
		.done(function(response)
		{
			location.reload();
		})
	});

</script>