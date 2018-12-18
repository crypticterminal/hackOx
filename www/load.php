<?php
	putenv('LANG=en_US.UTF-8');
	
	require("config.php");
	include("functions.php");

	include("core/class.Template.php");
	include("core/class.Navigation.php");
	include("core/class.View.php");
	include("core/class.Module.php");
	include("core/class.LogManager.php");
	include("core/class.Application.php");
	include("core/class.Response.php");
    include("core/class.Database.php");

	if(DEBUG) {
		ini_set("display_errors", "On");
		error_reporting(E_ALL);
	}
	
	if(!checkSession()) {
		header("Location: /login.php");
	}