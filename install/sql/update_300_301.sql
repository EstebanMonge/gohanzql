--
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
--
--  Gohan ZQL
--
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
--
--  (c) 2005-2023 by Martin Willisegger
--  (c) 2026 by Esteban Monge - Sempai Space
--
--  Project   : Gohan ZQL (fork of NagiosQL)
--  Component : Update from NagiosQL 3.0.0 to NagiosQL 3.0.1
--  Website   : https://github.com/EstebanMonge/gohanzql
--  Version   : 4.0.0
--  GIT Repo  : https://github.com/EstebanMonge/gohanzql
--
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
--

--
--  Modify existing tbl_settings
--
UPDATE `tbl_settings` SET `value` = '3.0.1' WHERE `tbl_settings`.`name` = 'version' LIMIT 1;
--
--  Modify existing tbl_logbook
--
ALTER TABLE `tbl_logbook` ADD `domain` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `ipadress`;
ALTER TABLE `tbl_logbook` CHANGE `entry` `entry` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
