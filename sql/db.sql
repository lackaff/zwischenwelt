-- phpMyAdmin SQL Dump
-- version 2.7.0-pl2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: May 15, 2006 at 06:40 PM
-- Server version: 4.1.12
-- PHP Version: 4.4.0-3ubuntu2
-- 
-- Database: `zw`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `action`
-- 

CREATE TABLE `action` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `building` int(10) unsigned NOT NULL default '0',
  `cmd` int(10) unsigned NOT NULL default '0',
  `param1` int(10) unsigned NOT NULL default '0',
  `param2` int(10) unsigned NOT NULL default '0',
  `starttime` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `building` (`building`),
  KEY `cmd` (`cmd`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `action`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `army`
-- 

CREATE TABLE `army` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` char(64) NOT NULL default '',
  `user` int(11) NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `nextactiontime` int(10) unsigned NOT NULL default '0',
  `frags` double NOT NULL default '0',
  `lumber` int(15) NOT NULL default '0',
  `stone` int(15) NOT NULL default '0',
  `food` int(15) NOT NULL default '0',
  `metal` int(15) NOT NULL default '0',
  `runes` int(15) NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `idle` int(10) unsigned NOT NULL default '0',
  `quest` int(10) unsigned NOT NULL default '0',
  `hellhole` int(10) unsigned NOT NULL default '0',
  `follow` int(10) unsigned NOT NULL default '0',
  `counttolimit` tinyint(3) unsigned NOT NULL default '1',
  `useditem` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `pos` (`x`,`y`),
  KEY `user` (`user`),
  KEY `type` (`type`),
  KEY `counttolimit` (`counttolimit`),
  KEY `useditem` (`useditem`),
  KEY `y` (`y`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `army`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `armyaction`
-- 

CREATE TABLE `armyaction` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `army` int(10) unsigned NOT NULL default '0',
  `starttime` int(10) unsigned NOT NULL default '0',
  `cmd` int(11) NOT NULL default '0',
  `param1` int(11) NOT NULL default '0',
  `param2` int(11) NOT NULL default '0',
  `param3` int(11) NOT NULL default '0',
  `orderval` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `army` (`army`),
  KEY `cmd` (`cmd`),
  KEY `starttime` (`starttime`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `armyaction`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `armytransfer`
-- 

CREATE TABLE `armytransfer` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `sourcebuildingtype` int(10) unsigned NOT NULL default '0',
  `sourcearmytype` int(10) unsigned NOT NULL default '0',
  `sourcetransport` int(10) unsigned NOT NULL default '0',
  `targetarmytype` int(10) unsigned NOT NULL default '0',
  `transportarmytype` int(10) unsigned NOT NULL default '0',
  `unitsbuildingtype` int(10) unsigned NOT NULL default '0',
  `idlemod` int(10) unsigned NOT NULL default '0',
  `transportertype` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=22 ;

-- 
-- Dumping data for table `armytransfer`
-- 

INSERT INTO `armytransfer` VALUES (1, 'Maschinen', 11, 0, 0, 1, 0, 0, 0, 0);
INSERT INTO `armytransfer` VALUES (2, 'Viecher', 9, 0, 0, 4, 0, 9, 0, 0);
INSERT INTO `armytransfer` VALUES (3, 'Armeen', 8, 0, 0, 4, 0, 0, 0, 0);
INSERT INTO `armytransfer` VALUES (4, 'Stationierung', 47, 0, 0, 1, 0, 0, 0, 0);
INSERT INTO `armytransfer` VALUES (5, 'Stationierung', 47, 0, 0, 4, 0, 0, 0, 0);
INSERT INTO `armytransfer` VALUES (6, 'Flotten', 47, 0, 0, 3, 0, 0, 0, 0);
INSERT INTO `armytransfer` VALUES (7, 'Verschiffen', 47, 0, 0, 3, 4, 0, 0, 0);
INSERT INTO `armytransfer` VALUES (8, 'Austausch(Besatzung)', 0, 3, 1, 3, 4, 0, 3, 0);
INSERT INTO `armytransfer` VALUES (9, 'Landung(Maschine)', 0, 3, 1, 1, 0, 0, 30, 0);
INSERT INTO `armytransfer` VALUES (10, 'Landung(Armee)', 0, 3, 1, 4, 0, 0, 60, 0);
INSERT INTO `armytransfer` VALUES (11, 'Austausch', 0, 4, 0, 4, 0, 0, 3, 0);
INSERT INTO `armytransfer` VALUES (12, 'Austausch(Schiffe)', 0, 3, 0, 3, 0, 0, 3, 0);
INSERT INTO `armytransfer` VALUES (13, 'Karawane', 16, 0, 0, 5, 0, 0, 0, 0);
INSERT INTO `armytransfer` VALUES (14, 'Arbeiter', 7, 0, 0, 6, 0, 0, 0, 0);
INSERT INTO `armytransfer` VALUES (15, 'Austausch', 0, 5, 0, 5, 0, 0, 3, 0);
INSERT INTO `armytransfer` VALUES (16, 'Austausch', 0, 6, 0, 6, 0, 0, 3, 0);
INSERT INTO `armytransfer` VALUES (17, 'Stationierung', 47, 0, 0, 6, 0, 0, 0, 0);
INSERT INTO `armytransfer` VALUES (18, 'Verschiffen', 47, 0, 0, 3, 6, 0, 0, 0);
INSERT INTO `armytransfer` VALUES (19, 'Landung(Arbeiter)', 0, 3, 1, 6, 0, 0, 30, 0);
INSERT INTO `armytransfer` VALUES (20, 'Austausch(Besatzung)', 0, 3, 1, 3, 1, 0, 3, 0);
INSERT INTO `armytransfer` VALUES (21, 'Austausch(Besatzung)', 0, 3, 1, 3, 6, 0, 3, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `armytype`
-- 

CREATE TABLE `armytype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `limit` int(10) NOT NULL default '0',
  `ownerflags` int(10) unsigned NOT NULL default '0',
  `addtechs` varchar(255) NOT NULL default '',
  `subtechs` varchar(255) NOT NULL default '',
  `weightlimit` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=8 ;

-- 
-- Dumping data for table `armytype`
-- 

INSERT INTO `armytype` VALUES (1, 'Maschine', 1, 470090979, '', '', 1500);
INSERT INTO `armytype` VALUES (3, 'Flotte', 4, 593631, '', '', 5000000);
INSERT INTO `armytype` VALUES (4, 'Armee', 4, 471432415, '', '', 5000000);
INSERT INTO `armytype` VALUES (5, 'Karawane', 4, 591051, '', '', 1000000);
INSERT INTO `armytype` VALUES (6, 'Arbeiter', 10, 29979851, '', '', 100000);
INSERT INTO `armytype` VALUES (7, 'Magier', 0, 0, '', '', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `bug`
-- 

CREATE TABLE `bug` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `finder` int(10) unsigned NOT NULL default '0',
  `creator` int(10) unsigned NOT NULL default '0',
  `created` int(10) unsigned NOT NULL default '0',
  `closed` int(10) unsigned NOT NULL default '0',
  `name` varchar(128) NOT NULL default '',
  `text` text NOT NULL,
  `desc` text NOT NULL,
  `topic` tinyint(4) NOT NULL default '0',
  `assigned_user` int(10) unsigned NOT NULL default '0',
  `prio` tinyint(3) unsigned NOT NULL default '0',
  `status` tinyint(3) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `img` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `finder` (`finder`,`creator`,`created`,`topic`,`assigned_user`,`prio`),
  KEY `status` (`status`),
  KEY `closed` (`closed`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `bug`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `building`
-- 

CREATE TABLE `building` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `type` tinyint(4) unsigned NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  `level` int(10) unsigned NOT NULL default '0',
  `upgrades` int(10) unsigned NOT NULL default '0',
  `upgradetime` int(10) unsigned NOT NULL default '0',
  `hp` float NOT NULL default '0',
  `mana` float NOT NULL default '0',
  `construction` int(10) unsigned NOT NULL default '0',
  `param` char(4) NOT NULL default '',
  `nwse` tinyint(3) unsigned NOT NULL default '0',
  `supportslots` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `pos` (`x`,`y`),
  KEY `user` (`user`),
  KEY `type` (`type`),
  KEY `construction` (`construction`),
  KEY `flags` (`flags`),
  KEY `hp` (`hp`),
  KEY `y` (`y`)
) TYPE=MyISAM AUTO_INCREMENT=567427 ;

-- 
-- Dumping data for table `building`
-- 

INSERT INTO `building` VALUES (32191, 734, -25, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 13, 0);
INSERT INTO `building` VALUES (32190, 735, -23, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32188, 735, -24, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32187, 737, -25, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32166, 736, -36, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32186, 736, -25, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32106, 736, -21, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 11, 0);
INSERT INTO `building` VALUES (32165, 737, -35, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32185, 738, -26, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32105, 738, -23, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32192, 734, -24, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 13, 0);
INSERT INTO `building` VALUES (32020, 731, -28, 249, 7, 0, 127, 0, 0, 581, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (459323, 720, -34, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (459333, 742, -18, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32345, 720, -39, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32207, 728, -22, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 7, 0);
INSERT INTO `building` VALUES (32180, 725, -33, 249, 10, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32179, 727, -36, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 3, 0);
INSERT INTO `building` VALUES (32104, 738, -24, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32258, 721, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32103, 737, -22, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32173, 724, -33, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 3, 0);
INSERT INTO `building` VALUES (32164, 737, -36, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32100, 739, -22, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 7, 0);
INSERT INTO `building` VALUES (32099, 739, -23, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 7, 0);
INSERT INTO `building` VALUES (32030, 733, -27, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32098, 739, -24, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 7, 0);
INSERT INTO `building` VALUES (32163, 738, -34, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32097, 739, -25, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 7, 0);
INSERT INTO `building` VALUES (459328, 726, -18, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32019, 730, -29, 249, 7, 0, 127, 0, 0, 581, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32162, 720, -36, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32161, 738, -35, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32095, 739, -26, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 6, 0);
INSERT INTO `building` VALUES (32094, 738, -25, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32198, 727, -26, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32160, 738, -36, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32092, 737, -24, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32172, 735, -36, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 9, 0);
INSERT INTO `building` VALUES (32091, 736, -23, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32159, 739, -33, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 3, 0);
INSERT INTO `building` VALUES (32090, 735, -22, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32089, 734, -21, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 9, 0);
INSERT INTO `building` VALUES (32088, 739, -21, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 3, 0);
INSERT INTO `building` VALUES (32158, 739, -34, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 7, 0);
INSERT INTO `building` VALUES (32087, 738, -22, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32157, 739, -35, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 7, 0);
INSERT INTO `building` VALUES (32084, 736, -24, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32083, 735, -25, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32269, 727, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32082, 734, -26, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 12, 0);
INSERT INTO `building` VALUES (32081, 723, -21, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 9, 0);
INSERT INTO `building` VALUES (32080, 724, -22, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32079, 725, -23, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32153, 737, -37, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32101, 735, -21, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 11, 0);
INSERT INTO `building` VALUES (32156, 739, -36, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 7, 0);
INSERT INTO `building` VALUES (32155, 739, -37, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 6, 0);
INSERT INTO `building` VALUES (32315, 742, -39, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32152, 736, -37, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32151, 735, -37, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 12, 0);
INSERT INTO `building` VALUES (32078, 726, -24, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32077, 727, -25, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32076, 728, -26, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 6, 0);
INSERT INTO `building` VALUES (32181, 735, -26, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32075, 727, -33, 249, 2, 0, 127, 0, 0, 1395, 1280, 0, '', 0, 0);
INSERT INTO `building` VALUES (32337, 724, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32260, 722, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32250, 720, -23, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32167, 737, -33, 249, 16, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32265, 724, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32333, 727, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32150, 726, -37, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32149, 725, -37, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32148, 724, -36, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32067, 728, -28, 249, 15, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32154, 738, -37, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32145, 720, -37, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32142, 720, -38, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32143, 724, -37, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32026, 732, -30, 249, 7, 0, 127, 0, 0, 581, 0, 0, '', 6, 0);
INSERT INTO `building` VALUES (32177, 725, -34, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 3, 0);
INSERT INTO `building` VALUES (32140, 735, -35, 249, 12, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32139, 727, -35, 249, 11, 0, 127, 0, 0, 581, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32063, 732, -32, 249, 9, 0, 127, 0, 0, 291, 0, 0, '', 0, 55);
INSERT INTO `building` VALUES (32137, 723, -36, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 13, 0);
INSERT INTO `building` VALUES (32299, 742, -28, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32285, 740, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (459314, 723, -37, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32135, 723, -33, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 9, 0);
INSERT INTO `building` VALUES (459331, 736, -18, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32133, 724, -34, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32062, 730, -32, 249, 9, 0, 127, 0, 0, 291, 0, 0, '', 0, 55);
INSERT INTO `building` VALUES (32297, 742, -26, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32332, 728, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32146, 724, -35, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32279, 735, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32132, 725, -35, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32277, 733, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32131, 726, -36, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32130, 727, -37, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 6, 0);
INSERT INTO `building` VALUES (32129, 724, -25, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32271, 729, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (459325, 731, -40, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32128, 725, -24, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32127, 724, -24, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32126, 724, -23, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32125, 723, -23, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 13, 0);
INSERT INTO `building` VALUES (32124, 723, -24, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 13, 0);
INSERT INTO `building` VALUES (32123, 723, -25, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 13, 0);
INSERT INTO `building` VALUES (32122, 723, -26, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 12, 0);
INSERT INTO `building` VALUES (32121, 723, -22, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 13, 0);
INSERT INTO `building` VALUES (32120, 726, -23, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32119, 727, -21, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 11, 0);
INSERT INTO `building` VALUES (32061, 731, -33, 249, 9, 0, 127, 0, 0, 291, 0, 0, '', 0, 55);
INSERT INTO `building` VALUES (32060, 732, -34, 249, 9, 0, 127, 0, 0, 291, 0, 0, '', 0, 55);
INSERT INTO `building` VALUES (32118, 727, -22, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32117, 726, -22, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32116, 725, -22, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32059, 730, -34, 249, 9, 0, 127, 0, 0, 291, 0, 0, '', 0, 69);
INSERT INTO `building` VALUES (32115, 728, -21, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 3, 0);
INSERT INTO `building` VALUES (32058, 731, -35, 249, 9, 0, 127, 0, 0, 291, 0, 0, '', 0, 55);
INSERT INTO `building` VALUES (32113, 726, -21, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 11, 0);
INSERT INTO `building` VALUES (32112, 725, -21, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 11, 0);
INSERT INTO `building` VALUES (32111, 724, -21, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 11, 0);
INSERT INTO `building` VALUES (32110, 738, -21, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 11, 0);
INSERT INTO `building` VALUES (32109, 737, -21, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 11, 0);
INSERT INTO `building` VALUES (32057, 732, -36, 249, 9, 0, 127, 0, 0, 291, 0, 0, '', 0, 96);
INSERT INTO `building` VALUES (32056, 730, -36, 249, 9, 0, 127, 0, 0, 291, 0, 0, '', 0, 96);
INSERT INTO `building` VALUES (32246, 720, -25, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32069, 724, -28, 249, 15, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (459332, 742, -24, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32254, 720, -19, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32270, 728, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32286, 741, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (459324, 720, -29, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32055, 732, -24, 249, 13, 0, 200, 0, 0, 400, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32251, 720, -22, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32054, 732, -22, 249, 13, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32053, 730, -22, 249, 13, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32052, 731, -23, 249, 13, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32051, 730, -24, 249, 13, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32278, 734, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32049, 732, -26, 249, 13, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32048, 730, -26, 249, 13, 0, 200, 0, 0, 400, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32031, 733, -31, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32047, 738, -30, 249, 14, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32018, 731, -29, 249, 1, 0, 127, 0, 0, 14525, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32073, 735, -33, 249, 2, 0, 127, 0, 0, 1395, 1280, 0, '', 0, 0);
INSERT INTO `building` VALUES (32296, 742, -25, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32046, 738, -28, 249, 14, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32169, 737, -34, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 9, 0);
INSERT INTO `building` VALUES (32045, 737, -29, 249, 14, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32044, 736, -30, 249, 14, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32043, 736, -28, 249, 14, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32024, 730, -28, 249, 7, 0, 127, 0, 0, 581, 0, 0, '', 9, 0);
INSERT INTO `building` VALUES (32042, 735, -29, 249, 14, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32023, 730, -30, 249, 7, 0, 127, 0, 0, 581, 0, 0, '', 12, 0);
INSERT INTO `building` VALUES (32022, 732, -29, 249, 7, 0, 127, 0, 0, 581, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32021, 732, -28, 249, 7, 0, 127, 0, 0, 581, 0, 0, '', 3, 0);
INSERT INTO `building` VALUES (32041, 734, -30, 249, 14, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32040, 734, -28, 249, 14, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32065, 727, -29, 249, 15, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32068, 725, -29, 249, 15, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32070, 724, -30, 249, 15, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32071, 726, -30, 249, 15, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32183, 736, -26, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32025, 731, -30, 249, 7, 0, 127, 0, 0, 581, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32184, 737, -26, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32147, 725, -36, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (459334, 742, -29, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32102, 736, -22, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32086, 737, -23, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32267, 725, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32028, 729, -31, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32029, 729, -27, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32294, 742, -23, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32195, 734, -22, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 13, 0);
INSERT INTO `building` VALUES (32138, 723, -35, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 13, 0);
INSERT INTO `building` VALUES (32320, 739, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32176, 720, -35, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32178, 726, -35, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 3, 0);
INSERT INTO `building` VALUES (32168, 738, -33, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 9, 0);
INSERT INTO `building` VALUES (32064, 728, -30, 249, 15, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32171, 736, -35, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 9, 0);
INSERT INTO `building` VALUES (32144, 723, -34, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 13, 0);
INSERT INTO `building` VALUES (32199, 725, -26, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32050, 731, -25, 249, 13, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (459327, 720, -18, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32241, 720, -26, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32307, 742, -33, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32194, 734, -23, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 13, 0);
INSERT INTO `building` VALUES (32066, 726, -28, 249, 15, 0, 127, 0, 0, 291, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32263, 723, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (459317, 726, -40, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32204, 728, -25, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 7, 0);
INSERT INTO `building` VALUES (32203, 726, -25, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32202, 725, -25, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32201, 724, -26, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32200, 726, -26, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (32197, 720, -33, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32205, 728, -24, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 7, 0);
INSERT INTO `building` VALUES (32206, 728, -23, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 7, 0);
INSERT INTO `building` VALUES (32208, 727, -24, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32209, 727, -23, 249, 6, 0, 127, 0, 0, 291, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (32210, 720, -32, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32211, 720, -31, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32214, 720, -30, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32224, 720, -28, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32335, 725, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32331, 729, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32232, 720, -27, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (459329, 731, -18, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (459326, 720, -24, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32252, 720, -21, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32253, 720, -20, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32340, 722, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32325, 734, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32322, 737, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32281, 737, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32274, 730, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32288, 742, -19, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32338, 723, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32312, 742, -37, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32290, 742, -20, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32324, 735, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32276, 732, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32291, 742, -21, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32282, 738, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32284, 739, -18, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32327, 733, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32310, 742, -35, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32329, 732, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32292, 742, -22, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32311, 742, -36, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32298, 742, -27, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32321, 738, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (459318, 736, -40, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32302, 742, -30, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32305, 742, -31, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (32306, 742, -32, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (459322, 742, -34, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32314, 742, -38, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (459315, 720, -40, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32342, 721, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (459319, 742, -40, 249, 73, 768, 127, 0, 0, 349, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (32318, 741, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32319, 740, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (32330, 730, -40, 249, 5, 0, 127, 0, 0, 349, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (151502, 459, -483, 249, 3, 0, 10, 0, 0, 6, 0, 0, '', 8, 0);
INSERT INTO `building` VALUES (151501, 461, -482, 249, 3, 0, 10, 0, 0, 6, 0, 0, '', 1, 0);
INSERT INTO `building` VALUES (151500, 461, -483, 249, 3, 0, 10, 0, 0, 6, 0, 0, '', 6, 0);
INSERT INTO `building` VALUES (151499, 460, -483, 249, 3, 0, 10, 0, 0, 6, 0, 0, '', 10, 0);
INSERT INTO `building` VALUES (151503, 460, -482, 249, 46, 0, 10, 0, 0, 115, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (151504, 461, -484, 249, 47, 0, 15, 0, 0, 147, 0, 0, '', 8, 0);
INSERT INTO `building` VALUES (151505, 461, -485, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (151506, 461, -486, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 15, 0);
INSERT INTO `building` VALUES (151507, 461, -487, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 5, 0);
INSERT INTO `building` VALUES (151508, 461, -488, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 14, 0);
INSERT INTO `building` VALUES (151509, 460, -486, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 8, 0);
INSERT INTO `building` VALUES (151510, 462, -486, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 2, 0);
INSERT INTO `building` VALUES (151511, 460, -488, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 8, 0);
INSERT INTO `building` VALUES (151512, 462, -488, 249, 48, 0, 10, 0, 0, 35, 0, 0, '', 2, 0);
INSERT INTO `building` VALUES (151513, 462, -483, 249, 8, 0, 10, 0, 0, 173, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (261109, 725, -39, 249, 64, 0, 10, 0, 0, 58, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (459777, 731, -27, 249, 51, 0, 100, 0, 0, 375, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567426, 731, -31, 249, 10, 0, 10, 0, 0, 115, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567386, 733, -32, 249, 14, 256, 15, 112, 1147644997, 123, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567387, 733, -33, 249, 14, 256, 15, 112, 1147645117, 123, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567388, 733, -34, 249, 14, 256, 15, 112, 1147645252, 123, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567389, 734, -34, 249, 14, 256, 15, 112, 1147645432, 123, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567390, 734, -33, 249, 14, 256, 15, 112, 1147645553, 123, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567391, 734, -32, 249, 14, 256, 15, 112, 1147645612, 123, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567392, 728, -32, 249, 6, 256, 13, 114, 1147646800, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567393, 728, -33, 249, 6, 256, 13, 114, 1147646861, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567394, 728, -34, 249, 6, 256, 13, 114, 1147646980, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567395, 728, -31, 249, 6, 256, 13, 114, 1147647101, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567396, 729, -33, 249, 7, 256, 11, 116, 1147644109, 233, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567397, 727, -34, 249, 6, 256, 13, 114, 1147647340, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567398, 726, -34, 249, 6, 256, 13, 114, 1147647460, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567399, 726, -33, 249, 6, 256, 13, 114, 1147647581, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567400, 726, -32, 249, 6, 256, 13, 114, 1147647701, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567401, 727, -32, 249, 6, 256, 13, 114, 1147647821, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567402, 727, -31, 249, 6, 256, 13, 114, 1147647941, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567403, 726, -31, 249, 6, 256, 13, 114, 1147648060, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567404, 725, -31, 249, 6, 256, 13, 114, 1147648180, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567405, 725, -32, 249, 6, 256, 13, 114, 1147648301, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567406, 724, -32, 249, 6, 256, 13, 114, 1147648420, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567407, 724, -31, 249, 6, 256, 13, 114, 1147648540, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567408, 723, -31, 249, 6, 256, 13, 114, 1147648600, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567409, 723, -32, 249, 6, 256, 13, 114, 1147648721, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567410, 722, -31, 249, 6, 256, 13, 114, 1147648840, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567411, 721, -31, 249, 6, 256, 13, 114, 1147649021, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567412, 722, -33, 249, 6, 256, 13, 114, 1147649140, 120, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567413, 722, -34, 249, 6, 256, 12, 115, 1147643821, 118, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567414, 722, -35, 249, 6, 256, 12, 115, 1147644002, 118, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567415, 721, -35, 249, 6, 256, 12, 115, 1147644122, 118, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567416, 721, -36, 249, 6, 256, 12, 115, 1147644241, 118, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567417, 722, -36, 249, 6, 256, 12, 115, 1147644301, 118, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567418, 722, -37, 249, 6, 256, 12, 115, 1147644481, 118, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567419, 721, -37, 249, 6, 256, 12, 115, 1147644601, 118, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567420, 722, -32, 249, 6, 256, 12, 115, 1147644722, 118, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567421, 721, -32, 249, 6, 256, 12, 115, 1147644842, 118, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567422, 721, -33, 249, 6, 256, 12, 115, 1147644961, 118, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567423, 728, -37, 249, 6, 256, 12, 115, 1147645082, 118, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567424, 728, -36, 249, 6, 256, 12, 115, 1147645201, 118, 0, 0, '', 0, 0);
INSERT INTO `building` VALUES (567425, 728, -35, 249, 6, 256, 12, 115, 1147645262, 118, 0, 0, '', 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `buildinglevel`
-- 

CREATE TABLE `buildinglevel` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `building` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `subtype` int(10) unsigned NOT NULL default '0',
  `level` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `buildinglevel`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `buildingname`
-- 

CREATE TABLE `buildingname` (
  `id` int(10) unsigned NOT NULL default '0',
  `name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `buildingname`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `buildingparam`
-- 

CREATE TABLE `buildingparam` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `building` int(10) unsigned NOT NULL default '0',
  `name` varchar(32) NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `building` (`building`)
) TYPE=MyISAM AUTO_INCREMENT=134901 ;

-- 
-- Dumping data for table `buildingparam`
-- 

INSERT INTO `buildingparam` VALUES (124013, 459323, 'tax', '');
INSERT INTO `buildingparam` VALUES (124014, 459333, 'tax', '');
INSERT INTO `buildingparam` VALUES (124015, 459328, 'tax', '');
INSERT INTO `buildingparam` VALUES (124016, 459331, 'tax', '');
INSERT INTO `buildingparam` VALUES (124017, 459325, 'tax', '');
INSERT INTO `buildingparam` VALUES (124018, 459332, 'tax', '');
INSERT INTO `buildingparam` VALUES (124019, 459324, 'tax', '');
INSERT INTO `buildingparam` VALUES (124020, 459334, 'tax', '');
INSERT INTO `buildingparam` VALUES (124021, 459327, 'tax', '');
INSERT INTO `buildingparam` VALUES (124022, 459317, 'tax', '');
INSERT INTO `buildingparam` VALUES (124023, 459329, 'tax', '');
INSERT INTO `buildingparam` VALUES (124024, 459326, 'tax', '');
INSERT INTO `buildingparam` VALUES (124025, 459318, 'tax', '');
INSERT INTO `buildingparam` VALUES (124026, 459322, 'tax', '');
INSERT INTO `buildingparam` VALUES (124027, 459315, 'tax', '');
INSERT INTO `buildingparam` VALUES (124028, 459319, 'tax', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `buildingtype`
-- 

CREATE TABLE `buildingtype` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `descr` text NOT NULL,
  `cost_lumber` int(10) unsigned NOT NULL default '0',
  `cost_stone` int(10) unsigned NOT NULL default '0',
  `cost_food` int(10) unsigned NOT NULL default '0',
  `cost_metal` int(10) unsigned NOT NULL default '0',
  `cost_runes` int(10) unsigned NOT NULL default '0',
  `req_geb` varchar(128) NOT NULL default '',
  `req_tech` varchar(128) NOT NULL default '',
  `buildtime` int(10) unsigned NOT NULL default '0',
  `maxhp` int(11) NOT NULL default '0',
  `basemana` int(11) NOT NULL default '0',
  `script` varchar(64) NOT NULL default '',
  `color` varchar(8) NOT NULL default '',
  `letter` varchar(8) NOT NULL default '',
  `lettercolor` varchar(8) NOT NULL default '',
  `speed` int(11) NOT NULL default '0',
  `gfx` varchar(128) NOT NULL default '',
  `special` int(10) unsigned NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  `weightlimit` int(10) unsigned NOT NULL default '0',
  `cssclass` varchar(64) NOT NULL default '',
  `orderval` tinyint(4) NOT NULL default '0',
  `ruinbtype` int(10) unsigned NOT NULL default '0',
  `race` tinyint(3) unsigned NOT NULL default '1',
  `terrain_needed` tinyint(3) unsigned NOT NULL default '0',
  `mod_a` float NOT NULL default '1',
  `mod_v` float NOT NULL default '1',
  `mod_f` float NOT NULL default '1',
  `connectto_terrain` varchar(255) NOT NULL default '',
  `connectto_building` varchar(255) NOT NULL default '',
  `neednear_building` varchar(255) NOT NULL default '',
  `require_building` varchar(255) NOT NULL default '',
  `exclude_building` varchar(255) NOT NULL default '',
  `border` tinyint(3) unsigned NOT NULL default '1',
  `movable_flag` int(10) unsigned NOT NULL default '0',
  `movable_override_terrain` tinyint(3) unsigned NOT NULL default '1',
  `convert_into_terrain` int(10) unsigned NOT NULL default '0',
  `maxgfxlevel` int(10) unsigned NOT NULL default '1',
  `maxrandcenter` int(10) unsigned NOT NULL default '0',
  `maxrandborder` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `race` (`race`),
  KEY `maxhp` (`maxhp`),
  KEY `movable_flag` (`movable_flag`)
) TYPE=MyISAM AUTO_INCREMENT=76 ;

-- 
-- Dumping data for table `buildingtype`
-- 

INSERT INTO `buildingtype` VALUES (1, 'Haupthaus', '+12 Bev&ouml;lkerungsmaximum + 12 je Stufe<br> +10 Slots je Rohstoff Produktion + 10 je Stufe<br> +250 Lagerkapazit&auml;t je Rohstoff + 250 je Stufe<p> \r\nDas maximale Level andere Geb&auml;ude ist anfangs 3.<br> mit jedem Haupthaus-Level sind 3 weitere upgrades m&ouml;glich.<br> Wenn das Haupthaus zerst&ouml;rt oder abgerissen wird,<br> werden auch alle Geb&auml;ude, Armeen und Forschungen zerst&ouml;rt.', 800, 800, 800, 800, 0, '', '', 36, 5000, 0, 'hq', '#FFFF00', 'H', 'black', 0, 'gebaeude-r%R%/hq-%L%.png', 0, 0, 0, 'hq', 1, 42, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (2, 'Magieturm', 'hier kann man Zauber erforschen, Runen produzieren und Zauberer ausbilden<p>\r\n\r\nProduktion:<br>\r\n* Turmzauberer<br>\r\n* Runen<p>\r\n\r\nForschung:<br>\r\n* Effiziente Runenproduktion<p>\r\n\r\nZauber:<br>\r\n* Spieler Defensiv<br>\r\n* Spieler Offensiv<br>\r\n* Area Defensiv<br>\r\n* Area Offensiv<br>\r\n* Armee Defensiv<br>\r\n* Armee Offensiv<br>\r\n* Armeezauberer', 5000, 5000, 5000, 5000, 0, '1:5', '', 69120, 480, 10, 'magic_tower', '#8888FF', 'T', 'yellow', 0, 'gebaeude-r%R%/magitower-%L%.png', 0, 512, 2400, 'magitower', 20, 27, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (3, 'Weg', 'auch der l�ngste Weg beginnt mit dem ersten Schritt,\r\n<br>hier l�uft die Armee schneller.', 5, 5, 0, 0, 0, '1:0', '', 2880, 5, 0, 'way', '#949454', '#', 'black', 60, 'path/path-%NWSE%-%L%.png', 0, 0, 0, 'path_%NWSE%', 11, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (4, 'BROID - Obelisk', 'Blue Ray Of Instant Death - besser nicht in die n�he kommen =), kann Felder niederbrennen', 9999999, 9999999, 9999999, 9999999, 0, '', '', 69120, 999999999, 0, 'broid', 'black', 'i', 'white', 0, 'gebaeude-r%R%/broid-%L%.png', 1, 512, 0, 'broid', 0, 39, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (5, 'Wall', 'eine fette dicke Mauer, versperrt Armeen den Weg.', 5, 10, 0, 0, 0, '11:0,1:0', '', 5760, 120, 0, 'wall', 'gray', 'W', 'black', 0, 'wall/wall-%NWSE%-%L%.png', 0, 0, 0, 'wall_%NWSE%', 12, 0, 0, 0, 1, 1, 1, '', '73', '', '', '', 0, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (6, 'Haus', 'Ein sch�nes Wohnhaus, damit die Bev�lkerung\r\n<br>nicht beim Haupthaus kampieren muss.\r\n<p>+10 Bev�lkerungsmaximum + 10 je Stufe', 100, 100, 0, 0, 0, '1:0', '', 11520, 100, 0, 'house', 'red', 'm', 'yellow', 0, 'gebaeude-r%R%/house-%L%.png', 0, 0, 0, 'house', 2, 41, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 2, 0, 0);
INSERT INTO `buildingtype` VALUES (7, 'Lager', 'das Rohstofflager, je weiter Baustellen vom Lager entfernt\r\n<br>sind, desto l�nger brauchen sie zum bauen\r\n<p>+250 Lagerkapazit�t je Rohstoff + 250 je Stufe', 50, 50, 0, 0, 0, '1:0', '', 14400, 200, 0, 'lager', '#FFFF96', 'L', 'black', 0, 'gebaeude-r%R%/lager-%L%.png', 0, 0, 0, 'lager', 3, 26, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 2, 0, 0);
INSERT INTO `buildingtype` VALUES (8, 'Kaserne', 'hier kann man folgende Einheiten produzieren<p>\r\n\r\nProduktion:<br>\r\n* Miliz<br>\r\n* K�mpfer<br>\r\n* SchwertKrieger<br>\r\n* LanzenTr�ger<br>\r\n* Berserker<br>\r\n* Ritter', 80, 50, 0, 50, 0, '12:0,1:0', '', 34560, 150, 0, 'kaserne', '#FF80FF', 't', 'white', 0, 'gebaeude-r%R%/barracks-%L%.png', 0, 512, 0, 'barracks', 10, 38, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (9, 'Bauernhof', 'kikerikiiiii! gack-gack-gack-gack.. grunz-grunz <p>\r\n+10 Slots f�r Nahrungs-Produktion + 10 je Stufe<br>\r\n+2 extra f�r angrenzende Getreidefelder<p>\r\n\r\nForschung:<br>\r\n* Sense', 20, 15, 0, 0, 0, '1:0', '', 8640, 100, 0, 'farm', '#ffcc44', 'F', 'black', 0, 'gebaeude-r%R%/farm-%L%.png', 0, 0, 0, 'farm', 6, 35, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (10, 'Entwickleranstalt', 'Hier sind all die flei�igen Entwickler dieses tollen Spiels, wenn Sie nicht gerade ganz flei�ig weiter daran baun :). Vielleicht sollte man sie auch einfach hier drin lassen *G*, aber machen kann man hiermit trotzdem nix.', 5000, 5000, 5000, 5000, 0, '', '', 69120, 100, 0, 'hospital', 'blue', 'h', 'yellow', 0, 'gebaeude-r%R%/hospital-%L%.png', 1, 512, 0, 'hospital', 16, 37, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (11, 'Werkstatt', 'lustige spielzeuge hehehe...<br>\r\nHier werden die m�chtigen Rammb�cke gefertigt,<br>\r\ndie Mauern und Geb�ude dem Erdboden gleichmachen.<p>\r\n\r\nProduktion:<br>\r\n* Rammen<p>\r\n\r\nForschung:<br>\r\n* Architektur', 100, 50, 0, 150, 0, '1:2', '', 34560, 200, 0, 'werkstatt', '#CC8800', 'W', 'black', 0, 'gebaeude-r%R%/werkstatt-%L%.png', 0, 512, 1000, 'werkstatt', 8, 34, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (12, 'Schmiede', 'hier werden Klingen geschmiedet, Pfeilspitzen gegossen und R�stungen gefertigt.<p>\r\n\r\nForschung:<br>\r\n* Kettenr�stung<br>\r\n* geh�rtete Klingen<br>\r\n* Plattenpanzer<br>\r\n* Lederr�stung', 50, 100, 0, 300, 0, '1:3', '', 34560, 100, 0, 'schmiede', '#444444', 'S', 'white', 0, 'gebaeude-r%R%/schmiede-%L%.png', 0, 512, 0, 'schmiede', 9, 31, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (13, 'Holzf&auml;ller', 'hier wird fleissig das Hackebeil geschwungen<p>\r\n+10 Slots f�r Holz Produktion + 10 je Stufe<br>\r\n+2 extra f�r jeden angrenzenden Wald<p>\r\n\r\nForschung:<br>\r\n* Axt', 20, 15, 0, 0, 0, '1:0', '', 8640, 100, 0, 'farm', '#ffcc44', 'H', 'black', 0, 'gebaeude-r%R%/holzfaeller-%L%-b%BUSY%.png', 0, 0, 0, 'holzfaeller', 4, 40, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (14, 'Steinmetz', 'Hau Druff, Hau Druff, Hau Druff, AUA MEIN DAUMEN!<p>\r\n+10 Slots f�r Stein Produktion + 10 je Stufe<br>\r\n+2 extra f�r jeden angrenzenden Berg<p>\r\n\r\nForschung:<br>\r\n* Hammer', 20, 15, 0, 0, 0, '1:0', '', 8640, 100, 0, 'steinmetz', '#ffcc44', 'S', 'black', 0, 'gebaeude-r%R%/steinmetz-%L%-b%BUSY%.png', 0, 0, 0, 'steinmetz', 5, 32, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (15, 'Eisenmine', 'in dunstigen, sp�rlich beleuchteten Stollen, tief unter <br>der Erde, verrichten hier die Bergarbeiter ihr Tagwerk <br>und f�rdern das wertvolle Eisenerz zu Tage, das dann <br>haupts�chlich f�r das Schmieden von Waffen verwendet wird.<p>\r\n+10 Slots f�r Eisen Produktion + 10 je Stufe<br>\r\n+2 extra f�r jeden angrenzenden Berg<p>\r\n\r\nForschung:<br>\r\n* Spitzhacke', 20, 15, 0, 0, 0, '1:0', '', 8640, 100, 0, 'eisenmine', '#ffcc44', 'S', 'black', 0, 'gebaeude-r%R%/eisenmine-%L%-b%BUSY%.png', 0, 0, 0, 'eisenmine', 7, 36, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (16, 'Marktplatz', 'Frische Datteln, sch���ne frische Datteln...\r\n<br>hier kann man Rohstoffhandel betreiben', 50, 50, 0, 10, 0, '', '', 17280, 100, 0, 'marketplace', 'red', 'M', 'white', 0, 'gebaeude-r%R%/marketplace-%L%.png', 0, 512, 0, 'marketplace', 26, 28, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (17, 'Tor', 'man selber kann immer durch seine Tore, aber die anderen\r\n<br>(zB Gildemitglieder, Fremde) nur wenn es offen ist.', 50, 60, 0, 10, 0, '11:2,1:0', '', 5760, 90, 0, 'gate', '#eeeeee', 'G', 'black', 60, 'gate/tor-zu-%NWSE%-%L%.png', 0, 0, 0, 'gate_%NWSE%', 13, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (18, 'Br&uuml;cke', '...', 80, 50, 0, 10, 0, '11:2,1:0', '', 5760, 10, 0, 'way', 'red', 'B', 'green', 60, 'gate/bridge-%NWSE%-%L%.png', 0, 0, 0, 'bridge_%NWSE%', 11, 0, 0, 2, 1, 1, 1, '', '', '', '', '', 0, 15, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (19, 'Schild', 'ein kleines nettes Schild, auf das man ganz viele Dinge schreiben kann', 15, 0, 0, 0, 0, '1:0', '', 2880, 10, 0, 'schild', 'brown', 'S', 'black', 60, 'gebaeude-r%R%/schild-%L%.png', 0, 0, 0, 'schild', 30, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (20, 'Tempel', 'hier kann man seine Bewohner opfern, um irgendwelchen G�ttern zu huldigen.', 5, 200, 50, 0, 0, '1:5', '', 11520, 60, 0, 'tempel', 'blue', 'T', 'white', 0, 'gebaeude-r%R%/tempel-%L%-%M%.png', 0, 512, 0, 'tempel', 24, 33, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (21, 'H�llenschlund', 'hier erscheinen b�se Monster', 99999, 99999, 99999, 99999, 0, '', '', 34560, 300, 0, 'hellhole', '#FF0000', 'H', 'black', 0, 'landschaft/hellhole-%L%.gif', 1, 0, 0, 'hellhole', 0, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (22, 'Schachplatz', 'Lust auf eine kleines Schachspiel gegen andere Leute aus der Zwischenwelt...\r\n<br>kein Problem einfach hier Spielen', 500, 500, 50, 50, 0, '1:0', '', 11520, 50, 0, 'schachplatz', 'black', 'C', 'white', 0, 'gebaeude-r%R%/schachplatz-%L%.png', 0, 512, 0, 'schachplatz', 44, 29, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (23, 'Portal', 'Eine wunderbare M�glichkeit �ber weite Strecken zu reisen, man baut sein eigenes Portal, muss Zugang zu seinem Zielportal haben und los gehts, zum Aktivieren ist nat�rlich eine gewisse Aufwandsentsch�digung zu zahlen, ausserdem kann die andere Seite Zoll verlangen\r\n<p>\r\nReichweite: 100 Pro Stufe\r\n<br>Aktiviert f�r: 1+stufe/3 transporte</br></p>\r\n', 5500, 5500, 2500, 7500, 5000, '11:15,1:10,2:3', '', 36000, 900, 0, 'portal', '#ccccaa', 'P', 'black', 0, 'gate/portal-zu-%L%.png', 0, 512, 0, 'portal', 21, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (24, 'Torbr&uuml;cke', 'wie die Towerbridge in London ....', 100, 80, 0, 20, 0, '11:3,1:0', '', 5725, 90, 0, 'gate_bridge', '', 'GB', '', 120, 'gate/gb-zu-%NWSE%-%L%.png', 0, 0, 0, 'gb_%NWSE%', 13, 0, 0, 2, 1, 1, 1, '', '', '', '', '', 0, 15, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (25, 'Brunnen', 'kleiner Brunnen, der das Stadtbild versch�nert', 0, 15, 0, 0, 0, '1:0', '', 2880, 10, 0, 'brunnen', '#666666', '', '', 60, 'gebaeude-r%R%/brunnen-%L%.png', 0, 0, 0, 'brunnen', 31, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (26, 'LagerRuine', 'das Rohstofflager, je weiter Baustellen vom Lager entfernt\r\n<br>sind, desto l�nger brauchen sie zum bauen\r\n<p>+250 Lagerkapazit�t je Rohstoff + 250 je Stufe', 50, 50, 0, 0, 0, '1:0', '', 28800, 200, 0, '', '#FFFF96', 'L', 'black', 180, 'gebaeude-r%R%/lager-dead.png', 1, 0, 0, 'lager_dead', 3, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (27, 'MagieturmRuine', 'hier kann man Zauber erforschen, Runen produzieren und Zauberer ausbilden<p>\r\n\r\nProduktion:<br>\r\n* Turmzauberer<br>\r\n* Runen<p>\r\n\r\nForschung:<br>\r\n* Effiziente Runenproduktion<p>\r\n\r\nZauber:<br>\r\n* Spieler Defensiv<br>\r\n* Spieler Offensiv<br>\r\n* Area Defensiv<br>\r\n* Area Offensiv<br>\r\n* Armee Defensiv<br>\r\n* Armee Offensiv<br>\r\n* Armeezauberer', 5000, 5000, 5000, 5000, 0, '1:5', '', 69120, 480, 10, '', '#8888FF', 'T', 'yellow', 180, 'gebaeude-r%R%/magitower-dead.png', 1, 0, 0, 'magitower_dead', 15, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (28, 'MartkplatzRuine', 'Frische Datteln, sch���ne frische Datteln...\r\n<br>hier kann man Rohstoffhandel betreiben', 50, 50, 0, 10, 0, '', '', 17280, 100, 0, '', 'red', 'M', 'white', 180, 'gebaeude-r%R%/marketplace-dead.png', 1, 0, 0, 'marketplace_dead', 14, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (29, 'SchachplatzRuine', 'Lust auf eine kleines Schachspiel gegen andere Leute aus der Zwischenwelt...\r\n<br>kein Problem einfach hier Spielen', 500, 500, 50, 50, 0, '1:0', '', 11520, 50, 0, '', 'black', 'C', 'white', 180, 'gebaeude-r%R%/schachplatz-dead.png', 1, 0, 0, 'schachplatz_dead', 18, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (30, 'SchildRuine', 'ein kleines nettes Schild, auf das man ganz viele Dinge schreiben kann', 15, 0, 0, 0, 0, '1:0', '', 240, 10, 0, '', 'brown', 'S', 'black', 180, 'gebaeude-r%R%/schild-dead.png', 1, 0, 0, 'schild_dead', 19, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (31, 'SchmiedeRuine', 'hier werden Klingen geschmiedet, Pfeilspitzen gegossen und R�stungen gefertigt.<p>\r\n\r\nForschung:<br>\r\n* Kettenr�stung<br>\r\n* geh�rtete Klingen<br>\r\n* Plattenpanzer<br>\r\n* Lederr�stung', 50, 100, 0, 300, 0, '1:3', '', 34560, 100, 0, '', '#444444', 'S', 'white', 180, 'gebaeude-r%R%/schmiede-dead.png', 1, 0, 0, 'schmiede_dead', 9, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (32, 'SteinmetzRuine', 'Hau Druff, Hau Druff, Hau Druff, AUA MEIN DAUMEN!<p>\r\n+10 Slots f�r Stein Produktion + 10 je Stufe<br>\r\n+2 extra f�r jeden angrenzenden Berg<p>\r\n\r\nForschung:<br>\r\n* Hammer', 20, 15, 0, 0, 0, '1:0', '', 17280, 100, 0, '', '#ffcc44', 'S', 'black', 180, 'gebaeude-r%R%/steinmetz-dead.png', 1, 0, 0, 'steinmetz_dead', 5, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (33, 'TempelRuine', 'hier kann man seine Bewohner opfern, um irgendwelchen G�ttern zu huldigen.', 5, 200, 50, 0, 0, '1:5', '', 11520, 60, 0, '', 'blue', 'T', 'white', 180, 'gebaeude-r%R%/tempel-dead.png', 1, 0, 0, 'tempel_dead', 16, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (34, 'WerkstattRuine', 'lustige spielzeuge hehehe...<br>\r\nHier werden die m�chtigen Rammb�cke gefertigt,<br>\r\ndie Mauern und Geb�ude dem Erdboden gleichmachen.<p>\r\n\r\nProduktion:<br>\r\n* Rammen<p>\r\n\r\nForschung:<br>\r\n* Architektur', 100, 50, 0, 150, 0, '1:2', '', 34560, 200, 0, '', '#CC8800', 'W', 'black', 180, 'gebaeude-r%R%/werkstatt-dead.png', 1, 0, 0, 'werkstatt_dead', 8, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (35, 'BauernhofRuine', 'kikerikiiiii! gack-gack-gack-gack.. grunz-grunz <p>\r\n+10 Slots f�r Nahrungs-Produktion + 10 je Stufe<br>\r\n+2 extra f�r jeden angrenzenden Fluss + Bonus f�r angrenzende Getreidefelder<p>\r\n\r\nForschung:<br>\r\n* Sense', 20, 15, 0, 0, 0, '1:0', '', 17280, 100, 0, '', '#ffcc44', 'F', 'black', 180, 'gebaeude-r%R%/farm-dead.png', 1, 0, 0, 'farm_dead', 6, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (36, 'EisenminenRuine', 'in dunstigen, sp�rlich beleuchteten Stollen, tief unter <br>der Erde, verrichten hier die Bergarbeiter ihr Tagwerk <br>und f�rdern das wertvolle Eisenerz zu Tage, das dann <br>haupts�chlich f�r das Schmieden von Waffen verwendet wird.<p>\r\n+10 Slots f�r Eisen Produktion + 10 je Stufe<br>\r\n+2 extra f�r jeden angrenzenden Berg<p>\r\n\r\nForschung:<br>\r\n* Spitzhacke', 20, 15, 0, 0, 0, '1:0', '', 17280, 100, 0, '', '#ffcc44', 'S', 'black', 180, 'gebaeude-r%R%/eisenmine-dead.png', 1, 0, 0, 'eisenmine_dead', 7, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (37, 'EntwickleranstaltRuine', 'Hier sind all die flei�igen Entwickler dieses tollen Spiels, wenn Sie nicht gerade ganz flei�ig weiter daran baun :). Vielleicht sollte man sie auch einfach hier drin lassen *G*, aber machen kann man hiermit trotzdem nix.', 5000, 5000, 5000, 5000, 0, '', '', 69120, 100, 0, '', 'blue', 'h', 'yellow', 180, 'gebaeude-r%R%/hospital-dead.png', 1, 0, 0, 'hospital_dead', 16, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (38, 'KasernenRuine', 'hier kann man folgende Einheiten produzieren<p>\r\n\r\nProduktion:<br>\r\n* Miliz<br>\r\n* K�mpfer<br>\r\n* SchwertKrieger<br>\r\n* LanzenTr�ger<br>\r\n* Berserker<br>\r\n* Ritter', 80, 50, 0, 50, 0, '12:0,1:0', '', 34560, 150, 0, '', '#FF80FF', 't', 'white', 180, 'gebaeude-r%R%/barracks-dead.png', 1, 0, 0, 'barracks_dead', 10, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (39, 'BROID - Ruine', 'Blue Ray Of Instant Death - besser nicht in die n�he kommen =), kann Felder niederbrennen', 9999999, 9999999, 9999999, 9999999, 0, '', '', 69120, 999999999, 0, '', 'black', 'i', 'white', 180, 'gebaeude-r%R%/broid-dead.png', 1, 0, 0, 'broid_dead', 0, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (40, 'Holzf&auml;llerRuine', 'hier wird fleissig das Hackebeil geschwungen<p>\r\n+10 Slots f�r Holz Produktion + 10 je Stufe<br>\r\n+2 extra f�r jeden angrenzenden Wald<p>\r\n\r\nForschung:<br>\r\n* Axt', 20, 15, 0, 0, 0, '1:0', '', 17280, 100, 0, '', '#ffcc44', 'H', 'black', 180, 'gebaeude-r%R%/holzfaeller-dead.png', 1, 0, 0, 'holzfaeller_dead', 4, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (41, 'HausRuine', 'Ein sch�nes Wohnhaus, damit die Bev�lkerung\r\n<br>nicht beim Haupthaus kampieren muss.\r\n<p>+10 Bev�lkerungsmaximum + 10 je Stufe', 100, 100, 0, 0, 0, '1:0', '', 23040, 100, 0, '', 'red', 'm', 'yellow', 180, 'gebaeude-r%R%/house-dead.png', 1, 0, 0, 'house_dead', 2, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (42, 'HaupthausRuine', '+12 Bev�lkerungsmaximum + 12 je Stufe<br> +10 Slots je Rohstoff Produktion + 10 je Stufe<br> +250 Lagerkapazit�t je Rohstoff + 250 je Stufe<p> \r\nDas maximale Level andere Geb�ude ist anfangs 3.<br> mit jedem Haupthaus-Level sind 3 weitere upgrades m�glich.<br> Wenn das Haupthaus zerst�rt oder abgerissen wird,<br> werden auch alle Geb�ude, Armeen und Forschungen zerst�rt.', 800, 800, 800, 800, 0, '', '', 36, 5000, 0, '', '#FFFF00', 'H', 'black', 180, 'gebaeude-r%R%/hq-dead.png', 1, 0, 0, 'hq_dead', 1, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (43, 'Teehaus', '', 150, 50, 100, 50, 0, '', '', 11520, 50, 0, 'teahouse', 'black', 'T', 'white', 60, 'gebaeude-r%R%/teahouse-%L%.png', 0, 0, 0, 'teahouse', 42, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (44, 'G&auml;rtnerei', 'Im Moment noch ohne Effekt', 100, 10, 100, 10, 0, '1:15, 25:0', '49:1', 17280, 50, 0, 'garden', '', '', '', 0, 'gebaeude-r%R%/gaertnerei-%L%.png', 0, 0, 0, '', 45, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (45, 'Observatorium', '-', 500, 2000, 800, 5000, 2500, '', '', 18000, 500, 0, '', 'blue', 'O', 'white', 0, 'gebaeude-r%R%/observe-0.png', 0, 0, 0, 'obs', 15, 0, 2, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (46, 'Schiffswerft', '*s�g* *h�mmer*', 1000, 500, 500, 1000, 0, '1:10,47:0', '', 43200, 100, 0, 'werft', '#5f5f5f', 'WS', '#000000', 0, 'hafen/schiffswerft-0.png', 0, 512, 0, 'schiffswerft', 15, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (47, 'Hafen', 'hier kann man seine Schiffe zu Flotten machen und ausserdem kann man hier (wenn man stege gebaut hat) auch soldaten einladen', 500, 1000, 500, 1000, 0, '1:10', '', 43200, 120, 0, 'harbor', '#0f0f0f', 'HR', '#ffffff', 0, 'hafen/hafen-%NWSE%-0.png', 0, 512, 0, 'harbor-%NWSE%', 14, 0, 1, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (48, 'Steg', '', 100, 100, 0, 50, 0, '47:1', '', 3600, 30, 0, 'steg', '', '', '', 0, 'hafen/steg-%NWSE%-0.png', 0, 0, 0, 'steg-%NWSE%', 16, 0, 1, 6, 1, 1, 1, '', '', '', '47,48', '', 0, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (49, 'Seewall', '', 0, 500, 50, 250, 0, '47:2,48:1', '', 3600, 120, 0, 'seewall', '', '', '', 0, 'hafen/mole-%NWSE%-0.png', 0, 0, 0, 'mole-%NWSE%', 17, 0, 1, 6, 1, 1, 1, '', '', '', '73,5,49,50', '', 0, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (50, 'Seagate', '', 50, 500, 50, 500, 0, '49:1,47:2,48:1', '', 3600, 90, 0, 'seegate', '', '', '', 240, 'hafen/seagate-zu-%NWSE%-0.png', 0, 0, 0, 'seagate_%NWSE%', 18, 0, 1, 6, 1, 1, 1, '', '', '', '5,49,50', '', 0, 56, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (51, 'Taverne', 'hier kann man sihc mit anderen Spieler unterhalten', 50, 100, 50, 10, 0, '', '', 11520, 150, 0, 'taverne', 'white', 'T', 'black', 0, 'gebaeude-r%R%/taverne-0.png', 0, 512, 0, 'tav', 41, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (52, 'Galgen', 'damit man auch nach dem Regieren seinen Spa� haben kann.', 25, 10, 5, 0, 0, '1:0', '', 1800, 10, 0, '', 'black', 'G', 'white', 60, 'gebaeude/galgen.png', 0, 0, 0, 'glg', 40, 0, 0, 0, 1, 1, 1, '', '', '1,3', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (53, 'Labor', '', 500, 3000, 1500, 5000, 5000, '45:0', '', 18000, 500, 0, '', 'blue', 'L', 'white', 0, 'gebaeude-r%R%/labor-%L%.png', 0, 0, 0, 'lab', 16, 41, 2, 0, 1, 1, 1, '', '', '1,3', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (54, 'Fabrik', '', 0, 5000, 1000, 5000, 5000, '1:0', '', 18000, 500, 0, '', 'blue', 'F', 'white', 0, 'gebaeude-r%R%/fabrik-%L%.png', 0, 0, 0, 'fab', 10, 0, 2, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (55, 'Weizen', 'Bodenschatz', 0, 0, 10000, 0, 0, '', '', 3600, 9999, 0, '', 'yellow', '#', 'white', 120, 'mineral/corn.png', 1, 0, 0, 'bs1', 25, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (56, 'Kristalle', 'Bodenschatz', 0, 0, 0, 0, 10000, '', '', 3600, 9999, 0, '', 'yellow', '#', 'white', 120, 'mineral/diamant.png', 1, 0, 0, 'bs1', 25, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (57, 'Erz', 'Bodenschatz', 0, 0, 0, 10000, 0, '', '', 3600, 9999, 0, '', 'yellow', '#', 'white', 120, 'mineral/erz.png', 1, 0, 0, 'bs1', 25, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (58, 'Fische', 'Bodenschatz', 0, 0, 10000, 0, 0, '', '', 3600, 9999, 0, '', 'yellow', '#', 'white', 120, 'mineral/fish.png', 1, 0, 0, 'bs1', 25, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 63, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (59, 'Fr�chte', 'Bodenschatz', 0, 0, 10000, 0, 0, '', '', 3600, 9999, 0, '', 'yellow', '#', 'white', 120, 'mineral/fruit.png', 1, 0, 0, 'bs1', 25, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (60, 'EichenHolz', 'Bodenschatz', 10000, 0, 0, 0, 0, '', '', 3600, 9999, 0, '', 'yellow', '#', 'white', 120, 'mineral/holz.png', 1, 0, 0, 'bs1', 25, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (61, 'Marmor', 'Bodenschatz', 0, 10000, 0, 0, 0, '', '', 3600, 9999, 0, '', 'yellow', '#', 'white', 120, 'mineral/marmor.png', 1, 0, 0, 'bs1', 25, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (62, 'Granit', 'Bodenschatz', 0, 10000, 0, 0, 0, '', '', 3600, 9999, 0, '', 'yellow', '#', 'white', 120, 'mineral/stone.png', 1, 0, 0, 'bs1', 25, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (63, 'Wild', 'Bodenschatz', 0, 0, 10000, 0, 0, '', '', 3600, 9999, 0, '', 'yellow', '#', 'white', 120, 'mineral/fell.png', 1, 0, 0, 'bs1', 25, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (64, 'Spielhalle', 'Spielhalle', 500, 500, 0, 100, 0, '', '', 3600, 50, 0, 'spielhalle', 'red', 'H', 'blue', 0, 'gebaeude/spielhalle.png', 0, 512, 0, '', 50, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (65, 'Platz', '', 5, 5, 0, 0, 0, '1:0', '', 3000, 10, 0, '', '#949454', '#', 'black', 60, 'platz/platz-%NWSE%.png', 0, 0, 0, '', 12, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 7, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (66, 'Leuchtturm', '*heim-leucht*', 0, 1000, 0, 200, 200, '47:0', '', 7200, 100, 0, '', '', '', '', 0, 'gebaeude/leuchtturm.gif', 0, 0, 0, '', 99, 0, 1, 1, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (67, 'Monolith', 'edit me', 0, 0, 0, 0, 0, '', '', 0, 9999, 0, 'schild', '', '', '', 0, 'gebaeude/monolith.png', 1, 0, 0, '', 0, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (68, 'Kralle', 'edit me', 0, 0, 0, 0, 0, '', '', 0, 9999, 0, '', '', '', '', 0, 'gebaeude/kralle_e.png', 1, 0, 0, '', 0, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (69, 'Kralle', 'edit me', 0, 0, 0, 0, 0, '', '', 0, 9999, 0, '', '', '', '', 0, 'gebaeude/kralle_w.png', 1, 0, 0, '', 0, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (70, 'Kralle', 'edit me', 0, 0, 0, 0, 0, '', '', 0, 9999, 0, '', '', '', '', 0, 'gebaeude/kralle_s.png', 1, 0, 0, '', 0, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (71, 'Kralle', 'edit me', 0, 0, 0, 0, 0, '', '', 0, 9999, 0, '', '', '', '', 0, 'gebaeude/kralle_n.png', 1, 0, 0, '', 0, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 1, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (72, 'Kornfeld', '', 50, 0, 250, 0, 0, '9>10', '', 3600, 5, 0, '', 'yellow', '#', 'black', 60, 'landschaft/cornfield.png', 0, 0, 0, '', 50, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 7, 0, 8, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (73, 'Verteidigungsturm', 'turm', 0, 1000, 0, 500, 0, '11>10', '', 86400, 120, 0, '', '', '', '', 0, 'gebaeude/turm/tower-%NWSE%-%L%.png', 0, 752, 1500, '', 0, 0, 0, 0, 1, 1, 1, '', '73,5,17', '', '', '', 0, 0, 0, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (74, 'Ameisen-H�gel', '', 1000, 1000, 0, 0, 0, '', '', 3600, 100, 0, '', '', '', '', 0, 'landschaft/bughole.gif', 1, 0, 0, '', 0, 0, 0, 0, 1, 1, 1, '', '', '', '', '', 0, 0, 1, 0, 1, 0, 0);
INSERT INTO `buildingtype` VALUES (75, 'Muehle', 'bis jetzt nur reine Dekoration', 100, 100, 0, 0, 0, '1:0', '', 14400, 100, 0, '', 'red', 'A', 'yellow', 0, 'gebaeude-r1/muehle.gif', 0, 0, 0, '', 5, 0, 0, 0, 1, 1, 1, '', '', '3', '', '', 1, 0, 1, 0, 1, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `casinohighscore`
-- 

CREATE TABLE `casinohighscore` (
  `user` int(10) unsigned NOT NULL default '0',
  `game` int(10) unsigned NOT NULL default '0',
  `score` int(11) NOT NULL default '0',
  `timesplayed` int(10) unsigned NOT NULL default '0',
  UNIQUE KEY `user` (`user`,`game`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `casinohighscore`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `construction`
-- 

CREATE TABLE `construction` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `x` int(10) NOT NULL default '0',
  `y` int(10) NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `priority` int(10) unsigned NOT NULL default '0',
  `param` char(4) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `x` (`x`),
  KEY `y` (`y`),
  KEY `user` (`user`),
  KEY `type` (`type`)
) TYPE=MyISAM AUTO_INCREMENT=41 ;

-- 
-- Dumping data for table `construction`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `fight`
-- 

CREATE TABLE `fight` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `attacker` int(10) unsigned NOT NULL default '0',
  `defender` int(10) unsigned NOT NULL default '0',
  `start` int(10) unsigned NOT NULL default '0',
  `fightlog` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `attacker` (`attacker`,`defender`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `fight`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `fightlog`
-- 

CREATE TABLE `fightlog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fight` int(10) unsigned NOT NULL default '0',
  `startunits1` text NOT NULL,
  `startunits2` text NOT NULL,
  `starttransport1` text NOT NULL,
  `starttransport2` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `fightlog`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `fof_guild`
-- 

CREATE TABLE `fof_guild` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `master` int(10) unsigned NOT NULL default '0',
  `other` int(10) unsigned NOT NULL default '0',
  `class` tinyint(3) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `fof_guild`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `fof_user`
-- 

CREATE TABLE `fof_user` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `master` int(10) unsigned NOT NULL default '0',
  `other` int(10) unsigned NOT NULL default '0',
  `class` tinyint(3) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `fof_user`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `global`
-- 

CREATE TABLE `global` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM AUTO_INCREMENT=77 ;

-- 
-- Dumping data for table `global`
-- 

INSERT INTO `global` VALUES (1, 'lasttick', '1147643764');
INSERT INTO `global` VALUES (2, 'crontime', '0.00385367334525');
INSERT INTO `global` VALUES (3, 'building_hq', '1');
INSERT INTO `global` VALUES (4, 'building_lumber', '13');
INSERT INTO `global` VALUES (5, 'building_stone', '14');
INSERT INTO `global` VALUES (6, 'building_food', '9');
INSERT INTO `global` VALUES (7, 'building_metal', '15');
INSERT INTO `global` VALUES (8, 'prod_slots', '10');
INSERT INTO `global` VALUES (9, 'prod_faktor_slotless', '0.03');
INSERT INTO `global` VALUES (10, 'prod_faktor', '0.65');
INSERT INTO `global` VALUES (11, 'store', '500');
INSERT INTO `global` VALUES (12, 'building_house', '6');
INSERT INTO `global` VALUES (13, 'building_store', '7');
INSERT INTO `global` VALUES (14, 'pop_slots_hq', '12');
INSERT INTO `global` VALUES (15, 'pop_slots_house', '10');
INSERT INTO `global` VALUES (16, 'building_gate', '17');
INSERT INTO `global` VALUES (17, 'building_runes', '2');
INSERT INTO `global` VALUES (18, 'fc_prod_runes', '2.6');
INSERT INTO `global` VALUES (19, 'fc_prod_metal', '0');
INSERT INTO `global` VALUES (20, 'fc_prod_food', '0');
INSERT INTO `global` VALUES (21, 'fc_prod_lumber', '0');
INSERT INTO `global` VALUES (22, 'fc_prod_stone', '0');
INSERT INTO `global` VALUES (23, 'mc_prod_runes', '1.9');
INSERT INTO `global` VALUES (27, 'ticks', '5529');
INSERT INTO `global` VALUES (24, 'stats_nexttime', '1147647700');
INSERT INTO `global` VALUES (25, 'tech_architecture', '34');
INSERT INTO `global` VALUES (26, 'terrain_cornfield', '8');
INSERT INTO `global` VALUES (28, 'building_bridge', '18');
INSERT INTO `global` VALUES (29, 'lc_prod_runes', '1.2');
INSERT INTO `global` VALUES (30, 'sc_prod_runes', '1.2');
INSERT INTO `global` VALUES (46, 'lastpngmapcreep', '1146256317');
INSERT INTO `global` VALUES (45, 'lastpngmap-guild', '1146256317');
INSERT INTO `global` VALUES (44, 'lastpngmap', '1146256317');
INSERT INTO `global` VALUES (34, 'minimap_left', '449');
INSERT INTO `global` VALUES (35, 'minimap_right', '752');
INSERT INTO `global` VALUES (36, 'minimap_top', '-498');
INSERT INTO `global` VALUES (37, 'minimap_bottom', '-8');
INSERT INTO `global` VALUES (43, 'weather', '5');
INSERT INTO `global` VALUES (41, 'lastminitick', '1147643764');
INSERT INTO `global` VALUES (47, 'testminimap', '1121257976');
INSERT INTO `global` VALUES (48, 'unitresratio', '100');
INSERT INTO `global` VALUES (49, 'kArmyRecalcBlockedRoute_Timeout', '300');
INSERT INTO `global` VALUES (50, 'kArmyAutoAttackRangeMonster_Timeout', '300');
INSERT INTO `global` VALUES (51, 'kArmy_BigArmyGoSlowLimit', '10000');
INSERT INTO `global` VALUES (52, 'kArmy_BigArmyGoSlowFactorPer1000Units', '1.003');
INSERT INTO `global` VALUES (53, 'kBaracksDestMaxDist', '30');
INSERT INTO `global` VALUES (54, 'kPortalTaxUnitNum', '1000');
INSERT INTO `global` VALUES (55, 'kArmyMinIdle_Split', '180');
INSERT INTO `global` VALUES (56, 'kArmyMinIdle_Merge', '180');
INSERT INTO `global` VALUES (57, 'kTerraFormer_SicherheitsAbstand', '10');
INSERT INTO `global` VALUES (58, 'kArmy_AW_for_one_exp', '800.0');
INSERT INTO `global` VALUES (59, 'kArmy_ExpBonus', '10');
INSERT INTO `global` VALUES (60, 'liveupdate', '0');
INSERT INTO `global` VALUES (61, 'randomspawnmonsters', '11,12,13,14,18,19,31,20,21,23,32');
INSERT INTO `global` VALUES (62, 'wb_max_gp', '60000');
INSERT INTO `global` VALUES (63, 'gp_pts_ratio', '1000');
INSERT INTO `global` VALUES (64, 'wb_paybacklimit', '80000');
INSERT INTO `global` VALUES (65, 'wb_payback_perc', '5');
INSERT INTO `global` VALUES (66, 'prod_slots_lumber', '10');
INSERT INTO `global` VALUES (67, 'prod_slots_stone', '10');
INSERT INTO `global` VALUES (68, 'prod_slots_food', '10');
INSERT INTO `global` VALUES (69, 'prod_slots_metal', '10');
INSERT INTO `global` VALUES (70, 'prod_slots_runes', '30');
INSERT INTO `global` VALUES (71, 'hq_max_x', '1200');
INSERT INTO `global` VALUES (72, 'hq_min_x', '-800');
INSERT INTO `global` VALUES (73, 'hq_max_y', '2000');
INSERT INTO `global` VALUES (74, 'hq_min_y', '-800');
INSERT INTO `global` VALUES (75, 'typecache_version_adder', '223');
INSERT INTO `global` VALUES (76, 'stats_trade_sum', '0');

-- --------------------------------------------------------

-- 
-- Table structure for table `guild`
-- 

CREATE TABLE `guild` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `founder` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `color` varchar(32) NOT NULL default 'blue',
  `time` int(10) unsigned NOT NULL default '0',
  `lumber` int(11) NOT NULL default '0',
  `stone` int(11) NOT NULL default '0',
  `food` int(11) NOT NULL default '0',
  `metal` int(11) NOT NULL default '0',
  `runes` int(11) NOT NULL default '0',
  `max_lumber` int(10) unsigned NOT NULL default '0',
  `max_stone` int(10) unsigned NOT NULL default '0',
  `max_food` int(10) unsigned NOT NULL default '0',
  `max_metal` int(10) unsigned NOT NULL default '0',
  `max_runes` int(10) unsigned NOT NULL default '0',
  `profile` text NOT NULL,
  `gfx` varchar(128) NOT NULL default '',
  `internprofile` text NOT NULL,
  `message` text NOT NULL,
  `stdstatus` int(10) unsigned NOT NULL default '0',
  `forumurl` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=86 ;

-- 
-- Dumping data for table `guild`
-- 

INSERT INTO `guild` VALUES (8, 249, 'Weltbank', 'blue', 1108292994, 576368, 576441, 582178, 582186, 640000, 582000, 582000, 582000, 582000, 640000, 'Die Weltbank ist eine zur Unterst�tzung kleiner Spieler gedachte Gilde. Jeder wenig Punkte und ohne Gilde ist kommt hier rein und kann sich dann bedienen, finanziert wird die Gilde �ber kurz oder lang durch Einnahmen aus �ffentlichen Portalen, und durch das ''Admin'' account', '', 'Bedient euch an den Rohstoffen aber denkt dran auch andere wollen was. Man wird automatisch aus der Gilde geworfen, wenn man mehr als 60k Punkte hat, wobei die negativen Punkte dabei nicht in die Rechnung miteinbezogen werden.', 'Bedient euch an den Rohstoffen aber denkt dran auch andere wollen was. Man wird automatisch aus der Gilde geworfen, wenn man mehr als 60k Punkte hat, wobei die negativen Punkte dabei nicht in die Rechnung miteinbezogen werden.', 15, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `guild_forum`
-- 

CREATE TABLE `guild_forum` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `guild` int(10) unsigned NOT NULL default '0',
  `date` int(10) unsigned NOT NULL default '0',
  `head` varchar(128) NOT NULL default '',
  `content` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user` (`user`,`guild`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `guild_forum`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `guild_forum_comment`
-- 

CREATE TABLE `guild_forum_comment` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `article` int(10) unsigned NOT NULL default '0',
  `ref` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `date` int(10) unsigned NOT NULL default '0',
  `head` varchar(128) NOT NULL default '',
  `comment` text NOT NULL,
  `guild` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `article` (`article`,`ref`,`user`,`guild`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `guild_forum_comment`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `guild_forum_read`
-- 

CREATE TABLE `guild_forum_read` (
  `user` int(11) unsigned NOT NULL default '0',
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` tinyint(4) NOT NULL default '0',
  `ref` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `user` (`user`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `guild_forum_read`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `guild_msg`
-- 

CREATE TABLE `guild_msg` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `guild` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `text` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `guild` (`guild`,`user`,`time`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `guild_msg`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `guild_pref`
-- 

CREATE TABLE `guild_pref` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `guild` int(10) unsigned NOT NULL default '0',
  `var` varchar(128) NOT NULL default '',
  `value` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `guild` (`guild`,`var`)
) TYPE=MyISAM AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `guild_pref`
-- 

INSERT INTO `guild_pref` VALUES (1, 8, 'limit_249', '0');
INSERT INTO `guild_pref` VALUES (2, 8, 'stdlimit', '0');
INSERT INTO `guild_pref` VALUES (3, 8, 'takealllimit', '0');

-- --------------------------------------------------------

-- 
-- Table structure for table `guild_request`
-- 

CREATE TABLE `guild_request` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `guild` int(10) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `comment` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user` (`user`),
  KEY `guild` (`guild`)
) TYPE=MyISAM AUTO_INCREMENT=781 ;

-- 
-- Dumping data for table `guild_request`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `guild_right`
-- 

CREATE TABLE `guild_right` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `right` int(10) unsigned NOT NULL default '0',
  `desc` varchar(128) NOT NULL default '',
  `gfx` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `right` (`right`)
) TYPE=MyISAM AUTO_INCREMENT=9 ;

-- 
-- Dumping data for table `guild_right`
-- 

INSERT INTO `guild_right` VALUES (1, 2, 'Das Mitglied kann die Rechteliste sehen', 'icon/guild-view.png');
INSERT INTO `guild_right` VALUES (2, 3, 'Das Mitglied kann Rohstoffe aus dem Gildelager nehmen', 'icon/guild-out.png');
INSERT INTO `guild_right` VALUES (3, 5, 'Das Mitglied kann Rohstoffe in die Gildelager einzahlen', 'icon/guild-in.png');
INSERT INTO `guild_right` VALUES (4, 7, 'Das Mitglied ist Gilde-Komandeur', 'icon/guild-gc.png');
INSERT INTO `guild_right` VALUES (5, 23, 'Das Mitglied ist Gildeadmin', 'icon/guild-admin.png');
INSERT INTO `guild_right` VALUES (6, 11, 'Das Mitglied darf Gildenachrichten verschicken', 'icon/guild-send.png');
INSERT INTO `guild_right` VALUES (7, 13, 'Das Mitglied darf MessageOfTheDay Texte setzen', 'icon/guild-message.png');
INSERT INTO `guild_right` VALUES (8, 27, 'Mitglied ist Schatzmeister', 'icon/guild-schatz.png');

-- --------------------------------------------------------

-- 
-- Table structure for table `guildlog`
-- 

CREATE TABLE `guildlog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `time` int(10) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `user1` int(10) unsigned NOT NULL default '0',
  `user2` int(10) unsigned NOT NULL default '0',
  `guild1` int(10) unsigned NOT NULL default '0',
  `guild2` int(10) unsigned NOT NULL default '0',
  `trigger` varchar(64) NOT NULL default '',
  `what` varchar(255) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `time` (`time`),
  KEY `id1` (`user1`),
  KEY `id2` (`user2`),
  KEY `count` (`count`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `guildlog`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `hellhole`
-- 

CREATE TABLE `hellhole` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `type2` int(10) unsigned NOT NULL default '0',
  `ai_type` int(10) unsigned NOT NULL default '0',
  `ai_data` varchar(255) NOT NULL default '',
  `lastupgrade` int(11) NOT NULL default '0',
  `level` tinyint(4) NOT NULL default '0',
  `maxlevel` int(10) unsigned NOT NULL default '99',
  `armysize` int(10) unsigned NOT NULL default '0',
  `armysize2` int(10) unsigned NOT NULL default '1',
  `num` int(10) unsigned NOT NULL default '0',
  `spawndelay` int(10) unsigned NOT NULL default '0',
  `spawntime` int(10) unsigned NOT NULL default '0',
  `totalspawns` int(10) unsigned NOT NULL default '0',
  `radius` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `x` (`x`),
  KEY `y` (`y`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `hellhole`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `item`
-- 

CREATE TABLE `item` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` int(10) unsigned NOT NULL default '0',
  `army` int(10) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `quest` int(10) unsigned NOT NULL default '0',
  `building` int(10) unsigned NOT NULL default '0',
  `spell` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `amount` int(10) unsigned NOT NULL default '1',
  `param` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `army` (`army`),
  KEY `pos` (`x`,`y`),
  KEY `y` (`y`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `item`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `itemtrade`
-- 

CREATE TABLE `itemtrade` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `building` int(10) unsigned NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  `amount` int(15) NOT NULL default '0',
  `offer` varchar(255) NOT NULL default '',
  `price` varchar(255) NOT NULL default '',
  `starttime` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `building` (`building`)
) TYPE=MyISAM AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `itemtrade`
-- 

INSERT INTO `itemtrade` VALUES (1, 0, 57174, 0, -1, '31:1', '30:2', 1111610155);
INSERT INTO `itemtrade` VALUES (2, 0, 57174, 0, -1, '33:1', '30:1,31:1', 1111610188);
INSERT INTO `itemtrade` VALUES (3, 0, 57174, 0, -1, '32:1', '30:2', 1111610231);
INSERT INTO `itemtrade` VALUES (4, 0, 57174, 0, -1, '30:2', '31:1,33:1', 1111610275);

-- --------------------------------------------------------

-- 
-- Table structure for table `itemtype`
-- 

CREATE TABLE `itemtype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `gfx` varchar(128) NOT NULL default '',
  `descr` text NOT NULL,
  `flags` int(10) unsigned NOT NULL default '0',
  `weight` float NOT NULL default '0',
  `maxamount` float NOT NULL default '0',
  `gammeltype` int(10) unsigned NOT NULL default '0',
  `gammeltime` int(10) unsigned NOT NULL default '0',
  `buildings` varchar(255) NOT NULL default '',
  `cost_lumber` int(11) NOT NULL default '0',
  `cost_stone` int(11) NOT NULL default '0',
  `cost_food` int(11) NOT NULL default '0',
  `cost_metal` int(11) NOT NULL default '0',
  `cost_runes` int(11) NOT NULL default '0',
  `value` float NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=104 ;

-- 
-- Dumping data for table `itemtype`
-- 

INSERT INTO `itemtype` VALUES (2, 'Mondstein', 'item/moonstone.png', 'sobald das Mondlicht auf diesen matten l�nglichen Stein mit seiner seltsamen Oberfl�che  f�llt, erscheit das Innere auf einmal in solch einer Tiefe, da� man kaum vermag seinen Blick abzuwenden und langsam im Dunkel verschwindet...', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (3, 'Jadefalke', 'item/falke.png', 'dieses wei� schimmernde Abbild eines Falken sieht ganz und gar nicht wie eine Statue aus, ja vielmehr wie das Werk eines m�chtigen Bannzaubers', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (8, 'Dorfbewohner', 'item/man.png', 'wurde entf�hrt', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (9, 'Dorfbewohnerin', 'item/woman.png', 'wurde entf�hrt', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (10, 'Kind', 'item/kid.png', 'wurde entf�hrt', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (11, 'Rubin', 'item/edelstein_rot.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (12, 'Smaragd', 'item/edelstein_gruen.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (13, 'Saphir', 'item/edelstein_blau.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (14, 'Amethyst', 'item/edelstein_lila.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (15, 'Bernstein', 'item/edelstein_gelb.png', 'mit m�cke', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (19, 'Onyx', 'item/edelstein_schwarz.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (18, 'Diamant', 'item/edelstein_weiss.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (20, 'Topas', 'item/edelstein_gelb.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (21, 'Opal', 'item/edelstein_blau.png', 'Edelstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (22, 'Erdm�nnchen', 'item/erdm.png', '-', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (23, 'Blaurohr', 'item/pilz_blau.png', 'dieser seltene Pilz kommt nur in ganz abgelegenen bergigen Gegenden vor', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (24, 'Fliegenpilz', 'item/pilz_rot.png', 'ein kleiner rotschimmernder wei�gefleckter Pilz', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (25, 'blanko Urkunde', 'item/urkunde.png', 'ein vergilbtes St�ck Papier, das irgend etwas ganz ganz wichtiges bekundet', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (26, 'Retter Urkunde', 'item/urkunde.png', 'Retter von Paindorf', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (28, 'Labyrinth Urkunde', 'item/urkunde.png', 'Meister der Entwirrung', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (29, 'Cheating Urkunde', 'item/urkunde.png', 'Ist beim Cheaten erwischt worden :P ... ok musste sich gegen einen bug verteigigen .. aber trotzdem', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (30, 'blauer Portalstein', 'item/portalstein_blau.png', 'teleportiert die Armee zum n�chsten �ffentlichen Portal', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (31, 'gr�ner Portalstein', 'item/portalstein_gruen.png', 'teleportiert die Armee zum n�chsten eigenen Lager', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (32, 'schwarzer Portalstein', 'item/portalstein_schwarz.png', 'teleportiert die Armee zur n�chsten eigenen Kaserne', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (33, 'roter Portalstein', 'item/portalstein_rot.png', 'teleportiert die Armee zur n�chsten anderen eigenen Armee', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (34, 'Bl�melein in rot', 'item/blume-rot.png', 'fleischfressend ;)', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (35, 'bl�melein blau', 'item/blume-blau.png', 'die ist brav', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (36, 'Pilz', 'item/pilz_orange.png', 'Pilz', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (37, 'Hypnok�rbis', 'item/kuerbis.png', 'Portal in die Traumwelt', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (38, 'Krone', 'item/krone.png', 'Wer sich heimlich ein K�nig ausrufen will ...', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (39, 'Kaktus', 'item/kaktus.png', 'Vorsicht piekst', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (40, 'Sch�del', 'item/sch�del.png', 'Mit Smaragden als Augen', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (41, 'Blaues Ding', 'item/blauesding.png', 'Blaues Ding', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (42, 'GeburtstagsTorte', 'item/torte.gif', 'Alles Gute zum Geburtstag !!!!', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (43, 'Drachenei', 'item/drachenei.png', 'das kleine seltene Ei eines Drachens', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (44, 'Holz', 'res_lumber.gif', '', 104, 1, 0, 0, 0, '', 1, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (45, 'Stein', 'res_stone.gif', '', 104, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (46, 'Nahrung', 'res_food.gif', '', 104, 1, 0, 0, 0, '', 0, 0, 1, 0, 0, 1);
INSERT INTO `itemtype` VALUES (47, 'Metall', 'res_metal.gif', '', 104, 1, 0, 0, 0, '', 0, 0, 0, 1, 0, 1);
INSERT INTO `itemtype` VALUES (48, 'Runen', 'res_runes.gif', '', 104, 1, 0, 0, 0, '', 0, 0, 0, 0, 1, 1);
INSERT INTO `itemtype` VALUES (49, 'Zigarren', 'waren/cigar.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (50, 'Wolle', 'waren/wolle.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (51, 'Weintrauben', 'waren/weintrauben.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (52, 'Wein', 'waren/wein.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (53, 'Stoff', 'waren/stoff.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (54, 'Erz', 'waren/erz.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (55, 'Schmuck', 'waren/schmuck.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (56, 'Schafe', 'waren/schaf.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (57, 'Leder', 'waren/leder.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (58, 'Getreide', 'waren/korn.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (59, 'Kleidung', 'waren/kleidung.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (60, 'Kartoffeln', 'waren/kartoffeln.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (61, 'Hopfen', 'waren/hopfen.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (62, 'Gold', 'waren/gold.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (63, 'Fisch', 'waren/fisch.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (64, 'Fell', 'waren/fell.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (65, 'Brot', 'waren/brot.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (66, 'Baumwolle', 'waren/baumwolle.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (67, 'F�sser', 'waren/fass.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (68, 'Bier', 'waren/fass.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (69, 'Whiskey', 'waren/fass.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (70, 'Grog', 'waren/fass.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (71, 'Tee', 'waren/tea.png', '', 8, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1);
INSERT INTO `itemtype` VALUES (72, 'Osterei', 'item/osterei1.png', 'Osterei : erzeugt H�llenhund, roter Portalstein, Flucht vor Monsterkampf', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (73, 'Osterei', 'item/osterei2.png', 'Osterei : erzeugt Wald', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (74, 'Osterei', 'item/osterei3.png', 'Osterei : erzeugt Schutt', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (75, 'Osterei', 'item/osterei4.png', 'Osterei : erzeugt Felder', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (76, 'Osterei', 'item/osterei5.png', 'Osterei : erzeugt H�hnchen, gr�ner Portalstein', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (77, 'Osterei', 'item/osterei6.png', 'Osterei : teleportiert Ramme zur Armee', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (100, 'Faules Ei', 'item/osterei_faul.png', 'sieht vergammelt aus, und riecht unangenehm', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (101, 'Amboss', 'item/acme.png', 'der legend�re ACME Amboss', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (102, '7 Meilen Stiefel', 'item/stiefel.png', 'damit rennt man doppelt so schnell', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
INSERT INTO `itemtype` VALUES (103, 'Spam', 'item/spam.png', 'reduziert de Nahrungsverbrauch einer Armee, wenn er aktiviert ist', 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `log`
-- 

CREATE TABLE `log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `time` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `text` text NOT NULL,
  `url` varchar(255) default NULL,
  `frame` varchar(64) default NULL,
  `type` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `log`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `map`
-- 

CREATE TABLE `map` (
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `id` int(11) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`),
  KEY `x` (`x`),
  KEY `y` (`y`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `map`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `mapmark`
-- 

CREATE TABLE `mapmark` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `mapmark`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `maptemplate`
-- 

CREATE TABLE `maptemplate` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `cx` int(10) unsigned NOT NULL default '0',
  `cy` int(10) unsigned NOT NULL default '0',
  `name` varchar(128) NOT NULL default '',
  `terrain` text NOT NULL,
  `building` text NOT NULL,
  `army` text NOT NULL,
  `item` text NOT NULL,
  `hellhole` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=18 ;

-- 
-- Dumping data for table `maptemplate`
-- 

INSERT INTO `maptemplate` VALUES (11, 11, 11, 'zentauren_labyrinth', '', '0|0|5|10|138|0|#0|1|5|255|9120|0|#0|2|5|255|9120|0|#0|3|5|255|9120|0|#0|4|5|255|9120|0|#0|5|5|255|9120|0|#0|6|5|255|9120|0|#0|7|5|255|9120|0|#0|8|5|255|9120|0|#0|9|5|255|9120|0|#0|10|5|10|138|0|#1|0|5|255|9120|0|#1|1|3|10|6|0|#1|2|17|10|104|0|#1|3|3|255|381|0|#1|4|3|10|6|0|#1|5|3|255|381|0|#1|6|17|10|104|0|#1|7|3|255|381|0|#1|8|5|10|138|0|#1|9|3|255|381|0|#1|10|5|255|9120|0|#2|0|5|10|138|0|#2|1|17|10|104|0|#2|2|5|10|138|0|#2|3|5|10|138|0|#2|4|5|10|138|0|#2|5|5|10|138|0|#2|6|5|10|138|0|#2|7|3|255|381|0|#2|8|5|10|138|0|#2|9|17|10|104|0|#2|10|5|255|9120|0|#3|0|5|255|9120|0|#3|1|3|10|6|0|#3|2|5|255|9120|0|#3|3|3|255|381|0|#3|4|3|10|6|0|#3|5|3|10|6|0|#3|6|3|10|6|0|#3|7|3|255|381|0|#3|8|5|10|138|0|#3|9|3|255|381|0|#3|10|5|255|9120|0|#4|0|5|255|9120|0|#4|1|3|10|6|0|#4|2|5|255|9120|0|#4|3|3|10|6|0|#4|4|5|10|138|0|#4|5|17|10|104|0|#4|6|5|10|138|0|#4|7|17|10|104|0|#4|8|5|10|138|0|#4|9|3|255|381|0|#4|10|5|255|9120|0|#5|0|5|255|9120|0|#5|1|3|10|6|0|#5|2|5|255|9120|0|#5|3|3|10|6|0|#5|4|5|10|138|0|#5|5|3|255|381|0|#5|6|5|255|18119|0|#5|7|3|10|6|0|#5|8|17|10|104|0|#5|9|3|10|6|0|#5|10|5|255|9120|0|#6|0|5|255|9120|0|#6|1|3|10|6|0|#6|2|5|255|9120|0|#6|3|3|10|6|0|#6|4|5|10|138|0|#6|5|5|10|138|0|#6|6|5|10|138|0|#6|7|17|10|104|0|#6|8|5|10|138|0|#6|9|3|10|6|0|#6|10|5|255|9120|0|#7|0|5|255|9120|0|#7|1|3|10|6|0|#7|2|5|255|9120|0|#7|3|3|10|6|0|#7|4|3|10|6|0|#7|5|3|10|6|0|#7|6|3|255|381|0|#7|7|3|255|381|0|#7|8|5|255|9120|0|#7|9|3|10|6|0|#7|10|5|255|9120|0|#8|0|5|255|9120|0|#8|1|3|10|6|0|#8|2|5|10|138|0|#8|3|5|255|9120|0|#8|4|5|255|9120|0|#8|5|17|10|104|0|#8|6|5|10|138|0|#8|7|3|255|381|0|#8|8|3|10|6|0|#8|9|3|10|6|0|#8|10|5|255|9120|0|#9|0|5|255|9120|0|#9|1|3|10|6|0|#9|2|3|10|6|0|#9|3|3|10|6|0|#9|4|3|10|6|0|#9|5|3|10|6|0|#9|6|5|10|138|0|#9|7|5|255|9120|0|#9|8|5|255|9120|0|#9|9|5|255|9120|0|#9|10|5|10|138|0|#10|0|5|10|138|0|#10|1|5|255|9120|0|#10|2|5|255|9120|0|#10|3|5|255|9120|0|#10|4|5|255|9120|0|#10|5|5|255|9120|0|#10|6|5|10|138|0|', '5|5|15|9999|Schatzkiste|0#7|6|30|5000|Zentaur|1028', '5|8|48|0#5|8|46|0#5|8|45|0#5|8|44|0#5|8|47|0#9|5|46|99999', '');
INSERT INTO `maptemplate` VALUES (10, 11, 11, 'OrkDorf', '1|4|11#1|6|11#4|1|11#4|9|11#6|1|11#6|9|11#9|4|11#9|6|11', '0|5|3|9|6|0|#1|5|3|9|6|0|#2|2|5|9|137|0|#2|3|5|9|137|0|#2|4|5|9|137|0|#2|5|17|9|103|0|#2|6|5|2|124|0|#2|7|5|2|124|0|#2|8|5|9|137|0|#3|2|5|9|137|0|#3|3|6|9|114|0|#3|5|3|9|6|0|#3|7|6|9|114|0|#3|8|5|9|137|0|#4|2|5|9|137|0|#4|4|3|9|6|0|#4|5|3|9|6|0|#4|6|3|9|6|0|#4|8|5|9|137|0|#5|0|3|9|6|0|#5|1|3|9|6|0|#5|2|17|9|103|0|#5|3|3|9|6|0|#5|4|3|9|6|0|#5|5|21|10|345|0|#5|6|3|9|6|0|#5|7|3|9|6|0|#5|8|17|9|103|0|#5|9|3|0|5|0|#5|10|3|0|5|0|#6|2|5|9|137|0|#6|4|3|9|6|0|#6|5|3|9|6|0|#6|6|3|9|6|0|#6|8|5|9|137|0|#7|2|5|9|137|0|#7|3|20|9|69|0|#7|5|3|9|6|0|#7|7|19|10|12|0|(i40) EINDRINGLINGE WERDEN GEFRESSEN (i40)#7|8|5|9|137|0|#8|2|5|9|137|0|#8|3|5|9|137|0|#8|4|5|9|137|0|#8|5|17|9|103|0|#8|6|5|9|137|0|#8|7|5|9|137|0|#8|8|5|9|137|0|#9|5|3|9|6|0|#10|5|3|9|6|0|', '4|3|21|9999|H�uptling|2052', '0|0|45|0#0|0|45|0#0|0|48|0#0|0|47|0#0|0|46|0#0|0|44|0#0|0|47|0#0|0|48|0#0|0|46|0#0|0|44|0#3|4|45|9999#3|6|45|9999#4|5|48|0#4|5|47|15000#4|5|46|0#4|5|45|0#4|5|44|0#6|3|47|9999#6|7|44|9999#7|4|46|9999#7|6|44|9999#10|0|48|0#10|0|47|0#10|0|46|0#10|0|45|0#10|0|44|0', '5|5|21|10|1|3000|1|2|3600|5');
INSERT INTO `maptemplate` VALUES (16, 11, 11, 'AmeisenH�gel', '1|0|11#7|0|11#3|0|10#4|0|11#6|0|10#7|1|11#3|1|8#2|1|8#0|1|8#4|1|11#5|1|10#6|1|10#10|1|11#2|2|8#7|2|10#4|2|10#10|2|10#1|3|10#0|3|8#5|3|8#6|3|8#2|3|8#3|3|8#4|3|8#10|3|11#3|4|8#0|4|8#4|4|8#1|4|8#2|4|8#5|4|10#10|4|10#2|5|8#4|5|10#1|5|10#5|5|10#3|6|8#0|6|8#4|6|8#2|6|8#1|6|8#6|6|8#5|6|11#7|6|10#10|6|11#2|7|11#3|7|11#4|7|10#10|7|11#10|8|10#0|10|11#1|10|11#2|10|11#3|10|11#4|10|10#5|10|11#8|10|10', '5|0|26|151|1|0|#3|2|35|192|1|0|#1|2|35|192|1|0|#6|5|35|191|1|0|#5|5|74|100|250|0|', '7|5|55|50|Ameise|1610906692#3|6|55|50|Ameise|1610906692', '', '5|5|55|54|3|50|1|5|3600|5');
INSERT INTO `maptemplate` VALUES (17, 1, 1, 'AmeisenH�gel', '0|0|10', '0|0|74|100|250|0|', '', '', '0|0|55|54|3|50|1|5|3600|5');

-- --------------------------------------------------------

-- 
-- Table structure for table `marketplace`
-- 

CREATE TABLE `marketplace` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `building` int(10) unsigned NOT NULL default '0',
  `offer_res` tinyint(3) unsigned NOT NULL default '0',
  `offer_count` int(10) unsigned NOT NULL default '0',
  `price_res` tinyint(3) unsigned NOT NULL default '0',
  `price_count` int(10) unsigned NOT NULL default '0',
  `starttime` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `building` (`building`),
  KEY `offer_res` (`offer_res`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `marketplace`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `message`
-- 

CREATE TABLE `message` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `folder` int(10) unsigned NOT NULL default '0',
  `from` int(10) unsigned NOT NULL default '0',
  `to` int(10) unsigned NOT NULL default '0',
  `subject` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `date` int(11) NOT NULL default '1',
  `status` tinyint(3) NOT NULL default '1',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `html` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `from` (`from`),
  KEY `to` (`to`),
  KEY `status` (`status`),
  KEY `type` (`type`),
  KEY `folder` (`folder`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `message`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `message_folder`
-- 

CREATE TABLE `message_folder` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` tinyint(4) NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `parent` int(11) NOT NULL default '0',
  `name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`,`parent`)
) TYPE=MyISAM AUTO_INCREMENT=5452 ;

-- 
-- Dumping data for table `message_folder`
-- 

INSERT INTO `message_folder` VALUES (232, 0, 249, 0, 'Eingang');
INSERT INTO `message_folder` VALUES (233, 3, 249, 0, 'Ausgang');
INSERT INTO `message_folder` VALUES (234, 2, 249, 0, 'Berichte');

-- --------------------------------------------------------

-- 
-- Table structure for table `newlog`
-- 

CREATE TABLE `newlog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` int(10) unsigned NOT NULL default '0',
  `topic` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `i1` int(11) NOT NULL default '0',
  `i2` int(11) NOT NULL default '0',
  `i3` int(11) NOT NULL default '0',
  `s1` varchar(128) NOT NULL default '',
  `s2` varchar(128) NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `topic` (`topic`),
  KEY `user` (`user`),
  KEY `count` (`count`),
  KEY `time` (`time`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `newlog`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `path`
-- 

CREATE TABLE `path` (
  `x0` int(11) NOT NULL default '0',
  `y0` int(11) NOT NULL default '0',
  `x1` int(11) NOT NULL default '0',
  `y1` int(11) NOT NULL default '0',
  `l` int(10) unsigned NOT NULL default '0',
  `t` int(10) unsigned NOT NULL default '0',
  `wp` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`x0`,`y0`,`x1`,`y1`),
  KEY `l` (`l`,`t`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `path`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pending`
-- 

CREATE TABLE `pending` (
  `name` varchar(64) NOT NULL default '',
  `mail` varchar(128) NOT NULL default '',
  `time` int(10) unsigned NOT NULL default '0',
  `key` varchar(255) NOT NULL default '',
  `pass` varchar(255) NOT NULL default '',
  `from` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `ip` varchar(16) NOT NULL default '',
  UNIQUE KEY `name` (`name`,`mail`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `pending`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `phperror`
-- 

CREATE TABLE `phperror` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `datetime` varchar(255) NOT NULL default '',
  `errornum` int(10) unsigned NOT NULL default '0',
  `errortype` varchar(255) NOT NULL default '',
  `errormsg` varchar(255) NOT NULL default '',
  `scriptname` varchar(255) NOT NULL default '',
  `scriptlinenum` int(10) unsigned NOT NULL default '0',
  `code` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `errormsg` (`errormsg`),
  KEY `scriptname` (`scriptname`),
  KEY `scriptlinenum` (`scriptlinenum`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `phperror`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pillage`
-- 

CREATE TABLE `pillage` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `army` int(10) unsigned NOT NULL default '0',
  `building` int(10) unsigned NOT NULL default '0',
  `start` int(10) unsigned NOT NULL default '0',
  `type` tinyint(4) NOT NULL default '-1',
  `lumber` float NOT NULL default '0',
  `stone` float NOT NULL default '0',
  `food` float NOT NULL default '0',
  `metal` float NOT NULL default '0',
  `runes` float NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `army` (`army`),
  KEY `type` (`type`),
  KEY `building` (`building`)
) TYPE=MyISAM AUTO_INCREMENT=534652 ;

-- 
-- Dumping data for table `pillage`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `poll`
-- 

CREATE TABLE `poll` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `created` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `created` (`created`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `poll`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `poll_answer`
-- 

CREATE TABLE `poll_answer` (
  `poll` int(10) unsigned NOT NULL default '0',
  `number` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`poll`,`number`,`user`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `poll_answer`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `poll_choice`
-- 

CREATE TABLE `poll_choice` (
  `number` int(10) unsigned NOT NULL default '1',
  `poll` int(10) unsigned NOT NULL default '0',
  `text` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`number`,`poll`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `poll_choice`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `profile`
-- 

CREATE TABLE `profile` (
  `page` varchar(64) NOT NULL default '',
  `time` float NOT NULL default '0',
  `max` float NOT NULL default '0',
  `sql` int(10) unsigned NOT NULL default '0',
  `sqlmax` int(10) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `mem` int(10) unsigned NOT NULL default '0',
  `memmax` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`page`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `profile`
-- 

INSERT INTO `profile` VALUES ('guild.php', 0.457576, 0.457576, 18, 18, 1, 4229344, 4229344);

-- --------------------------------------------------------

-- 
-- Table structure for table `quest`
-- 

CREATE TABLE `quest` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `running` tinyint(3) unsigned NOT NULL default '0',
  `start` int(10) unsigned NOT NULL default '0',
  `dur` int(10) unsigned NOT NULL default '0',
  `repeat` int(10) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `lumber` int(10) unsigned NOT NULL default '0',
  `stone` int(10) unsigned NOT NULL default '0',
  `food` int(10) unsigned NOT NULL default '0',
  `metal` int(10) unsigned NOT NULL default '0',
  `runes` int(10) unsigned NOT NULL default '0',
  `rewarditemtype` int(10) unsigned NOT NULL default '0',
  `rewarditemamount` float NOT NULL default '1',
  `flags` int(10) unsigned NOT NULL default '0',
  `params` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `descr` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `quest`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `race`
-- 

CREATE TABLE `race` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default '',
  `desc` text NOT NULL,
  `gfx` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `race`
-- 

INSERT INTO `race` VALUES (1, 'Menschen', 'lala wir sind die lustigen kleinen Menschchen', 'race/human.png');
INSERT INTO `race` VALUES (2, 'Gnome', 'wer will fleissige Handwerker sehen, der muss zu uns Gnomen gehen...', 'race/gnome.png');

-- --------------------------------------------------------

-- 
-- Table structure for table `session`
-- 

CREATE TABLE `session` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sid` varchar(128) NOT NULL default '',
  `ip` varchar(16) NOT NULL default '',
  `userid` int(10) unsigned NOT NULL default '0',
  `lastuse` int(12) unsigned NOT NULL default '0',
  `agent` varchar(128) NOT NULL default '',
  `usegfx` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=41 ;

-- 
-- Dumping data for table `session`
-- 

INSERT INTO `session` VALUES (38, 'logout-JDEkY1ouUjRrTGskbmN1QnhlbFliMU5K', '127.0.0.1', 249, 1147711179, 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.13) Gecko/20060418 Firefox/1.0.8 (Ubuntu package 1.0.8)', 1);
INSERT INTO `session` VALUES (37, 'JDEkLnFvSVQwQkwkVWlGdUQwcjU2U1Rx', '127.0.0.1', 2, 1147710531, 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.13) Gecko/20060418 Firefox/1.0.8 (Ubuntu package 1.0.8)', 1);
INSERT INTO `session` VALUES (39, 'logout-JDEkWDdGM1pUQmwkMnIwbjAzZHhDSWVG', '127.0.0.1', 249, 1147711189, 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.13) Gecko/20060418 Firefox/1.0.8 (Ubuntu package 1.0.8)', 1);
INSERT INTO `session` VALUES (40, 'logout-JDEkRzhWUU4wYmskeUdkSE5FR3B3OWl3', '127.0.0.1', 249, 1147711213, 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.13) Gecko/20060418 Firefox/1.0.8 (Ubuntu package 1.0.8)', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `shooting`
-- 

CREATE TABLE `shooting` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `attacker` int(10) unsigned NOT NULL default '0',
  `attackertype` int(10) unsigned NOT NULL default '0',
  `defender` int(10) unsigned NOT NULL default '0',
  `defendertype` int(10) unsigned NOT NULL default '0',
  `start` int(10) unsigned NOT NULL default '0',
  `lastshot` int(10) unsigned NOT NULL default '0',
  `fightlog` int(10) unsigned NOT NULL default '0',
  `autocancel` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=35664 ;

-- 
-- Dumping data for table `shooting`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `siege`
-- 

CREATE TABLE `siege` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `army` int(10) unsigned NOT NULL default '0',
  `building` int(10) unsigned NOT NULL default '0',
  `start` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `army` (`army`),
  KEY `building` (`building`)
) TYPE=MyISAM AUTO_INCREMENT=103391 ;

-- 
-- Dumping data for table `siege`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `spell`
-- 

CREATE TABLE `spell` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `radius` int(10) unsigned NOT NULL default '0',
  `target` int(10) unsigned NOT NULL default '0',
  `targettype` int(10) unsigned NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `owner` int(10) unsigned NOT NULL default '0',
  `lasts` int(10) unsigned NOT NULL default '0',
  `mod` float NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `target` (`target`),
  KEY `owner` (`owner`),
  KEY `x` (`x`),
  KEY `y` (`y`),
  KEY `type` (`type`)
) TYPE=MyISAM AUTO_INCREMENT=45539 ;

-- 
-- Dumping data for table `spell`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `spelltype`
-- 

CREATE TABLE `spelltype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `target` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(60) NOT NULL default '',
  `baserange` int(11) NOT NULL default '0',
  `basetime` int(11) NOT NULL default '0',
  `baseeffect` float NOT NULL default '0',
  `basemod` float NOT NULL default '0',
  `desc` text NOT NULL,
  `primetech` int(10) unsigned NOT NULL default '0',
  `cost_lumber` int(10) unsigned NOT NULL default '0',
  `cost_food` int(10) unsigned NOT NULL default '0',
  `cost_metal` int(10) unsigned NOT NULL default '0',
  `cost_stone` int(10) unsigned NOT NULL default '0',
  `cost_runes` int(10) unsigned NOT NULL default '0',
  `cost_mana` int(10) unsigned NOT NULL default '0',
  `req_tech` varchar(128) NOT NULL default '',
  `req_building` varchar(128) NOT NULL default '',
  `gfx` varchar(90) NOT NULL default '',
  `orderval` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `target` (`target`)
) TYPE=MyISAM AUTO_INCREMENT=22 ;

-- 
-- Dumping data for table `spelltype`
-- 

INSERT INTO `spelltype` VALUES (1, 2, 'FruchtbaresLand', 0, 43200, 0.05, 0.025, 'Dieser Zauber sorgt fuer eine erhoehte Nahrungsproduktion', 19, 0, 80, 0, 0, 600, 1, '19:1', '', 'zauber/land.png', 3);
INSERT INTO `spelltype` VALUES (2, 2, 'Erzbaron', 0, 43200, 0.05, 0.025, 'Hoehere Erzproduktion', 19, 0, 0, 80, 0, 600, 1, '19:1', '', 'zauber/erz.png', 4);
INSERT INTO `spelltype` VALUES (3, 2, 'Zauberwald', 0, 43200, 0.05, 0.025, 'Hoehere Holzproduktion', 19, 80, 0, 0, 0, 600, 1, '19:1', '', 'zauber/wald.png', 1);
INSERT INTO `spelltype` VALUES (4, 2, 'Steinreich', 0, 43200, 0.05, 0.025, 'Hoehere Steinproduktion', 19, 0, 0, 0, 80, 600, 1, '19:1', '', 'zauber/stein.png', 2);
INSERT INTO `spelltype` VALUES (5, 3, 'Erdbeben', 1, 7200, 15, 10, 'Ein haeftiges Erdbeben, das Haeuser beschaedigt ', 31, 300, 300, 300, 300, 4000, 50, '31:1,30:8', '', 'zauber/erdbeben.png', 30);
INSERT INTO `spelltype` VALUES (6, 3, 'ArmeeDerToten', 5, 7200, 0, 0, '', 33, 900, 0, 15000, 900, 15000, 40, '33:1,58>2', '', 'zauber/skelett.png', 20);
INSERT INTO `spelltype` VALUES (7, 3, 'Strike', 0, 0, 1, 0, '-1*level HP auf ein Gebaeude', 35, 2500, 2500, 1500, 1500, 10000, 50, '35:1,30:10', '', 'zauber/strike.png', 31);
INSERT INTO `spelltype` VALUES (8, 2, 'LoveAndJoy', 0, 43200, 0, 0.1, '', 37, 100, 100, 100, 100, 800, 10, '37:1,19:2', '', 'zauber/love_and_joy.png', 5);
INSERT INTO `spelltype` VALUES (9, 2, 'Regen', 0, 43200, 0, 0, '', 64, 0, 500, 0, 0, 0, 20, '64>1', '', 'zauber/wasser.png', 6);
INSERT INTO `spelltype` VALUES (10, 2, 'D�rre', 0, 86400, 0.05, 0.025, '', 62, 20000, 50000, 50000, 20000, 50000, 200, '62>1,58>1', '', 'zauber/duerre.png', 10);
INSERT INTO `spelltype` VALUES (11, 2, 'Pest', 0, 86400, 0, 0.1, '', 63, 50000, 100000, 100000, 50000, 100000, 250, '63>1,58>2', '', 'zauber/pest.png', 11);
INSERT INTO `spelltype` VALUES (12, 3, 'Portalstein', 0, 0, 0, 0, '', 60, 0, 0, 5000, 5000, 5000, 20, '60>1,58>1', '', 'item/portalstein_blau.png', 40);
INSERT INTO `spelltype` VALUES (13, 2, '7-Meilen-Stiefel', 0, 3600, 0, 0, '', 61, 0, 60000, 0, 0, 60000, 150, '61>1,58>2', '', 'item/stiefel.png', 7);
INSERT INTO `spelltype` VALUES (14, 3, 'Steinschlag', 0, 0, 0, 0, '', 69, 0, 0, 0, 100, 10000, 100, '69>1,58>1', '', 'zauber/stein.png', 32);
INSERT INTO `spelltype` VALUES (15, 3, 'Komet', 0, 0, 0, 0, '', 70, 0, 0, 0, 100000, 150000, 250, '70>1,58>2', '', 'zauber/komet.png', 33);
INSERT INTO `spelltype` VALUES (16, 3, 'Schatzsuche', 0, 0, 0, 0, '', 66, 0, 0, 0, 0, 30000, 30, '66>1,58>1', '', 'units/schatztruhe.png', 22);
INSERT INTO `spelltype` VALUES (17, 3, 'Spinnennetz', 0, 1800, 0, 0, '', 68, 0, 0, 50000, 0, 100000, 100, '68>1,58>2', '', 'zauber/netz.png', 41);
INSERT INTO `spelltype` VALUES (19, 3, 'H�llenauge', 0, 0, 0, 0, '', 65, 0, 0, 60000, 0, 20000, 50, '65>1,58>1', '', 'zauber/hoellenauge.png', 21);
INSERT INTO `spelltype` VALUES (20, 3, 'Bann', 0, 0, 0, 0, '', 67, 40000, 0, 0, 0, 40000, 200, '67>1,58>2', '', 'zauber/entwickler.png', 23);
INSERT INTO `spelltype` VALUES (21, 3, 'Brandrodung', 0, 0, 0, 0, '', 73, 0, 0, 0, 100, 5000, 100, '49>2,73>1', '', 'zauber/feuerball.png', 34);

-- --------------------------------------------------------

-- 
-- Table structure for table `sqlbookmark`
-- 

CREATE TABLE `sqlbookmark` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `sql` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=25 ;

-- 
-- Dumping data for table `sqlbookmark`
-- 

INSERT INTO `sqlbookmark` VALUES (3, 'kasernen', 'SELECT * FROM `building` WHERE `type` = 8');
INSERT INTO `sqlbookmark` VALUES (5, 'blob/orkdorf', 'SELECT * FROM `hellhole` WHERE `ai_type` > 0');
INSERT INTO `sqlbookmark` VALUES (4, 'monster', 'SELECT * FROM `army` WHERE `user` = 0 AND `hellhole` = 0');
INSERT INTO `sqlbookmark` VALUES (6, 'schilder', 'SELECT * FROM `building` WHERE `type` = 19');
INSERT INTO `sqlbookmark` VALUES (13, 'items', 'SELECT * FROM `item` WHERE `amount` > 9999 ORDER BY RAND() LIMIT 20');
INSERT INTO `sqlbookmark` VALUES (11, 'phperror', 'SELECT *,COUNT(`id`) as `c` FROM `phperror` GROUP BY `scriptname`,`scriptlinenum` ORDER BY `c` DESC LIMIT 5');
INSERT INTO `sqlbookmark` VALUES (10, 'non-player buildings', 'SELECT * FROM `building` WHERE `user` = 0 AND `hp` > 1 AND `type` NOT IN (5,21,17);');
INSERT INTO `sqlbookmark` VALUES (15, 'kristalle', 'SELECT * FROM building WHERE type = 56 ORDER BY ABS(x) + ABS(y)');
INSERT INTO `sqlbookmark` VALUES (17, 'MultiRammen', 'SELECT `army`.* FROM `army`,`unit` WHERE `army`.`id` = `unit`.`army` AND `unit`.`type` = 10 AND `unit`.`amount` > 1');
INSERT INTO `sqlbookmark` VALUES (18, 'maxtech', 'SELECT MAX(level),type,technologytype.name FROM technology,technologytype WHERE technologytype.id = technology.type GROUP BY type');
INSERT INTO `sqlbookmark` VALUES (19, 'TerraFormer', 'SELECT * FROM `user` WHERE flags&1>0');
INSERT INTO `sqlbookmark` VALUES (21, 'TerraFormer Aktivit�t', 'SELECT count(*) as fieldcount,u.id,u.name FROM `terrain` t,`user` u WHERE t.creator=u.id GROUP BY u.id ORDER BY fieldcount DESC');
INSERT INTO `sqlbookmark` VALUES (22, 'WP-Boom-Detector', 'SELECT COUNT(*) as c FROM `waypoint` GROUP BY `army` ORDER BY c DESC');
INSERT INTO `sqlbookmark` VALUES (23, 'WP-Boom-Detector', 'SELECT *,COUNT(*) as c FROM `waypoint` GROUP BY `army` ORDER BY c DESC LIMIT 10');
INSERT INTO `sqlbookmark` VALUES (24, 'blocked ameisen search', 'SELECT * FROM `army` WHERE `idle` > 1800 AND `name` = ''Ameise''');

-- --------------------------------------------------------

-- 
-- Table structure for table `sqlerror`
-- 

CREATE TABLE `sqlerror` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `time` int(10) unsigned NOT NULL default '0',
  `self` varchar(64) NOT NULL default '',
  `query` varchar(255) NOT NULL default '',
  `sqlquery` varchar(255) NOT NULL default '',
  `error` varchar(255) NOT NULL default '',
  `stacktrace` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `time` (`time`)
) TYPE=MyISAM AUTO_INCREMENT=7 ;

-- 
-- Dumping data for table `sqlerror`
-- 

INSERT INTO `sqlerror` VALUES (1, 1147615429, 'cron.php : cron.php - army_move', '', 'SELECT * FROM `army` WHERE `id`=', 'You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '''' at line 1', '/var/www/zw/cron.php:580:include(/var/www/zw/minicron.php)\n/var/www/zw/minicron.php:85:armythink(Object)\n/var/www/zw/lib.armythink.php:448:questtrigger_armymove(,32,80)\n/var/www/zw/lib.quest.php:190:sqlgetobject(SELECT * FROM `army` WHERE `id`=)\n/var/www/zw/lib.php:489:sql(SELECT * FROM `army` WHERE `id`=)\n/var/www/zw/lib.php:415:stacktrace()\n');
INSERT INTO `sqlerror` VALUES (2, 1147622643, 'cron.php : cron.php - army_move', '', 'SELECT * FROM `army` WHERE `id`=', 'You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '''' at line 1', '/var/www/zw/cron.php:580:include(/var/www/zw/minicron.php)\n/var/www/zw/minicron.php:85:armythink(Object)\n/var/www/zw/lib.armythink.php:448:questtrigger_armymove(,31,81)\n/var/www/zw/lib.quest.php:190:sqlgetobject(SELECT * FROM `army` WHERE `id`=)\n/var/www/zw/lib.php:489:sql(SELECT * FROM `army` WHERE `id`=)\n/var/www/zw/lib.php:415:stacktrace()\n');
INSERT INTO `sqlerror` VALUES (3, 1147626269, 'cron.php : cron.php - army_move', '', 'SELECT * FROM `army` WHERE `id`=', 'You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '''' at line 1', '/var/www/zw/cron.php:580:include(/var/www/zw/minicron.php)\n/var/www/zw/minicron.php:85:armythink(Object)\n/var/www/zw/lib.armythink.php:448:questtrigger_armymove(,32,80)\n/var/www/zw/lib.quest.php:190:sqlgetobject(SELECT * FROM `army` WHERE `id`=)\n/var/www/zw/lib.php:489:sql(SELECT * FROM `army` WHERE `id`=)\n/var/www/zw/lib.php:415:stacktrace()\n');
INSERT INTO `sqlerror` VALUES (4, 1147629880, 'cron.php : cron.php - army_move', '', 'SELECT * FROM `army` WHERE `id`=', 'You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '''' at line 1', '/var/www/zw/cron.php:580:include(/var/www/zw/minicron.php)\n/var/www/zw/minicron.php:85:armythink(Object)\n/var/www/zw/lib.armythink.php:448:questtrigger_armymove(,31,81)\n/var/www/zw/lib.quest.php:190:sqlgetobject(SELECT * FROM `army` WHERE `id`=)\n/var/www/zw/lib.php:489:sql(SELECT * FROM `army` WHERE `id`=)\n/var/www/zw/lib.php:415:stacktrace()\n');
INSERT INTO `sqlerror` VALUES (5, 1147633494, 'cron.php : cron.php - army_move', '', 'SELECT * FROM `army` WHERE `id`=', 'You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '''' at line 1', '/var/www/zw/cron.php:580:include(/var/www/zw/minicron.php)\n/var/www/zw/minicron.php:85:armythink(Object)\n/var/www/zw/lib.armythink.php:448:questtrigger_armymove(,31,81)\n/var/www/zw/lib.quest.php:190:sqlgetobject(SELECT * FROM `army` WHERE `id`=)\n/var/www/zw/lib.php:489:sql(SELECT * FROM `army` WHERE `id`=)\n/var/www/zw/lib.php:415:stacktrace()\n');
INSERT INTO `sqlerror` VALUES (6, 1147637104, 'cron.php : cron.php - army_move', '', 'SELECT * FROM `army` WHERE `id`=', 'You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '''' at line 1', '/var/www/zw/cron.php:580:include(/var/www/zw/minicron.php)\n/var/www/zw/minicron.php:85:armythink(Object)\n/var/www/zw/lib.armythink.php:448:questtrigger_armymove(,31,81)\n/var/www/zw/lib.quest.php:190:sqlgetobject(SELECT * FROM `army` WHERE `id`=)\n/var/www/zw/lib.php:489:sql(SELECT * FROM `army` WHERE `id`=)\n/var/www/zw/lib.php:415:stacktrace()\n');

-- --------------------------------------------------------

-- 
-- Table structure for table `stats`
-- 

CREATE TABLE `stats` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  `i1` int(11) NOT NULL default '0',
  `i2` int(11) NOT NULL default '0',
  `i3` int(11) NOT NULL default '0',
  `f1` float NOT NULL default '0',
  `f2` float NOT NULL default '0',
  `f3` float NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`),
  KEY `type` (`type`),
  KEY `time` (`time`)
) TYPE=MyISAM AUTO_INCREMENT=9 ;

-- 
-- Dumping data for table `stats`
-- 

INSERT INTO `stats` VALUES (1, 0, 6, 1147604500, 43, 0, 0, 0, 0, 0);
INSERT INTO `stats` VALUES (2, 0, 7, 1147604500, 0, 0, 0, 0, 0, 0);
INSERT INTO `stats` VALUES (3, 0, 2, 1147604500, 1, 41, 1620, 2.96063, 4452, 0);
INSERT INTO `stats` VALUES (4, 0, 8, 1147604500, 0, 0, 0, 0, 0, 0);
INSERT INTO `stats` VALUES (5, 0, 4, 1147604500, 1, 0, 6454, 2.95473e+08, 0, 0);
INSERT INTO `stats` VALUES (6, 0, 3, 1147604500, 135, 0, 0, 0, 0, 0);
INSERT INTO `stats` VALUES (7, 0, 5, 1147604500, 0, 0, 0, 0, 0, 0);
INSERT INTO `stats` VALUES (8, 2, 1, 1147604500, 5608, 271872, -459974362, 1620, 3, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `technology`
-- 

CREATE TABLE `technology` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `current_level` tinyint(6) NOT NULL default '0',
  `level` tinyint(6) unsigned NOT NULL default '0',
  `upgrades` tinyint(6) unsigned NOT NULL default '0',
  `upgradetime` int(10) unsigned NOT NULL default '0',
  `upgradebuilding` int(10) unsigned NOT NULL default '0',
  `status` tinyint(3) NOT NULL default '0',
  `statuschange` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `user` (`user`),
  KEY `upgradebuilding` (`upgradebuilding`),
  KEY `status` (`status`)
) TYPE=MyISAM AUTO_INCREMENT=25588 ;

-- 
-- Dumping data for table `technology`
-- 

INSERT INTO `technology` VALUES (1344, 34, 249, 0, 127, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1356, 3, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1349, 24, 249, 0, 127, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1348, 23, 249, 0, 127, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1347, 21, 249, 0, 127, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1346, 22, 249, 0, 127, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1345, 26, 249, 0, 10, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1352, 25, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1359, 38, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1354, 48, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1358, 27, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1357, 2, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (1355, 1, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8619, 60, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8618, 35, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8617, 31, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8616, 30, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8614, 32, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8613, 63, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8612, 62, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8611, 64, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8610, 61, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8609, 37, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8608, 19, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8607, 58, 249, 0, 2, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (12551, 33, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (7200, 49, 249, 0, 15, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (7762, 51, 249, 0, 3, 0, 0, 151503, 0, 0);
INSERT INTO `technology` VALUES (7763, 52, 249, 0, 5, 0, 0, 151503, 0, 0);
INSERT INTO `technology` VALUES (7764, 53, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (7765, 54, 249, 0, 5, 0, 0, 151503, 0, 0);
INSERT INTO `technology` VALUES (7766, 55, 249, 0, 5, 0, 0, 151503, 0, 0);
INSERT INTO `technology` VALUES (7767, 56, 249, 0, 5, 0, 0, 151503, 0, 0);
INSERT INTO `technology` VALUES (8909, 65, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8910, 66, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8911, 67, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8912, 69, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8913, 70, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (8914, 68, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (11178, 73, 249, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `technology` VALUES (23422, 71, 249, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `technologygroup`
-- 

CREATE TABLE `technologygroup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `buildingtype` int(10) unsigned NOT NULL default '0',
  `group` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `descr` text NOT NULL,
  `gfx` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=21 ;

-- 
-- Dumping data for table `technologygroup`
-- 

INSERT INTO `technologygroup` VALUES (1, 2, 0, 'Spieler Defensiv', 'zu den defensiven Zaubern z&auml;hlen unter anderem Schutz und Boost Zauber, und Zauber zur Erh&ouml;hung der Produktion<p>\r\n\r\n* Allgemeine DefensivZauber - Spieler<br>\r\n* Love & Joy', 'zauber/p-pos.png');
INSERT INTO `technologygroup` VALUES (7, 2, 0, 'Spieler Offensiv', 'Destruktive Zauber auf Spieler', 'zauber/p-neg.png');
INSERT INTO `technologygroup` VALUES (8, 2, 0, 'Area Defensiv', 'Defensiv Zauber f&uuml;r Gebiete und Gel&auml;ndefl&uuml;che, die nicht direkt Schaden zuf&uuml;gen<p>\r\n\r\n* Armee der Toten', 'zauber/g-pos.png');
INSERT INTO `technologygroup` VALUES (10, 2, 0, 'Area Offensiv', 'destruktive Zauber auf Gebieten<p>\r\n\r\n* Erdbeben<br>\r\n* Strike', 'zauber/g-neg.png');
INSERT INTO `technologygroup` VALUES (11, 2, 0, 'Armee Defensiv', '', 'zauber/a-pos.png');
INSERT INTO `technologygroup` VALUES (12, 2, 0, 'Armee Offensiv', 'destruktive zauber auf armeen', 'zauber/a-neg.png');
INSERT INTO `technologygroup` VALUES (13, 2, 0, 'Armeezauberer', 'zauberer, die zum supporten von armeen mitgenommen werden koennen, muessen auch erst erforscht werden', 'zauber/a-.png');
INSERT INTO `technologygroup` VALUES (14, 20, 0, 'Element Feuer', 'Feuer ist seit Menschengedenken ein Bestandteil unserer Kulturen, es ist lebensspendend und bietet einen Schutz gegen K�lte. Doch wie es unser  Leben auch f�rdert kann es auch vernichtend wirken, Anh�nger des Feuers m�ssen sich also entscheiden, ob sie sich auf die heilenden und schutzbietenden Aspekte des Feuers berufen wollen oder ob sie seine zerst�rerische Macht verehren wollen.', 'zauber/feuer.png');
INSERT INTO `technologygroup` VALUES (15, 20, 0, 'Element Wasser', 'Eine Balance zu Feuer, wie sein Gegenteil ist es lebensspendend. Wasser ist das Element m�chtiger Schutzmagie und macht viele magische Dinge effektiver. Wer das Wasser verehrt kann sich auf g�nstige Winde bei Schiffsreisen verlassen und bei Seek�mpfen kommen ihm vielleicht sogar Seeungeheuer zur Hilfe.', 'zauber/wasser.png');
INSERT INTO `technologygroup` VALUES (16, 20, 0, 'Element Luft', 'Die Macht der Ver�nderung. Luft unterst�tzt Feuer. ', 'zauber/luft.png');
INSERT INTO `technologygroup` VALUES (17, 20, 0, 'Element Erde', 'Basis menschlichen Lebens ', 'zauber/erde.png');
INSERT INTO `technologygroup` VALUES (18, 20, 0, 'Zwischenwelt-Entwickler', 'Alternativ zu den Elementen kann man auch die zw-Entwickler verehren, diese stellen sich vielleicht als launische G�tter heraus, aber es kann von Zeit zu Zeit vielleicht auch n�tzlich sein. Wer sie nervt wird �bel bestraft, wer sich nach ihrem Willen richtet mag belohnt werden oder auch nicht.  F�r Anh�nger der Entwickler gibt es kaum feste Features oder verbesserte Wert. Ver�nderungen der  std. Werte k�nnen zuf�llig tempor�r auftreten aber sie m�ssen nicht positiv sein.<p>\r\n\r\nHEIL ERIS\r\nHEIL DISCORDIA\r\n\r\n*g* ', 'zauber/entwickler.png');
INSERT INTO `technologygroup` VALUES (19, 1, 0, 'Ausbildung', 'Hier k�nnen der Bev�lkerung verschiedene Fertigkeiten beigebracht werden, um neue Berufsgruppen entstehen zu lassen:<p>\r\n\r\n-Landschaftsgestaltung', 'upgrades/upgrade_base.png');
INSERT INTO `technologygroup` VALUES (20, 2, 0, 'Synthese', '', 'res_mana.gif');

-- --------------------------------------------------------

-- 
-- Table structure for table `technologytype`
-- 

CREATE TABLE `technologytype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `buildingtype` int(10) unsigned NOT NULL default '0',
  `buildinglevel` int(10) unsigned NOT NULL default '0',
  `group` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `descr` text NOT NULL,
  `basecost_lumber` int(10) unsigned NOT NULL default '0',
  `basecost_stone` int(10) unsigned NOT NULL default '0',
  `basecost_food` int(10) unsigned NOT NULL default '0',
  `basecost_metal` int(10) unsigned NOT NULL default '0',
  `basecost_runes` int(10) unsigned NOT NULL default '0',
  `basetime` int(10) unsigned NOT NULL default '0',
  `maxlevel` int(10) unsigned NOT NULL default '10',
  `increment` float NOT NULL default '0',
  `req_tech` varchar(255) NOT NULL default '',
  `req_geb` varchar(255) NOT NULL default '',
  `gfx` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=75 ;

-- 
-- Dumping data for table `technologytype`
-- 

INSERT INTO `technologytype` VALUES (1, 12, 5, 0, 'Kettenr&uuml;stung', 'Durch das verflechten unz�hliger Metallringe entsteht ein wirkungsvoller Schutz gegen Klingen und Stichwaffen. Gegen Hiebwaffen n�tzt dies jedoch wenig.<br>\r\nWird f�r SchwertKrieger, LanzenTr�ger und Berserker ben�tigt, und st�rkt deren Verteidigung.', 100, 700, 500, 1500, 0, 43200, 4, 0.5, '3:3,27:5', '12:10,8:5', 'tech/kette.png');
INSERT INTO `technologytype` VALUES (3, 12, 0, 0, 'geh&auml;rtete Klingen', 'durch die Verwendung ausgefeilter Schmiedetechniken, besonderer Metalle, und einem besonders starkem Schliff  gelingt es die Qualit&auml;t der Klingen immer weiter zu steigern.<br>\r\nSteigert den Angriff von SchwertKrieger und Ritter\r\n', 400, 100, 400, 2000, 0, 14400, 10, 1, '', '', 'tech/klinge.png');
INSERT INTO `technologytype` VALUES (2, 12, 0, 0, 'Plattenpanzer', 'Bietet wirkungvollen Schutz selbst gegen Hiebwaffen, schr�nkt aber die Beweglichkeit ein.<br>\r\nWird f�r Ritter ben�tigt, und st�rkt deren Verteidigung.', 3000, 1000, 3000, 4000, 0, 86400, 4, 1, '1>2,58<0', '', 'tech/panzer.png');
INSERT INTO `technologytype` VALUES (19, 2, 0, 1, 'Spieler Defensiv', 'Generelle Forschung f&uuml;r DefensivZauber und produktionssteigernde Zauber<p>\r\n* FruchtbaresLand<br>\r\n* Erzbaron<br>\r\n* Zauberwald<br>\r\n* Steinreich<p>\r\nErh&ouml;hen die Effizienz eurer Arbeiter (ca 10% mehr Produktion f�r 10 Stunden - Achtung Werte zufallsbedingt!)', 750, 750, 750, 750, 1500, 36000, 10, 0.5, '', '', 'zauber/p-pos.png');
INSERT INTO `technologytype` VALUES (30, 2, 0, 10, 'Area Offensiv', 'Generelle Forschung f&uuml;r fl&auml;chendeckende Offensivzauber', 700, 700, 700, 700, 1400, 42800, 15, 0.3, '', '', 'zauber/g-neg.png');
INSERT INTO `technologytype` VALUES (21, 14, 0, 0, 'Hammer', 'Um Steine sinnvoll aus dem Fels hauen zu k�nnen m�ssen gute H�mmer her...<p>\r\n\r\n+ 1.5% Produktion pro Stufe', 1000, 2000, 500, 1500, 0, 43200, 20, 0.4, '[16],22<15,23<15,24<15', '14:7', 'tech/hammer.png');
INSERT INTO `technologytype` VALUES (22, 13, 0, 0, 'Axt', 'Fr�hlich W�lder kleinhauen...<p>\r\n\r\n+ 1.5% Produktion pro Stufe', 1500, 0, 500, 1500, 0, 43200, 20, 0.4, '[16],21<15,23<15,24<15', '13:7', 'tech/axt.png');
INSERT INTO `technologytype` VALUES (31, 2, 5, 10, 'Erdbeben', 'Besch&auml;digt Geb&auml;ude im Umkreis des Zentrums mit abfallendem Schaden<br>\r\n[(15+(level-1)*10 HP)] bis zur maximalen Reichweite von [1+LEVEL]<br>\r\nDieser Spruch kann Geb&auml;ude nur bis auf eine bestimmte HP-Zahl besch&auml;digen:<br>\r\nF&uuml;r HQ: [100+5*level]<br>\r\nandere: [1+level]<p>\r\n\r\n[level] Stufe des zu besch&auml;digenden Geb&auml;udes<br>\r\n[LEVEL] Erforschte Stufe des Zaubers', 5000, 5000, 5000, 5000, 10000, 40000, 10, 0.8, '30:8', '', 'zauber/erdbeben.png');
INSERT INTO `technologytype` VALUES (23, 9, 0, 0, 'Sense', 'Hier gehts nicht um den Tod...<p>\r\n\r\n+ 1.5% Produktion pro Stufe', 1500, 500, 1000, 1500, 0, 43200, 20, 0.4, '[16],21<15,22<15,24<15', '9:7', 'tech/sense.png');
INSERT INTO `technologytype` VALUES (24, 15, 0, 0, 'Spitzhacke', 'Um das wertvolle Erz schneller f�rdern zu k�nnen, braucht man besseres Werkzeug.<p>\r\n\r\n+ 1.5% Produktion pro Stufe', 2000, 1500, 700, 2000, 0, 43200, 20, 0.4, '[16],21<15,22<15,23<15', '15:7', 'tech/spitzhacke.png');
INSERT INTO `technologytype` VALUES (25, 2, 5, 0, 'Effiziente Runenprod.', '+ 50% Produktion', 5000, 5000, 5000, 5000, 5000, 43200, 10, 0.5, '', '', 'tech/runenprod.png');
INSERT INTO `technologytype` VALUES (26, 1, 5, 0, 'Schichtarbeit', 'ab Haupthaus Stufe 5 erforschbar.\r\n+ 0.5 slots in den Produktionsstellen\r\n', 1500, 1500, 3000, 1500, 0, 43200, 10, 0.5, '', '1>5', 'tech/schicht.png');
INSERT INTO `technologytype` VALUES (27, 12, 0, 0, 'Lederr�stung', 'ein leichter Schutzpanzer, wiegt wenig, bietet aber auch wenig Schutz', 500, 500, 500, 0, 0, 36000, 5, 0.5, '', '', 'tech/leder.png');
INSERT INTO `technologytype` VALUES (32, 2, 0, 8, 'Area Defensiv', 'generelle defensiv flaechenzauberforschung', 500, 500, 500, 500, 1000, 46200, 15, 0.25, '', '', 'zauber/g-pos.png');
INSERT INTO `technologytype` VALUES (33, 2, 5, 8, 'ArmeeDerToten', 'bei K�mpfen in der verfluchten Gegend kommen die gefallenen als untote Ritter wieder und k�mpfen an der Seite ihrer Kameraden weiter', 25000, 25000, 25000, 25000, 50000, 86400, 3, 2, '32:5,58>2', '20:5,8:10,2:10', 'zauber/skelett.png');
INSERT INTO `technologytype` VALUES (34, 11, 0, 0, 'Architektur', 'beschleunigt das Bauen von Geb&auml;uden', 500, 500, 500, 500, 0, 18000, 10, 2, '', '', 'tech/architektur.png');
INSERT INTO `technologytype` VALUES (35, 2, 10, 10, 'Strike', 'Macht genau ein Punkt Schaden. Wenn die HP unter 1 fallen, wird das Geb&auml;ude zerst&ouml;rt.', 15000, 15000, 15000, 15000, 25000, 172800, 1, 0, '30:10', '', 'zauber/strike.png');
INSERT INTO `technologytype` VALUES (37, 2, 2, 1, 'LoveAndJoy', 'Erh�ht die Geburtenrate um zus�tzliche<br>\r\n[(40 + MAX_POP/100)*LEVEL*0.1]<br>\r\npro stunde', 1000, 1000, 1000, 50, 500, 36000, 10, 0.25, '19:2', '2:2', 'zauber/love_and_joy.png');
INSERT INTO `technologytype` VALUES (38, 12, 5, 0, 'Lanze', 'Lanzentraeger brauchen ja auch was zum kaempfen', 500, 500, 500, 1000, 0, 43200, 5, 0.9, '3:3,58<1', '', 'tech/lanze.png');
INSERT INTO `technologytype` VALUES (48, 11, 10, 0, 'Verbesserte Rammen', '+3 Schaden pro Level', 7000, 7000, 3500, 10000, 0, 43200, 5, 0.4, '', '1:15, 11:15', 'tech/rammen.png');
INSERT INTO `technologytype` VALUES (49, 1, 15, 0, 'Landschaftsgestaltung', '', 50000, 25000, 50000, 25000, 10000, 86400, 15, 1, '', '1>15', 'tech/terraformer.png');
INSERT INTO `technologytype` VALUES (51, 46, 5, 0, 'Einmaster', 'edit me', 3000, 50, 3000, 3000, 50, 36000, 3, 0.7, '', '', 'units/schiff-1.png');
INSERT INTO `technologytype` VALUES (52, 46, 10, 0, 'Kampfschiffe', 'edit me', 5000, 5000, 10000, 5000, 5000, 21600, 5, 1, '', '', 'units/schiff-2.png');
INSERT INTO `technologytype` VALUES (53, 46, 15, 0, 'Rammbock', 'edit me', 5000, 0, 4000, 10000, 5000, 43200, 5, 1, '', '', 'tech/rammen.png');
INSERT INTO `technologytype` VALUES (54, 46, 0, 0, 'Segelkunst', 'edit me', 2500, 2500, 2500, 2500, 0, 21600, 5, 0.3, '', '', 'tech/segelkunst.png');
INSERT INTO `technologytype` VALUES (55, 46, 0, 0, 'Enterhaken', 'edit me', 500, 500, 1000, 5000, 0, 43200, 5, 0.7, '', '', 'tech/enterhaken.png');
INSERT INTO `technologytype` VALUES (56, 46, 0, 0, 'Seekampf', 'edit me', 5000, 2500, 2500, 5000, 0, 43200, 5, 0.5, '', '', 'tech/seekampf.png');
INSERT INTO `technologytype` VALUES (58, 2, 10, 0, 'Magie-Meisterschaft', '', 200000, 200000, 200000, 200000, 200000, 432000, 2, 1, '2<0', '2:10', 'turmzauberer2.png');
INSERT INTO `technologytype` VALUES (60, 2, 10, 20, 'Portalstein', '', 100000, 100000, 100000, 100000, 100000, 86400, 1, 1, '58>1', '2>10', 'item/portalstein_blau.png');
INSERT INTO `technologytype` VALUES (61, 2, 10, 1, '7-Meilen-Stiefel', '', 200000, 200000, 200000, 200000, 200000, 86400, 1, 1, '58>2', '2>10', 'item/stiefel.png');
INSERT INTO `technologytype` VALUES (62, 2, 10, 7, 'D�rre', '', 100000, 100000, 100000, 100000, 100000, 86400, 3, 1, '58>1', '2>10', 'zauber/duerre.png');
INSERT INTO `technologytype` VALUES (63, 2, 10, 7, 'Pest', '', 300000, 300000, 300000, 300000, 300000, 172800, 3, 1, '58>2', '2>10', 'zauber/pest.png');
INSERT INTO `technologytype` VALUES (64, 2, 0, 1, 'Regen', '', 500, 500, 500, 0, 500, 259200, 1, 1, '', '', 'zauber/wasser.png');
INSERT INTO `technologytype` VALUES (65, 2, 10, 8, 'H�llenauge', '', 0, 0, 0, 60000, 60000, 86400, 3, 0, '58>1', '2>10', 'zauber/hoellenauge.png');
INSERT INTO `technologytype` VALUES (66, 2, 0, 8, 'Schatzsuche', '', 0, 0, 0, 300000, 300000, 86400, 3, 1, '58>1', '', 'units/schatztruhe.png');
INSERT INTO `technologytype` VALUES (67, 2, 0, 8, 'Bann', '', 100000, 0, 0, 0, 100000, 86400, 2, 1, '58>2', '', 'zauber/entwickler.png');
INSERT INTO `technologytype` VALUES (68, 2, 10, 20, 'Spinnennetz', '', 0, 0, 0, 100000, 100000, 86400, 1, 1, '58>2', '2>10', 'zauber/netz.png');
INSERT INTO `technologytype` VALUES (69, 2, 10, 10, 'Steinschlag', '', 0, 100000, 0, 0, 100000, 86400, 2, 1, '58>1', '2>10', 'zauber/stein.png');
INSERT INTO `technologytype` VALUES (70, 2, 10, 10, 'Komet', '', 500000, 500000, 500000, 500000, 300000, 604800, 2, 1, '58>2', '2>10', 'zauber/komet.png');
INSERT INTO `technologytype` VALUES (71, 51, 0, 0, 'Brauereikunst', 'Brauereikunst dient nur der Freude und der Punkte. Hier geht es nur darum, wer das beste Bier braut.', 100, 100, 100, 100, 100, 60, 255, 100, '', '', 'tech/bier.png');
INSERT INTO `technologytype` VALUES (73, 2, 0, 10, 'Brandrodung', '', 200000, 0, 0, 100000, 100000, 86400, 1, 0, '49>2', '2>10,44>3', 'zauber/feuerball.png');
INSERT INTO `technologytype` VALUES (74, 12, 20, 0, 'Bogen', '', 5000, 500, 500, 3000, 0, 21600, 10, 1, '58<0', '', 'units/unit_7.png');

-- --------------------------------------------------------

-- 
-- Table structure for table `terrain`
-- 

CREATE TABLE `terrain` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `param` char(4) NOT NULL default '',
  `nwse` tinyint(3) unsigned NOT NULL default '0',
  `kills` int(10) unsigned NOT NULL default '0',
  `steps` int(10) unsigned NOT NULL default '0',
  `creator` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `pos` (`x`,`y`),
  KEY `type` (`type`),
  KEY `y` (`y`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `terrain`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `terrainpatchtype`
-- 

CREATE TABLE `terrainpatchtype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `gfx` varchar(128) NOT NULL default '',
  `here` int(10) unsigned NOT NULL default '0',
  `up` int(10) unsigned NOT NULL default '0',
  `down` int(10) unsigned NOT NULL default '0',
  `left` int(10) unsigned NOT NULL default '0',
  `right` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `here` (`here`,`up`,`down`,`left`,`right`)
) TYPE=MyISAM AUTO_INCREMENT=429 ;

-- 
-- Dumping data for table `terrainpatchtype`
-- 

INSERT INTO `terrainpatchtype` VALUES (1, 'terrainpatch/quelle-wse.png', 3, 2, 3, 3, 3);
INSERT INTO `terrainpatchtype` VALUES (2, 'see/swamp-.png', 16, 6, 6, 6, 6);
INSERT INTO `terrainpatchtype` VALUES (3, 'see/swamp-e.png', 16, 6, 6, 6, 16);
INSERT INTO `terrainpatchtype` VALUES (4, 'see/swamp-n.png', 16, 16, 6, 6, 6);
INSERT INTO `terrainpatchtype` VALUES (5, 'see/swamp-ne.png', 16, 16, 6, 6, 16);
INSERT INTO `terrainpatchtype` VALUES (6, 'see/swamp-ns.png', 16, 16, 16, 6, 6);
INSERT INTO `terrainpatchtype` VALUES (7, 'see/swamp-nse.png', 16, 16, 16, 6, 16);
INSERT INTO `terrainpatchtype` VALUES (8, 'see/swamp-nw.png', 16, 16, 6, 16, 6);
INSERT INTO `terrainpatchtype` VALUES (9, 'see/swamp-nwe.png', 16, 16, 6, 16, 16);
INSERT INTO `terrainpatchtype` VALUES (10, 'see/swamp-nws.png', 16, 16, 16, 16, 6);
INSERT INTO `terrainpatchtype` VALUES (11, 'see/swamp-s.png', 16, 6, 16, 6, 6);
INSERT INTO `terrainpatchtype` VALUES (12, 'see/swamp-se.png', 16, 6, 16, 6, 16);
INSERT INTO `terrainpatchtype` VALUES (13, 'see/swamp-w.png', 16, 6, 6, 16, 6);
INSERT INTO `terrainpatchtype` VALUES (14, 'see/swamp-we.png', 16, 6, 6, 16, 16);
INSERT INTO `terrainpatchtype` VALUES (15, 'see/swamp-ws.png', 16, 6, 16, 16, 6);
INSERT INTO `terrainpatchtype` VALUES (16, 'see/swamp-wse.png', 16, 6, 16, 16, 16);
INSERT INTO `terrainpatchtype` VALUES (17, 'see/wueste-.png', 7, 6, 6, 6, 6);
INSERT INTO `terrainpatchtype` VALUES (18, 'see/wueste-e.png', 7, 6, 6, 6, 7);
INSERT INTO `terrainpatchtype` VALUES (19, 'see/wueste-n.png', 7, 7, 6, 6, 6);
INSERT INTO `terrainpatchtype` VALUES (20, 'see/wueste-ne.png', 7, 7, 6, 6, 7);
INSERT INTO `terrainpatchtype` VALUES (21, 'see/wueste-ns.png', 7, 7, 7, 6, 6);
INSERT INTO `terrainpatchtype` VALUES (22, 'see/wueste-nse.png', 7, 7, 7, 6, 7);
INSERT INTO `terrainpatchtype` VALUES (23, 'see/wueste-nw.png', 7, 7, 6, 7, 6);
INSERT INTO `terrainpatchtype` VALUES (24, 'see/wueste-nwe.png', 7, 7, 6, 7, 7);
INSERT INTO `terrainpatchtype` VALUES (25, 'see/wueste-nws.png', 7, 7, 7, 7, 6);
INSERT INTO `terrainpatchtype` VALUES (26, 'see/wueste-s.png', 7, 6, 7, 6, 6);
INSERT INTO `terrainpatchtype` VALUES (27, 'see/wueste-se.png', 7, 6, 7, 6, 7);
INSERT INTO `terrainpatchtype` VALUES (28, 'see/wueste-w.png', 7, 6, 6, 7, 6);
INSERT INTO `terrainpatchtype` VALUES (29, 'see/wueste-we.png', 7, 6, 6, 7, 7);
INSERT INTO `terrainpatchtype` VALUES (30, 'see/wueste-ws.png', 7, 6, 7, 7, 6);
INSERT INTO `terrainpatchtype` VALUES (31, 'see/wueste-wse.png', 7, 6, 7, 7, 7);
INSERT INTO `terrainpatchtype` VALUES (32, 'river/river-see-n.png', 2, 2, 6, 0, 0);
INSERT INTO `terrainpatchtype` VALUES (33, 'river/river-see-w.png', 2, 0, 0, 2, 6);
INSERT INTO `terrainpatchtype` VALUES (34, 'river/river-see-s.png', 2, 6, 2, 0, 0);
INSERT INTO `terrainpatchtype` VALUES (35, 'river/river-see-e.png', 2, 0, 0, 6, 2);
INSERT INTO `terrainpatchtype` VALUES (36, 'landschaft/steppe/w2steppe-e.png', 7, 7, 7, 7, 28);
INSERT INTO `terrainpatchtype` VALUES (37, 'landschaft/steppe/w2steppe-n.png', 28, 7, 28, 28, 28);
INSERT INTO `terrainpatchtype` VALUES (38, 'landschaft/steppe/w2steppe-ne.png', 7, 28, 7, 7, 28);
INSERT INTO `terrainpatchtype` VALUES (39, 'landschaft/steppe/w2steppe-ns.png', 7, 28, 28, 7, 7);
INSERT INTO `terrainpatchtype` VALUES (40, 'landschaft/steppe/w2steppe-nse.png', 7, 28, 28, 7, 28);
INSERT INTO `terrainpatchtype` VALUES (41, 'landschaft/steppe/w2steppe-nw.png', 7, 28, 7, 28, 7);
INSERT INTO `terrainpatchtype` VALUES (42, 'landschaft/steppe/w2steppe-nwe.png', 7, 28, 7, 28, 28);
INSERT INTO `terrainpatchtype` VALUES (43, 'landschaft/steppe/w2steppe-nws.png', 7, 28, 28, 28, 7);
INSERT INTO `terrainpatchtype` VALUES (44, 'landschaft/steppe/w2steppe-s.png', 7, 7, 28, 7, 7);
INSERT INTO `terrainpatchtype` VALUES (45, 'landschaft/steppe/w2steppe-se.png', 7, 7, 28, 7, 28);
INSERT INTO `terrainpatchtype` VALUES (46, 'landschaft/steppe/w2steppe-we.png', 7, 7, 7, 28, 28);
INSERT INTO `terrainpatchtype` VALUES (47, 'landschaft/steppe/w2steppe-ws.png', 28, 28, 7, 7, 28);
INSERT INTO `terrainpatchtype` VALUES (48, 'landschaft/steppe/w2steppe-wse.png', 7, 7, 28, 28, 28);
INSERT INTO `terrainpatchtype` VALUES (49, 'landschaft/steppe/w2steppe-w.png', 7, 7, 7, 28, 7);
INSERT INTO `terrainpatchtype` VALUES (328, 'landschaft/steppe/w2steppe-n.png', 7, 28, 7, 7, 7);
INSERT INTO `terrainpatchtype` VALUES (428, 'landschaft/steppe/w2steppe-ws.png', 7, 7, 28, 28, 7);

-- --------------------------------------------------------

-- 
-- Table structure for table `terrainsegment4`
-- 

CREATE TABLE `terrainsegment4` (
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '1',
  PRIMARY KEY  (`x`,`y`),
  KEY `y` (`y`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `terrainsegment4`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `terrainsegment64`
-- 

CREATE TABLE `terrainsegment64` (
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '1',
  PRIMARY KEY  (`x`,`y`),
  KEY `y` (`y`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `terrainsegment64`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `terrainsubtype`
-- 

CREATE TABLE `terrainsubtype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `terraintype` int(10) unsigned NOT NULL default '0',
  `terrainconnecttype` int(10) unsigned NOT NULL default '0',
  `gfx` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `terraintype` (`terraintype`,`terrainconnecttype`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `terrainsubtype`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `terraintype`
-- 

CREATE TABLE `terraintype` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `descr` text NOT NULL,
  `speed` int(11) NOT NULL default '0',
  `buildable` tinyint(4) NOT NULL default '0',
  `color` varchar(8) NOT NULL default '',
  `gfx` varchar(128) NOT NULL default '',
  `cssclass` varchar(64) NOT NULL default '',
  `mod_a` float NOT NULL default '1',
  `mod_v` float NOT NULL default '1',
  `mod_f` float NOT NULL default '1',
  `movable_flag` int(10) unsigned NOT NULL default '0',
  `connectto_terrain` varchar(255) NOT NULL default '',
  `connectto_building` varchar(255) NOT NULL default '',
  `maxrandcenter` int(10) unsigned NOT NULL default '0',
  `maxrandborder` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `movable_flag` (`movable_flag`)
) TYPE=MyISAM AUTO_INCREMENT=33 ;

-- 
-- Dumping data for table `terraintype`
-- 

INSERT INTO `terraintype` VALUES (1, 'Gras', 'eine gr�ne Wiese', 120, 1, '#66AA55', 'landschaft/grassrandom/grass_nwse_%RND%.png', 'gr', 1, 1, 1, 1, '', '', 10, 10);
INSERT INTO `terraintype` VALUES (2, 'Fluss', 'ein pl�tschender Fluss', 120, 0, '#0000FF', 'river/river-%NWSE%.png', 'fluss_%NWSE%', 1, 1, 1, 8, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (3, 'Berg', 'riesige un�berwindbare Berge', 180, 0, '#7E7E7E', 'mountain/berg-%NWSE%.png', 'hill_%NWSE%', 1, 1, 1, 4, '15', '', 0, 0);
INSERT INTO `terraintype` VALUES (4, 'Wald', 'dichter dunkler Wald', 180, 0, '#0D7F24', 'wald/wald-%NWSE%.png', 'forest_%NWSE%', 1, 1, 1, 2, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (5, 'Loch', 'da klafft ein gro&szlig;es Loch', 180, 0, '#666666', 'landschaft/loch.png', 'loch', 1, 1, 1, 1, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (6, 'See', 'ganz viel Wasser...', 240, 0, '#0000FF', 'see/see-%NWSE%.png', 'see_%NWSE%', 1, 1, 1, 16, '2,6,7,16,18', '47', 0, 0);
INSERT INTO `terraintype` VALUES (7, 'Wueste', 'trockene, leblose Wueste', 300, 0, '#ECD322', 'wueste/wueste-%NWSE%.png', 'wueste-%NWSE%', 1, 1, 1, 1, '6,9,20', '', 0, 0);
INSERT INTO `terraintype` VALUES (8, 'Kornfeld', 'bringt der angrenzenden Farm einen Bonus', 120, 1, 'yellow', 'landschaft/cornfield.png', 'cfield', 1, 1, 1, 1, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (9, 'Oase', 'Teich mit Palmen in der W�ste :-)', 120, 0, '#6666aa', 'wueste/oase.png', 'oase', 1, 1, 1, 1, '7,9,20', '', 0, 0);
INSERT INTO `terraintype` VALUES (10, 'Blumen', 'Wiese mit Bl�mchen', 120, 1, '#CC3366', 'landschaft/blumen.png', 'blumen', 1, 1, 1, 1, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (11, 'Ger�ll', 'zerbrochenes Gestein', 180, 1, '#686868', 'landschaft/geroell.png', 'sc', 1, 1, 1, 1, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (12, 'Baumstumpf', 'die Reste eines abgeholzten Waldes', 120, 1, '#66AA55', 'landschaft/baumstumpf.png', 'bs', 1, 1, 1, 1, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (13, 'junger Wald', 'kleine B�umchen fangen hier an zu wachsen', 120, 1, '#66AA55', 'landschaft/jungwald.png', 'yw', 1, 1, 1, 1, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (14, 'Schnee', 'eine zugeschneite Wiese', 120, 1, '#66AA55', 'winter/landschaft/grass.png', 'wgr', 1, 1, 1, 1, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (15, 'schneebedeckter Berg', 'riesige schneebedeckte Berge', 0, 0, '#bbbbbb', 'mountain/snow-%NWSE%.png', 'snowhill_%NWSE%', 1, 1, 0, 4, '3', '', 0, 0);
INSERT INTO `terraintype` VALUES (16, 'Sumpf', '.', 300, 0, '#1F8177', 'swamp/swamp-%NWSE%.png', 'sw-%NWSE%', 1, 1, 1, 1, '6', '', 0, 0);
INSERT INTO `terraintype` VALUES (17, 'Dschungel', '.', 240, 0, 'green', 'dschungel/dschungel-%NWSE%.png', 'dsch_%NWSE%', 1, 1, 1, 1, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (18, 'tiefe See', 'ganz viel tiefes Wasser...', 240, 0, '#0000D5', 'see/tief-%NWSE%.png', 'tsee_%NWSE%', 1, 1, 1, 32, '18', '', 0, 0);
INSERT INTO `terraintype` VALUES (20, 'Palmen', 'ein paar schattenspendende Palmen, welch ein Segen.', 120, 0, '#127E32', 'wueste/palmen.png', '', 1, 1, 1, 1, '7,9,20', '', 0, 0);
INSERT INTO `terraintype` VALUES (21, 'Blumen', 'Wiese mit Bl�mchen', 120, 1, '#CC3366', 'landschaft/blumen2.png', '', 1, 1, 1, 1, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (22, 'Blumen', 'Wiese mit Bl�mchen', 120, 1, '#CC3366', 'landschaft/blumen3.png', '', 1, 1, 1, 1, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (23, 'H�gel', '', 240, 0, '#595959', 'landschaft/huegel/huegel-%NWSE%.png', '', 1, 1.5, 1, 1, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (24, 'Tundra', '', 120, 0, '#913022', 'landschaft/tundra.png', '', 1, 1, 1, 7, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (25, 'Taiga', '', 160, 0, '#498237', 'landschaft/taiga.png', '', 1, 1, 1, 7, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (26, 'Erde', '', 180, 1, '#65473A', 'earth/earth_%NWSE%.png', '', 1, 1, 1, 1, '30,31', '', 0, 0);
INSERT INTO `terraintype` VALUES (27, 'steiniger Boden', '', 180, 1, '#747474', 'rock/rock_%NWSE%.png', '', 1, 1, 1, 5, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (28, 'Steppe', 'oede Steppe ohne Elche', 80, 0, '#AF9B48', 'landschaft/steppe/g2steppe-%NWSE%.png', '', 1, 1, 1, 7, '28', '', 0, 0);
INSERT INTO `terraintype` VALUES (29, 'Schnee', '', 180, 1, '#D4D4D4', 'schnee/schnee_%NWSE%.png', '', 1, 1, 1, 1, '', '', 0, 0);
INSERT INTO `terraintype` VALUES (30, 'LavaStrom', '', 0, 0, '#ff8800', 'lava/lavafluss/Lavafluss-%NWSE%.gif', '', 1, 1, 1, 0, '30,31', '', 0, 0);
INSERT INTO `terraintype` VALUES (31, 'Vulkan', '', 0, 0, '#AA4400', 'lava/lavaberg/Lavaberg-%NWSE%.gif', '', 1, 1, 1, 0, '30', '', 0, 0);
INSERT INTO `terraintype` VALUES (32, 'Nadelwald', '', 120, 0, '#2D6220', 'nadelwald/nadelwald-%NWSE%.png', '', 1, 1, 1, 2, '26', '', 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `ticket`
-- 

CREATE TABLE `ticket` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `assigned_user` int(10) unsigned NOT NULL default '0',
  `assigned_bug` int(10) unsigned NOT NULL default '0',
  `subject` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  `created` int(10) unsigned NOT NULL default '0',
  `topic` tinyint(3) unsigned NOT NULL default '0',
  `prio` tinyint(3) unsigned NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `img` varchar(128) NOT NULL default '0',
  `eventtime` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`,`assigned_user`,`created`,`topic`,`prio`),
  KEY `flags` (`flags`),
  KEY `x` (`x`,`y`),
  KEY `eventtime` (`eventtime`),
  KEY `assigned_bug` (`assigned_bug`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `ticket`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `ticket_reply`
-- 

CREATE TABLE `ticket_reply` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ticket` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `created` int(10) unsigned NOT NULL default '0',
  `body` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ticket` (`ticket`,`user`,`created`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `ticket_reply`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `title`
-- 

CREATE TABLE `title` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `time` int(10) unsigned NOT NULL default '0',
  `text` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user` (`user`)
) TYPE=MyISAM AUTO_INCREMENT=7 ;

-- 
-- Dumping data for table `title`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `triggerlog`
-- 

CREATE TABLE `triggerlog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `time` int(10) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `id1` int(10) unsigned NOT NULL default '0',
  `id2` int(10) unsigned NOT NULL default '0',
  `trigger` varchar(64) NOT NULL default '',
  `what` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `time` (`time`),
  KEY `id1` (`id1`),
  KEY `id2` (`id2`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `triggerlog`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `unit`
-- 

CREATE TABLE `unit` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `army` int(10) unsigned NOT NULL default '0',
  `building` int(10) unsigned NOT NULL default '0',
  `transport` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `amount` double NOT NULL default '0',
  `spell` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `army` (`army`),
  KEY `building` (`building`),
  KEY `type` (`type`),
  KEY `amount` (`amount`),
  KEY `transport` (`transport`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `unit`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `unittype`
-- 

CREATE TABLE `unittype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `descr` text NOT NULL,
  `orderval` int(10) unsigned NOT NULL default '0',
  `a` int(10) unsigned NOT NULL default '0',
  `v` int(10) unsigned NOT NULL default '0',
  `f` int(10) unsigned NOT NULL default '0',
  `r` int(10) unsigned NOT NULL default '0',
  `cooldown` int(10) unsigned NOT NULL default '0',
  `speed` float NOT NULL default '1',
  `pillage` int(10) unsigned NOT NULL default '0',
  `weight` float NOT NULL default '0',
  `cost_lumber` int(10) unsigned NOT NULL default '0',
  `cost_stone` int(10) unsigned NOT NULL default '0',
  `cost_food` int(10) unsigned NOT NULL default '0',
  `cost_metal` int(10) unsigned NOT NULL default '0',
  `cost_runes` int(10) unsigned NOT NULL default '0',
  `last` int(10) unsigned NOT NULL default '0',
  `buildtime` int(10) unsigned NOT NULL default '0',
  `gfx` varchar(64) NOT NULL default '',
  `buildingtype` int(10) unsigned NOT NULL default '0',
  `armytype` int(10) unsigned NOT NULL default '0',
  `flags` int(10) unsigned NOT NULL default '0',
  `treasure` tinytext NOT NULL,
  `req_tech_a` varchar(128) NOT NULL default '',
  `req_tech_v` varchar(128) NOT NULL default '',
  `req_geb` varchar(128) NOT NULL default '',
  `movable_flag` int(10) unsigned NOT NULL default '0',
  `eff_sail` float unsigned NOT NULL default '0',
  `eff_fightondeck` float unsigned NOT NULL default '0',
  `eff_capture` float unsigned NOT NULL default '0',
  `eff_siege` float NOT NULL default '0',
  `elite` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `f` (`f`,`r`),
  KEY `cooldown` (`cooldown`),
  KEY `movable_flag` (`movable_flag`),
  KEY `elite` (`elite`)
) TYPE=MyISAM AUTO_INCREMENT=59 ;

-- 
-- Dumping data for table `unittype`
-- 

INSERT INTO `unittype` VALUES (1, 'Miliz', 'Mit Spitzhacken und Heugabeln kann man zwar k�mpfen, aber wohl kaum einen Krieg gewinnen.', 2, 5, 10, 0, 0, 0, 301, 10, 2, 20, 0, 5, 5, 0, 30, 60, 'units/unit_1.png', 8, 4, 0, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (2, 'K�mpfer', 'mit einfachen Schwertern bewaffnet,\r\naber ohne R�stung, zwar beweglich aber auch nicht besonders widerstandsf�hig.\r\n\r\nben�tigt<br>\r\n<br>\r\ngeh�rtete Klingen: 1<br>\r\nLederr�stung: 1<br>\r\nKettenr�stung: 0<br>\r\nPlattenpanzer: 0<br>', 3, 15, 5, 0, 0, 0, 121, 10, 5, 5, 0, 5, 20, 0, 20, 60, 'units/unit_2.png', 8, 4, 0, '', '3:1', '27:1', '8:1', 3, 0, 0, 0, 0, 26);
INSERT INTO `unittype` VALUES (21, 'Ork', 'stinkenden gr�ne Fieslinge', 17, 40, 20, 0, 0, 0, 1, 25, 0, 0, 0, 0, 0, 0, 2, 0, 'units/ork.png', 0, 4, 2, '47:1', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (22, 'Huhn', 'Gaaaack ! gackgackgack', 18, 10, 10, 0, 0, 0, 241, 0, 10, 0, 0, 100, 0, 0, 20, 60, 'units/huhn.png', 9, 4, 2, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (3, 'SchwertKrieger', 'Mit einem geh�rteten Schwert und einer starken R�stung bereit sich in den Kampf zu st�rzen.\r\n\r\nben�tigt<br>\r\n<br>\r\ngeh�rtete Klingen: 3<br>\r\nLederr�stung: 2<br>\r\nKettenr�stung: 0<br>\r\nPlattenpanzer: 0<br>', 5, 20, 20, 0, 0, 0, 181, 10, 6, 5, 0, 5, 40, 0, 20, 60, 'units/unit_3.png', 8, 4, 0, '', '3:3', '27:2', '8:2', 3, 0, 0, 0, 0, 25);
INSERT INTO `unittype` VALUES (4, 'LanzenTr�ger', 'Nicht so schlagkr�ftig wie die SchwertKrieger,\r\nkann aber daf�r seine Gegner mit der Lanze auf Abstand halten.\r\n\r\nben�tigt<br>\r\n<br>\r\nLanze: 1<br>\r\nLederr�stung: 0<br>\r\nKettenr�stung: 1<br>\r\nPlattenpanzer: 0<br>', 7, 15, 75, 0, 0, 0, 241, 10, 11, 30, 0, 5, 30, 0, 25, 60, 'units/unit_4.png', 8, 4, 0, '', '38:1', '1:1,58<1', '8:5', 3, 0, 0, 0, 0, 27);
INSERT INTO `unittype` VALUES (5, 'Berserker', 'Mit zwei wuchtigen Streit�xten bewaffnet und von starken Panzerung gesch�tzt\r\nmetzeln diese furchteinfl�ssenden Barbaren alles weg was sich bewegt =)\r\n\r\nben�tigt<br>\r\n<br>\r\ngeh�rtete Klingen: 6<br>\r\nLederr�stung: 0<br>\r\nKettenr�stung: 2<br>\r\nPlattenpanzer: 0<br>', 9, 80, 30, 0, 0, 0, 121, 10, 13, 20, 0, 10, 60, 0, 40, 60, 'units/unit_5.png', 8, 4, 0, '', '3:6', '1:2,58<1', '8:8', 3, 0, 0, 0, 0, 28);
INSERT INTO `unittype` VALUES (6, 'Ritter', 'Berittene Krieger sind nicht so leicht zu Fall zu bringen, und metzeln ordentlich was weg\r\n\r\nben�tigt<br>\r\n<br>\r\ngeh�rtete Klingen: 10<br>\r\nLederr�stung: 0<br>\r\nKettenr�stung: 0<br>\r\nPlattenpanzer: 1<br>', 11, 60, 100, 0, 0, 0, 60, 10, 19, 20, 0, 10, 100, 0, 40, 100, 'units/unit_6.png', 8, 4, 0, '', '3:10', '2>1,58<0', '8:10', 3, 0, 0, 0, 0, 29);
INSERT INTO `unittype` VALUES (10, 'Ramme', 'Mit diesem m�chtigen Rammbock kann man Mauern und Geb�ude dem Erdboden gleichmachen.', 15, 15, 0, 0, 0, 0, 1, 0, 1000, 2000, 0, 0, 1000, 0, 2, 36000, 'units/ramme.png', 11, 1, 0, '', '48:0', '', '', 3, 0, 0, 0, 1, 0);
INSERT INTO `unittype` VALUES (11, 'Baummonster', 'Ein D�mon in Gestalt eines Baumes verbreitet Furcht und Schrecken.', 8, 200, 400, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 80, 3600, 'units/baum.png', 0, 4, 2, '44:1', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (12, 'EisenGolem', 'Ein Mechanisches Monster, geschaffen um Verw�stung und Tod in die Welt zu tragen.', 8, 400, 400, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 80, 3600, 'units/golem.png', 0, 4, 2, '47:1', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (13, 'SteinGolem', 'Ein steinernes sehr robustes Monster, geschaffen um Verw�stung und Tod in die Welt zu tragen.', 8, 500, 600, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 90, 3600, 'units/steingolem.png', 0, 4, 2, '45:1', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (14, 'Trollk�nig', 'der grausame, stinkende und menschenfressende Herrscher �ber alles �bel in der Zwischenwelt.', 10, 1000, 1000, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 200, 0, 'units/trollking.png', 0, 4, 2, '44:1,45:1,46:1,47:1', '', '', '', 3, 0, 0, 0, 1e-04, 0);
INSERT INTO `unittype` VALUES (15, 'Schatzkiste', 'Juwelen, Diamanten, Goldm�nzen und arghhh vieles vieles mehr *g*.', 11, 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 280, 0, 'units/schatztruhe.png', 0, 4, 2, '44:2,45:2,46:1,47:2', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (16, 'Turmzauberer', 'ein Turmzauberer ist ein alter weisshaariger Zauberer das Geschlecht ist in grauer Vorzeit vergessen denn er/sie/es besch�ftigt sich seit langer Zeit nur mit kraftvoller Magie, er/sie/es kann aus seinem Turm heraus wirken und das Turmmana verbrauchen', 12, 0, 0, 0, 0, 0, 0, 0, 10, 500, 500, 1000, 700, 2500, 2, 10800, 'units/turmzauberer.png', 2, 7, 0, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (17, 'GhostKnight', 'Durch Armee der Toten auferstandene Krieger', 99, 90, 150, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 2, 0, 'units/ghostknight.png', 0, 4, 0, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (18, 'Bug', 'Ups, da ist wohl irgendwo ein Bug aufgetreten. Wird zeit, da� den jemand pl�ttet.', 14, 10, 10, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 40, 0, 'units/bug.png', 0, 4, 2, '', '', '', '', 3, 0, 0, 0, 0.01, 0);
INSERT INTO `unittype` VALUES (19, 'Blob', 'der b�se BLoB owned euch alle voll KraSS W�g', 15, 300, 300, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 60, 0, 'units/blob.png', 0, 4, 2, '44:-1,45:-1,46:-1,47:-1', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (20, 'Puschel', 'wer kennt die s��en kleinen Tierchen auf SOM nicht *G*', 16, 5, 5, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 5, 0, 'units/pogo.png', 0, 4, 2, '44:1,45:1', '', '', '', 3, 0, 0, 0, 0.01, 0);
INSERT INTO `unittype` VALUES (23, 'Schlange', 'Giftiges Kriechtier', 19, 100, 20, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 80, 0, 'units/wurm.png', 0, 4, 2, '46:1', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (24, 'Squid', 'Schleimiges Seemonster', 20, 100, 50, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 666, 160, 60, 'units/squid.png', 25, 4, 2, '44:-1,45:-1,46:-1,47:-1', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (25, 'Schwertmeister', 'Durch langes Training k�nnen Schwertkrieger ihr Talent und ihre Technik im Umgang mit dem Schwert bis zur vervollkommnung steigern', 6, 40, 40, 0, 0, 0, 181, 10, 2, 10, 0, 10, 80, 0, 25, 0, 'units/unit_3-elite.png', 8, 4, 1, '', '3:3', '27:2', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (26, 'Elitek�mpfer', '', 4, 25, 15, 0, 0, 0, 121, 10, 5, 10, 0, 10, 40, 0, 30, 0, 'units/unit_2-elite.png', 8, 4, 1, '', '3:1', '27:1', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (27, 'Lanzentr�gerveteran', '', 8, 25, 115, 0, 0, 0, 241, 10, 8, 60, 0, 10, 60, 0, 35, 0, 'units/unit_4-elite.png', 8, 4, 1, '', '38:1', '1:1', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (28, 'Berserkerh�uptling', '', 10, 120, 50, 0, 0, 0, 121, 10, 9, 40, 0, 20, 120, 0, 50, 0, 'units/unit_5-elite.png', 8, 4, 1, '', '3:8', '1:2', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (29, 'Rittermeister', '', 12, 100, 155, 0, 0, 0, 61, 10, 17, 40, 0, 20, 200, 0, 50, 0, 'units/unit_6-elite.png', 8, 4, 1, '', '3:10', '2:1', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (30, 'Zentaur', '', 20, 150, 200, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 200, 0, 'units/zentaur.png', 0, 4, 2, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (31, 'H�llenhund', 'eine z�hnefletschende geifernde Bestie, der W�chter H�llenpforte', 15, 1500, 1000, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 40, 0, 'units/hellhound.png', 0, 4, 2, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (32, 'Gurke', 'eine klein gr�ne v�llig nutzlose Gurke', 20, 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 100, 0, 0, 5, 60, 'units/gurke.png', 9, 4, 2, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (33, 'HyperBlob', 'blub und �berall sind Bl�mchen...', 20, 100, 100, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 30, 0, 'hyperblob/blob-.png', 0, 4, 2, '44:-1,45:-1,46:-1,47:-1', '', '', '', 63, 0, 0, 0, 0.001, 0);
INSERT INTO `unittype` VALUES (34, 'Drip', 'Drips sind kleine drei�ugige Tierchen, die total knuffig sind, bis sie zubei�en', 5, 88, 111, 0, 0, 0, 1, 10, 0, 20, 0, 10, 100, 0, 20, 100, 'units/drip.png', 0, 4, 2, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (35, 'Nebellicht', 'ein kleines blau leuchtendes Licht mit vier seidigen kleinen Fl�geln', 50, 5, 50, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 2, 0, 'units/nebellicht.png', 0, 4, 2, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (38, 'Gnomsch�tze', 'HIHIHI', 60, 1, 1, 10, 5, 120, 1, 0, 0, 0, 0, 0, 0, 0, 5, 3600, 'units/gnome-range.png', 0, 4, 0, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (39, 'Einmaster', '', 15, 10, 40, 0, 0, 0, 240, 0, 30, 500, 50, 50, 500, 0, 30, 120, 'units/schiff-1.png', 47, 3, 0, '', '', '51>1', '', 48, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (40, 'Schlachtschiff', 'grosse schlachtschiffe', 18, 50, 100, 0, 0, 0, 480, 0, 100, 1000, 300, 500, 1000, 50, 80, 180, 'units/schiff-2.png', 47, 3, 0, '', '53>1', '51>3,52>1', '', 48, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (41, 'Einfacher Matrose', 'edit me', 20, 1, 30, 0, 0, 0, 181, 0, 5, 20, 5, 30, 15, 0, 10, 180, 'units/simplematrose.png', 8, 4, 0, '', '56>1', '54>1', '', 3, 0.5, 2, 0, 0, 0);
INSERT INTO `unittype` VALUES (42, 'Matrose', 'edit me', 21, 2, 35, 0, 0, 0, 241, 0, 8, 30, 10, 60, 30, 0, 15, 240, 'units/matrose.png', 8, 4, 0, '', '56>1', '54>2', '', 3, 0.7, 2.3, 0, 0, 0);
INSERT INTO `unittype` VALUES (43, 'Marinesoldat', 'edit me', 22, 15, 40, 0, 0, 0, 241, 0, 13, 30, 20, 60, 50, 0, 0, 240, 'units/marinematrose.png', 8, 4, 0, '', '56>2', '54>3', '', 3, 0, 4, 0.2, 0, 0);
INSERT INTO `unittype` VALUES (44, 'Enterer', 'edit me', 22, 15, 40, 0, 0, 0, 301, 0, 15, 30, 30, 100, 60, 0, 0, 300, 'units/entermatrose.png', 8, 4, 0, '', '55>1,56>3', '54>2', '', 3, 0, 1, 5, 0, 0);
INSERT INTO `unittype` VALUES (45, 'Katamaran', 'ein kleines schnelles Schiff', 17, 5, 25, 0, 0, 0, 60, 0, 20, 800, 0, 500, 1000, 1000, 20, 900, 'units/katamaran.png', 47, 3, 0, '', '', '', '', 56, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (47, 'MegaBlob', '', 20, 2300, 2300, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1000, 0, 'megablob/blob-.png', 0, 4, 2, '44:-1,45:-1,46:-1,47:-1', '', '', '', 63, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (48, 'Geist', '', 8, 90, 10, 0, 0, 0, 61, 0, 1, 0, 0, 0, 0, 0, 1, 0, 'units/ghost.png', 0, 4, 6, '', '', '', '', 15, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (49, 'Zombie', '', 6, 40, 100, 0, 0, 0, 1, 10, 10, 0, 0, 0, 0, 0, 25, 0, 'units/zombie.png', 0, 4, 6, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (50, 'Kamel', 'Kamel', 11, 0, 70, 0, 0, 0, 60, 0, 10, 20, 0, 50, 0, 0, 200, 100, 'units/kamel.png', 16, 5, 0, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (51, 'Arbeiter', 'Arbeiter', 11, 0, 10, 0, 0, 0, 120, 0, 10, 20, 0, 30, 10, 0, 10, 100, 'units/arbeiter.png', 7, 6, 0, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (52, 'Super Ghouly', 'Da Supaaa Ghouly!', 100, 23, 23, 0, 0, 0, 1, 0, 23, 0, 0, 0, 0, 0, 23, 0, 'units/ghouly.png', 0, 4, 2, '', '', '', '', 15, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (53, 'Kanone', 'Kanone', 0, 0, 1, 2500, 5, 300, 180, 0, 1000, 0, 0, 0, 2500, 0, 0, 3600, 'units/kanone0.png', 73, 1, 0, '', '', '', '', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (54, 'AmeisenK�nigin', '', 0, 1000, 1000, 0, 0, 0, 60, 200, 0, 1000, 1000, 0, 0, 0, 5000, 3600, 'units/ant-king.png', 0, 4, 2, '', '', '', '', 15, 0, 0, 0, 0.01, 0);
INSERT INTO `unittype` VALUES (55, 'Ameise', '', 0, 100, 100, 0, 0, 0, 60, 50, 0, 100, 100, 0, 0, 0, 500, 3600, 'units/ant.png', 0, 0, 2, '', '', '', '', 15, 0, 0, 0, 0.01, 0);
INSERT INTO `unittype` VALUES (56, 'Bogensch�tzen', '', 12, 30, 10, 30, 5, 60, 60, 10, 50, 100, 0, 10, 80, 0, 10, 240, 'units/unit_7.png', 8, 4, 0, '', '74>1,58<0', '27>1', '8:20', 3, 0, 0, 0, 0, 0);
INSERT INTO `unittype` VALUES (57, 'Katapult', '', 0, 0, 0, 30, 7, 60, 60, 0, 1000, 5000, 0, 0, 5000, 0, 2, 86400, 'units/katapult.png', 11, 1, 0, '', '48:0,58<0', '', '11:20', 3, 0, 0, 0, 1, 0);
INSERT INTO `unittype` VALUES (58, 'Astraldrache', 'im Licht bunt schimmernder Drache', 50, 1500, 1500, 1500, 3, 60, 60, 0, 100, 10, 0, 500, 50, 1000, 10, 86400, 'units/dragon_darian_rainbow_anim.gif', 0, 4, 2, '', '', '', '', 63, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `user`
-- 

CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `pass` varchar(255) NOT NULL default '',
  `mail` varchar(128) NOT NULL default '',
  `homepage` varchar(128) NOT NULL default '',
  `admin` tinyint(3) unsigned NOT NULL default '0',
  `logins` int(10) unsigned NOT NULL default '0',
  `lastlogin` int(10) unsigned NOT NULL default '0',
  `iplock` tinyint(3) unsigned NOT NULL default '1',
  `gfxpath` varchar(128) NOT NULL default '',
  `localstyles` tinyint(3) unsigned NOT NULL default '0',
  `guild` int(10) unsigned NOT NULL default '0',
  `guildstatus` int(10) unsigned NOT NULL default '1',
  `pop` double unsigned NOT NULL default '10',
  `maxpop` int(15) unsigned NOT NULL default '10',
  `lumber` double NOT NULL default '1500',
  `stone` double NOT NULL default '1500',
  `food` double NOT NULL default '800',
  `metal` double NOT NULL default '800',
  `runes` double NOT NULL default '0',
  `max_lumber` int(15) unsigned NOT NULL default '1500',
  `max_stone` int(15) unsigned NOT NULL default '1500',
  `max_food` int(15) unsigned NOT NULL default '800',
  `max_metal` int(15) unsigned NOT NULL default '800',
  `max_runes` int(15) unsigned NOT NULL default '0',
  `worker_lumber` float unsigned NOT NULL default '25',
  `worker_stone` float unsigned NOT NULL default '25',
  `worker_food` float unsigned NOT NULL default '25',
  `worker_metal` float unsigned NOT NULL default '25',
  `worker_runes` float unsigned NOT NULL default '0',
  `worker_repair` float unsigned NOT NULL default '0',
  `prod_runes` float NOT NULL default '0',
  `prod_lumber` float NOT NULL default '2.5',
  `prod_stone` float NOT NULL default '2.5',
  `prod_food` float NOT NULL default '2.5',
  `prod_metal` float NOT NULL default '2.5',
  `color` varchar(8) NOT NULL default '#00ff00',
  `mapmode` tinyint(3) unsigned NOT NULL default '1',
  `lastusedarmy` int(10) unsigned NOT NULL default '0',
  `guildpoints` int(8) NOT NULL default '0',
  `general_pts` int(11) NOT NULL default '0',
  `army_pts` int(11) NOT NULL default '0',
  `registered` int(10) unsigned NOT NULL default '0',
  `msgmode` tinyint(3) unsigned NOT NULL default '0',
  `flatview` tinyint(4) NOT NULL default '0',
  `race` tinyint(3) unsigned NOT NULL default '1',
  `flags` int(10) unsigned NOT NULL default '0',
  `moral` int(10) unsigned NOT NULL default '100',
  PRIMARY KEY  (`id`),
  KEY `guild` (`guild`),
  KEY `pop` (`pop`),
  KEY `general_pts` (`general_pts`,`army_pts`),
  KEY `army_pts` (`army_pts`),
  KEY `guildstatus` (`guildstatus`),
  KEY `race` (`race`)
) TYPE=MyISAM AUTO_INCREMENT=2240 ;

-- 
-- Dumping data for table `user`
-- 

INSERT INTO `user` VALUES (249, 'Admin', '', 'admin@localhost', '', 1, 12, 1147711194, 1, '', 0, 8, 18648630, 11666.9566666665, 151946, 428586.696837105, 432102.256758105, 581999.593333333, 582000, 625725, 582000, 582000, 582000, 582000, 640000, 30, 30, 20, 20, 0, 0, 0, 16821.4, 16821.4, 11197.6, 11147.6, '#000000', 1, 160074, 2147483647, 6033956, 206, 1108292583, 0, 1, 1, 98, 100);

-- --------------------------------------------------------

-- 
-- Table structure for table `userkills`
-- 

CREATE TABLE `userkills` (
  `user` int(10) unsigned NOT NULL default '0',
  `unittype` int(10) unsigned NOT NULL default '0',
  `kills` float NOT NULL default '0',
  PRIMARY KEY  (`user`,`unittype`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `userkills`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `userprofil`
-- 

CREATE TABLE `userprofil` (
  `id` int(10) unsigned NOT NULL default '0',
  `profil` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `userprofil`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `userrecord`
-- 

CREATE TABLE `userrecord` (
  `userid` int(10) unsigned NOT NULL default '0',
  `text` text NOT NULL,
  UNIQUE KEY `userid` (`userid`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `userrecord`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `uservalue`
-- 

CREATE TABLE `uservalue` (
  `user` int(10) unsigned NOT NULL default '0',
  `name` varchar(32) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`user`,`name`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `uservalue`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `waypoint`
-- 

CREATE TABLE `waypoint` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `army` int(10) unsigned NOT NULL default '0',
  `priority` int(10) unsigned NOT NULL default '0',
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `army` (`army`),
  KEY `x` (`x`),
  KEY `y` (`y`)
) TYPE=MyISAM AUTO_INCREMENT=13244 ;

-- 
-- Dumping data for table `waypoint`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `weather`
-- 

CREATE TABLE `weather` (
  `time` int(10) unsigned NOT NULL default '0',
  `weather` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`time`),
  KEY `weather` (`weather`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `weather`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `wonder`
-- 

CREATE TABLE `wonder` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `spelltype` int(10) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `wonder`
-- 

CREATE TABLE `calllog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(10) unsigned NOT NULL default '0',
  `user` int(10) unsigned NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '',
  `script` varchar(255) NOT NULL default '',
  `query` text NOT NULL,
  `post` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE `fire` (
`x` INT NOT NULL ,
`y` INT NOT NULL ,
`nextdamage` INT UNSIGNED NOT NULL DEFAULT '0',
`nextspread` INT UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY ( `x` , `y` ) ,
INDEX ( `nextdamage` , `nextspread` )
) TYPE = MYISAM ;
ALTER TABLE `fire` ADD `created` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `fire` ADD `putoutprob` SMALLINT UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `terraintype` ADD `flags` INT UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `buildingtype` ADD `fire_prob` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `terraintype` ADD `fire_prob` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `terraintype` ADD `fire_burnout_type` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `user` ADD `buildings_on_fire` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `fire` ADD INDEX ( `created` );

ALTER TABLE `buildingtype` ADD `collapse_prob` TINYINT NOT NULL; 
ALTER TABLE `buildingtype` CHANGE `collapse_prob` `collapse_prob` SMALLINT UNSIGNED NOT NULL ;

DROP TABLE `guild_right`;
