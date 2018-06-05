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
	require(dirname(__FILE__) . '/../../configuration.php');
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');
	require_once(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
	require_once('functions.php');

	//Get book information
	$bookslug=htmlspecialchars($_GET["id"], ENT_QUOTES);
	$sql = "SELECT ID, File, Author, Title FROM books WHERE Slug='$bookslug'";
	$result = $db->query($sql);
	while($row = $result->fetch_assoc())
	{
		$bookid=htmlspecialchars($row["ID"], ENT_QUOTES);
		$File=htmlspecialchars($row["File"], ENT_QUOTES);
		$author=htmlspecialchars($row["Author"], ENT_QUOTES);
		$title=htmlspecialchars($row["Title"], ENT_QUOTES);
	}

	//Check to see if you user has access to book
	if($_SESSION['usertype']!="parent")
	{
		$userid=finduseridcore($_SESSION['useremail']);
		$sql = "SELECT COUNT(*) FROM books_libraries WHERE Book_ID='$bookid' AND User_ID='$userid'";
	}
	else
	{
		$userid=finduseridparent($_SESSION['useremail']);
		$sql = "SELECT COUNT(*) FROM books_libraries WHERE Book_ID='$bookid' AND Parent_ID='$userid'";
	}
	$result = $db->query($sql);
	$returnrow = $result->fetch_assoc();
	$numrows = $returnrow["COUNT(*)"];

	if($_SESSION['usertype']=="student")
	{
		$sql = "SELECT COUNT(*) FROM books WHERE ID='$bookid' AND Students_Required=1";
		$result = $db->query($sql);
		$returnrow = $result->fetch_assoc();
		$numrows2 = $returnrow["COUNT(*)"];
	}

	if($_SESSION['usertype']=="staff")
	{
		$sql = "SELECT COUNT(*) FROM books WHERE ID='$bookid' AND Staff_Required=1";
		$result = $db->query($sql);
		$returnrow = $result->fetch_assoc();
		$numrows2 = $returnrow=["COUNT(*)"];
	}

	$access=0;
	if($numrows>0 or $numrows2>0)
	{
			$access=1;
			echo "<script src='modules/".basename(__DIR__)."/js/epub.0.2.15.min.js'></script>";

			echo "<script src='modules/".basename(__DIR__)."/js/zip.min.js'></script>";
			?>

			<div id="main">
				<div id="divider" style='display:none'></div>
				<div id="titlebar">
					<div id="metainfo">
						<div class="row valign-wrapper">
							<div class="col l6 hide-on-small-only ellipsis">
								<span><b><?php echo $title; ?></b></span> - <span><?php echo $author; ?></span>
							</div>
							<div class="col l6 s12 right-align">
								<select id='toc'></select>
							</div>
						</div>
					</div>
				</div>

				<!-- Controls and Viewer -->
				<div id="prev" class="arrow">‹</div>
			    <div id="viewer"></div>
			    <div id="next" class="arrow">›</div>
			    <div id="bookloader" style='z-index:1000; position:absolute; bottom:50%; right:50%; margin-right:-12px;'><div class="mdl-spinner mdl-js-spinner is-active"></div></div>
			</div>



			<script>

				$(function()
				{
					<?php
						$cloudsetting=constant("USE_GOOGLE_CLOUD");
						if ($cloudsetting=="true") {
							$bucket = constant("GC_BUCKET");
							echo "var epubbook = 'https://storage.googleapis.com/$bucket/private_html/books/$bookslug.epub';";
							echo "var Book = ePub(epubbook);";
						}
						else {
							echo "var Book = ePub('/content/books/$bookslug/');";
						}
					?>
					Book.renderTo("viewer");

					//Show book loader
					Book.on('book:ready', function(){
					   $("#bookloader").hide();
					   $("#divider").show();
					});

					//Next Page Button
					$("#next").click(function() {
						Book.nextPage();
					});

					//Previous Page Button
					$("#prev").click(function() {
						Book.prevPage();
					});

					//Book Table of Contents
					Book.getToc().then(function(toc)
					{
						var $select = document.getElementById("toc"),
						docfrag = document.createDocumentFragment();

						toc.forEach(function(chapter) {
							var option = document.createElement("option");
							option.textContent = chapter.label;
							option.ref = chapter.href;
							docfrag.appendChild(option);
						});

						$select.appendChild(docfrag);

						//Option Change
						$select.onchange = function(){
							var index = $select.selectedIndex,
							url = $select.options[index].ref;

							Book.goto(url);
							return false;
						}
					});

			        //Format Book
					Book.setStyle("line-height", "1.5em");

					//Keyboard Shortcuts
					$(document).keyup(function (event) {
						if ((event.keyCode || event.which) == 37) {
					    	Book.prevPage();
					    }
						if ((event.keyCode || event.which) == 39) {
					    	Book.nextPage();
					    }
					});

					//Fullscreen View
					$("#titlebar").mouseenter(function(e) {
						if ($(e.target).is('select'))
						{

						}
						else
						{
							$("header").show();
						}
					});

					//Hide header bar on enter main
					$("#main").mouseenter(function() {
						$("header").hide();
					});

					//Show header if leave main or header
					$("#main, header").mouseleave(function() {
					   $("header").show();
					});

				});
			</script>

	<?php
		}
		if($access==0)
		{
			echo "You do not have access to this book.";
		}
	?>