<?php
/* ----------------------------------------------------------------------------
 Gohan ZQL
-------------------------------------------------------------------------------
 (c) 2005-2022 by Martin Willisegger
 (c) 2026 by Esteban Monge - Sempai Space

 Project   : Gohan ZQL (fork of NagiosQL)
 Component : Installer preprocessing script
 Website   : https://github.com/EstebanMonge/gohanzql
 Version   : 4.0.0
 GIT Repo  : https://github.com/EstebanMonge/gohanzql
-----------------------------------------------------------------------------*/
error_reporting(E_ALL);
/**
 * Class and variable includes
 * @var string $preBasePath from index.php
 */
/*
Define common variables
*/
$strErrorMessage = '';  /* All error messages (red) */
$strInfoMessage = '';  /* All information messages (green) */
/*
// Start PHP session
*/
session_start(['name' => 'nagiosql_install']);
/*
Include external function/class files
*/
require $preBasePath . 'functions/Autoloader.php';
functions\Autoloader::register($preBasePath);
/*
Initialize class
*/
$myInstClass = new install\functions\NagInstallClass($_SESSION);