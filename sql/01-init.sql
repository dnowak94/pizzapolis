-- phpMyAdmin SQL Dump
-- version 3.4.7.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 24, 2014 at 08:34 PM
-- Server version: 5.1.49
-- PHP Version: 5.3.3-7+squeeze18

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `pizzapolis`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblAdresse`
--

CREATE TABLE IF NOT EXISTS `tblAdresse` (
  `id_adresse` int(10) NOT NULL AUTO_INCREMENT,
  `dtVorname` varchar(255) NOT NULL,
  `dtNachname` varchar(255) NOT NULL,
  `dtAdresse` varchar(255) NOT NULL,
  `dtPostleitzahl` varchar(255) NOT NULL,
  `dtOrtschaft` varchar(255) NOT NULL,
  `dtStandard` int(1) DEFAULT NULL,
  `fi_kunde` int(10) NOT NULL,
  PRIMARY KEY (`id_adresse`),
  KEY `fi_kunde` (`fi_kunde`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Table structure for table `tblBenutzer`
--

CREATE TABLE IF NOT EXISTS `tblBenutzer` (
  `id_benutzer` int(10) NOT NULL AUTO_INCREMENT,
  `dtVorname` varchar(255) NOT NULL,
  `dtNachname` varchar(255) NOT NULL,
  `dtUsername` varchar(255) NOT NULL,
  `dtPasswort` varchar(255) NOT NULL,
  `dtTelefonnummer` varchar(255) NOT NULL,
  `dtE-Mail` varchar(255) NOT NULL,
  `istAktiviert` int(1) NOT NULL,
  `dtAktivierungscode` varchar(255) NOT NULL,
  `istRestaurant` int(1) NOT NULL,
  `istAdmin` int(1) NOT NULL,
  PRIMARY KEY (`id_benutzer`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `tblBenutzer`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblBestehen_aus`
--

