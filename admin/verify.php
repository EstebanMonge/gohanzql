<?php
/* ----------------------------------------------------------------------------
 NagiosQL
-------------------------------------------------------------------------------
 (c) 2005-2023 by Martin Willisegger

 Project   : NagiosQL
 Component : Configuration verification
 Website   : https:/*sourceforge.net/projects/nagiosql/
 Version   : 3.5.0
 GIT Repo  : https:/*gitlab.com/wizonet/NagiosQL
-----------------------------------------------------------------------------*/

use functions\MysqliDbClass;
use functions\NagConfigClass;
use functions\NagDataClass;
use functions\NagVisualClass;

/**
 * Class and variable includes
 * @var HTML_Template_IT $conttp Content template
 * @var HTML_Template_IT $maintp Main template
 * @var MysqliDbClass $myDBClass MySQL database class
 * @var NagVisualClass $myVisClass Visual content class
 * @var NagDataClass $myDataClass NagiosQL data class
 * @var NagConfigClass $myConfigClass NagiosQL configuration class
 * @var string $setFileVersion from prepend_adm.php -> Application version string
 * @var string $prePageKey from prepend_adm.php -> Menu group id
 * @var int $chkDomainId from prepend_adm.php -> Configuration domain id
 * @var string $chkButValue1 from prepend_content.php -> Write monitoring data button
 * @var string $chkButValue2 from prepend_content.php -> Write additional data button
 * @var string $chkButValue3 from prepend_content.php -> Check configuration button
 * @var string $chkButValue4 from prepend_content.php -> Restart Nagios button
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
$prePageId = 30;
$preContent = 'admin/verify.htm.tpl';
$preAccess = 1;
$preFieldvars = 1;
$intModus = 0;
$strInfo = '';
$strErrorMessage = '';
$strInfoMessage = '';
/*
Include preprocessing files
*/
require $preBasePath . 'functions/prepend_adm.php';
require $preBasePath . 'functions/prepend_content.php';
/*
Get configuration set ID
*/
$intMethod = 0;
$strMethod = '';
$myConfigClass->getConfigTargets($arrConfigSet);
$intConfigId = (int)$arrConfigSet[0];
if ($myConfigClass->getConfigValues($intConfigId, 'method', $strMethod) === 0) {
    $intMethod = (int)$strMethod;
}
/*
Process form variables
*/
$intProcessError = 0;
$intError = 0;
/* Write monitoring data */
if (($chkButValue1 !== '') && ($chkButValue1 !== null)) {
    $strNoData = translate('Writing of the configuration failed - no dataset or not activated dataset found') . '::';
    /* Write host configuration */
    $strSQL1 = "SELECT `id` FROM `tbl_host` WHERE `config_id` = $chkDomainId AND `active`='1'";
    $myDBClass->hasDataArray($strSQL1, $arrData, $intDataCount);
    if ($intDataCount !== 0) {
        $intError = 0;
        foreach ($arrData as $data) {
            $intReturn = $myConfigClass->createConfigSingle('tbl_host', $data['id']);
            $intError += $intReturn;
        }
    }
    if (($intError === 0) && ($intDataCount !== 0)) {
        $myVisClass->processMessage(translate('Write host configurations') . ' ...', $strInfo);
        $myVisClass->processMessage('Hosts: ' . translate('Configuration file successfully written!'), $strInfo);
    } elseif ($intDataCount !== 0) {
        $myVisClass->processMessage('Hosts: ' . translate('Cannot open/overwrite the configuration file (check the '
                . 'permissions)!'), $strErrorMessage);
        $intProcessError = 1;
    } else {
        $myVisClass->processMessage('Hosts: ' . translate('No configuration items defined!'), $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write service configuration */
    $strSQL = 'SELECT `id`, `config_name` '
        . "FROM `tbl_service` WHERE `config_id` = $chkDomainId AND `active`='1' GROUP BY `config_name`";
    $myDBClass->hasDataArray($strSQL, $arrData, $intDataCount);
    if ($intDataCount !== 0) {
        $intError = 0;
        foreach ($arrData as $data) {
            $intReturn = $myConfigClass->createConfigSingle('tbl_service', $data['id']);
            $intError += $intReturn;
        }
    }
    if (($intError === 0) && ($intDataCount !== 0)) {
        $myVisClass->processMessage(translate('Write service configurations') . ' ...', $strInfo);
        $myVisClass->processMessage('Services: ' . translate('Configuration file successfully written!'), $strInfo);
    } elseif ($intDataCount !== 0) {
        $myVisClass->processMessage('Services: ' . translate('Cannot open/overwrite the configuration file (check the '
                . 'permissions)!'), $strErrorMessage);
        $intProcessError = 1;
    } else {
        $myVisClass->processMessage('Services: ' . translate('No configuration items defined!'), $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write hostgroup configuration */
    $intReturn1 = $myConfigClass->createConfig('tbl_hostgroup');
    if ($intReturn1 === 0) {
        $myVisClass->processMessage(translate('Write') . ' hostgroups.cfg ...', $strInfo);
        $myVisClass->processMessage('Hostgroups: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else if ($myConfigClass->strErrorMessage === $strNoData) {
        $myVisClass->processMessage(translate('Write') . ' hostgroups.cfg ...', $strInfo);
        $myVisClass->processMessage('Hostgroups: ' . translate('No dataset or no activated dataset found - empty '
                . 'configuration written') . '::', $strInfo);
    } else {
        $myVisClass->processMessage('Hostgroups: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write servicegroup configuration */
    $intReturn2 = $myConfigClass->createConfig('tbl_servicegroup');
    if ($intReturn2 === 0) {
        $myVisClass->processMessage(translate('Write') . ' servicegroups.cfg ...', $strInfo);
        $myVisClass->processMessage('Servicegroups: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else if ($myConfigClass->strErrorMessage === $strNoData) {
        $myVisClass->processMessage(translate('Write') . ' servicegroups.cfg ...', $strInfo);
        $myVisClass->processMessage('Servicegroups: ' . translate('No dataset or no activated dataset found - empty '
                . 'configuration written') . '::', $strInfo);
    } else {
        $myVisClass->processMessage('Servicegroups: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write hosttemplate configuration */
    $intReturn3 = $myConfigClass->createConfig('tbl_hosttemplate');
    if ($intReturn3 === 0) {
        $myVisClass->processMessage(translate('Write') . ' hosttemplates.cfg ...', $strInfo);
        $myVisClass->processMessage('Hosttemplates: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else if ($myConfigClass->strErrorMessage === $strNoData) {
        $myVisClass->processMessage(translate('Write') . ' hosttemplates.cfg ...', $strInfo);
        $myVisClass->processMessage('Hosttemplates: ' . translate('No dataset or no activated dataset found - empty '
                . 'configuration written') . '::', $strInfo);
    } else {
        $myVisClass->processMessage('Hosttemplates: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write servicetemplate configuration */
    $intReturn4 = $myConfigClass->createConfig('tbl_servicetemplate');
    if ($intReturn4 === 0) {
        $myVisClass->processMessage(translate('Write') . ' servicetemplates.cfg ...', $strInfo);
        $myVisClass->processMessage('Servicetemplates: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else if ($myConfigClass->strErrorMessage === $strNoData) {
        $myVisClass->processMessage(translate('Write') . ' servicetemplates.cfg ...', $strInfo);
        $myVisClass->processMessage('Servicetemplates: ' . translate('No dataset or no activated dataset found - '
                . 'empty configuration written') . '::', $strInfo);
    } else {
        $myVisClass->processMessage('Servicetemplates: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
}
/* Write additional data */
if (($chkButValue2 !== '') && ($chkButValue2 !== null)) {
    $strNoData = translate('Writing of the configuration failed - no dataset or not activated dataset found') . '::';
    /* Write timeperiod configuration */
    $intReturn5 = $myConfigClass->createConfig('tbl_timeperiod');
    if ($intReturn5 === 0) {
        $myVisClass->processMessage(translate('Write') . ' timeperiods.cfg ...', $strInfo);
        $myVisClass->processMessage('Timeperiods: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else {
        $myVisClass->processMessage('Timeperiods: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write command configuration */
    $intReturn6 = $myConfigClass->createConfig('tbl_command');
    if ($intReturn6 === 0) {
        $myVisClass->processMessage(translate('Write') . ' commands.cfg ...', $strInfo);
        $myVisClass->processMessage('Commands: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else {
        $myVisClass->processMessage('Commands: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write contact configuration */
    $intReturn7 = $myConfigClass->createConfig('tbl_contact');
    if ($intReturn7 === 0) {
        $myVisClass->processMessage(translate('Write') . ' contacts.cfg ...', $strInfo);
        $myVisClass->processMessage('Contacts: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else {
        $myVisClass->processMessage('Contacts: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write contactgroup configuration */
    $intReturn8 = $myConfigClass->createConfig('tbl_contactgroup');
    if ($intReturn8 === 0) {
        $myVisClass->processMessage(translate('Write') . ' contactgroups.cfg ...', $strInfo);
        $myVisClass->processMessage('Contactgroups: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else {
        $myVisClass->processMessage('Contactgroups: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write contacttemplate configuration */
    $intReturn9 = $myConfigClass->createConfig('tbl_contacttemplate');
    if ($intReturn9 === 0) {
        $myVisClass->processMessage(translate('Write') . ' contacttemplates.cfg ...', $strInfo);
        $myVisClass->processMessage('Contacttemplates: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else if ($myConfigClass->strErrorMessage === $strNoData) {
        $myVisClass->processMessage(translate('Write') . ' contacttemplates.cfg ...', $strInfo);
        $myVisClass->processMessage('Contacttemplates: ' . translate('No dataset or no activated dataset found - '
                . 'empty configuration written') . '::', $strInfo);
    } else {
        $myVisClass->processMessage('Contacttemplates: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write servicedependency configuration */
    $intReturn10 = $myConfigClass->createConfig('tbl_servicedependency');
    if ($intReturn10 === 0) {
        $myVisClass->processMessage(translate('Write') . ' servicedependencies.cfg ...', $strInfo);
        $myVisClass->processMessage('Servicedependencies: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else if ($myConfigClass->strErrorMessage === $strNoData) {
        $myVisClass->processMessage(translate('Write') . ' servicedependencies.cfg ...', $strInfo);
        $myVisClass->processMessage('Servicedependencies: ' . translate('No dataset or no activated dataset found - '
                . 'empty configuration written') . '::', $strInfo);
    } else {
        $myVisClass->processMessage('Servicedependencies: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write hostdependency configuration */
    $intReturn11 = $myConfigClass->createConfig('tbl_hostdependency');
    if ($intReturn11 === 0) {
        $myVisClass->processMessage(translate('Write') . ' hostdependencies.cfg ...', $strInfo);
        $myVisClass->processMessage('Hostdependencies: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else if ($myConfigClass->strErrorMessage === $strNoData) {
        $myVisClass->processMessage(translate('Write') . ' hostdependencies.cfg ...', $strInfo);
        $myVisClass->processMessage('Hostdependencies: ' . translate('No dataset or no activated dataset found - '
                . 'empty configuration written') . '::', $strInfo);
    } else {
        $myVisClass->processMessage('Hostdependencies: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write serviceescalation configuration */
    $intReturn12 = $myConfigClass->createConfig('tbl_serviceescalation');
    if ($intReturn12 === 0) {
        $myVisClass->processMessage(translate('Write') . ' serviceescalations.cfg ...', $strInfo);
        $myVisClass->processMessage('Serviceescalations: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else if ($myConfigClass->strErrorMessage === $strNoData) {
        $myVisClass->processMessage(translate('Write') . ' serviceescalations.cfg ...', $strInfo);
        $myVisClass->processMessage('Serviceescalations: ' . translate('No dataset or no activated dataset found - '
                . 'empty configuration written') . '::', $strInfo);
    } else {
        $myVisClass->processMessage('Serviceescalations: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write hostescalation configuration */
    $intReturn13 = $myConfigClass->createConfig('tbl_hostescalation');
    if ($intReturn13 === 0) {
        $myVisClass->processMessage(translate('Write') . ' hostescalations.cfg ...', $strInfo);
        $myVisClass->processMessage('Hostescalations: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else if ($myConfigClass->strErrorMessage === $strNoData) {
        $myVisClass->processMessage(translate('Write') . ' hostescalations.cfg ...', $strInfo);
        $myVisClass->processMessage('Hostescalations: ' . translate('No dataset or no activated dataset found - '
                . 'empty configuration written') . '::', $strInfo);
    } else {
        $myVisClass->processMessage('Hostescalations: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write serviceextinfo configuration */
    $intReturn14 = $myConfigClass->createConfig('tbl_serviceextinfo');
    if ($intReturn14 === 0) {
        $myVisClass->processMessage(translate('Write') . ' serviceextinfo.cfg ...', $strInfo);
        $myVisClass->processMessage('Serviceextinfo: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else if ($myConfigClass->strErrorMessage === $strNoData) {
        $myVisClass->processMessage(translate('Write') . ' serviceextinfo.cfg ...', $strInfo);
        $myVisClass->processMessage('Serviceextinfo: ' . translate('No dataset or no activated dataset found - '
                . 'empty configuration written') . '::', $strInfo);
    } else {
        $myVisClass->processMessage('Serviceextinfo: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
    /* Write hostextinfo configuration */
    $intReturn15 = $myConfigClass->createConfig('tbl_hostextinfo');
    if ($intReturn15 === 0) {
        $myVisClass->processMessage(translate('Write') . ' hostextinfo.cfg ...', $strInfo);
        $myVisClass->processMessage('Hostextinfo: ' . $myConfigClass->strInfoMessage, $strInfo);
    } else if ($myConfigClass->strErrorMessage === $strNoData) {
        $myVisClass->processMessage(translate('Write') . ' hostextinfo.cfg ...', $strInfo);
        $myVisClass->processMessage('Hostextinfo: ' . translate('No dataset or no activated dataset found - empty '
                . 'configuration written') . '::', $strInfo);
    } else {
        $myVisClass->processMessage('Hostextinfo: ' . $myConfigClass->strErrorMessage, $strErrorMessage);
        $intProcessError = 1;
    }
}
/* Check configuration */
if (($chkButValue3 !== '') && ($chkButValue3 !== null)) {
    $myConfigClass->getConfigValues($intConfigId, 'binaryfile', $strBinary);
    $myConfigClass->getConfigValues($intConfigId, 'basedir', $strBaseDir);
    $myConfigClass->getConfigValues($intConfigId, 'nagiosbasedir', $strNagiosBaseDir);
    $myConfigClass->getConfigValues($intConfigId, 'conffile', $strConffile);
    if ($intMethod === 1) {
        if (file_exists($strBinary) && is_executable($strBinary)) {
            $resFile = popen($strBinary . ' -v ' . $strConffile, 'r');
        } else {
            $myVisClass->processMessage(
                translate('Cannot find the Nagios binary or no execute permissions!'),
                $strErrorMessage
            );
        }
    } elseif ($intMethod === 2) {
        $booReturn = 0;
        if (empty($myConfigClass->conFTPConId)) {
            $booReturn = $myConfigClass->getFTPConnection($intConfigId);
        }
        if ($booReturn === 1) {
            $myVisClass->processMessage($myConfigClass->strErrorMessage, $strErrorMessage);
        } else {
            $intErrorReporting = error_reporting();
            error_reporting(0);
            if (!($resFile = ftp_exec($myConfigClass->conFTPConId, $strBinary . ' -v ' . $strConffile))) {
                $myVisClass->processMessage(translate('Remote execution (FTP SITE EXEC) is not supported on your '
                    . 'system!'), $strErrorMessage);
            }
            ftp_close($myConfigClass->conFTPConId);
            error_reporting($intErrorReporting);
        }
    } elseif ($intMethod === 3) {
        $booReturn = 0;
        if (empty($myConfigClass->resSSHConId) || !is_resource($myConfigClass->resSSHConId)) {
            $booReturn = $myConfigClass->getSSHConnection($intConfigId);
        }
        if ($booReturn === 1) {
            $myVisClass->processMessage($myConfigClass->strErrorMessage, $strErrorMessage);
        } else if (($strBinary !== '') && ($strConffile !== '') &&
            $myConfigClass->sendSSHCommand('ls ' . $strBinary, $arrTemp) === 0 &&
            $myConfigClass->sendSSHCommand('ls ' . $strConffile, $arrTemp) === 0) {
            $intResult = $myConfigClass->sendSSHCommand($strBinary . ' -v ' . $strConffile, $arrResult, 15000);
            if (!is_array($arrResult)) {
                $myVisClass->processMessage(translate('Remote execution of nagios verify command failed (remote '
                    . 'SSH)!'), $strErrorMessage);
            }
        } else {
            $myVisClass->processMessage(
                translate('Nagios binary or configuration file not found (remote SSH)!'),
                $strErrorMessage
            );
        }
    }
}
/* Restart nagios */
if (($chkButValue4 !== '') && ($chkButValue4 !== null)) {
    /* Read config file */
    $myConfigClass->getConfigValues($intConfigId, 'commandfile', $strCommandfile);
    $myConfigClass->getConfigValues($intConfigId, 'binaryfile', $strBinary);
    $myConfigClass->getConfigValues($intConfigId, 'pidfile', $strPidfile);
    $myConfigClass->getConfigValues($intConfigId, 'version', $intVersion);
    /* Check state nagios demon */
    clearstatcache();
    if ($intMethod === 1) {
        if (substr_count(PHP_OS, 'Linux') !== 0) {
            exec('ps -ef | grep ' . basename($strBinary) . ' | grep -v grep', $arrExec);
        } else {
            $arrExec[0] = 1;
        }
        if (file_exists($strCommandfile) && is_writable($strCommandfile)) {
            if ($intVersion === 4) {
                $strCommandString = '[' . time() . "] RESTART_PROGRAM\n";
            } else {
                $strCommandString = '[' . time() . '] RESTART_PROGRAM;' . time() . "\n";
            }
            $timeout = 3;
            $old = ini_set('default_socket_timeout', $timeout);
            $resCmdFile = fopen($strCommandfile, 'wb');
            ini_set('default_socket_timeout', $old);
            stream_set_timeout($resCmdFile, $timeout);
            stream_set_blocking($resCmdFile, 0);
            if ($resCmdFile) {
                fwrite($resCmdFile, $strCommandString);
                fclose($resCmdFile);
                $myDataClass->writeLog(translate('Nagios daemon successfully restarted'));
                $myVisClass->processMessage(
                    translate('Restart command successfully send to Nagios'),
                    $strInfoMessage
                );
            } else {
                $myDataClass->writeLog(translate('Restart failed - Nagios command file not found or no execute '
                    . 'permissions'));
                $myVisClass->processMessage(
                    translate('Nagios command file not found or no write permissions!'),
                    $strErrorMessage
                );
            }
        } else {
            $myDataClass->writeLog(translate('Restart failed - Nagios command file not found or no execute '
                . 'permissions'));
            $myVisClass->processMessage(translate('Restart failed - Nagios command file not found or no rights '
                . 'to execute'), $strErrorMessage);
        }
    } elseif ($intMethod === 2) {
        $myDataClass->writeLog(translate('Restart failed - FTP restrictions'));
        $myVisClass->processMessage(
            translate('Nagios restart is not possible via FTP remote connection!'),
            $strErrorMessage
        );
    } elseif ($intMethod === 3) {
        $booReturn = 0;
        if (empty($myConfigClass->resSSHConId) || !is_resource($myConfigClass->resSSHConId)) {
            $booReturn = $myConfigClass->getSSHConnection($intConfigId);
        }
        if ($booReturn === 1) {
            $myVisClass->processMessage($myConfigClass->strErrorMessage, $strErrorMessage);
        } else if ($myConfigClass->sendSSHCommand('ls ' . $strCommandfile, $arrTemp) === 0) {
            if ($intVersion === 4) {
                $strCommandString = '[' . time() . "] RESTART_PROGRAM\n";
            } else {
                $strCommandString = '[' . time() . '] RESTART_PROGRAM;' . time() . "\n";
            }
            $arrInfo1 = ssh2_sftp_stat($myConfigClass->resSFTP, $strCommandfile);
            $intFileStamp1 = $arrInfo1['mtime'];
            $myConfigClass->sendSSHCommand('echo "' . $strCommandString . '" >> ' . $strCommandfile, $arrResult);
            $arrInfo2 = ssh2_sftp_stat($myConfigClass->resSFTP, $strCommandfile);
            $intFileStamp2 = $arrInfo2['mtime'];
            if ($intFileStamp2 <= $intFileStamp1) {
                $myVisClass->processMessage(translate('Restart failed - Nagios command file not found or no '
                    . 'rights to execute (remote SSH)!'), $strErrorMessage);
            } else {
                $myDataClass->writeLog(translate('Nagios daemon successfully restarted (remote SSH)'));
                $myVisClass->processMessage(
                    translate('Restart command successfully send to Nagios (remote SSH)'),
                    $strInfoMessage
                );
            }
        } else {
            $myVisClass->processMessage(translate('Nagios command file not found (remote SSH)!'), $strErrorMessage);
        }
    }
}
/*
Include content
*/
$conttp->setVariable('TITLE', translate('Check written configuration files'));
$conttp->setVariable('CHECK_CONFIG', translate('Check configuration files:'));
$conttp->setVariable('RESTART_NAGIOS', translate('Restart Nagios:'));
$conttp->setVariable('WRITE_MONITORING_DATA', translate('Write monitoring data'));
$conttp->setVariable('WRITE_ADDITIONAL_DATA', translate('Write additional data'));
if (($chkButValue3 === '') && ($chkButValue4 === '')) {
    $conttp->setVariable('WARNING', translate('Warning, always check the configuration files before restarting '
        . 'Nagios!'));
}
$conttp->setVariable('MAKE', translate('Do it'));
$conttp->setVariable('IMAGE_PATH', $_SESSION['SETS']['path']['base_url'] . 'images/');
$conttp->setVariable('ACTION_INSERT', filter_input(INPUT_SERVER, 'PHP_SELF'));
$strOutput = '';
if (isset($resFile) && ($resFile !== false)) {
    $intError = 0;
    $intWarning = 0;
    $intLines = 0;
    while (!feof($resFile)) {
        $strLine = fgets($resFile, 1024);
        $intLines++;
        if ((substr_count($strLine, 'Error:') !== 0) || (substr_count($strLine, 'Total Errors:') !== 0)) {
            $conttp->setVariable('VERIFY_CLASS', 'errormessage');
            $conttp->setVariable('VERIFY_LINE', $strLine);
            $conttp->parse('verifyline');
            $intError++;
            if (substr_count($strLine, 'Total Errors:') !== 0) {
                $intError--;
            }
        }
        if ((substr_count($strLine, 'Warning:') !== 0) || (substr_count($strLine, 'Total Warnings:') !== 0)) {
            $conttp->setVariable('VERIFY_CLASS', 'warnmessage');
            $conttp->setVariable('VERIFY_LINE', $strLine);
            $conttp->parse('verifyline');
            $intWarning++;
            if (substr_count($strLine, 'Total Warnings:') !== 0) {
                $intWarning--;
            }
        }
        $strOutput .= $strLine . '<br>';
    }
    $myDataClass->writeLog(translate('Nagios written configuration files checked - Warnings/Errors:') . ' '
        . $intWarning . '/' .
        $intError);
    pclose($resFile);
    if (($intError === 0) && ($intWarning === 0) && ($intLines > 5)) {
        $conttp->setVariable('VERIFY_CLASS', 'greenmessage');
        $conttp->setVariable('VERIFY_LINE', '<b>' . translate('Written configuration files are valid, Nagios can be '
                . 'restarted!') . '</b>');
        $conttp->parse('verifyline');
    }
    if ($intLines < 5) {
        $conttp->setVariable('VERIFY_CLASS', 'redmessage');
        $conttp->setVariable('VERIFY_LINE', '<b>' . translate('The configuration could not be tested successfully. '
                . 'The Nagios binary may have crashed during the test. Please repeat the test or try using the '
                . 'commandline to test. A running Nagios service should not be restarted because the configuration may '
                . 'be invalid.') . '</b>');
        $conttp->parse('verifyline');
    }
    $conttp->setVariable('DATA', $strOutput);
    $conttp->parse('verifyline');
} elseif (isset($arrResult) && is_array($arrResult)) {
    $intError = 0;
    $intWarning = 0;
    foreach ($arrResult as $elem) {
        if ((substr_count($elem, 'Error:') !== 0) || (substr_count($elem, 'Total Errors:') !== 0)) {
            $conttp->setVariable('VERIFY_CLASS', 'errormessage');
            $conttp->setVariable('VERIFY_LINE', $elem);
            $conttp->parse('verifyline');
            $intError++;
            if (substr_count($elem, 'Total Errors:') !== 0) {
                $intError--;
            }
        }
        if ((substr_count($elem, 'Warning:') !== 0) || (substr_count($elem, 'Total Warnings:') !== 0)) {
            $conttp->setVariable('VERIFY_CLASS', 'warnmessage');
            $conttp->setVariable('VERIFY_LINE', $elem);
            $conttp->parse('verifyline');
            $intWarning++;
            if (substr_count($elem, 'Total Warnings:') !== 0) {
                $intWarning--;
            }
        }
        $strOutput .= $elem . '<br>';
    }
    $myDataClass->writeLog(translate('Nagios written configuration files checked - Warnings/Errors:') . ' '
        . $intWarning . '/' .
        $intError);
    if (($intError === 0) && ($intWarning === 0)) {
        $conttp->setVariable('VERIFY_CLASS', 'greenmessage');
        $conttp->setVariable('VERIFY_LINE', '<b>' . translate('Written configuration files are valid, Nagios can be '
                . 'restarted!') . '</b>');
        $conttp->parse('verifyline');
    }
    $conttp->setVariable('DATA', $strOutput);
    $conttp->parse('verifyline');
}
if ($strErrorMessage !== '') {
    $conttp->setVariable('ERRORMESSAGE', $strErrorMessage);
}
$conttp->setVariable('INFOMESSAGE', $strInfoMessage);
if ($strInfo !== '') {
    $conttp->setVariable('VERIFY_CLASS', 'greenmessage');
    $conttp->setVariable('VERIFY_LINE', '<br>' . $strInfo);
    $conttp->parse('verifyline');
}
/* Check access rights for adding new objects */
if ($myVisClass->checkAccountGroup($prePageKey, 'write') !== 0) {
    $conttp->setVariable('ADD_CONTROL', 'disabled="disabled"');
}
$conttp->parse('main');
$conttp->show('main');
/*
Insert footer
*/
$maintp->setVariable('VERSION_INFO', "<a href='https:/*sourceforge.net/projects/nagiosql/' "
    . "target='_blank'>NagiosQL</a> $setFileVersion");
$maintp->parse('footer');
$maintp->show('footer');