-- MySQL dump 10.13  Distrib 5.1.41, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: biotorrents
-- ------------------------------------------------------
-- Server version	5.1.41-3ubuntu12.7

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
-- Table structure for table `licenses`
--

DROP TABLE IF EXISTS `licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `licenses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` varchar(256) NOT NULL,
  `url` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `licenses`
--

LOCK TABLES `licenses` WRITE;
/*!40000 ALTER TABLE `licenses` DISABLE KEYS */;
INSERT INTO `licenses` VALUES (1,'Public Domain','No Copyright','http://en.wikipedia.org/wiki/Public_domain'),(2,'CC By','Creative Commons Attribution','http://creativecommons.org/licenses/by/3.0/'),(3,'CC By-SA','Creative Commons Attribution Share Alike','http://creativecommons.org/licenses/by-sa/3.0/'),(4,'CC By-ND','Creative Commons Attribution No Derivatives','http://creativecommons.org/licenses/by-nd/3.0/'),(5,'CC By-NC','Creative Commons Attribution Non-Commercial','http://creativecommons.org/licenses/by-nc/3.0/'),(6,'CC By-NC-SA','Creative Commons Attribution Non-Commercial Share Alike','http://creativecommons.org/licenses/by-nc-sa/3.0/'),(7,'CC By-NC-ND','Creative Commons Attribution Non-Commercial No Derivatives','http://creativecommons.org/licenses/by-nc-nd/3.0/'),(8,'GPL','GNU General Public License','http://www.opensource.org/licenses/gpl-3.0.html'),(9,'LGPL','The GNU Lesser General Public License','http://www.opensource.org/licenses/lgpl-3.0.html'),(10,'BSD','The BSD License','http://www.opensource.org/licenses/bsd-license.php'),(11,'Other','See Torrent Description',''),(12,'CC0','Creative Commons Zero','http://creativecommons.org/publicdomain/zero/1.0/'),(13,'PDDL','Open Data Commons Public Domain Dedication and Licence','http://www.opendatacommons.org/licenses/pddl/1-0/');
/*!40000 ALTER TABLE `licenses` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-03-30 20:22:36
