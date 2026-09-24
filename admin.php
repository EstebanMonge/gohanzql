<?php
/* ----------------------------------------------------------------------------
 Gohan ZQL
-------------------------------------------------------------------------------
 (c) 2005-2022 by Martin Willisegger
 (c) 2026 by Esteban Monge - Sempai Space

 Project   : Gohan ZQL (fork of NagiosQL)
 Component : Admin main site
 Website   : https://sourceforge.net/projects/nagiosql/
 Version   : 4.0.0
 GIT Repo  : https://gitlab.com/wizonet/NagiosQL
-----------------------------------------------------------------------------*/
/**
 * Class and variable includes
 * @var functions\NagTemplateClass $conttp
 * @var functions\NagTemplateClass $maintp
 * @var string $setFileVersion from prepend_adm.php -> Application version string
 * @var string $setGITVersion from prepend_adm.php -> Application GIT subversion string
 */
/*
Path settings
*/
$preRelPath = strstr(filter_input(INPUT_SERVER, 'PHP_SELF'), 'admin.php', true);
$preBasePath = strstr(filter_input(INPUT_SERVER, 'SCRIPT_FILENAME'), 'admin.php', true);
/*
Define common variables
*/
$prePageId = 1;
$preContent = 'admin/mainpages.htm.twig';
$preAccess = 1;
$preFieldvars = 1;
/*
Include preprocessing files
*/
require $preBasePath . 'functions/prepend_adm.php';
require $preBasePath . 'functions/prepend_content.php';
/*
Include Content
*/
$conttp->setVariable('TITLE', translate('Gohan ZQL Administration'));
$conttp->parse('header');
$conttp->show('header');
$conttp->setVariable('DESC', translate('Welcome to Gohan ZQL, the administration module that can be used to easily '
    . 'create, modify and delete configuration files for Nagios. The data is stored in a database '
    . 'and can be written directly to the standard files at any time you want.'));
$conttp->parse('main');
$conttp->show('main');
/*
Include footer
*/
$maintp->setVariable('VERSION_INFO', "Gohan ZQL $setFileVersion - GIT Version: $setGITVersion (fork of "
    . "<a href='https://sourceforge.net/projects/nagiosql/' target='_blank'>NagiosQL</a>)");
$maintp->parse('footer');
$maintp->show('footer');