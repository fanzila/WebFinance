--
-- NBI Backoffice schema
-- 
-- Requires InnoDB.
-- Apply with mysql -u root --password=topsecret < schema.sql
-- 
-- Nicolas Bouthors <nbouthors@nbi.fr>
--
-- $Id$

DROP TABLE IF EXISTS `dns`;
DROP TABLE IF EXISTS `domain`;
DROP TABLE IF EXISTS `facture`;
DROP TABLE IF EXISTS `facture_ligne`;
DROP TABLE IF EXISTS `naf`;
DROP TABLE IF EXISTS `personne`;
DROP TABLE IF EXISTS `client`;
DROP TABLE IF EXISTS `pref`;
DROP TABLE IF EXISTS `publication_method`;
DROP TABLE IF EXISTS `suivi`;
DROP TABLE IF EXISTS `type_entreprise`;
DROP TABLE IF EXISTS `type_tva`;
DROP TABLE IF EXISTS `type_presta`;
DROP TABLE IF EXISTS `type_suivi`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `userlog`;

CREATE TABLE `client` (
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
  `id_type_entreprise` INT NOT NULL DEFAULT 1,
  PRIMARY KEY  (`id_client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `domain` (
  `id_domain` int(11) NOT NULL auto_increment,
  `nom` varchar(255) default NULL,
  `date_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `id_client` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_domain`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dns` (
  `id_dns` int(11) NOT NULL auto_increment,
  `id_domain` int(11) NOT NULL default '0',
  `name` varchar(50) default NULL,
  `record_type` enum('A','CNAME','MX','NS','AAAA','TXT') default 'CNAME',
  `value` varchar(50) default 'nbi.fr',
  `date_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_dns`),
  KEY `id_domain` (`id_domain`),
  CONSTRAINT `pfk_domain` FOREIGN KEY (`id_domain`) REFERENCES `domain` (`id_domain`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `facture` (
  `id_facture` int(11) NOT NULL auto_increment,
  `id_client` int(11) NOT NULL default '0',
  `date_created` datetime default NULL,
  `date_generated` datetime default NULL,
  `date_sent` datetime default NULL,
  `date_paiement` datetime default NULL,
  `is_paye` tinyint(4) default '0',
  `num_facture` varchar(10) default NULL,
  `type_paiement` varchar(255) default 'À réception de cette facture',
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
  PRIMARY KEY  (`id_facture`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `facture_ligne` (
  `id_facture_ligne` int(11) NOT NULL auto_increment,
  `id_facture` int(11) NOT NULL default '0',
  `description` blob,
  `qtt` decimal(5,2) default NULL,
  `ordre` int(10) unsigned default NULL,
  `prix_ht` decimal(20,5) default NULL,
  PRIMARY KEY  (`id_facture_ligne`),
  CONSTRAINT `pfk_facture` FOREIGN KEY (`id_facture`) REFERENCES `facture` (`id_facture`) ON DELETE RESTRICT
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `naf` (
  `id_naf` int(11) NOT NULL auto_increment,
  `code` varchar(4) NOT NULL default '',
  `nom` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id_naf`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `personne` (
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
  KEY `pfk_client` (`client`),
  CONSTRAINT `pfk_client` FOREIGN KEY (`client`) REFERENCES `client` (`id_client`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `pref` (
  `id_pref` int(11) NOT NULL auto_increment,
  `owner` INT NOT NULL DEFAULt -1,
  `type_pref` varchar(100) default NULL,
  `value` blob,
  PRIMARY KEY  (`id_pref`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `publication_method` (
  `id_publication_method` int(11) NOT NULL auto_increment,
  `nom` varchar(50) default NULL,
  `code` varchar(20) default NULL,
  `description` blob,
  PRIMARY KEY  (`id_publication_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `suivi` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `type_entreprise` (
  `id_type_entreprise` int(11) NOT NULL auto_increment,
  `nom` varchar(255) default NULL,
  PRIMARY KEY  (`id_type_entreprise`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `type_tva` (
  `id_type_tva` int(11) NOT NULL auto_increment,
  `nom` varchar(255) default NULL,
  `taux` decimal(5,3) default NULL,
  PRIMARY KEY  (`id_type_tva`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `type_presta` (
  `id_type_presta` int(11) NOT NULL auto_increment,
  `nom` varchar(255) default NULL,
  PRIMARY KEY  (`id_type_presta`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `type_suivi` (
  `id_type_suivi` int(11) NOT NULL auto_increment,
  `name` varchar(200) default NULL,
  `selectable` tinyint(4) default '1',
  PRIMARY KEY  (`id_type_suivi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `userlog` (
  `id_userlog` int(11) NOT NULL auto_increment,
  `log` blob,
  `date` datetime default NULL,
  `id_user` int(11) default NULL,
  PRIMARY KEY  (`id_userlog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- DEFAULT DATA INSERT 
--

INSERT INTO `type_presta` VALUES (1,'Formation'),(2,'Web'),(3,'Dev'),(4,'Support');
INSERT INTO `type_suivi` VALUES 
(NULL,'Création entreprise',0),
(NULL,'Contact Téléphonique',1),
(NULL,'Courier envoyé',1),
(NULL,'Courier reçu',1),
(NULL,'Rendez-vous',1),
(NULL,'Intervention sur site',1);

INSERT INTO `type_tva` VALUES
(NULL,'Taux normal 19,6%', 19.6),
(NULL,'Pas de tva facturée (export...)', 0) ;

INSERT INTO `user` (login, password, disabled) VALUES
('admin', md5('admin'), 0)
;

INSERT INTO `type_entreprise` (nom) VALUES
    ('Client'),('Prospect'),('Fournisseur'),('Archive');

-- vim: fileencoding=utf8
-- EOF
