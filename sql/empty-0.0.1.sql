-- MySQL dump 10.13  Distrib 5.7.18, for Linux (x86_64)
--
-- Host: localhost    Database: glpi
-- ------------------------------------------------------
-- Server version	5.7.18-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `glpi_plugin_nagios_fields`
--

DROP TABLE IF EXISTS `glpi_plugin_nagios_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object_type` enum('HG','HT','SG','ST','CO','RO','US') COLLATE utf8_unicode_ci DEFAULT NULL,
  `field_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `field_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `field_flag` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `field_style` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_field_key` (`name`(255),object_type)
) ENGINE=MyISAM AUTO_INCREMENT=128 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_nagios_fields`
--

LOCK TABLES `glpi_plugin_nagios_fields` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_nagios_fields` DISABLE KEYS */;
INSERT INTO `glpi_plugin_nagios_fields` VALUES (46,'ST','LIST_ORDR','_O:PluginNagiosServiceGroup','servicegroups','ServiceGroup','M',NULL),(47,'ST','CHOICE','','is_volatile',NULL,'N',NULL),(48,'ST','SPECIAL','_O:PluginNagiosCommand','check_command',NULL,'N',NULL),(49,'ST','LIST','o:w:u:c','initial_state',NULL,'N',NULL),(50,'ST','NUMBER','','max_check_attempts',NULL,'N',NULL),(51,'ST','NUMBER','','check_interval',NULL,'N','size=4'),(52,'ST','NUMBER','','retry_interval',NULL,'N',NULL),(53,'ST','CHOICE','','active_checks_enabled',NULL,'N',NULL),(54,'ST','CHOICE','','passive_checks_enabled',NULL,'N',NULL),(55,'ST','LIST','_O:Calendar','check_period',NULL,'N',NULL),(56,'ST','CHOICE','','obsess_over_service',NULL,'N',NULL),(57,'ST','CHOICE','','check_freshness',NULL,'N',NULL),(58,'ST','TEXT','','freshness_threshold',NULL,'N',NULL),(59,'ST','LIST','_O:PluginNagiosCommand','event_handler',NULL,'N',NULL),(60,'ST','CHOICE','','event_handler_enabled',NULL,'N',NULL),(61,'ST','TEXT','','low_flap_threshold',NULL,'N',NULL),(62,'ST','TEXT','','high_flap_threshold',NULL,'N',NULL),(63,'ST','CHOICE','','flap_detection_enabled',NULL,'N',NULL),(64,'ST','LIST','o:w:c:u','flap_detection_options',NULL,'N',NULL),(65,'ST','CHOICE','','process_perf_data',NULL,'N',NULL),(66,'ST','CHOICE','','retain_status_information',NULL,'N',NULL),(67,'ST','CHOICE','','retain_nonstatus_information',NULL,'N',NULL),(68,'ST','NUMBER','','notification_interval',NULL,'N',NULL),(69,'ST','TEXT','','first_notification_delay',NULL,'N',NULL),(70,'ST','LIST','_O:Calendar','notification_period',NULL,'',NULL),(71,'ST','LIST','w:u:c:r:f:s','notification_options',NULL,'N',NULL),(72,'ST','CHOICE','','notifications_enabled',NULL,'N',NULL),(73,'ST','LIST_ORDR','_O:User','contacts',NULL,'M',NULL),(74,'ST','LIST_ORDR','_O:Group','contact_groups',NULL,'M',NULL),(75,'ST','CHOICE','','stalking_options',NULL,'N',NULL),(76,'ST','TEXT','','notes_url',NULL,'N',NULL),(77,'ST','TEXT','','action_url',NULL,'N',NULL),(78,'ST','FILE','','icon_image',NULL,'N',NULL),(79,'ST','TEXT','','icon_image_alt',NULL,'N',NULL),(80,'HG','LIST_ORDR','_O:PluginNagiosHostGroup','use','Hostgroups','M',NULL),(81,'HG','LIST_ORDR','_O:PluginNagiosHost','members','Host members','M',NULL),(82,'HG','LIST_ORDR','_O:PluginNagiosHostGroup','hostgroup_members',NULL,'M',NULL),(83,'HG','TEXT','','notes',NULL,'N',NULL),(84,'HG','TEXT','','action_url',NULL,'N',NULL),(85,'HT','LIST_ORDR','_O:PluginNagiosHost','use','HostTemplates','M',NULL),(86,'HT','LIST_ORDR','_O:PluginNagiosHost','parents',NULL,'M',NULL),(87,'HT','LIST_ORDR','_O:PluginNagiosHostGroup','hostgroups',NULL,'M',NULL),(88,'HT','SPECIAL','_O:PluginNagiosCommand','check_command',NULL,'N',NULL),(89,'HT','LIST','o:d:u','initial_state',NULL,'N',NULL),(90,'HT','NUMBER','','max_check_attempts',NULL,'N',NULL),(91,'HT','NUMBER','','check_interval',NULL,'N','size=4'),(92,'HT','NUMBER','','retry_interval',NULL,'N',NULL),(93,'HT','CHOICE','1','active_checks_enabled',NULL,'N',NULL),(94,'HT','CHOICE','1','passive_checks_enabled',NULL,'N',NULL),(95,'HT','LIST','_O:Calendar','check_period',NULL,'N',NULL),(96,'HT','CHOICE','','obsess_over_host',NULL,'N',NULL),(97,'HT','CHOICE','','check_freshness',NULL,'N',NULL),(98,'HT','NUMBER','','freshness_threshold',NULL,'N',NULL),(99,'HT','LIST','24x24','event_handler',NULL,'N',NULL),(100,'HT','CHOICE','','event_handler_enabled',NULL,'N',NULL),(101,'HT','NUMBER','','low_flap_threshold',NULL,'N',NULL),(102,'HT','NUMBER','','high_flap_threshold',NULL,'N',NULL),(103,'HT','CHOICE','','flap_detection_enabled',NULL,'N',NULL),(104,'HT','LIST_MULT','o:d:u','flap_detection_options',NULL,'N',NULL),(105,'HT','CHOICE','','process_perf_data',NULL,'N',NULL),(106,'HT','CHOICE','','retain_status_information',NULL,'N',NULL),(107,'HT','CHOICE','','retain_nonstatus_information',NULL,'N',NULL),(108,'HT','LIST_ORDR','_O:User','contacts',NULL,'M',NULL),(109,'HT','LIST_ORDR','_O:Group','contact_groups',NULL,'M',NULL),(110,'HT','NUMBER','','notification_interval',NULL,'N',NULL),(111,'HT','NUMBER','','first_notification_delay',NULL,'N',NULL),(112,'HT','LIST','_O:Calendar','notification_period',NULL,'',NULL),(113,'HT','LIST_MULT','d:u:r:f:s','notification_options',NULL,'N',NULL),(114,'HT','CHOICE','','notifications_enabled',NULL,'N',NULL),(115,'HT','LIST','o:d:u','stalking_options',NULL,'N',NULL),(116,'HT','TEXT','','notes_url',NULL,'N',NULL),(117,'HT','TEXT','','action_url',NULL,'N',NULL),(118,'HT','FILE','','icon_image',NULL,'N',NULL),(119,'HT','TEXT','','icon_image_alt',NULL,'N',NULL),(120,'HT','FILE','','vrml_image',NULL,'N',NULL),(121,'HT','FILE','','statusmap_image',NULL,'N',NULL),(122,'HT','TEXT','','2d_coords','Coordonnées 2D','N',NULL),(123,'HT','TEXT','','3d_coords',NULL,'N',NULL),(124,'ST','TEXT','','check_command_args','Check command arguments','N','size=50'),(125,'HT','SPECIAL','','address','Monitoring IP','N',NULL),(126,'ST','LIST_ORDR','_O:PluginNagiosService','use','ServiceTemplate','M',NULL),(127,'ST','LIST_ORDR','_O:PluginNagiosHost','host_name','','M',NULL),(128,'US','CHOICE','','host_notifications_enabled','','N',''),(129,'US','CHOICE','','service_notifications_enabled','','N',''),(130,'US','LIST','_O:Calendar','host_notification_period','','N',''),(131,'US','LIST','_O:Calendar','service_notification_period','','',''),(132,'US','LIST_MULT','d:u:r:f:s:n','host_notification_options','','N',''),(133,'US','LIST','w:u:c:r:f:s:n','service_notification_options','','N',''),(134,'US','LIST','_O:PluginNagiosCommand','host_notification_commands','','N',''),(135,'US','LIST_ORDR','_O:PluginNagiosCommand','service_notification_commands','','N',''); 

/*!40000 ALTER TABLE `glpi_plugin_nagios_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_nagios_objects`
--

DROP TABLE IF EXISTS `glpi_plugin_nagios_objects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_objects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) DEFAULT NULL,
  `parent_objects_id` int(11) DEFAULT NULL,
  `type` enum('HT','HG','ST','SG','CO','RO') COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alias` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `types_id` int(11) DEFAULT NULL,
  `is_model` tinyint(1) NOT NULL DEFAULT '0',
  `is_disabled` tinyint(1) DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `glpi_plugin_nagios_objects_idx1` (`entities_id`,`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_nagios_objects`
--

LOCK TABLES `glpi_plugin_nagios_objects` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_nagios_objects` DISABLE KEYS */;
/*!40000 ALTER TABLE `glpi_plugin_nagios_objects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_nagios_objectvalues`
--

DROP TABLE IF EXISTS `glpi_plugin_nagios_objectvalues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_objectvalues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_nagios_objects_id` int(11) NOT NULL,
  `plugin_nagios_fields_id` int(11) NOT NULL,
  `value` varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL,
  `flag` enum('m','i') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_unique_1` (`plugin_nagios_objects_id`,`plugin_nagios_fields_id`),
  UNIQUE KEY `glpi_plugin_nagios_objectvalues_uniq_1` (`plugin_nagios_objects_id`,`plugin_nagios_fields_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_nagios_objectvalues`
--

LOCK TABLES `glpi_plugin_nagios_objectvalues` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_nagios_objectvalues` DISABLE KEYS */;
/*!40000 ALTER TABLE `glpi_plugin_nagios_objectvalues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_nagios_links`
--

DROP TABLE IF EXISTS `glpi_plugin_nagios_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_links` (
  `plugin_nagios_objects_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT '0',
  `owner_id` int(11) DEFAULT '0',
  `itemtype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `items_id` int(11) NOT NULL,
  PRIMARY KEY (`plugin_nagios_objects_id`,`itemtype`,`items_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_nagios_links`
--

LOCK TABLES `glpi_plugin_nagios_links` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_nagios_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `glpi_plugin_nagios_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_nagios_commands`
--

DROP TABLE IF EXISTS `glpi_plugin_nagios_commands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_commands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `line` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_nagios_commands`
--

LOCK TABLES `glpi_plugin_nagios_commands` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_nagios_commands` DISABLE KEYS */;
/*!40000 ALTER TABLE `glpi_plugin_nagios_commands` ENABLE KEYS */;
UNLOCK TABLES;