CREATE TABLE IF NOT EXISTS `tblBestehen_aus` (
  `fi_bestellung` int(10) NOT NULL,
  `fi_gericht` int(10) NOT NULL,
  `dtQuantitaet` int(10) NOT NULL,
  PRIMARY KEY (`fi_bestellung`,`fi_gericht`),
  KEY `fi_gericht` (`fi_gericht`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- --------------------------------------------------------

--
-- Table structure for table `tblBestellung`
--

CREATE TABLE IF NOT EXISTS `tblBestellung` (
  `id_bestellung` int(10) NOT NULL AUTO_INCREMENT,
  `dtLieferdatum` date NOT NULL,
  `dtLieferzeit` varchar(255) NOT NULL,
  `dtLieferart` int(1) NOT NULL,
  `fi_adresse` int(10) DEFAULT NULL,
  `fi_kunde` int(10) NOT NULL,
  `dtStatus` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_bestellung`),
  KEY `fi_user` (`fi_kunde`),
  KEY `fi_adresse` (`fi_adresse`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `tblBestellungsadresse`
--

CREATE TABLE IF NOT EXISTS `tblBestellungsadresse` (
  `id_bestellungsadresse` int(10) NOT NULL AUTO_INCREMENT,
  `dtVorname` varchar(255) NOT NULL,
  `dtNachname` varchar(255) NOT NULL,
  `dtAdresse` varchar(255) NOT NULL,
  `dtPostleitzahl` varchar(255) NOT NULL,
  `dtOrtschaft` varchar(255) NOT NULL,
  `fi_kunde` int(10) NOT NULL,
  `fi_adresse` int(10) DEFAULT NULL,
  PRIMARY KEY (`id_bestellungsadresse`),
  KEY `fi_kunde` (`fi_kunde`),
  KEY `fi_adresse` (`fi_adresse`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `tblGericht`
--

CREATE TABLE IF NOT EXISTS `tblGericht` (
  `id_gericht` int(10) NOT NULL AUTO_INCREMENT,
  `dtBezeichnung` varchar(255) NOT NULL,
  `dtZutaten` varchar(255) NOT NULL,
  `dtPreis` float NOT NULL,
  `dtFoto` varchar(255) NOT NULL,
  `dtZeitstempel` varchar(255) NOT NULL,
  `fi_kategorie` int(11) NOT NULL,
  PRIMARY KEY (`id_gericht`),
  KEY `fi_kategorie` (`fi_kategorie`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;



-- --------------------------------------------------------

--
-- Table structure for table `tblKategorie`
--

CREATE TABLE IF NOT EXISTS `tblKategorie` (
  `id_kategorie` int(10) NOT NULL AUTO_INCREMENT,
  `dtBezeichnung` varchar(255) NOT NULL,
  PRIMARY KEY (`id_kategorie`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;



-- --------------------------------------------------------

--
-- Table structure for table `tblKraftstoffkosten`
--

CREATE TABLE IF NOT EXISTS `tblKraftstoffkosten` (
  `id_kraftstoffkosten` int(11) NOT NULL AUTO_INCREMENT,
  `dtDatum` date NOT NULL,
  `dtLiter` int(11) NOT NULL,
  `dtKosten` int(11) NOT NULL,
  `dtAktuellerPreis` float NOT NULL,
  `fi_verwalter` int(10) NOT NULL,
  PRIMARY KEY (`id_kraftstoffkosten`),
  KEY `fi_verwalter` (`fi_verwalter`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;


-- --------------------------------------------------------

--
-- Table structure for table `tblLieferzeit`
--

CREATE TABLE IF NOT EXISTS `tblLieferzeit` (
  `id_lieferzeit` int(11) NOT NULL AUTO_INCREMENT,
  `dtTag_von` varchar(3) NOT NULL,
  `dtTag_bis` varchar(3) NOT NULL,
  `dtZeit_von` varchar(5) NOT NULL,
  `dtZeit_bis` varchar(5) NOT NULL,
  `fi_verwalter` int(10) NOT NULL,
  PRIMARY KEY (`id_lieferzeit`),
  KEY `fi_verwalter` (`fi_verwalter`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;


-- --------------------------------------------------------

--
-- Table structure for table `tblNews`
--

CREATE TABLE IF NOT EXISTS `tblNews` (
  `id_news` int(10) NOT NULL AUTO_INCREMENT,
  `dtTitel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dtInhalt` longtext COLLATE utf8_unicode_ci NOT NULL,
  `dtZeitstempel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `istSichtbar` int(1) NOT NULL,
  `fi_ersteller` int(10) NOT NULL,
  PRIMARY KEY (`id_news`),
  KEY `fi_ersteller` (`fi_ersteller`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--


-- --------------------------------------------------------

--
-- Table structure for table `tblRechnung`
--

CREATE TABLE IF NOT EXISTS `tblRechnung` (
  `id_rechnung` int(10) NOT NULL AUTO_INCREMENT,
  `fi_bestellung` int(10) NOT NULL,
  PRIMARY KEY (`id_rechnung`),
  KEY `fi_bestellung` (`fi_bestellung`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `tblRechnung`
--


-- --------------------------------------------------------

--
-- Table structure for table `tblSeite`
--

CREATE TABLE IF NOT EXISTS `tblSeite` (
  `id_seite` int(10) NOT NULL AUTO_INCREMENT,
  `dtTitel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dtInhalt` longtext COLLATE utf8_unicode_ci NOT NULL,
  `dtZeitstempel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `istSichtbar` int(1) NOT NULL,
  `istMenu` int(1) NOT NULL,
  `fi_ersteller` int(10) NOT NULL,
  PRIMARY KEY (`id_seite`),
  KEY `fi_ersteller` (`fi_ersteller`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;


--
-- Constraints for table `tblAdresse`
--
ALTER TABLE `tblAdresse`
  ADD CONSTRAINT `tblAdresse_ibfk_2` FOREIGN KEY (`fi_kunde`) REFERENCES `tblBenutzer` (`id_benutzer`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tblBestehen_aus`
--
ALTER TABLE `tblBestehen_aus`
  ADD CONSTRAINT `tblBestehen_aus_ibfk_1` FOREIGN KEY (`fi_bestellung`) REFERENCES `tblBestellung` (`id_bestellung`),
  ADD CONSTRAINT `tblBestehen_aus_ibfk_2` FOREIGN KEY (`fi_gericht`) REFERENCES `tblGericht` (`id_gericht`);

--
-- Constraints for table `tblBestellung`
--
ALTER TABLE `tblBestellung`
  ADD CONSTRAINT `tblBestellung_ibfk_3` FOREIGN KEY (`fi_adresse`) REFERENCES `tblBestellungsadresse` (`id_bestellungsadresse`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `tblBestellung_ibfk_4` FOREIGN KEY (`fi_kunde`) REFERENCES `tblBenutzer` (`id_benutzer`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `tblBestellungsadresse`
--
ALTER TABLE `tblBestellungsadresse`
  ADD CONSTRAINT `tblBestellungsadresse_ibfk_2` FOREIGN KEY (`fi_adresse`) REFERENCES `tblAdresse` (`id_adresse`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `tblGericht`
--
ALTER TABLE `tblGericht`
  ADD CONSTRAINT `tblGericht_ibfk_3` FOREIGN KEY (`fi_kategorie`) REFERENCES `tblKategorie` (`id_kategorie`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `tblNews`
--
ALTER TABLE `tblNews`
  ADD CONSTRAINT `tblNews_ibfk_1` FOREIGN KEY (`fi_ersteller`) REFERENCES `tblBenutzer` (`id_benutzer`);

--
-- Constraints for table `tblSeite`
--
ALTER TABLE `tblSeite`
  ADD CONSTRAINT `tblSeite_ibfk_1` FOREIGN KEY (`fi_ersteller`) REFERENCES `tblBenutzer` (`id_benutzer`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
