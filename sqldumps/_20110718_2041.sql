-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.1.54-1ubuntu4


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema testdb
--

CREATE DATABASE IF NOT EXISTS testdb;
USE testdb;

--
-- Definition of table `testdb`.`accesskey`
--

DROP TABLE IF EXISTS `testdb`.`accesskey`;
CREATE TABLE  `testdb`.`accesskey` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'key id',
  `passkey` mediumtext CHARACTER SET utf8 NOT NULL COMMENT 'key',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=latin1 COMMENT='list of available keys to be used by testers';

--
-- Dumping data for table `testdb`.`accesskey`
--

/*!40000 ALTER TABLE `accesskey` DISABLE KEYS */;
LOCK TABLES `accesskey` WRITE;
INSERT INTO `testdb`.`accesskey` VALUES  (67,'pqpz5uiz5pt774en4zxw'),
 (66,'amd6tcq91n8nqp03nz64'),
 (61,'9w16cxtkhnfk8gslhtke'),
 (62,'eo5n7wgaqis1d2kdegr3'),
 (63,'szxg1iczbenf9irtog2h'),
 (64,'so7kkr8qnklpvk2fd7h7'),
 (65,'yzqle3acr7cr8ey9gvfo'),
 (69,'kapo3gpu8tfpltv7rq7c'),
 (70,'hrafl479tlnhdzb3f0oi');
UNLOCK TABLES;
/*!40000 ALTER TABLE `accesskey` ENABLE KEYS */;


--
-- Definition of table `testdb`.`album`
--

DROP TABLE IF EXISTS `testdb`.`album`;
CREATE TABLE  `testdb`.`album` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'unique number used to identify albums',
  `owner_id` int(10) unsigned NOT NULL COMMENT 'id of the user that owns this album',
  `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'number of images in this album',
  `title` text NOT NULL COMMENT 'title of the album',
  `isResume` tinytext NOT NULL COMMENT 'is this album a resume',
  `time` datetime NOT NULL COMMENT 'time this album was created\n',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='holds info about albums';

--
-- Dumping data for table `testdb`.`album`
--

/*!40000 ALTER TABLE `album` DISABLE KEYS */;
LOCK TABLES `album` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `album` ENABLE KEYS */;


--
-- Definition of table `testdb`.`comment`
--

DROP TABLE IF EXISTS `testdb`.`comment`;
CREATE TABLE  `testdb`.`comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'comment id',
  `dest` tinytext NOT NULL COMMENT 'user name of recipient',
  `source` tinytext NOT NULL COMMENT 'user name of user that created the comment\n',
  `comment` text NOT NULL COMMENT 'the comment',
  `time` datetime NOT NULL COMMENT 'time of comment',
  `resume_album` int(10) unsigned NOT NULL COMMENT 'resume this comment belongs to',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='table that holds all comments posted on resme';

--
-- Dumping data for table `testdb`.`comment`
--

/*!40000 ALTER TABLE `comment` DISABLE KEYS */;
LOCK TABLES `comment` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `comment` ENABLE KEYS */;


--
-- Definition of table `testdb`.`notification`
--

DROP TABLE IF EXISTS `testdb`.`notification`;
CREATE TABLE  `testdb`.`notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'notification id\n',
  `data` blob NOT NULL COMMENT 'data contained in this notification\n',
  `created` datetime NOT NULL COMMENT 'time this notification created\n',
  `type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'type of notification\n',
  `from` text CHARACTER SET utf8 NOT NULL COMMENT 'username of sender\n',
  `to` text CHARACTER SET utf8 NOT NULL COMMENT 'username of receiver\n',
  `deleteonread` tinyint(1) NOT NULL COMMENT 'if true, delete on read',
  `deleteonexpire` tinyint(1) NOT NULL COMMENT 'if true, delete after set amount of time\n',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='table of notifications';

--
-- Dumping data for table `testdb`.`notification`
--

/*!40000 ALTER TABLE `notification` DISABLE KEYS */;
LOCK TABLES `notification` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `notification` ENABLE KEYS */;


--
-- Definition of table `testdb`.`resume`
--

DROP TABLE IF EXISTS `testdb`.`resume`;
CREATE TABLE  `testdb`.`resume` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'resume id',
  `pdf_path` text COMMENT 'filepath of pdf file',
  `type` int(10) unsigned NOT NULL COMMENT 'resume type. ex: intern, experienced',
  `title` text NOT NULL COMMENT 'title of resume',
  `owner_id` int(10) unsigned NOT NULL COMMENT 'id of owner',
  `album_id` int(10) unsigned NOT NULL COMMENT 'id of album',
  `created` datetime NOT NULL COMMENT 'time this resume was uploaded\n',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='information related to a resume';

--
-- Dumping data for table `testdb`.`resume`
--

/*!40000 ALTER TABLE `resume` DISABLE KEYS */;
LOCK TABLES `resume` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `resume` ENABLE KEYS */;


--
-- Definition of table `testdb`.`resumepage`
--