DROP TABLE IF EXISTS `glpi_plugin_nagios_calendars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_calendars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `calendars_id` int(11) DEFAULT NULL,
  `alias` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `extras` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;



DROP TABLE IF EXISTS `glpi_plugin_nagios_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) DEFAULT NULL,
  `host_notifications_enabled` char(1) DEFAULT 0,
  `service_notifications_enabled` char(1) DEFAULT 0,
  `host_notification_period`  int(11) DEFAULT NULL,
  `service_notification_period` int(11) DEFAULT NULL,
  `host_notification_options` varchar(20) DEFAULT NULL,
  `service_notification_options` varchar(20) DEFAULT NULL, 
  `host_notification_commands` int(11)  DEFAULT NULL,
  `service_notification_commands` int(11)  DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;







--
-- Table structure for table `glpi_plugin_nagios_satellites`
--

DROP TABLE IF EXISTS `glpi_plugin_nagios_satellites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_satellites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `desc` varchar(255) DEFAULT NULL,
  `ipaddr` varchar(14) DEFAULT NULL,
  `before_scripts_id` int(11) DEFAULT NULL,
  `after_scripts_id` int(11) DEFAULT NULL,
  `file_nagios` longtext,
  `file_resource` longtext,
  `args` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_nagios_satellites`
