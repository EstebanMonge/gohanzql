<?php
/* ----------------------------------------------------------------------------
 Gohan ZQL
-------------------------------------------------------------------------------
 (c) 2005-2022 by Martin Willisegger
 (c) 2026 by Esteban Monge - Sempai Space

 Project   : Gohan ZQL (fork of NagiosQL)
 Component : Tools overview
 Website   : https://sourceforge.net/projects/nagiosql/
 Version   : 4.0.0
 GIT Repo  : https://gitlab.com/wizonet/NagiosQL
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
$prePageId = 6;
$preContent = 'admin/mainpages.htm.twig';
/*
Include preprocessing file
*/
require $preBasePath . 'functions/prepend_adm.php';
/*
Include content
*/
$conttp->setVariable('TITLE', translate('Different tools'));
$conttp->setVariable('DESC', translate('Useful functions for data import, main configuration, daemon control, etc.'));
$conttp->parse('main');
$conttp->show('main');
/*
Include Footer
*/
$maintp->setVariable('VERSION_INFO', "Gohan ZQL $setFileVersion (fork of "
    . "<a href='https://sourceforge.net/projects/nagiosql/' target='_blank'>NagiosQL</a>)");
$maintp->parse('footer');
$maintp->show('footer');