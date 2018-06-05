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
	require_once(dirname(__FILE__) . '/../../core/abre_google_login.php');
  require_once(dirname(__FILE__) . '/../../core/abre_functions.php');
  require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');

		echo "<hr class='widget_hr'>";
		echo "<div class='widget_holder'>";
			echo "<div class='widget_container widget_body' style='color:#666;'>Your Recent Books<i class='right material-icons widget_holder_refresh pointer' data-path='/modules/Abre-Books/widget_content.php' data-reload='true'>refresh</i></div>";
		echo "</div>";

		$count = 0;
    $usertype = $_SESSION['usertype'];

    if($usertype != "parent"){
      $id = finduseridcore($_SESSION['useremail']);

      $sql = "SELECT Title, Slug, Cover FROM books_libraries LEFT JOIN books ON books_libraries.Book_ID = books.ID WHERE books_libraries.User_ID = '$id' ORDER BY books_libraries.Book_ID DESC LIMIT 4";
      $query = $db->query($sql);
      while($result = $query->fetch_assoc()){
				if($count == 0){
					echo "<hr class='widget_hr'>";
				}
        $title = $result['Title'];
        $slug = $result['Slug'];
        $cover = $result['Cover'];

        $coverimage = $portal_root."/modules/Abre-Books/serveimage.php?file=$cover&ext=png";

        echo "<div style='width:50%; float:left; text-align:center; margin:10px 0px 10px 0px;'>";
          echo "<img src='$coverimage' class='appicon_modal' style='object-fit: cover;'>";
          echo "<span><a href='#books/$slug' class='applink truncate'>$title</a></span>";
        echo "</div>";
				$count++;
      }
    }else{
      $sql = "SELECT id FROM users_parent WHERE email = '".$_SESSION['useremail']."' LIMIT 1";
      $query = $db->query($sql);
      $result = $query->fetch_assoc();
      $id = $result["id"];

      $sql = "SELECT Title, Slug, Cover FROM books_libraries LEFT JOIN books ON books_libraries.Book_ID = books.ID WHERE books_libraries.Parent_ID = '$id' ORDER BY books_libraries.Book_ID DESC LIMIT 4";
      $query = $db->query($sql);
      while($result = $query->fetch_assoc()){
				if($count == 0){
					echo "<hr class='widget_hr'>";
				}
        $title = $result['Title'];
        $slug = $result['Slug'];
        $cover = $result['Cover'];

        $coverimage = $portal_root."/modules/Abre-Books/serveimage.php?file=$cover&ext=png";

        echo "<div style='width:50%; float:left; text-align:center; margin:10px 0px 10px 0px;'>";
          echo "<img src='$coverimage' class='appicon_modal' style='object-fit: cover;'>";
          echo "<span><a href='#books/$slug' class='applink truncate'>$title</a></span>";
        echo "</div>";
				$count++;
      }
    }
    $db->close();

?>