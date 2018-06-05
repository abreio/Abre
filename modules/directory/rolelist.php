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
	require_once('permissions.php');

	echo "<option value='' disabled selected>Choose a Role</option>";

	//Assessment Roles
	if(isAppActive("Abre-Assessments")){
		if(strpos($role, 'Assessment Administrator') !== false){
			echo "<option value='Assessment Administrator' selected='selected'>Assessment Administrator</option>";
		}else{
			echo "<option value='Assessment Administrator'>Assessment Administrator</option>";
		}
	}

	//Conduct Roles
	if(isAppActive("Abre-Conduct")){
		if(strpos($role, 'Conduct Administrator') !== false){
			echo "<option value='Conduct Administrator' selected='selected'>Conduct Administrator</option>";
		}else{
			echo "<option value='Conduct Administrator'>Conduct Administrator</option>";
		}

		if(strpos($role, 'Conduct Verification Monitor') !== false){
			echo "<option value='Conduct Verification Monitor' selected='selected'>Conduct Verification Monitor</option>";
		}else{
			echo "<option value='Conduct Verification Monitor'>Conduct Verification Monitor</option>";
		}
	}

	//Curriculum Roles
	if(isAppActive("Abre-Curriculum")){
		if(strpos($role, 'Curriculum Administrator') !== false){
			echo "<option value='Curriculum Administrator' selected='selected'>Curriculum Administrator</option>";
		}else{
			echo "<option value='Curriculum Administrator'>Curriculum Administrator</option>";
		}
	}

	//Directory Roles
	if(strpos($role, 'Directory Administrator') !== false){
		echo "<option value='Directory Administrator' selected='selected'>Directory Administrator</option>";
	}else{
		echo "<option value='Directory Administrator'>Directory Administrator</option>";
	}

	if(strpos($role, 'Directory Advisor') !== false){
		echo "<option value='Directory Advisor' selected='selected'>Directory Advisor</option>";
	}else{
		echo "<option value='Directory Advisor'>Directory Advisor</option>";
	}

	if(strpos($role, 'Directory Supervisor') !== false){
		echo "<option value='Directory Supervisor' selected='selected'>Directory Supervisor</option>";
	}else{
		echo "<option value='Directory Supervisor'>Directory Supervisor</option>";
	}

	//Plans Roles
	if(isAppActive("Abre-Plans")){
		if(strpos($role, 'Building Plans Administrator') !== false){
			echo "<option value='Building Plans Administrator' selected='selected'>Building Plans Administrator</option>";
		}else{
			echo "<option value='Building Plans Administrator'>Building Plans Administrator</option>";
		}

		if(strpos($role, 'District Plans Administrator') !== false){
			echo "<option value='District Plans Administrator' selected='selected'>District Plans Administrator</option>";
		}else{
			echo "<option value='District Plans Administrator'>District Plans Administrator</option>";
		}
	}

	//Forms Roles
	if(isAppActive("Abre-Forms")){
		if(strpos($role, 'Forms Administrator') !== false){
			echo "<option value='Forms Administrator' selected='selected'>Forms Administrator</option>";
		}else{
			echo "<option value='Forms Administrator'>Forms Administrator</option>";
		}
	}

	//Stream and Headline Roles
	if(strpos($role, 'Stream and Headline Administrator') !== false){
		echo "<option value='Stream and Headline Administrator' selected='selected'>Stream and Headline Administrator</option>";
	}else{
		echo "<option value='Stream and Headline Administrator'>Stream and Headline Administrator</option>";
	}

?>
