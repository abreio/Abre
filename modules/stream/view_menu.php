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
?>

    <div class="col s12 hide-on-large-only">
		<ul class="tabs_2" style='background-color: <?php echo getSiteColor(); ?>'>
			<li class="tab col s3 stream pointer"><a class="active" href="#stream">Stream</a></li>
			<li class="tab col s3 widgets pointer"><a href="#widgets">Widgets</a></li>
		</ul>
	</div>

<script>

	$(function(){
		
		$('.stream').addClass('tabmenuover');

		$(".stream").unbind().click(function(event){
			event.preventDefault();
			$('.stream').addClass('tabmenuover');
			$('.widgets').removeClass('tabmenuover');
			$("#streamstream").removeClass("streamstream_toggle");
			$("#streamwidgets").removeClass("streamwidgets_toggle");
		});
		
		$(".widgets").unbind().click(function(event){
			event.preventDefault();
			$('.widgets').addClass('tabmenuover');
			$('.stream').removeClass('tabmenuover');
			$("#streamstream").addClass("streamstream_toggle");
			$("#streamwidgets").addClass("streamwidgets_toggle");
		});
		
		$(window).resize(function (){
		    var viewportWidth = $(window).width();
		    if (viewportWidth > 992){
		    	$("#streamstream").removeClass("streamstream_toggle");
				$("#streamwidgets").removeClass("streamwidgets_toggle");
				$('.stream').addClass('tabmenuover');
				$('.widgets').removeClass('tabmenuover');
		    }
		});
		
	});

</script>