<?php
/* ----------------------------------------------------------------------------
 Gohan ZQL
-------------------------------------------------------------------------------
 (c) 2005-2022 by Martin Willisegger
 (c) 2026 by Esteban Monge - Sempai Space

 Project   : Gohan ZQL (fork of NagiosQL)
 Component : Administration overview
 Website   : https://github.com/EstebanMonge/gohanzql
 Version   : 4.0.0
 GIT Repo  : https://github.com/EstebanMonge/gohanzql
-----------------------------------------------------------------------------*/
/**
 * Class and variable includes
 * @var functions\NagTemplateClass $conttp Content template
 * @var functions\NagTemplateClass $maintp Main template
 * @var string $setFileVersion from prepend_adm.php -> Application version string
 */
/*
Path settings
*/
$strPattern = '(admin/[^/]*.php)';
$preRelPath = preg_replace($strPattern, '', filter_input(INPUT_SERVER, 'PHP_SELF'));
$preBasePath = preg_replace($strPattern, '', filter_input(INPUT_SERVER, 'SCRIPT_FILENAME'));
/*
Define common variables
*/
$prePageId = 7;
$preContent = 'admin/mainpages.htm.twig';
/*
Include preprocessing file
*/
require $preBasePath . 'functions/prepend_adm.php';
/*
Include content
*/
$conttp->setVariable('TITLE', translate('Administration'));
$conttp->parse('header');
$conttp->show('header');
$conttp->setVariable('DESC', translate('Functions to administrate Gohan ZQL V4'));
$conttp->parse('main');
$conttp->show('main');
/*
Include Footer
*/
$maintp->setVariable('VERSION_INFO', "<a href='https://github.com/EstebanMonge/gohanzql' target='_blank'>Gohan ZQL</a> $setFileVersion (fork of "
    . "<a href='https://sourceforge.net/projects/nagiosql/' target='_blank'>NagiosQL</a>)");
$maintp->parse('footer');
$maintp->show('footer');