--

LOCK TABLES `glpi_plugin_nagios_satellites` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_nagios_satellites` DISABLE KEYS */;
/*!40000 ALTER TABLE `glpi_plugin_nagios_satellites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_nagios_scripts`
--

DROP TABLE IF EXISTS `glpi_plugin_nagios_scripts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_scripts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `command` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `args` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_nagios_scripts`
--

LOCK TABLES `glpi_plugin_nagios_scripts` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_nagios_scripts` DISABLE KEYS */;
/*!40000 ALTER TABLE `glpi_plugin_nagios_scripts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_nagios_entities`
--

DROP TABLE IF EXISTS `glpi_plugin_nagios_entities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_entities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) DEFAULT NULL,
  `plugin_nagios_satellites_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_nagios_entities1` (`entities_id`,`plugin_nagios_satellites_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_nagios_entities`
--

LOCK TABLES `glpi_plugin_nagios_entities` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_nagios_entities` DISABLE KEYS */;
/*!40000 ALTER TABLE `glpi_plugin_nagios_entities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_nagios_profiles`
--

DROP TABLE IF EXISTS `glpi_plugin_nagios_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_profiles` (
  `id` int(11) NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_profiles (id)',
  `right` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_nagios_profiles`
--

LOCK TABLES `glpi_plugin_nagios_profiles` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_nagios_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `glpi_plugin_nagios_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_nagios_configs`
--

DROP TABLE IF EXISTS `glpi_plugin_nagios_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_nagios_configs`
--

LOCK TABLES `glpi_plugin_nagios_configs` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_nagios_configs` DISABLE KEYS */;
/*!40000 ALTER TABLE `glpi_plugin_nagios_configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_nagios_macros`
--

DROP TABLE IF EXISTS `glpi_plugin_nagios_macros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_macros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_nagios_objects_id` int(11) DEFAULT NULL,
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_secure` int(1) DEFAULT NULL,
  `is_global` char(1) COLLATE utf8_unicode_ci DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_nagios_macro1` (`plugin_nagios_objects_id`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_nagios_macros`
--

LOCK TABLES `glpi_plugin_nagios_macros` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_nagios_macros` DISABLE KEYS */;
/*!40000 ALTER TABLE `glpi_plugin_nagios_macros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_nagios_objectlinks`
--

DROP TABLE IF EXISTS `glpi_plugin_nagios_objectlinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glpi_plugin_nagios_objectlinks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_nagios_objects_id` int(11) NOT NULL,
  `items_id` int(11) NOT NULL,
  `itemtype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_unique_objectlinks_1` (`plugin_nagios_objects_id`,`itemtype`,`items_id`),
  UNIQUE KEY `glpi_plugin_nagios_objectlinks_index1` (`itemtype`,`items_id`,`plugin_nagios_objects_id`),
  KEY `glpi_plugin_nagios_objectlinks_index2` (`itemtype`,`items_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


create view glpi_plugin_nagios_services_view as (select * from glpi_plugin_nagios_objects where type='ST') ;
create view glpi_plugin_nagios_servicegroups_view as (select * from glpi_plugin_nagios_objects where type='SG') ;
create view glpi_plugin_nagios_hosts_view as (select * from glpi_plugin_nagios_objects where type='HT') ;
create view glpi_plugin_nagios_hostgroups_view as (select * from glpi_plugin_nagios_objects where type='HG') ;
create view glpi_plugin_nagios_roles_view as (select * from glpi_plugin_nagios_objects where type='RO') ;

--
-- Dumping data for table `glpi_plugin_nagios_objectlinks`
--

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-05-09 14:50:59
