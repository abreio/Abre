<?php

	/*
	* Copyright (C) 2016-2017 Abre.io LLC
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
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');

?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Checkbox</title>

  <style>
	* {
	  box-sizing: border-box;
	}

	ul {
	  margin: 0;
	  padding: 0;
	}

	ul li {
	  cursor: pointer;
	  position: relative;
	  padding: 12px 8px 12px 40px;
	  background: #eee;
	  font-size: 18px;
	  transition: 0.2s;

	  -webkit-user-select: none;
	  -moz-user-select: none;
	  -ms-user-select: none;
	  user-select: none;
	}

	ul li:nth-child(odd) {
	  background: #f9f9f9;
	}

	ul li:hover {
	  background: #ddd;
	}

	ul li.checked {
	  background: #888;
	  color: #fff;
	  text-decoration: line-through;
	}

	ul li.checked::before {
	  content: '';
	  position: absolute;
	  border-color: #fff;
	  border-style: solid;
	  border-width: 0 2px 2px 0;
	  top: 10px;
	  left: 16px;
	  transform: rotate(45deg);
	  height: 15px;
	  width: 7px;
	}

	.close {
	  position: absolute;
	  right: 0;
	  top: 0;
	  padding: 12px 16px 12px 16px;
	}

	.close:hover {
	  background-color: #f44336;
	  color: white;
	}

	.header {
	  background-color: #f44336;
	  padding: 30px 40px;
	  color: white;
	  text-align: center;
	}

	.header:after {
	  content: "";
	  display: table;
	  clear: both;
	}

	input {
	  margin: 0;
	  border: none;
	  border-radius: 0;
	  width: 75%;
	  padding: 10px;
	  float: left;
	  font-size: 16px;
	}

	.addBtn {
	  padding: 10px;
	  width: 25%;
	  background: #d9d9d9;
	  color: #555;
	  float: left;
	  text-align: center;
	  font-size: 16px;
	  cursor: pointer;
	  transition: 0.3s;
	  border-radius: 0;
	}

	.addBtn:hover {
	  background-color: #bbb;
	}
  </style>
</head>

	<body>
	<div id="myDIV" class="header">
	  <h2>To Do List</h2>
	  <input type="text" id="myInput" placeholder="Title...">
	  <span onclick="newElement()" class="addBtn">Add</span>
	</div>

	<ul id="myUL">
	  <li>Math HW</li>
	  <li class="checked">English HW</li>
	  <li>Mr. Marchal HW</li>
	</ul>
	
	<script>
		var myNodelist = document.getElementsByTagName("LI");
	var i;
	for (i = 0; i < myNodelist.length; i++) {
	  var span = document.createElement("SPAN");
	  var txt = document.createTextNode("\u00D7");
	  span.className = "close";
	  span.appendChild(txt);
	  myNodelist[i].appendChild(span);
	}

	// Click on a close button to hide the current list item
	var close = document.getElementsByClassName("close");
	var i;
	for (i = 0; i < close.length; i++) {
	  close[i].onclick = function() {
		var div = this.parentElement;
		div.style.display = "none";
	  }
	}

	// Add a "checked" symbol when clicking on a list item
	var list = document.querySelector('ul');
	list.addEventListener('click', function(ev) {
	  if (ev.target.tagName === 'LI') {
		ev.target.classList.toggle('checked');
	  }
	}, false);

	// Create a new list item when clicking on the "Add" button
	function newElement() {
	  var li = document.createElement("li");
	  var inputValue = document.getElementById("myInput").value;
	  var t = document.createTextNode(inputValue);
	  li.appendChild(t);
	  if (inputValue === '') {
		alert("You must write something!");
	  } else {
		document.getElementById("myUL").appendChild(li);
	  }
	  document.getElementById("myInput").value = "";

	  var span = document.createElement("SPAN");
	  var txt = document.createTextNode("\u00D7");
	  span.className = "close";
	  span.appendChild(txt);
	  li.appendChild(span);

	  for (i = 0; i < close.length; i++) {
		close[i].onclick = function() {
		  var div = this.parentElement;
		  div.style.display = "none";
		}
	  }
	}
	</script> 
	</body>

</html>