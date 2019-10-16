-- MySQL dump 10.13  Distrib 5.7.9, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: laravel
-- ------------------------------------------------------
-- Server version	5.6.29-log

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
-- Table structure for table `admin_menu`
--

DROP TABLE IF EXISTS `admin_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `project` varchar(64) NOT NULL DEFAULT 'default' COMMENT '项目名称',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '菜单名',
  `english_name` varchar(64) NOT NULL DEFAULT '' COMMENT '菜单英文名，多个单词中间以-分割\n顶部菜单以top为前缀\n侧边菜单以side为前缀\n例：top-system-setting,side-system-setting,system-setting',
  `target` varchar(32) NOT NULL DEFAULT 'navtab' COMMENT '目标窗口类型',
  `url` varchar(128) NOT NULL DEFAULT 'javascript:;' COMMENT '跳转URL',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序值',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '当前顶部菜单是否为激活状态\n0：否\n1：是',
  `fresh` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '重复打开页面时，是否重新加载\n0：否\n1：是',
  `display` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示\n0：不显示\n1：显示',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='后台管理系统——菜单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_menu`
--

LOCK TABLES `admin_menu` WRITE;
/*!40000 ALTER TABLE `admin_menu` DISABLE KEYS */;
INSERT INTO `admin_menu` VALUES (1,0,'default','系统设置','top-system-setting','navtab','javascript:;',100,0,1,1,1492153824,1492153824),(2,1,'default','系统设置','side-system-setting','navtab','javascript:;',0,0,1,1,1492153824,1492153824),(3,2,'default','用户列表','user-list','navtab','javascript:;',0,0,1,1,1492153824,1492153824),(4,2,'default','角色权限','role-permission','navtab','javascript:;',0,0,1,1,1492153824,1492153824),(5,0,'default','网站设置','top-website-setting','navtab','javascript:;',0,1,1,1,1492153824,1492153824),(6,5,'default','网站设置','side-website-setting','navtab','javascript:;',0,0,1,1,1492153824,1492153824),(7,6,'default','网站信息','website-information','navtab','javascript:;',0,0,1,1,1492153824,1492153824);
/*!40000 ALTER TABLE `admin_menu` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-05-26 19:02:06
