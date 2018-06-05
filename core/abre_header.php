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

	//Display correct header
	if(file_exists('configuration.php')){
		require_once('abre_functions.php');
?>
		<!DOCTYPE html>
		<html lang='en'>
			<head>
				<meta charset='utf-8'>
				<meta http-equiv='X-UA-Compatible' content='IE=edge'>
				<meta name='viewport' content='width=device-width, initial-scale=1'>

				<?php
				//Site title
				echo "<title>".getSiteTitle()."</title>";
				echo "<meta property='og:title' content='".getSiteTitle()."'/>";

				//Site description
				echo "<meta name='description' property='og:description' content='".getSiteDescription()."'>";

				//Site favicon
				echo "<link rel='icon' type='image/png' href='".getSiteLogo()."'>";
				echo "<meta property='og:image' content='".getSiteLogo()."'>";

				//Chrome icon
				echo "<meta name='mobile-web-app-capable' content='yes'>";
				echo "<link rel='icon' sizes='192x192' href='".getSiteLogo()."'>";

				//iOS icon
				echo "<meta name='apple-mobile-web-app-title' content='".getSiteTitle()."'>";
				echo "<link rel='apple-touch-icon' href='".getSiteLogo()."'>";

				//Windows icon
				echo "<meta name='msapplication-TileImage' content='".getSiteLogo()."'>";
				echo "<meta name='msapplication-TileColor' content='".getSiteColor()."'>";

				//Theme Color
				echo "<meta name='theme-color' content='".getSiteColor()."'>";
				?>

				<link href='https://fonts.googleapis.com/css?family=Roboto:400,300,500,700,900,100' rel='stylesheet' type='text/css'>
				<link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>
				<link rel='stylesheet' href='core/css/materialize.0.97.7.2.min.css'>
				<link rel='stylesheet' href='core/css/material.1.2.1.min.css'>
				<link rel='stylesheet' href='core/css/abre.1.5.2.css'>
				<link rel='stylesheet' href='core/css/animate.min.css'>
				<link rel='stylesheet' href='core/css/timepicker.min.css'>
				<link rel='stylesheet' href='core/css/quill.snow.1.1.3.css'>
				<link rel="stylesheet" href='core/css/spectrum.0.0.1.css'>
				<link rel="stylesheet" href='modules/profile/css/calendar.css'>
				<link rel="stylesheet" href='core/css/style.php'>

				<script src='core/js/jquery-3.1.1.min.js'></script>
				<script src='core/js/jquery-ui.1.12.1.min.js'></script>
				<script src='core/js/materialize.0.97.7.min.js'></script>
				<script src='core/js/material.1.2.1.min.js'></script>
				<script src='core/js/routie.min.3.2.js'></script>
				<script src='core/js/jquery.tablesorter.min.js'></script>
				<script src='core/js/jquery.timepicker.min.js'></script>
				<script src='core/js/quill.min.1.1.3.js'></script>
				<script src='core/js/spectrum.js'></script>
				<script src='modules/profile/js/jquery-ui.multidatespicker.1.6.4.js'></script>
				<script src='https://www.gstatic.com/charts/loader.js'></script>
				<script src='core/js/chart.2.7.0.min.js'></script>
				<script src='core/js/chartjs-plugin-datalabels.min.js'></script>
			</head>
			<body>
			<script>google.charts.load('current', {'packages':['corechart']});</script>

	<?php
	}else{
	?>
		<!DOCTYPE html>
		<html lang='en'>
			<head>
				<meta charset='utf-8'>
				<meta http-equiv='X-UA-Compatible' content='IE=edge'>
				<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no'>
				<title>Abre Installation</title>

				<link href='https://fonts.googleapis.com/css?family=Roboto:400,300,500,700,900,100' rel='stylesheet' type='text/css'>
				<link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>
				<link rel='stylesheet' href='core/css/materialize.0.97.7.2.min.css'>
				<link rel='stylesheet' href='core/css/material.1.2.1.min.css'>
				<link rel='stylesheet' href='core/css/abre.1.5.2.css'>

				<script src='core/js/jquery-3.1.1.min.js'></script>
				<script src='core/js/jquery-ui.1.12.1.min.js'></script>
				<script src='core/js/materialize.0.97.7.min.js'></script>
				<script src='core/js/material.1.2.1.min.js'></script>
			</head>
			<body>
  <?php
	}
	?>
