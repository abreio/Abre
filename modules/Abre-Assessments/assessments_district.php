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
	require_once(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');
	require_once('functions.php');
	require_once('logic.php');
	require_once('permissions.php');

	if($pagerestrictions=="")
	{

		$sql = "SELECT COUNT(*) FROM assessments WHERE Verified='1' ORDER BY Title";
		$result = $db->query($sql);
		$returnrow = $result->fetch_assoc();
		$rowcount = $returnrow["COUNT(*)"];
		if($rowcount!=0)
		{
			echo "<div class='mdl-shadow--2dp' style='background-color:#fff; padding:20px 40px 40px 40px'>";
			echo "<div class='row' style='padding:15px;'>";
		?>
			<table id='myTable' class='tablesorter bordered highlight'>
				<thead>
					<tr class='pointer'>
						<th></th>
						<th>Name</th>
						<th class='hide-on-med-and-down'>Subject</th>
						<th class='hide-on-med-and-down'>Level</th>
						<th style='width:35px'></th>
					</tr>
				</thead>
				<tbody>

				<?php
					$sql = "SELECT ID, Title, Description, Subject, Level, Grade, Locked, Shared, Verified, Owner, Editors, Session_ID FROM assessments WHERE Verified='1' ORDER BY Title";
					$result = $db->query($sql);
					while($row = $result->fetch_assoc())
					{
						$Assessment_ID=htmlspecialchars($row["ID"], ENT_QUOTES);
						$Title=htmlspecialchars($row["Title"], ENT_QUOTES);
						$Description=htmlspecialchars($row["Description"], ENT_QUOTES);
						$Subject=htmlspecialchars($row["Subject"], ENT_QUOTES);
						$Level=htmlspecialchars($row["Level"], ENT_QUOTES);
						$Grade=htmlspecialchars($row["Grade"], ENT_QUOTES);
						$Locked=htmlspecialchars($row["Locked"], ENT_QUOTES);
						$Shared=htmlspecialchars($row["Shared"], ENT_QUOTES);
						$Verified=htmlspecialchars($row["Verified"], ENT_QUOTES);
						$Owner=htmlspecialchars($row["Owner"], ENT_QUOTES);
						$Editors=htmlspecialchars($row["Editors"], ENT_QUOTES);
						$Session_ID=htmlspecialchars($row["Session_ID"], ENT_QUOTES);
						$firstCharacter = $Title[0];

						if (strpos($Editors, $_SESSION['useremail']) !== false) { $SharedEditable=1; }else{ $SharedEditable=0; }

						$Student_Link="$portal_root/?url=assessments/session/$Assessment_ID/$Session_ID";

						//Check to see if Guided Learning Code Created
						$sql2 = "SELECT Board_ID FROM `guide_links` WHERE `Data` LIKE '%$Student_Link%'";
						$result2 = $db->query($sql2);
						$GLCode="Code not yet available";
						while($row2 = $result2->fetch_assoc())
						{
							$Board_ID=htmlspecialchars($row2["Board_ID"], ENT_QUOTES);

							//Get the Code
							$sql3 = "SELECT Code FROM `guide_boards` WHERE ID='$Board_ID'";
							$result3 = $db->query($sql3);
							while($row3 = $result3->fetch_assoc())
							{
								$GLCode=htmlspecialchars($row3["Code"], ENT_QUOTES);
							}
						}

						echo "<tr class='assessmentrow'>";

							//Icon
							echo "<td width='50px'><div style='padding:5px; text-align:center; background-color:"; echo getSiteColor(); echo "; color:#fff; width:30px; height:30px; border-radius: 15px;'>$firstCharacter</div></td>";

							//Title
							echo "<td>$Title</td>";

							//Subject
							echo "<td class='hide-on-med-and-down'>$Subject</td>";

							//Level
							echo "<td class='hide-on-med-and-down'>$Level</td>";

							//More Button
							echo "<td width=35px>";
								echo "<div class='morebutton' style='position:absolute; margin-top:-15px;'>";
									echo "<button id='demo-menu-bottom-left-$Assessment_ID' class='mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect mdl-color-text--grey-600'><i class='material-icons'>more_vert</i></button>";
									echo "<ul class='mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect' for='demo-menu-bottom-left-$Assessment_ID'>";

										echo "<li class='mdl-menu__item modal-giveassessment' href='#giveassessment' data-givetitle='Guided Learning Code' data-givelink='$GLCode' class='mdl-color-text--black' style='font-weight:400'>Give</a></li>";

										echo "<li class='mdl-menu__item mclicklink'><a href='#assessments/results/$Assessment_ID' class='mdl-color-text--black' style='font-weight:400'>Results</a></li>";

									echo "</ul>";
								echo "</div>";
							echo "</td>";


						echo "</tr>";
					}
				echo "</tbody>";
			echo "</table>";

			echo "</div>";
			echo "</div>";
		}
		else
		{
			echo "<div style='padding:56px; text-align:center; width:100%;'><span style='font-size: 22px; font-weight:700'>District Assessments</span><br><p style='font-size:16px; margin:20px 0 0 0;'>Assessments that the district creates will appear here.</p></div>";
		}

		$db->close();
	}

?>