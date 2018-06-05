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

	$WidgetCount = 0;

	//Find apps that have widgets
	echo "<div class='widgetsort'>";

		//Check to see if there is a saved order
		$sql = "SELECT * FROM profiles WHERE email = '".$_SESSION['useremail']."'";
		$result = $db->query($sql);
		$widgets_order = NULL;
		$widgets_hidden = NULL;
		while($row = $result->fetch_assoc()) {
			$widgets_order = htmlspecialchars($row["widgets_order"], ENT_QUOTES);
			$widgets_hidden = htmlspecialchars($row["widgets_hidden"], ENT_QUOTES);
		}

		//Display widgets if saved order and not hidden
		if($widgets_order!=NULL){
			$widgets = explode(",",$widgets_order);
			foreach($widgets as $widget) {

				//Check if hidden
				$HiddenWidgets = explode(',',$widgets_hidden);
				if(!in_array($widget, $HiddenWidgets)){

					$restrictions = NULL;

					if(file_exists(dirname(__FILE__) . '/../'.$widget.'/widget.php')){

						include(dirname(__FILE__) . '/../'.$widget.'/widget_config.php');

						if(strpos($restrictions,$_SESSION['usertype']) === false){

							echo "<div id='item_$widget'>";
								include(dirname(__FILE__) . '/../'.$widget.'/widget.php');
							echo "</div>";
							$WidgetCount++;

						}
					}
				}
			}
		}

		//Loop through all Apps and display widgets that were not already in saved order and not hidden
		$widgetsdirectory = dirname(__FILE__) . '/../';
		$widgetsfolders = scandir($widgetsdirectory);
		foreach($widgetsfolders as $result){

			//Check if hidden
			$HiddenWidgets = explode(',',$widgets_hidden);
			if(!in_array($result, $HiddenWidgets)){

				if (strpos($widgets_order, $result) === false) {

					$restrictions = NULL;

					if(file_exists(dirname(__FILE__) . '/../'.$result.'/widget.php')){

						include(dirname(__FILE__) . '/../'.$result.'/widget_config.php');

						if(strpos($restrictions,$_SESSION['usertype']) === false){

							echo "<div id='item_$result'>";
								include(dirname(__FILE__) . '/../'.$result.'/widget.php');
							echo "</div>";
							$WidgetCount++;

						}

					}

				}

			}

		}

		if($WidgetCount==0){
			echo "<div class='widget_placeholder widget mdl-card' style='padding:30px; text-align:center; width:100%;'><span style='font-size: 22px; font-weight:700'>Add Widgets</span><br><p style='font-size:16px; margin:0;'>Get timely information from your favorite apps at a glance. Add widgets by clicking 'Edit Widgets' below.</p></div><br>";
		}

	echo "</div>";

	//Edit Widgets
	echo "<div style='text-align:center;'>";
		echo "<a class='mdl-button mdl-js-button mdl-js-ripple-effect modal-editwidgets editwidgets' style='background-color: #e1e1e1' href='#editwidgets'>Edit Widgets</a>";
	echo "</div>";

?>

<script>

	$(function(){

		//Make Collapsible Widgets
		$('.widget').collapsible();

		//Sortable
		$( ".widgetsort" ).sortable({
			handle: ".widget .collapsible-header",
			placeholder: "widget_placeholder widget mdl-card",
			cursor: 'move',
			start: function (event, ui){
				ui.item.addClass('widget_tilt');
				ui.placeholder.height(ui.item.height());
			},
			stop: function (event, ui){
				ui.item.removeClass('widget_tilt');
			},
			update: function(event, ui){
				var postData = $(this).sortable('serialize', {expression: /(.+)[=_](.+)/});
				<?php
					echo "$.post('$portal_root/modules/stream/save_widget_order.php', {list: postData})";
				?>
			}
		});

		function loadWidget(BodyDiv,Path){
			$(BodyDiv).html("<div class='progress'><div class='indeterminate'></div></div>");
			$.get(Path, function(response) {
				$(BodyDiv).html(response);
			})
			.fail(function() {
				var Color = "<?php echo getSiteColor(); ?>";
				$(BodyDiv).html("<hr class='widget_hr'><div class='widget_holder'><div class='widget_container widget_body'>Check your connection and try again.<br><a class='widget_reload mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect' href='#' data-path='"+Path+"' style='margin:10px 0 5px 0; color:#fff; background-color: "+Color+"'>Reload</a></div></div>");
			});
		}

		//Load all widgets that have a class of active
		$(".widgetli").each(function(){
			var OpenStatus = $(this).hasClass("active");
			var Path = $(this).data('path');
			if(OpenStatus === true){
				var Div = "#widgetbody_"+Path;
				loadWidget(Div,'/modules/'+Path+'/widget_content.php')
			}
		});

		//Events when widget is opened or closed
		$(".widget .collapsible-header").off().on("click", function(event){

			//Load widget content
			var OpenStatus = $(this).hasClass("active");
			var Path = $(this).data('path');
			var Widget = $(this).data('widget');
			var BodyDiv = $(this).next();
			if(OpenStatus === false){
				loadWidget(BodyDiv,Path);
			}

			//Save open status to server
			$.post("/modules/stream/save_widget_state.php", {widget: Widget, status: OpenStatus});

		});

		//Reload Widget
		$('body').off().on('click', '.widget_reload', function(event){
			event.preventDefault();
			var BodyDiv = $(this).closest(".collapsible-body");
			var Path = $(this).data('path');
			loadWidget(BodyDiv,Path);
		});

	  	//Make Div Clickable
		$('body').off().on('click', '.widget_holder_link', function(event){
			event.preventDefault();
			var BodyDiv = $(this).closest(".collapsible-body");
			var Path = $(this).data('path');
			var Reload = $(this).data('reload');
			var newTab = $(this).data('newtab');
			if(!newTab){
				window.location.href = $(this).data('link');
			}else{
				window.open($(this).data('link'), '_blank');
			}
			if(Reload === true)
			{
				setTimeout(function(){ loadWidget(BodyDiv,Path); }, 5000);
			}
		});

	  	//Make Refresh Button
		$('body').on('click', '.widget_holder_refresh', function(event){
			event.preventDefault();
			var BodyDiv = $(this).closest(".collapsible-body");
			var Path = $(this).data('path');
			loadWidget(BodyDiv,Path);
		});

		//Edit Widget Modal
		$(".editwidgets").unbind().click(function(){
		    event.preventDefault();
			$('#editwidgets').openModal({ in_duration: 0, out_duration: 0 });
		});

		//Widget Header Link
		$(".widgeticonlink").off().on("click", function(event) {
			event.stopPropagation();
			var link = $(this).data('link');
			var newtab = $(this).data('newtab');
			if(link != ""){
				if(newtab){
					window.open($(this).data('link'), '_blank');
				}else{
					window.location.href = $(this).data('link');
				}
			}
		});


	});

</script>