DROP TABLE IF EXISTS `testdb`.`resumepage`;
CREATE TABLE  `testdb`.`resumepage` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'resume page id',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'account id of the owner',
  `album_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'album this page is in',
  `page` int(11) NOT NULL DEFAULT '-1' COMMENT 'page number [0 indexed]',
  `pdf_name` text COMMENT 'name of the pdf file this page belongs to',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='represents a single page of a resume';

--
-- Dumping data for table `testdb`.`resumepage`
--

/*!40000 ALTER TABLE `resumepage` DISABLE KEYS */;
LOCK TABLES `resumepage` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `resumepage` ENABLE KEYS */;


--
-- Definition of table `testdb`.`session`
--

DROP TABLE IF EXISTS `testdb`.`session`;
CREATE TABLE  `testdb`.`session` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id of this row (not a session id)',
  `session_id` tinytext NOT NULL COMMENT 'the session id assigned to the session by php',
  `session_data` blob NOT NULL COMMENT 'the actual session data (needs to be encrypted)\n',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'time when this session expires',
  `is_active` tinyint(1) NOT NULL COMMENT 'this is set if the user this session belongs to is logged in',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='holds session info used by resme users';

--
-- Dumping data for table `testdb`.`session`
--

/*!40000 ALTER TABLE `session` DISABLE KEYS */;
LOCK TABLES `session` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `session` ENABLE KEYS */;


--
-- Definition of table `testdb`.`submission`
--

DROP TABLE IF EXISTS `testdb`.`submission`;
CREATE TABLE  `testdb`.`submission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'submission id\n',
  `data` mediumblob NOT NULL COMMENT 'data tied to this submission, such as a \nURL',
  `title` mediumtext NOT NULL COMMENT 'title of this submission ("crazy person blah blah")',
  `description` mediumtext NOT NULL COMMENT 'short summary of submission',
  `type` tinyint(3) unsigned NOT NULL COMMENT 'an integer that represents the data type',
  `category` tinyint(3) unsigned NOT NULL COMMENT 'a bitfield representing the categories this submission falls under\n',
  `upvotes` int(10) unsigned NOT NULL COMMENT 'number of users that liked this submission',
  `downvotes` int(10) unsigned NOT NULL COMMENT 'number of users that disliked this submission\n',
  `submitter` text NOT NULL COMMENT 'username of user who submitted ',
  `time` datetime NOT NULL COMMENT 'time this submission was created',
  `overflow` mediumblob NOT NULL COMMENT 'extra column used to hold miscellaneous data',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='contains user submitted news';

--
-- Dumping data for table `testdb`.`submission`
--

/*!40000 ALTER TABLE `submission` DISABLE KEYS */;
LOCK TABLES `submission` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `submission` ENABLE KEYS */;


--
-- Definition of table `testdb`.`tag`
--

DROP TABLE IF EXISTS `testdb`.`tag`;
CREATE TABLE  `testdb`.`tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'tag id',
  `resumepage` int(11) DEFAULT NULL COMMENT 'id of the resumepage this tag belongs to',
  `x` int(11) DEFAULT NULL COMMENT 'x pos of top left corner',
  `y` int(11) DEFAULT NULL COMMENT 'y pos of top left corner',
  `width` int(11) DEFAULT NULL COMMENT 'width of tag',
  `height` int(11) DEFAULT NULL COMMENT 'height of tag',
  `data` blob COMMENT 'data contained in the tag',
  `type` tinyint(3) unsigned NOT NULL COMMENT 'the data stored in the tag. 1:text 2:album id 3:youtube id 4:audio id 5:document id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `testdb`.`tag`
--

/*!40000 ALTER TABLE `tag` DISABLE KEYS */;
LOCK TABLES `tag` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tag` ENABLE KEYS */;


--
-- Definition of table `testdb`.`user`
--

DROP TABLE IF EXISTS `testdb`.`user`;
CREATE TABLE  `testdb`.`user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) DEFAULT NULL,
  `password` varchar(45) DEFAULT NULL COMMENT 'blowfish encrypted value',
  `email` varchar(45) DEFAULT NULL,
  `firstname` varchar(45) DEFAULT NULL,
  `lastname` varchar(45) DEFAULT NULL,
  `profile_album` varchar(45) DEFAULT NULL COMMENT 'path to the album that contains profile pictures',
  `current_profile_picture` text NOT NULL COMMENT 'filename of current image',
  `resume_album` varchar(45) DEFAULT NULL COMMENT 'path to the album that contains the current displayed resume',
  `resumepage` int(11) DEFAULT NULL COMMENT 'id of the resumepage to display in the profile',
  `mainphone` text NOT NULL,
  `cellphone` text NOT NULL,
  `officephone` text NOT NULL,
  `profs` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'professions',
  `perms` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'privacy settings',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `testdb`.`user`
--

/*!40000 ALTER TABLE `user` DISABLE KEYS */;
LOCK TABLES `user` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `user` ENABLE KEYS */;


--
-- Definition of table `testdb`.`usermeta`
--

DROP TABLE IF EXISTS `testdb`.`usermeta`;
CREATE TABLE  `testdb`.`usermeta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` text NOT NULL COMMENT 'username of user this metadata belongs too',
  `user_id` int(10) unsigned NOT NULL COMMENT 'user id of the user this meta data belongs to',
  `stack_data` mediumblob NOT NULL COMMENT 'serialized data representing ids of bookmarked users',
  `vote_data` mediumblob NOT NULL COMMENT 'serialized data representing submission votes',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='holds info about how a user interacts with resme';

--
-- Dumping data for table `testdb`.`usermeta`
--

/*!40000 ALTER TABLE `usermeta` DISABLE KEYS */;
LOCK TABLES `usermeta` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `usermeta` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
