-- phpMyAdmin SQL Dump
-- version 2.8.0.2-Debian-4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 06, 2006 at 04:36 PM
-- Server version: 5.0.19
-- PHP Version: 4.4.2-1+b1
--
--

-- --------------------------------------------------------

--
-- Table structure for table `webcash_accounts`
--

DROP TABLE IF EXISTS `webcash_accounts`;
CREATE TABLE `webcash_accounts` (
  `id` int(11) NOT NULL auto_increment,
  `account_name` varchar(128) NOT NULL,
  `id_bank` int(11) NOT NULL default '0',
  `id_user` int(11) default '0',
  `account` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `currency` varchar(64) NOT NULL default 'EUR',
  `country` varchar(128) NOT NULL,
  `type` varchar(64) default 'compte commercial',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `account_name` (`account_name`),
  KEY `id_bank` (`id_bank`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `webcash_accounts`
--

INSERT INTO `webcash_accounts` (`id`, `account_name`, `id_bank`, `id_user`, `account`, `comment`, `currency`, `country`, `type`) VALUES (12, 'My first account', 26, 0, '666-007-000', 'no comment', 'EUR', 'AM', 'Credit card'),
(14, 'Soci&eacute;t&eacute;', 26, 0, '666-007-000', '', 'EUR', 'FR', 'Credit card');

-- --------------------------------------------------------

--
-- Table structure for table `webcash_banks`
--

DROP TABLE IF EXISTS `webcash_banks`;
CREATE TABLE `webcash_banks` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `short_name` varchar(64) default NULL,
  `phone` varchar(64) default '00.00.00.00',
  `mail` varchar(64) default 'example@example.com',
  `comment` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `short_name` (`short_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=30 ;

--
-- Dumping data for table `webcash_banks`
--

INSERT INTO `webcash_banks` (`id`, `name`, `short_name`, `phone`, `mail`, `comment`) VALUES (26, 'Banque populaire', 'banque_populaire', '111121212121', 'worldbank@worldbank.com', 'no comment'),
(27, 'World Bank', 'W.B.', '666.007', 'worldbank@worldbank.com', ''),
(28, 'World Bank2', 'W.B.2', '666.007', 'worldbank@worldbank.com', 'no comment');

-- --------------------------------------------------------

--
-- Table structure for table `webcash_categories`
--

DROP TABLE IF EXISTS `webcash_categories`;
CREATE TABLE `webcash_categories` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `comment` text,
  `color` varchar(32) default NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `webcash_categories`
--

INSERT INTO `webcash_categories` (`id`, `name`, `comment`, `color`) VALUES (2, 'TestCAteg', 'commentaire', 'SkyBlue'),
(3, 'Mat&eacute;riel', 'qgzergae', 'pink'),
(4, 'The category', 'no comment', 'blue'),
(5, 'Salaire', 'no comment', 'gray'),
(7, 'Serveur', '', 'cyan'),
(8, 'Impot', '', 'aquamarine1'),
(9, 'Another category', '', 'plum');

-- --------------------------------------------------------

--
-- Table structure for table `webcash_clients`
--

DROP TABLE IF EXISTS `webcash_clients`;
CREATE TABLE `webcash_clients` (
  `id_client` int(11) NOT NULL auto_increment,
  `nom` varchar(200) default NULL,
  `date_created` datetime default NULL,
  `tel` varchar(15) default NULL,
  `fax` varchar(200) default NULL,
  `addr1` varchar(255) default NULL,
  `cp` varchar(10) default NULL,
  `ville` varchar(100) default NULL,
  `addr2` varchar(255) default NULL,
  `addr3` varchar(255) default NULL,
  `pays` varchar(50) default 'France',
  `vat_number` varchar(40) default NULL,
  `has_unpaid` tinyint(1) default NULL,
  `state` enum('client','prospect','archive','fournisseur') default NULL,
  `ca_total_ht` decimal(20,4) default NULL,
  `ca_total_ht_year` decimal(20,4) default NULL,
  `has_devis` tinyint(4) NOT NULL default '0',
  `email` varchar(255) default NULL,
  `siren` varchar(50) default NULL,
  `total_du_ht` decimal(20,4) default NULL,
  `id_type_entreprise` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id_client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `webcash_clients`
--

INSERT INTO `webcash_clients` (`id_client`, `nom`, `date_created`, `tel`, `fax`, `addr1`, `cp`, `ville`, `addr2`, `addr3`, `pays`, `vat_number`, `has_unpaid`, `state`, `ca_total_ht`, `ca_total_ht_year`, `has_devis`, `email`, `siren`, `total_du_ht`, `id_type_entreprise`) VALUES (1, 'Entreprise X', '2006-04-06 10:57:27', '313131', '', 'Antananarivo', '', '', '', '', 'France', '', 1, 'prospect', 42.0000, 42.0000, 0, '', '', 42.0000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `webcash_company_types`
--

DROP TABLE IF EXISTS `webcash_company_types`;
CREATE TABLE `webcash_company_types` (
  `id_type_entreprise` int(11) NOT NULL auto_increment,
  `nom` varchar(255) default NULL,
  PRIMARY KEY  (`id_type_entreprise`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `webcash_company_types`
--

INSERT INTO `webcash_company_types` (`id_type_entreprise`, `nom`) VALUES (1, 'Client'),
(2, 'Prospect'),
(3, 'Fournisseur'),
(4, 'Archive');

-- --------------------------------------------------------

--
-- Table structure for table `webcash_dns`
--

DROP TABLE IF EXISTS `webcash_dns`;
CREATE TABLE `webcash_dns` (
  `id_dns` int(11) NOT NULL auto_increment,
  `id_domain` int(11) NOT NULL default '0',
  `name` varchar(50) default NULL,
  `record_type` enum('A','CNAME','MX','NS','AAAA','TXT') default 'CNAME',
  `value` varchar(50) default 'nbi.fr',
  `date_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_dns`),
  KEY `id_domain` (`id_domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `webcash_dns`
--


-- --------------------------------------------------------

--
-- Table structure for table `webcash_domain`
--

DROP TABLE IF EXISTS `webcash_domain`;
CREATE TABLE `webcash_domain` (
  `id_domain` int(11) NOT NULL auto_increment,
  `nom` varchar(255) default NULL,
  `date_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `id_client` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_domain`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `webcash_domain`
--


-- --------------------------------------------------------

--
-- Table structure for table `webcash_expense_details`
--

DROP TABLE IF EXISTS `webcash_expense_details`;
CREATE TABLE `webcash_expense_details` (
  `id` int(11) NOT NULL auto_increment,
  `id_expense` int(11) NOT NULL,
  `comment` text,
  `amount` decimal(14,2) NOT NULL default '0.00',
  `file` blob,
  `file_type` varchar(25) default NULL,
  `file_name` varchar(50) default NULL,
  PRIMARY KEY  (`id`),
  KEY `id_expense` (`id_expense`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `webcash_expense_details`
--


-- --------------------------------------------------------

--
-- Table structure for table `webcash_expenses`
--

DROP TABLE IF EXISTS `webcash_expenses`;
CREATE TABLE `webcash_expenses` (
  `id` int(11) NOT NULL auto_increment,
  `date` date default NULL,
  `id_user` int(11) NOT NULL,
  `id_transaction` int(11) NOT NULL,
  `comment` text,
  `date_update` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_transaction` (`id_transaction`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `webcash_expenses`
--


-- --------------------------------------------------------

--
-- Table structure for table `webcash_invoice_rows`
--

DROP TABLE IF EXISTS `webcash_invoice_rows`;
CREATE TABLE `webcash_invoice_rows` (
  `id_facture_ligne` int(11) NOT NULL auto_increment,
  `id_facture` int(11) NOT NULL default '0',
  `description` blob,
  `qtt` decimal(5,2) default NULL,
  `ordre` int(10) unsigned default NULL,
  `prix_ht` decimal(20,5) default NULL,
  PRIMARY KEY  (`id_facture_ligne`),
  KEY `pfk_facture` (`id_facture`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `webcash_invoice_rows`
--

INSERT INTO `webcash_invoice_rows` (`id_facture_ligne`, `id_facture`, `description`, `qtt`, `ordre`, `prix_ht`) VALUES (5, 7, 0x74657374, 2.00, NULL, 21.00000);

-- --------------------------------------------------------

--
-- Table structure for table `webcash_invoices`
--

DROP TABLE IF EXISTS `webcash_invoices`;
CREATE TABLE `webcash_invoices` (
  `id_facture` int(11) NOT NULL auto_increment,
  `id_client` int(11) NOT NULL default '0',
  `date_created` datetime default NULL,
  `date_generated` datetime default NULL,
  `date_sent` datetime default NULL,
  `date_paiement` datetime default NULL,
  `is_paye` tinyint(4) default '0',
  `num_facture` varchar(10) default NULL,
  `type_paiement` varchar(255) default 'Ã€ rÃ©ception de cette facture',
  `ref_contrat` varchar(255) default NULL,
  `extra_top` blob,
  `facture_file` varchar(255) default NULL,
  `accompte` decimal(10,4) default '0.0000',
  `extra_bottom` blob,
  `date_facture` datetime default NULL,
  `type_doc` enum('facture','devis') default 'facture',
  `commentaire` blob,
  `id_type_presta` int(11) default '1',
  `id_compte` int(11) NOT NULL default '34',
  `is_envoye` tinyint(4) default '0',
  PRIMARY KEY  (`id_facture`),
  UNIQUE KEY `num_facture` (`num_facture`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `webcash_invoices`
--

INSERT INTO `webcash_invoices` (`id_facture`, `id_client`, `date_created`, `date_generated`, `date_sent`, `date_paiement`, `is_paye`, `num_facture`, `type_paiement`, `ref_contrat`, `extra_top`, `facture_file`, `accompte`, `extra_bottom`, `date_facture`, `type_doc`, `commentaire`, `id_type_presta`, `id_compte`, `is_envoye`) VALUES (2, 1, '2006-04-06 12:06:06', NULL, NULL, NULL, 0, NULL, 'Ã€ rÃ©ception de cette facture', NULL, NULL, NULL, 0.0000, NULL, '2006-04-06 12:06:06', 'facture', NULL, 1, 34, 0),
(3, 1, '2006-04-06 12:07:40', NULL, NULL, NULL, 0, NULL, 'Ã€ rÃ©ception de cette facture', NULL, NULL, NULL, 0.0000, NULL, '2006-04-06 12:07:40', 'facture', NULL, 1, 34, 0),
(5, 1, '2006-04-06 12:09:28', NULL, NULL, NULL, 0, NULL, 'Ã€ rÃ©ception de cette facture', NULL, NULL, NULL, 0.0000, NULL, '2006-04-06 12:09:28', 'facture', NULL, 1, 34, 0),
(7, 1, '2006-04-06 15:33:47', NULL, NULL, NULL, 0, '', 'Ã€ rÃ©ception de cette facture', '', 0x6163636f6d707465, NULL, 0.0000, '', '2006-04-06 00:00:00', 'facture', '', 1, 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `webcash_naf`
--

DROP TABLE IF EXISTS `webcash_naf`;
CREATE TABLE `webcash_naf` (
  `id_naf` int(11) NOT NULL auto_increment,
  `code` varchar(4) NOT NULL default '',
  `nom` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id_naf`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `webcash_naf`
--


-- --------------------------------------------------------

--
-- Table structure for table `webcash_personne`
--

DROP TABLE IF EXISTS `webcash_personne`;
CREATE TABLE `webcash_personne` (
  `id_personne` int(11) NOT NULL auto_increment,
  `nom` varchar(100) default NULL,
  `prenom` varchar(100) default NULL,
  `date_created` datetime default NULL,
  `entreprise` varchar(30) default NULL,
  `fonction` varchar(30) default NULL,
  `tel` varchar(15) default NULL,
  `tel_perso` varchar(15) default NULL,
  `mobile` varchar(15) default NULL,
  `fax` varchar(15) default NULL,
  `email` varchar(255) default NULL,
  `adresse1` varchar(255) default NULL,
  `ville` varchar(255) default NULL,
  `cp` varchar(10) default NULL,
  `digicode` varchar(10) default NULL,
  `station_metro` varchar(10) default NULL,
  `date_anniversaire` varchar(10) default NULL,
  `note` blob,
  `client` int(11) NOT NULL default '-1',
  PRIMARY KEY  (`id_personne`),
  KEY `pfk_client` (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `webcash_personne`
--

INSERT INTO `webcash_personne` (`id_personne`, `nom`, `prenom`, `date_created`, `entreprise`, `fonction`, `tel`, `tel_perso`, `mobile`, `fax`, `email`, `adresse1`, `ville`, `cp`, `digicode`, `station_metro`, `date_anniversaire`, `note`, `client`) VALUES (1, 'MonNom', 'MonPrÃ©nom', '2006-04-06 10:59:40', NULL, 'Directeur', '', NULL, '', NULL, 'directeur@meta-x.com', NULL, NULL, NULL, NULL, NULL, NULL, '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `webcash_pref`
--

DROP TABLE IF EXISTS `webcash_pref`;
CREATE TABLE `webcash_pref` (
  `id_pref` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '-1',
  `type_pref` varchar(100) default NULL,
  `value` blob,
  PRIMARY KEY  (`id_pref`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `webcash_pref`
--

INSERT INTO `webcash_pref` (`id_pref`, `owner`, `type_pref`, `value`) VALUES (6, -1, 'rib', 0x547a6f344f694a7a644752446247467a637949364f447037637a6f324f694a6959573578645755694f334d364d545936496b4a68626e46315a5342776233423162474670636d55694f334d364d544d36496d527662576c6a6157787059585270623234694f334d364d544136496b4e6f5a58705957466859574667694f334d364d544536496d4e765a475666596d46756358566c496a747a4f6a5536496a45794d544978496a747a4f6a45794f694a6a6232526c5832643161574e6f5a5851694f334d364d7a6f694e6a497a496a747a4f6a5936496d4e76625842305a534937637a6f324f69497a4d54497a4d5449694f334d364e446f695932786c5a694937637a6f794f6949324e794937637a6f304f694a70596d4675496a747a4f6a5536496b6c4351553467496a747a4f6a5536496e4e3361575a30496a747a4f6a5936496b466152564a555753493766513d3d),
(5, -1, 'societe', 0x547a6f344f694a7a644752446247467a637949364e7a7037637a6f784e446f69636d46706332397558334e7659326c68624755694f334d364f546f6954586c446232317759573535496a747a4f6a49794f694a30646d466661573530636d466a62323174645735686458526861584a6c496a747a4f6a413649694937637a6f314f694a7a61584a6c62694937637a6f314f69497a4d544d784d694937637a6f314f694a685a4752794d534937637a6f784d6a6f695157353059573568626d467961585a76496a747a4f6a5536496d466b5a484979496a747a4f6a413649694937637a6f314f694a685a4752794d794937637a6f774f6949694f334d364d544d36496d52686447566659334a6c59585270623234694f334d364d446f69496a7439);

-- --------------------------------------------------------

--
-- Table structure for table `webcash_publication_method`
--

DROP TABLE IF EXISTS `webcash_publication_method`;
CREATE TABLE `webcash_publication_method` (
  `id_publication_method` int(11) NOT NULL auto_increment,
  `nom` varchar(50) default NULL,
  `code` varchar(20) default NULL,
  `description` blob,
  PRIMARY KEY  (`id_publication_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `webcash_publication_method`
--


-- --------------------------------------------------------

--
-- Table structure for table `webcash_suivi`
--

DROP TABLE IF EXISTS `webcash_suivi`;
CREATE TABLE `webcash_suivi` (
  `id_suivi` int(11) NOT NULL auto_increment,
  `type_suivi` tinyint(3) unsigned default NULL,
  `id_objet` int(11) NOT NULL default '0',
  `message` blob,
  `date_added` datetime default NULL,
  `date_modified` datetime default NULL,
  `added_by` int(11) default NULL,
  `rappel` datetime default NULL,
  `done` tinyint(3) unsigned default '0',
  `done_date` datetime default NULL,
  PRIMARY KEY  (`id_suivi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `webcash_suivi`
--


-- --------------------------------------------------------

--
-- Table structure for table `webcash_transactions`
--

DROP TABLE IF EXISTS `webcash_transactions`;
CREATE TABLE `webcash_transactions` (
  `id` int(11) NOT NULL auto_increment,
  `id_account` int(11) NOT NULL,
  `id_category` int(11) NOT NULL,
  `text` varchar(255) NOT NULL,
  `amount` decimal(14,2) NOT NULL default '0.00',
  `type` enum('real','prevision','asap') default NULL,
  `document` varchar(128) default '',
  `date` date NOT NULL,
  `date_update` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `comment` text,
  `file` mediumblob,
  `file_type` varchar(25) default NULL,
  `file_name` varchar(50) default NULL,
  PRIMARY KEY  (`id`),
  KEY `id_account` (`id_account`,`id_category`),
  KEY `id_category` (`id_category`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=52 ;

--
-- Dumping data for table `webcash_transactions`
--

INSERT INTO `webcash_transactions` (`id`, `id_account`, `id_category`, `text`, `amount`, `type`, `document`, `date`, `date_update`, `comment`, `file`, `file_type`, `file_name`) VALUES (2, 14, 4, 'REALISATION', 5000.00, 'real', '', '2005-01-16', '2006-04-03 14:35:22', '', '', '', ''),
(3, 14, 8, 'FRAIS DOSSIER DONT T', -125.64, 'real', '', '2005-03-16', '2006-04-03 14:24:02', '', NULL, NULL, NULL),
(4, 14, 5, 'RECUP. TIMBRES ACTE ', -6.00, 'real', '', '2005-03-16', '2006-04-03 14:24:02', '', NULL, NULL, NULL),
(6, 14, 0, 'VIRMENT', -221.26, 'real', '', '2005-03-17', '2006-04-03 14:28:41', '', '', '', ''),
(8, 14, 0, 'Achat', -1661.24, 'real', '', '2005-03-17', '2006-04-03 14:29:04', '', '', '', ''),
(10, 14, 5, 'VIR ASINFO F0189926 ', -2018.85, 'real', '', '2005-02-17', '2006-04-03 14:34:49', '', '', '', ''),
(12, 14, 0, 'VIR XXXXXXXXXX', 1000.00, 'real', '', '2005-03-18', '2006-04-03 14:21:20', 'Virement recu ', '', '', ''),
(13, 14, 9, 'VIR MR. AZERT', 10.00, 'real', '', '2005-03-29', '2006-04-03 14:32:13', '', '', '', ''),
(14, 14, 3, 'CHEQUE 0504041917806', -22.00, 'real', '', '2005-04-04', '2006-04-03 14:32:13', 'ras', NULL, NULL, NULL),
(15, 14, 8, 'VIR M OU MME OLIVIER', 10.00, 'real', '', '2005-04-04', '2006-04-03 14:32:13', '', NULL, NULL, NULL),
(16, 14, 8, 'CHEQUE 0038509500034', -64.80, 'real', '', '2005-04-05', '2006-04-03 14:32:13', '', NULL, NULL, NULL),
(20, 14, 0, 'CHEQUE 000', -449.50, 'prevision', '', '2006-04-07', '2006-04-03 14:29:59', 'Paiement &frac12;', '', '', ''),
(25, 14, 0, 'FAC 080405 CB:830290', -35.80, 'real', '', '2005-06-12', '2006-04-03 14:32:25', '?', '', '', ''),
(27, 14, 0, 'REM REMISE CB 539561', 69.07, 'real', '', '2005-04-16', '2006-03-28 15:14:13', '', NULL, NULL, NULL),
(28, 14, 5, 'REM REMISE CB 539561', 108.45, 'real', '', '2005-04-17', '2006-04-03 14:32:13', '', NULL, NULL, NULL),
(30, 14, 0, 'REM REMISE CB 539561', 138.37, 'real', '', '2005-04-18', '2006-03-28 15:14:13', '', NULL, NULL, NULL),
(32, 14, 0, 'VIR MR. AZERTY', 30.00, 'real', '', '2005-04-19', '2006-04-03 14:21:33', '', '', '', ''),
(33, 14, 8, 'REM REMISE CB 539561', 217.65, 'real', '', '2005-04-19', '2006-04-03 14:23:28', '', NULL, NULL, NULL),
(36, 14, 3, 'REM REMISE CB 539561', 157.46, 'real', '', '2005-04-20', '2006-04-03 14:23:28', '', NULL, NULL, NULL),
(37, 14, 7, 'REM REMISE CB 539561', 147.23, 'real', '', '2006-01-21', '2006-04-03 14:36:02', '', '', '', ''),
(38, 14, 5, 'REM REMISE CB 539561', 78.39, 'real', '', '2005-04-22', '2006-04-03 14:23:28', '', NULL, NULL, NULL),
(39, 14, 3, 'REM REMISE CB 539561', 336.55, 'real', '', '2005-08-23', '2006-04-03 14:26:11', '', '', '', ''),
(40, 14, 8, 'REM REMISE CB 539561', 49.08, 'real', '', '2005-04-24', '2006-04-03 14:23:28', '', NULL, NULL, NULL),
(41, 14, 5, 'REM REMISE CB 539561', 78.61, 'real', '', '2005-04-25', '2006-04-03 14:23:28', '', NULL, NULL, NULL),
(42, 14, 5, 'REM REMISE CB 539561', 138.21, 'real', '', '2005-04-26', '2006-04-03 14:23:28', '', NULL, NULL, NULL),
(44, 14, 9, 'REM REMISE CB 539', 88.39, 'real', '', '2005-12-27', '2006-04-03 14:31:35', '', '', '', ''),
(45, 14, 3, 'REM REMISE CB 53', 118.14, 'real', '', '2005-07-28', '2006-04-03 14:25:57', '', '', '', ''),
(46, 14, 7, 'CHEQUE 29', -159.00, 'real', '', '2005-04-29', '2006-04-03 14:23:28', '', '', '', ''),
(47, 14, 5, 'REM REMISE CB 5', 69.07, 'real', '', '2005-04-29', '2006-04-03 14:23:28', '', '', '', ''),
(48, 14, 5, 'REM REMISE CB EEEEE', 236.83, 'real', '', '2005-04-30', '2006-04-03 14:23:28', '', '', '', ''),
(49, 14, 3, 'REM REMISE CB ZZZZZZ', 29.31, 'real', '', '2005-05-01', '2006-04-03 14:23:28', '', '', '', ''),
(50, 14, 9, 'CHEQUE XXX', -19.00, 'real', '', '2005-05-02', '2006-04-03 14:23:28', '', '', '', ''),
(51, 14, 2, 'Test asap', 1000.00, 'asap', '', '2006-04-04', '2006-04-03 14:30:43', 'test asap', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `webcash_type_presta`
--

DROP TABLE IF EXISTS `webcash_type_presta`;
CREATE TABLE `webcash_type_presta` (
  `id_type_presta` int(11) NOT NULL auto_increment,
  `nom` varchar(255) default NULL,
  PRIMARY KEY  (`id_type_presta`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `webcash_type_presta`
--

INSERT INTO `webcash_type_presta` (`id_type_presta`, `nom`) VALUES (1, 'Formation'),
(2, 'Web'),
(3, 'Dev'),
(4, 'Support');

-- --------------------------------------------------------

--
-- Table structure for table `webcash_type_suivi`
--

DROP TABLE IF EXISTS `webcash_type_suivi`;
CREATE TABLE `webcash_type_suivi` (
  `id_type_suivi` int(11) NOT NULL auto_increment,
  `name` varchar(200) default NULL,
  `selectable` tinyint(4) default '1',
  PRIMARY KEY  (`id_type_suivi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `webcash_type_suivi`
--

INSERT INTO `webcash_type_suivi` (`id_type_suivi`, `name`, `selectable`) VALUES (1, 'CrÃ©ation entreprise', 0),
(2, 'Contact TÃ©lÃ©phonique', 1),
(3, 'Courier envoyÃ©', 1),
(4, 'Courier reÃ§u', 1),
(5, 'Rendez-vous', 1),
(6, 'Intervention sur site', 1);

-- --------------------------------------------------------

--
-- Table structure for table `webcash_type_tva`
--

DROP TABLE IF EXISTS `webcash_type_tva`;
CREATE TABLE `webcash_type_tva` (
  `id_type_tva` int(11) NOT NULL auto_increment,
  `nom` varchar(255) default NULL,
  `taux` decimal(5,3) default NULL,
  PRIMARY KEY  (`id_type_tva`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `webcash_type_tva`
--

INSERT INTO `webcash_type_tva` (`id_type_tva`, `nom`, `taux`) VALUES (1, 'Taux normal 19,6%', 19.600),
(2, 'Pas de tva facturÃ©e (export...)', 0.000);

-- --------------------------------------------------------

--
-- Table structure for table `webcash_userlog`
--

DROP TABLE IF EXISTS `webcash_userlog`;
CREATE TABLE `webcash_userlog` (
  `id_userlog` int(11) NOT NULL auto_increment,
  `log` blob,
  `date` datetime default NULL,
  `id_user` int(11) default NULL,
  PRIMARY KEY  (`id_userlog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `webcash_userlog`
--


-- --------------------------------------------------------

--
-- Table structure for table `webcash_users`
--

DROP TABLE IF EXISTS `webcash_users`;
CREATE TABLE `webcash_users` (
  `id_user` int(11) NOT NULL auto_increment,
  `last_name` varchar(100) default NULL,
  `first_name` varchar(100) default NULL,
  `login` varchar(10) default NULL,
  `password` varchar(100) default NULL,
  `email` varchar(255) default NULL,
  `disabled` tinyint(4) NOT NULL default '1',
  `last_login` datetime default NULL,
  `creation_date` datetime default NULL,
  `admin` tinyint(4) default '0',
  `role` varchar(30) default NULL,
  `modification_date` datetime default NULL,
  `prefs` blob,
  PRIMARY KEY  (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `webcash_users`
--

INSERT INTO `webcash_users` (`id_user`, `last_name`, `first_name`, `login`, `password`, `email`, `disabled`, `last_login`, `creation_date`, `admin`, `role`, `modification_date`, `prefs`) VALUES (1, NULL, NULL, 'admin', '21232f297a57a5a743894a0e4a801fc3', NULL, 0, '2006-04-06 16:34:51', NULL, 1, NULL, NULL, '');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `webcash_accounts`
--
ALTER TABLE `webcash_accounts`
  ADD CONSTRAINT `webcash_accounts_ibfk_1` FOREIGN KEY (`id_bank`) REFERENCES `webcash_banks` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `webcash_dns`
--
ALTER TABLE `webcash_dns`
  ADD CONSTRAINT `pfk_domain` FOREIGN KEY (`id_domain`) REFERENCES `webcash_domain` (`id_domain`) ON DELETE CASCADE;

--
-- Constraints for table `webcash_expense_details`
--
ALTER TABLE `webcash_expense_details`
  ADD CONSTRAINT `webcash_expense_details_ibfk_1` FOREIGN KEY (`id_expense`) REFERENCES `webcash_expenses` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `webcash_personne`
--
ALTER TABLE `webcash_personne`
  ADD CONSTRAINT `pfk_client` FOREIGN KEY (`client`) REFERENCES `webcash_clients` (`id_client`);

--
-- Constraints for table `webcash_transactions`
--
ALTER TABLE `webcash_transactions`
  ADD CONSTRAINT `webcash_transactions_ibfk_1` FOREIGN KEY (`id_account`) REFERENCES `webcash_accounts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
