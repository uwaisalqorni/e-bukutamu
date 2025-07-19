/*
SQLyog Ultimate v12.4.3 (64 bit)
MySQL - 10.4.34-MariaDB : Database - bukutamu
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`eabsensi` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */;

USE `eabsensi`;

/*Table structure for table `acara` */

DROP TABLE IF EXISTS `acara`;

CREATE TABLE `acara` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_acara` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/*Data for the table `acara` */

insert  into `acara`(`id`,`nama_acara`) values 
(2,'Rapat Koperasi Arofah RSIG'),
(3,'Rapat Sosialisasi RME');

/*Table structure for table `admin` */

DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/*Data for the table `admin` */

insert  into `admin`(`id`,`username`,`password`) values 
(1,'admin','$2y$10$VLomDfqCe8B0e8ZydSnrAugsd8DbbijN0SGLcdh89cIVMpn813yN2');

/*Table structure for table `buku_tamu` */

DROP TABLE IF EXISTS `buku_tamu`;

CREATE TABLE `buku_tamu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lokasi` int(11) NOT NULL,
  `id_acara` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nik` varchar(50) NOT NULL,
  `waktu_masuk` datetime DEFAULT current_timestamp(),
  `waktu_keluar` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_lokasi` (`id_lokasi`),
  KEY `id_acara` (`id_acara`),
  CONSTRAINT `buku_tamu_ibfk_1` FOREIGN KEY (`id_lokasi`) REFERENCES `lokasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `buku_tamu_ibfk_2` FOREIGN KEY (`id_acara`) REFERENCES `acara` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/*Data for the table `buku_tamu` */

insert  into `buku_tamu`(`id`,`id_lokasi`,`id_acara`,`nama`,`nik`,`waktu_masuk`,`waktu_keluar`) values 
(2,4,2,'Muhammad Nur Afandi','180051','2025-07-19 11:47:12','2025-07-19 12:10:43'),
(3,3,3,'Muhammad Nur Afandi','180051','2025-07-19 11:48:25','2025-07-19 12:11:13'),
(4,3,3,'Anita dewi','180051','2025-07-19 12:17:56','2025-07-19 12:18:16');

/*Table structure for table `lokasi` */

DROP TABLE IF EXISTS `lokasi`;

CREATE TABLE `lokasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lokasi` varchar(100) NOT NULL,
  `link_qr` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/*Data for the table `lokasi` */

insert  into `lokasi`(`id`,`nama_lokasi`,`link_qr`) values 
(3,'Ruang Rapat Yahya','http://192.168.0.191/bukutamu/index.php?id_lokasi=3'),
(4,'Aula Abdul Aziz','http://192.168.0.191/bukutamu/index.php?id_lokasi=4');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
