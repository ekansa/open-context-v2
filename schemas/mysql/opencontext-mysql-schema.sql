-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 01, 2014 at 11:31 PM
-- Server version: 5.6.17
-- PHP Version: 5.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `oc_new_use`
--

-- --------------------------------------------------------

--
-- Table structure for table `imp_fieldlinks`
--

CREATE TABLE IF NOT EXISTS `imp_fieldlinks` (
  `hashID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `subjectField` int(11) NOT NULL,
  `predicateUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `objectField` int(11) NOT NULL,
  `objectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `objectType` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`hashID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `imp_fields`
--

CREATE TABLE IF NOT EXISTS `imp_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `fieldNumber` int(11) NOT NULL,
  `cellIndex` int(11) NOT NULL,
  `label` varchar(200) NOT NULL,
  `originalName` varchar(200) NOT NULL,
  `ignore` int(11) NOT NULL,
  `itemType` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `dataType` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `itemPrefix` varchar(100) NOT NULL,
  `classURI` varchar(200) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Table structure for table `imp_lookups`
--

CREATE TABLE IF NOT EXISTS `imp_lookups` (
  `hashID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `fieldNumber` int(11) NOT NULL,
  `rowNumber` int(11) NOT NULL,
  `itemType` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`hashID`),
  KEY `uuid` (`uuid`,`sourceID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `imp_sourcetabs`
--

CREATE TABLE IF NOT EXISTS `imp_sourcetabs` (
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `label` varchar(200) NOT NULL,
  `filename` varchar(200) NOT NULL,
  `rootUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `licenseURI` varchar(200) NOT NULL,
  `note` text NOT NULL,
  `created` datetime NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sourceID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `link_annotations`
--

CREATE TABLE IF NOT EXISTS `link_annotations` (
  `hashID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `subjectType` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `predicateURI` varchar(200) NOT NULL,
  `objectURI` varchar(200) NOT NULL,
  `creatorUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`hashID`),
  KEY `uuid` (`uuid`),
  KEY `project_id` (`projectUUID`),
  KEY `source_id` (`sourceID`),
  KEY `predicateURI` (`predicateURI`),
  KEY `objectURI` (`objectURI`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `link_dcmetadata`
--

CREATE TABLE IF NOT EXISTS `link_dcmetadata` (
  `term` varchar(200) NOT NULL,
  `type` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `uri` varchar(200) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`term`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `link_entities`
--

CREATE TABLE IF NOT EXISTS `link_entities` (
  `uri` varchar(200) NOT NULL,
  `label` varchar(200) NOT NULL,
  `altLabel` varchar(200) NOT NULL,
  `vocabURI` varchar(200) NOT NULL,
  `type` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uri`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `link_hierarchies`
--

CREATE TABLE IF NOT EXISTS `link_hierarchies` (
  `hashID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `parentURI` varchar(200) NOT NULL,
  `childURI` varchar(200) NOT NULL,
  `vocabURI` varchar(200) NOT NULL,
  `tree` varchar(200) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`hashID`),
  KEY `parentURI` (`parentURI`),
  KEY `childURI` (`childURI`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `oc_assertions`
--

CREATE TABLE IF NOT EXISTS `oc_assertions` (
  `hashID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `subjectType` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `obsNode` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `obsNum` int(11) NOT NULL,
  `sort` decimal(8,3) NOT NULL,
  `visibility` int(11) NOT NULL,
  `predicateUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `objectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `objectType` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `dataNum` double NOT NULL,
  `dataDate` datetime NOT NULL,
  `created` datetime NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`hashID`),
  KEY `uuid` (`uuid`),
  KEY `predicateUUID` (`predicateUUID`),
  KEY `objectUUID` (`objectUUID`),
  KEY `predobj` (`predicateUUID`,`objectUUID`),
  KEY `project_id` (`projectUUID`),
  KEY `source_id` (`sourceID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- --------------------------------------------------------

--
-- Table structure for table `oc_chronology`
--

CREATE TABLE IF NOT EXISTS `oc_chronology` (
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `startLC` double NOT NULL,
  `startC` double NOT NULL,
  `endC` double NOT NULL,
  `endLC` double NOT NULL,
  `note` text NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`),
  KEY `project_id` (`projectUUID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `oc_datacache`
--

CREATE TABLE IF NOT EXISTS `oc_datacache` (
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `compressed` longblob NOT NULL,
  PRIMARY KEY (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `oc_documents`
--

CREATE TABLE IF NOT EXISTS `oc_documents` (
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `content` longtext NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`),
  KEY `project_id` (`projectUUID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `oc_geodata`
--

CREATE TABLE IF NOT EXISTS `oc_geodata` (
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `itemType` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `path` varchar(200) NOT NULL,
  `ftype` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `specificity` int(11) NOT NULL,
  `note` text NOT NULL,
  `geoJSON` text NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`),
  KEY `path` (`path`),
  KEY `project_id` (`projectUUID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `oc_identifiers`
--

CREATE TABLE IF NOT EXISTS `oc_identifiers` (
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `itemType` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `stableID` varchar(200) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `stableType` varchar(10) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uuid-id` (`uuid`,`stableID`(50)),
  KEY `stableID` (`stableID`),
  KEY `uuid` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `oc_manifest`
--

CREATE TABLE IF NOT EXISTS `oc_manifest` (
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `itemType` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `repo` varchar(200) NOT NULL,
  `classURI` varchar(200) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `label` varchar(200) NOT NULL,
  `desPropUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `views` int(11) NOT NULL,
  `indexed` datetime NOT NULL,
  `vcontrol` datetime NOT NULL,
  `archived` datetime NOT NULL,
  `published` datetime NOT NULL,
  `revised` datetime NOT NULL,
  `recordUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`),
  KEY `project_id` (`projectUUID`),
  KEY `source_id` (`sourceID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- --------------------------------------------------------

--
-- Table structure for table `oc_mediafiles`
--

CREATE TABLE IF NOT EXISTS `oc_mediafiles` (
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `mediaType` varchar(50) NOT NULL,
  `mimeTypeURI` varchar(200) NOT NULL,
  `thumbMimeURI` varchar(200) NOT NULL,
  `thumbURI` varchar(200) NOT NULL,
  `previewMimeURI` varchar(200) NOT NULL,
  `previewURI` varchar(200) NOT NULL,
  `fullURI` varchar(200) NOT NULL,
  `filesize` decimal(15,3) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`),
  KEY `project_id` (`projectUUID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `oc_persons`
--

CREATE TABLE IF NOT EXISTS `oc_persons` (
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `foafType` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `combined_name` varchar(200) NOT NULL,
  `given_name` varchar(200) NOT NULL,
  `surname` varchar(200) NOT NULL,
  `mid_names` varchar(200) NOT NULL,
  `mid_init` varchar(5) NOT NULL,
  `initials` varchar(10) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`),
  KEY `project_id` (`projectUUID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `oc_predicates`
--

CREATE TABLE IF NOT EXISTS `oc_predicates` (
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `archaeoMLtype` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `dataType` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `label` varchar(200) NOT NULL,
  `sort` decimal(6,3) NOT NULL,
  `created` datetime NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`),
  KEY `project_id` (`projectUUID`),
  KEY `source_id` (`sourceID`),
  KEY `label` (`label`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `oc_projects`
--

CREATE TABLE IF NOT EXISTS `oc_projects` (
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `content` longtext NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `oc_strings`
--

CREATE TABLE IF NOT EXISTS `oc_strings` (
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `hashID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `content` text NOT NULL,
  PRIMARY KEY (`uuid`),
  UNIQUE KEY `hashID` (`hashID`),
  KEY `project_id` (`projectUUID`),
  KEY `source_id` (`sourceID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `oc_subjects`
--

CREATE TABLE IF NOT EXISTS `oc_subjects` (
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `hashID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `context` varchar(200) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `hashID` (`hashID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `oc_types`
--

CREATE TABLE IF NOT EXISTS `oc_types` (
  `uuid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `hashID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `projectUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sourceID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `predicateUUID` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `rank` decimal(8,3) NOT NULL,
  `label` varchar(200) NOT NULL,
  `contentUUID` varchar(200) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uuid`),
  UNIQUE KEY `hashID` (`hashID`),
  KEY `project_id` (`projectUUID`),
  KEY `source_id` (`sourceID`),
  KEY `predicateUUID` (`predicateUUID`),
  KEY `contentUUID` (`contentUUID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
