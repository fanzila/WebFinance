-- phpMyAdmin SQL Dump
-- version 3.5.0
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 22, 2012 at 02:08 PM
-- Server version: 5.5.24-1~dotdeb.1-log
-- PHP Version: 5.4.9-1~dotdeb.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `webfinance`
--

-- --------------------------------------------------------

--
-- Table structure for table `direct_debit`
--

CREATE TABLE IF NOT EXISTS `direct_debit` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=51 ;

-- --------------------------------------------------------

--
-- Table structure for table `direct_debit_row`
--

CREATE TABLE IF NOT EXISTS `direct_debit_row` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) unsigned NOT NULL,
  `debit_id` int(11) unsigned DEFAULT NULL,
  `state` enum('todo','done') NOT NULL DEFAULT 'todo',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=976 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_categories`
--

CREATE TABLE IF NOT EXISTS `webfinance_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `comment` text,
  `re` varchar(255) DEFAULT NULL,
  `plan_comptable` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#cefce',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_clients`
--

CREATE TABLE IF NOT EXISTS `webfinance_clients` (
  `id_client` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(200) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `tel` varchar(15) DEFAULT NULL,
  `fax` varchar(200) DEFAULT NULL,
  `web` varchar(100) DEFAULT 'http://',
  `addr1` varchar(255) DEFAULT NULL,
  `cp` varchar(10) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `addr2` varchar(255) DEFAULT NULL,
  `addr3` varchar(255) DEFAULT NULL,
  `pays` varchar(50) DEFAULT 'France',
  `vat_number` varchar(40) DEFAULT NULL,
  `has_unpaid` tinyint(1) DEFAULT NULL,
  `has_devis` tinyint(4) NOT NULL DEFAULT '0',
  `email` varchar(255) DEFAULT NULL,
  `siren` varchar(50) DEFAULT NULL,
  `id_company_type` int(11) NOT NULL DEFAULT '1',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `password` varchar(100) DEFAULT NULL,
  `rib_titulaire` varchar(24) NOT NULL,
  `rib_banque` varchar(24) NOT NULL,
  `rib_code_banque` varchar(5) NOT NULL,
  `rib_code_guichet` varchar(5) NOT NULL,
  `rib_code_compte` varchar(11) NOT NULL,
  `rib_code_cle` varchar(2) NOT NULL,
  `rcs` varchar(100) NOT NULL,
  `capital` varchar(100) NOT NULL,
  `id_mantis` int(11) NOT NULL,
  `language` varchar(3) NOT NULL DEFAULT 'fr',
  `id_business_entity` int(11),
  `contract_signer` varchar(100),
  `id_contract_signer_role` int(11),
  PRIMARY KEY (`id_client`),
  UNIQUE KEY `nom` (`nom`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=567 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_clients2users`
--

CREATE TABLE IF NOT EXISTS `webfinance_clients2users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_client` int(11) unsigned NOT NULL,
  `id_user` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client2user` (`id_client`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_company_types`
--

CREATE TABLE IF NOT EXISTS `webfinance_company_types` (
  `id_company_type` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_company_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_dns`
--

CREATE TABLE IF NOT EXISTS `webfinance_dns` (
  `id_dns` int(11) NOT NULL AUTO_INCREMENT,
  `id_domain` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `record_type` enum('A','CNAME','MX','NS','AAAA','TXT') DEFAULT 'CNAME',
  `value` varchar(50) DEFAULT 'nbi.fr',
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_dns`),
  KEY `id_domain` (`id_domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_domain`
--

CREATE TABLE IF NOT EXISTS `webfinance_domain` (
  `id_domain` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) DEFAULT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_client` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_domain`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_expenses`
--

CREATE TABLE IF NOT EXISTS `webfinance_expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `id_transaction` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(14,2) DEFAULT '0.00',
  `comment` text,
  `date_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `file` blob,
  `file_type` varchar(32) DEFAULT NULL,
  `file_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_transaction` (`id_transaction`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_files`
--

CREATE TABLE IF NOT EXISTS `webfinance_files` (
  `id_file` int(11) NOT NULL AUTO_INCREMENT,
  `fk_id` int(11) NOT NULL DEFAULT '0',
  `wf_type` enum('transaction','user','client','invoice') NOT NULL DEFAULT 'transaction',
  `file_type` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `file_name` varchar(128) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `file` mediumblob NOT NULL,
  PRIMARY KEY (`id_file`),
  KEY `fk_id` (`fk_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=84 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_invoices`
--

CREATE TABLE IF NOT EXISTS `webfinance_invoices` (
  `id_facture` int(11) NOT NULL AUTO_INCREMENT,
  `id_client` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_generated` datetime DEFAULT NULL,
  `date_sent` datetime DEFAULT NULL,
  `date_paiement` datetime DEFAULT NULL,
  `is_paye` tinyint(4) DEFAULT '0',
  `num_facture` varchar(15) DEFAULT NULL,
  `type_paiement` varchar(255) DEFAULT 'À réception de cette facture',
  `ref_contrat` varchar(255) DEFAULT NULL,
  `extra_top` blob,
  `facture_file` varchar(255) DEFAULT NULL,
  `accompte` decimal(10,4) DEFAULT '0.0000',
  `extra_bottom` blob,
  `date_facture` datetime DEFAULT NULL,
  `type_doc` enum('facture','devis') DEFAULT 'facture',
  `commentaire` blob,
  `id_type_presta` int(11) DEFAULT '1',
  `id_compte` int(11) NOT NULL DEFAULT '34',
  `is_envoye` tinyint(4) DEFAULT '0',
  `period` enum('none','monthly','quarterly','yearly') DEFAULT 'none',
  `periodic_next_deadline` date DEFAULT NULL,
  `tax` decimal(4,2) NOT NULL DEFAULT '19.60',
  `exchange_rate` decimal(8,2) NOT NULL DEFAULT '1.00',
  `delivery` enum('email','postal') DEFAULT 'email',
  `payment_method` enum('unknown','direct_debit','check','wire_transfer','paypal') DEFAULT NULL,
  `is_abandoned` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_facture`),
  UNIQUE KEY `num_facture` (`num_facture`),
  KEY `period` (`period`),
  KEY `id_client` (`id_client`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3204 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_invoice_rows`
--

CREATE TABLE IF NOT EXISTS `webfinance_invoice_rows` (
  `id_facture_ligne` int(11) NOT NULL AUTO_INCREMENT,
  `id_facture` int(11) NOT NULL DEFAULT '0',
  `description` blob,
  `qtt` decimal(6,2) DEFAULT NULL,
  `ordre` int(10) unsigned DEFAULT NULL,
  `prix_ht` decimal(20,5) DEFAULT NULL,
  PRIMARY KEY (`id_facture_ligne`),
  KEY `pfk_facture` (`id_facture`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14221 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_naf`
--

CREATE TABLE IF NOT EXISTS `webfinance_naf` (
  `id_naf` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(4) NOT NULL DEFAULT '',
  `nom` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_naf`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_payment`
--

CREATE TABLE IF NOT EXISTS `webfinance_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_invoice` int(11) NOT NULL DEFAULT '0',
  `email` varchar(255) DEFAULT NULL,
  `reference` varchar(255) NOT NULL DEFAULT '',
  `state` enum('nok','pending','cancel','deny','ok') NOT NULL DEFAULT 'nok',
  `amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(50) NOT NULL,
  `autorisation` varchar(64) NOT NULL DEFAULT '',
  `transaction_id` varchar(64) NOT NULL DEFAULT '',
  `payment_type` varchar(64) NOT NULL DEFAULT '',
  `card_type` varchar(64) DEFAULT '',
  `transaction_sole_id` varchar(64) NOT NULL DEFAULT '',
  `error_code` varchar(64) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id_payment_type` int(11) NOT NULL COMMENT '1 = paybox, 2 = paypal',
  `payment_date` varchar(255) NOT NULL,
  `payment_fee` decimal(14,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `id_invoice` (`id_invoice`),
  KEY `email` (`email`),
  KEY `amount` (`amount`),
  KEY `currency` (`currency`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=54 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_personne`
--

CREATE TABLE IF NOT EXISTS `webfinance_personne` (
  `id_personne` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `entreprise` varchar(30) DEFAULT NULL,
  `fonction` varchar(30) DEFAULT NULL,
  `tel` varchar(15) DEFAULT NULL,
  `tel_perso` varchar(15) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `fax` varchar(15) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `adresse1` varchar(255) DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `cp` varchar(10) DEFAULT NULL,
  `digicode` varchar(10) DEFAULT NULL,
  `station_metro` varchar(10) DEFAULT NULL,
  `date_anniversaire` varchar(10) DEFAULT NULL,
  `note` blob,
  `client` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id_personne`),
  KEY `pfk_client` (`client`),
  KEY `pfk_user` (`id_user`),
  KEY `email` (`email`),
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=77 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_pref`
--

CREATE TABLE IF NOT EXISTS `webfinance_pref` (
  `id_pref` int(11) NOT NULL AUTO_INCREMENT,
  `owner` int(11) NOT NULL DEFAULT '-1',
  `type_pref` varchar(100) DEFAULT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `value` blob,
  PRIMARY KEY (`id_pref`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=92 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_publication_method`
--

CREATE TABLE IF NOT EXISTS `webfinance_publication_method` (
  `id_publication_method` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `description` blob,
  PRIMARY KEY (`id_publication_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_roles`
--

CREATE TABLE IF NOT EXISTS `webfinance_roles` (
  `id_role` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `description` blob,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_suivi`
--

CREATE TABLE IF NOT EXISTS `webfinance_suivi` (
  `id_suivi` int(11) NOT NULL AUTO_INCREMENT,
  `type_suivi` tinyint(3) unsigned DEFAULT NULL,
  `id_objet` int(11) NOT NULL DEFAULT '0',
  `message` blob,
  `date_added` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `rappel` datetime DEFAULT NULL,
  `done` tinyint(3) unsigned DEFAULT '0',
  `done_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_suivi`),
  KEY `date_added` (`date_added`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=146 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_transactions`
--

CREATE TABLE IF NOT EXISTS `webfinance_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_account` int(11) NOT NULL DEFAULT '0',
  `id_category` int(11) NOT NULL DEFAULT '1',
  `text` varchar(255) NOT NULL DEFAULT '',
  `amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `exchange_rate` decimal(8,2) NOT NULL DEFAULT '1.00',
  `type` enum('real','prevision','asap') DEFAULT NULL,
  `document` varchar(128) DEFAULT '',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `date_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `comment` text,
  `file` mediumblob,
  `file_type` varchar(25) DEFAULT NULL,
  `file_name` varchar(50) DEFAULT NULL,
  `lettrage` tinyint(4) DEFAULT '0',
  `id_invoice` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_account` (`id_account`,`id_category`),
  KEY `id_category` (`id_category`),
  KEY `date` (`date`),
  KEY `id_invoice` (`id_invoice`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5430 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_transaction_invoice`
--

CREATE TABLE IF NOT EXISTS `webfinance_transaction_invoice` (
  `id_transaction` int(11) NOT NULL DEFAULT '0',
  `id_invoice` int(11) NOT NULL DEFAULT '0',
  `date_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_transaction`,`id_invoice`),
  KEY `id_invoice` (`id_invoice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_type_presta`
--

CREATE TABLE IF NOT EXISTS `webfinance_type_presta` (
  `id_type_presta` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_type_presta`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_type_suivi`
--

CREATE TABLE IF NOT EXISTS `webfinance_type_suivi` (
  `id_type_suivi` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `selectable` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id_type_suivi`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_type_tva`
--

CREATE TABLE IF NOT EXISTS `webfinance_type_tva` (
  `id_type_tva` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) DEFAULT NULL,
  `taux` decimal(5,3) DEFAULT NULL,
  PRIMARY KEY (`id_type_tva`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_userlog`
--

CREATE TABLE IF NOT EXISTS `webfinance_userlog` (
  `id_userlog` int(11) NOT NULL AUTO_INCREMENT,
  `log` blob,
  `date` datetime DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_facture` int(11) DEFAULT NULL,
  `id_client` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_userlog`),
  KEY `date` (`date`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15873 ;

-- --------------------------------------------------------

--
-- Table structure for table `webfinance_users`
--

CREATE TABLE IF NOT EXISTS `webfinance_users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `last_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `login` varchar(255) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `creation_date` datetime DEFAULT NULL,
  `role` varchar(64) DEFAULT NULL,
  `modification_date` datetime DEFAULT NULL,
  `prefs` blob,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `webfinance_invoices`
--
ALTER TABLE `webfinance_invoices`
  ADD CONSTRAINT `webfinance_invoices_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `webfinance_clients` (`id_client`) ON DELETE CASCADE;

--
-- Constraints for table `webfinance_invoice_rows`
--
ALTER TABLE `webfinance_invoice_rows`
  ADD CONSTRAINT `webfinance_invoice_rows_ibfk_1` FOREIGN KEY (`id_facture`) REFERENCES `webfinance_invoices` (`id_facture`) ON DELETE CASCADE;

--
-- Constraints for table `webfinance_personne`
--
ALTER TABLE `webfinance_personne`
  ADD CONSTRAINT `pfk_client` FOREIGN KEY (`client`) REFERENCES `webfinance_clients` (`id_client`) ON DELETE CASCADE;

--
-- Constraints for table `webfinance_transaction_invoice`
--
ALTER TABLE `webfinance_transaction_invoice`
  ADD CONSTRAINT `webfinance_transaction_invoice_ibfk_1` FOREIGN KEY (`id_transaction`) REFERENCES `webfinance_transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `webfinance_transaction_invoice_ibfk_2` FOREIGN KEY (`id_invoice`) REFERENCES `webfinance_invoices` (`id_facture`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE IF NOT EXISTS `business_entity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO business_entity (name) VALUES
  ('SARL'),
  ('EURL'),
  ('Particulier'),
  ('Auto Entreprise'),
  ('SAS'),
  ('SA'),
  ('Association')
  ;

CREATE TABLE IF NOT EXISTS `contract_signer_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO contract_signer_role (name) VALUES
  ('Gérant'),
  ('Président'),
  ('Directeur Général')
  ;
