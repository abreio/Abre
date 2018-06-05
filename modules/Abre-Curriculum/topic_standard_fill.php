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
	require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

	$subjectvalue=$_GET["subject"];
	$bankvalue=$_GET["bank"];
	$gradevalue=$_GET["grade"];

	if(!empty($subjectvalue) && !empty($bankvalue) && !empty($gradevalue))
	{

		$subjectvalue = mysqli_real_escape_string($db, $subjectvalue);
		$bankvalue = mysqli_real_escape_string($db, $bankvalue);
		$gradevalue = mysqli_real_escape_string($db, $gradevalue);

		$sql = "SELECT returnId, standard_id, standard_statementNotation, standard_description FROM Abre_Standards_Description WHERE (subject='$subjectvalue' AND document_title='$bankvalue' AND educationLevel='$gradevalue') GROUP BY standard_description ORDER BY standard_statementNotation, standard_description";
		$result = $db->query($sql);
		while($row = $result->fetch_assoc())
		{
			$returnId=htmlspecialchars($row["returnId"], ENT_QUOTES);
			$standard_id=htmlspecialchars($row["standard_id"], ENT_QUOTES);
			$standard_statementNotation=htmlspecialchars($row["standard_statementNotation"], ENT_QUOTES);
			$standard_description=$row["standard_description"];

			if($standard_statementNotation!=''){
				echo "<option value='$standard_id'>$standard_statementNotation - $standard_description</option>";
			}
			else{
				echo "<option value='$standard_id'>$standard_description</option>";
			}

		}
	}
	else
	{
		echo "<option value=''></option>";
	}

?>
