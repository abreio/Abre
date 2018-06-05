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

	//Update system settings
	if(superadmin())
	{
		//Retrieve settings and group as json
		$certicabaseurl=$_POST["certicabaseurl"];
		$certicaaccesskey=$_POST["certicaaccesskey"];

		//Check to see if there are saved settings
		$sql = "SELECT COUNT(*) FROM assessments_settings";
		$result = $db->query($sql);
		$returnrow = $result->fetch_assoc();
		$rowcount = $returnrow["COUNT(*)"];

		if($rowcount!=0)
		{
			//Update the database
			mysqli_query($db, "UPDATE assessments_settings SET Certica_URL='$certicabaseurl', Certica_AccessKey='$certicaaccesskey'") or die (mysqli_error($db));
		}
		else
		{
			//Add to the database
			mysqli_query($db, "INSERT INTO assessments_settings (ID, Certica_URL, Certica_AccessKey) VALUES (NULL, '$certicabaseurl', '$certicaaccesskey')") or die (mysqli_error($db));
		}

		$db->close();
	}

?>