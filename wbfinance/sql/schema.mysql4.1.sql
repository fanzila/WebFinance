--
-- Webfinance schema
--
-- Requires InnoDB.
-- Apply with mysql -u root --password=topsecret < schema.sql
--
-- Nicolas Bouthors <nbouthors@nbi.fr>
--
-- $Id$

SET FOREIGN_KEY_CHECKS = 0;

-- DROP TABLE IF EXISTS `webfinance_accounts`;
-- CREATE TABLE `webfinance_accounts` (
--  `id` int(11) NOT NULL auto_increment,
--  `account_name` varchar(128) NOT NULL,
--  `id_bank` int(11) NOT NULL default '0',
--  `id_user` int(11) default '0',
--  `account` varchar(255) NOT NULL,
--  `comment` text NOT NULL,
--  `currency` varchar(64) NOT NULL default 'EUR',
--  `country` varchar(128) NOT NULL,
--  `type` varchar(64) default 'compte commercial',
--  PRIMARY KEY  (`id`),
--  UNIQUE KEY `account_name` (`account_name`),
--  KEY `id_bank` (`id_bank`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

-- DROP TABLE IF EXISTS `webfinance_banks`;
-- CREATE TABLE `webfinance_banks` (
--  `id` int(11) NOT NULL auto_increment,
--  `name` varchar(255) NOT NULL default '',
--  `short_name` varchar(64) default NULL,
--  `phone` varchar(64) default '00.00.00.00',
--  `mail` varchar(64) default 'example@example.com',
--  `comment` text,
--  PRIMARY KEY  (`id`),
--  UNIQUE KEY `name` (`name`),
--  UNIQUE KEY `short_name` (`short_name`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=30 ;


DROP TABLE IF EXISTS `webfinance_categories`;
CREATE TABLE `webfinance_categories` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `comment` text,
  `re` varchar(255), -- as in regexp
  `plan_comptable` varchar(100),
  `color` varchar(7) default '#cefce',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

DROP TABLE IF EXISTS `webfinance_clients`;
CREATE TABLE `webfinance_clients` (
  `id_client` int(11) NOT NULL auto_increment,
  `nom` varchar(200) default NULL,
  `date_created` datetime default NULL,
  `tel` varchar(15) default NULL,
  `fax` varchar(200) default NULL,
  `web` varchar(100) default 'http://',
  `addr1` varchar(255) default NULL,
  `cp` varchar(10) default NULL,
  `ville` varchar(100) default NULL,
  `addr2` varchar(255) default NULL,
  `addr3` varchar(255) default NULL,
  `pays` varchar(50) default 'France',
  `vat_number` varchar(40) default NULL,
  `has_unpaid` tinyint(1) default NULL,
  `ca_total_ht` decimal(20,4) default NULL,
  `ca_total_ht_year` decimal(20,4) default NULL,
  `has_devis` tinyint(4) NOT NULL default '0',
  `email` varchar(255) default NULL,
  `siren` varchar(50) default NULL,
  `total_du_ht` decimal(20,4) default NULL,
  `id_company_type` int(11) NOT NULL default '1',
  `id_user` int(11) NOT NULL default '0',
  `password` varchar(100) default NULL,
  PRIMARY KEY  (`id_client`),
  KEY `id_company_type` (`id_company_type`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

DROP TABLE IF EXISTS `webfinance_company_types`;
CREATE TABLE `webfinance_company_types` (
  `id_company_type` int(11) NOT NULL auto_increment,
  `nom` varchar(255) default NULL,
  PRIMARY KEY  (`id_company_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `webfinance_dns`;
CREATE TABLE `webfinance_dns` (
  `id_dns` int(11) NOT NULL auto_increment,
  `id_domain` int(11) NOT NULL default '0',
  `name` varchar(50) default NULL,
  `record_type` enum('A','CNAME','MX','NS','AAAA','TXT') default 'CNAME',
  `value` varchar(50) default 'nbi.fr',
  `date_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id_dns`),
  KEY `id_domain` (`id_domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `webfinance_domain`;
CREATE TABLE `webfinance_domain` (
  `id_domain` int(11) NOT NULL auto_increment,
  `nom` varchar(255) default NULL,
  `date_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `id_client` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_domain`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `webfinance_expenses`;
CREATE TABLE `webfinance_expenses` (
  `id` int(11) NOT NULL auto_increment,
  `id_user` int(11) NOT NULL,
  `id_transaction` int(11) NOT NULL,
  `amount` decimal(14,2) default '0.00',
  `comment` text,
  `date_update` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `file` blob,
  `file_type` varchar(32) default NULL,
  `file_name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_transaction` (`id_transaction`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

DROP TABLE IF EXISTS `webfinance_invoice_rows`;
CREATE TABLE `webfinance_invoice_rows` (
  `id_facture_ligne` int(11) NOT NULL auto_increment,
  `id_facture` int(11) NOT NULL default '0',
  `description` blob,
  `qtt` decimal(5,2) default NULL,
  `ordre` int(10) unsigned default NULL,
  `prix_ht` decimal(20,5) default NULL,
  PRIMARY KEY  (`id_facture_ligne`),
  KEY `pfk_facture` (`id_facture`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

DROP TABLE IF EXISTS `webfinance_invoices`;
CREATE TABLE `webfinance_invoices` (
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
  `period` enum('none','end of month','end of term','end of year') default 'none',
  `last_run` timestamp NULL default '0000-00-00 00:00:00',
  `tax` DECIMAL(5,2) NOT NULL default '19.60',
  `exchange_rate` decimal(8,2) NOT NULL default '1.00',
  PRIMARY KEY  (`id_facture`),
  UNIQUE KEY `num_facture` (`num_facture`),
  KEY `id_compte` (`id_compte`),
  KEY `id_client` (`id_client`),
  KEY `date_facture` (`date_facture`),
  KEY `id_type_presta` (`id_type_presta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

DROP TABLE IF EXISTS `webfinance_naf`;
CREATE TABLE `webfinance_naf` (
  `id_naf` int(11) NOT NULL auto_increment,
  `code` varchar(4) NOT NULL default '',
  `nom` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id_naf`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `webfinance_personne`;
CREATE TABLE `webfinance_personne` (
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

DROP TABLE IF EXISTS `webfinance_pref`;
CREATE TABLE `webfinance_pref` (
  `id_pref` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '-1',
  `type_pref` varchar(100) default NULL,
  `date_modified` TIMESTAMP,
  `value` blob,
  PRIMARY KEY  (`id_pref`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

DROP TABLE IF EXISTS `webfinance_publication_method`;
CREATE TABLE `webfinance_publication_method` (
  `id_publication_method` int(11) NOT NULL auto_increment,
  `nom` varchar(50) default NULL,
  `code` varchar(20) default NULL,
  `description` blob,
  PRIMARY KEY  (`id_publication_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `webfinance_suivi`;
CREATE TABLE `webfinance_suivi` (
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

DROP TABLE IF EXISTS `webfinance_transactions`;
CREATE TABLE `webfinance_transactions` (
  `id` int(11) NOT NULL auto_increment,
  `id_account` int(11) NOT NULL,
  `id_category` int(11) NOT NULL DEFAULT 1,
  `text` varchar(255) NOT NULL,
  `amount` decimal(14,2) NOT NULL default '0.00',
  `exchange_rate` decimal(8,2) NOT NULL default '1.00',
  `type` enum('real','prevision','asap') default NULL,
  `document` varchar(128) default '',
  `date` date NOT NULL,
  `date_update` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `comment` text,
  `file` mediumblob,
  `file_type` varchar(25) default NULL,
  `file_name` varchar(50) default NULL,
  `lettrage` tinyint default 0, -- 0 si transaction "perdue" 1 si liée à une facture, commande, fournisseur ...
  `id_invoice` int(11) default '0',
  PRIMARY KEY  (`id`),
  KEY `id_account` (`id_account`),
  KEY `id_category` (`id_category`),
  KEY `date` (`date`),
  KEY `id_invoice` (`id_invoice`)
--  UNIQUE `unique_transaction` (`id_account`, `amount`, `type`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

DROP TABLE IF EXISTS `webfinance_type_presta`;
CREATE TABLE `webfinance_type_presta` (
  `id_type_presta` int(11) NOT NULL auto_increment,
  `nom` varchar(255) default NULL,
  PRIMARY KEY  (`id_type_presta`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

DROP TABLE IF EXISTS `webfinance_type_suivi`;
CREATE TABLE `webfinance_type_suivi` (
  `id_type_suivi` int(11) NOT NULL auto_increment,
  `name` varchar(200) default NULL,
  `selectable` tinyint(4) default '1',
  PRIMARY KEY  (`id_type_suivi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

DROP TABLE IF EXISTS `webfinance_type_tva`;
CREATE TABLE `webfinance_type_tva` (
  `id_type_tva` int(11) NOT NULL auto_increment,
  `nom` varchar(255) default NULL,
  `taux` decimal(5,3) default NULL,
  PRIMARY KEY  (`id_type_tva`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

DROP TABLE IF EXISTS `webfinance_userlog`;
CREATE TABLE `webfinance_userlog` (
  `id_userlog` int(11) NOT NULL auto_increment,
  `log` blob,
  `date` datetime default NULL,
  `id_user` int(11) default NULL,
  PRIMARY KEY  (`id_userlog`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `webfinance_users`;
CREATE TABLE `webfinance_users` (
  `id_user` int(11) NOT NULL auto_increment,
  `last_name` varchar(100) default NULL,
  `first_name` varchar(100) default NULL,
  `login` varchar(10) NOT NULL,
  `password` varchar(100) default NULL,
  `email` varchar(255) default NULL,
  `disabled` tinyint(4) NOT NULL default '1',
  `last_login` datetime default NULL,
  `creation_date` datetime default NULL,
  `role` varchar(64) default NULL,
  `modification_date` datetime default NULL,
  `prefs` blob,
  PRIMARY KEY  (`id_user`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

DROP TABLE IF EXISTS `webfinance_roles`;
CREATE TABLE `webfinance_roles` (
  `id_role` int(11) NOT NULL auto_increment,
  `name` varchar(20) NOT NULL,
  `description` blob,
  PRIMARY KEY  (`id_role`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;


CREATE TABLE `webfinance_paybox` (
  `id_paybox` int(11) NOT NULL auto_increment,
  `id_invoice` int(11) NOT NULL,
  `email` varchar(255) default NULL,
  `reference` varchar(255) NOT NULL,
  `state` enum('nok','pending','cancel','deny','ok') NOT NULL default 'nok',
  `amount` decimal(14,2) NOT NULL default '0.00',
  `currency` int(2) NOT NULL default '978',
  `autorisation` varchar(64) NOT NULL default '',
  `transaction_id` varchar(64) NOT NULL default '',
  `payment_type` varchar(64) NOT NULL default '',
  `card_type` varchar(64) NOT NULL default '',
  `transaction_sole_id` varchar(64) NOT NULL default '',
  `error_code` varchar(64) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id_paybox`),
  UNIQUE KEY `reference` (`reference`),
  KEY `id_invoice` (`id_invoice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `webfinance_files` (
  `id_file` int(11) NOT NULL auto_increment,
  `fk_id` int(11) NOT NULL,
  `wf_type` enum('transaction','users','client') NOT NULL default 'transaction',
  `file_type` varchar(32) NOT NULL,
  `file_name` varchar(128) NOT NULL,
  `file` mediumblob NOT NULL,
  PRIMARY KEY  (`id_file`),
  KEY `fk_id` (`fk_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- DEFAULT DATA INSERT
--

INSERT INTO `webfinance_type_tva` (`id_type_tva`, `nom`, `taux`) VALUES (1, 'Taux normal 19,6%', 19.600),
(2, 'Pas de tva facturée (export...)', 0.000);

INSERT INTO `webfinance_type_presta` (`id_type_presta`, `nom`) VALUES (1, 'Formation'),
(2, 'Web'),
(3, 'Dev'),
(4, 'Support');

INSERT INTO `webfinance_type_suivi` (`id_type_suivi`, `name`, `selectable`) VALUES
(1, 'Création entreprise', 0),
(2, 'Contact Téléphonique', 1),
(3, 'Courier envoyé', 1),
(4, 'Courier reçu', 1),
(5, 'Rendez-vous', 1),
(6, 'Presta', 1);

INSERT INTO `webfinance_users` (`id_user`, `last_name`, `first_name`, `login`, `password`, `email`, `disabled`, `last_login`, `creation_date`, `role`, `modification_date`, `prefs`) VALUES (1, NULL, NULL, 'admin', '21232f297a57a5a743894a0e4a801fc3', NULL, 0, '2006-04-06 16:34:51', NULL, 'admin,manager', NULL, '');

INSERT INTO `webfinance_type_tva` VALUES
(NULL,'Taux normal 19,6%', 19.6),
(NULL,'Pas de tva facturée (export...)', 0) ;

INSERT INTO `webfinance_company_types` (nom) VALUES
    ('Client'),('Prospect'),('Fournisseur'),('Archive');

-- INSERT INTO `webfinance_banks` (`id`, `name`, `short_name`, `phone`, `mail`, `comment`) VALUES (1, 'My bank', 'mybank', '', '', '');

-- Voir http://www.plancomptable.com/pc99/titre-IV/liste_des_comptes_sb.htm
INSERT INTO `webfinance_categories` (`id`, `name`, `re`, `plan_comptable`) VALUES
(1   , 'Unknown', '', ''),
(NULL, 'Salaire', 'salaire',''),
(NULL, 'Loyer', 'loyer',''),
(NULL, 'Frais bancaires', '(cotisation signature pro|facturation progeliance net|net arrete au [0-9]{2} [0-9]{2} [0-9]{2})','627'),
(NULL, 'Téléphone mobile', ' prelevement sfr ','626'),
(NULL, 'FT', '(france telecom|TIP.*fr telecom)','626'),
(NULL, 'Timbres', ' la poste ','626'),
(NULL, 'Internet', 'Free Telecom Free HautDebit',''),
(NULL, 'Matériel', '',''),
(NULL, 'Fournitures bureau', 'jpg commerce electronique',''),
(NULL, 'Serveur', '',''),
(NULL, 'Transports', ' (websncf|sncf|esso|totalfinaelf) ',''),
(NULL, 'Impots - IS', '',''),
(NULL, 'Impots - TVA', '', '');

-- INSERT INTO `webfinance_clients` (`id_client`, `nom`, `date_created`, `tel`, `fax`, `addr1`, `cp`, `ville`, `addr2`, `addr3`, `pays`, `vat_number`, `has_unpaid`, `state`, `ca_total_ht`, `ca_total_ht_year`, `has_devis`, `email`, `siren`, `total_du_ht`, `id_company_type`) VALUES (1, 'Entreprise X', '2006-04-06 10:57:27', '313131', '', 'Antananarivo', '', '', '', '', 'France', '', 1, 'Madagascar', 42.0000, 42.0000, 0, '', '', 42.0000, 1);

-- INSERT INTO `webfinance_pref` (`id_pref`, `owner`, `type_pref`, `value`) VALUES (6, -1, 'rib', 0x547a6f344f694a7a644752446247467a637949364f447037637a6f324f694a6959573578645755694f334d364d545936496b4a68626e46315a5342776233423162474670636d55694f334d364d544d36496d527662576c6a6157787059585270623234694f334d364d544136496b4e6f5a58705957466859574667694f334d364d544536496d4e765a475666596d46756358566c496a747a4f6a5536496a45794d544978496a747a4f6a45794f694a6a6232526c5832643161574e6f5a5851694f334d364d7a6f694e6a497a496a747a4f6a5936496d4e76625842305a534937637a6f324f69497a4d54497a4d5449694f334d364e446f695932786c5a694937637a6f794f6949324e794937637a6f304f694a70596d4675496a747a4f6a5536496b6c4351553467496a747a4f6a5536496e4e3361575a30496a747a4f6a5936496b466152564a555753493766513d3d),
-- (5, -1, 'societe', 0x547a6f344f694a7a644752446247467a637949364e7a7037637a6f784e446f69636d46706332397558334e7659326c68624755694f334d364f546f6954586c446232317759573535496a747a4f6a49794f694a30646d466661573530636d466a62323174645735686458526861584a6c496a747a4f6a413649694937637a6f314f694a7a61584a6c62694937637a6f314f69497a4d544d784d694937637a6f314f694a685a4752794d534937637a6f784d6a6f695157353059573568626d467961585a76496a747a4f6a5536496d466b5a484979496a747a4f6a413649694937637a6f314f694a685a4752794d794937637a6f774f6949694f334d364d544d36496d52686447566659334a6c59585270623234694f334d364d446f69496a7439);



INSERT INTO `webfinance_roles` (`id_role`, `name`, `description`) VALUES (7, 'manager', 0x6d616e61676572),
(8, 'employee', 0x656d706c6f796565),
(9, 'accounting', 0x636f6d707461626c65),
(10, 'client', 0x636c69656e74);


-- Create view

-- CREATE VIEW  wf_view_invoices 
-- AS 
-- SELECT 
-- webfinance_invoices.id_facture, 
-- webfinance_invoices.num_facture,
-- webfinance_invoices.ref_contrat,  
-- webfinance_invoices.date_facture,  
-- is_paye, 
-- SUM( qtt * prix_ht ) as total_facture_ht
-- FROM webfinance_invoice_rows, webfinance_invoices
-- WHERE webfinance_invoice_rows.id_facture = webfinance_invoices.id_facture 
-- GROUP BY id_facture;


--
-- Constraints for dumped tables
--
-- ALTER TABLE `webfinance_accounts`
--  ADD CONSTRAINT `webfinance_accounts_ibfk_1` FOREIGN KEY (`id_bank`) REFERENCES `webfinance_banks` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
-- ALTER TABLE `webfinance_dns`
--   ADD CONSTRAINT `pfk_domain` FOREIGN KEY (`id_domain`) REFERENCES `webfinance_domain` (`id_domain`) ON DELETE CASCADE;
ALTER TABLE `webfinance_personne`
  ADD CONSTRAINT `pfk_client` FOREIGN KEY (`client`) REFERENCES `webfinance_clients` (`id_client`) ON DELETE CASCADE;
-- ALTER TABLE `webfinance_transactions`
--   ADD CONSTRAINT `webfinance_transactions_ibfk_1` FOREIGN KEY (`id_account`) REFERENCES `webfinance_accounts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- 
-- Constraints for table `webfinance_invoice_rows`
-- 
ALTER TABLE `webfinance_invoice_rows`
  ADD CONSTRAINT `webfinance_invoice_rows_ibfk_1` FOREIGN KEY (`id_facture`) REFERENCES `webfinance_invoices` (`id_facture`) ON DELETE CASCADE;

-- 
-- Constraints for table `webfinance_invoices`
-- 
ALTER TABLE `webfinance_invoices`
  ADD CONSTRAINT `webfinance_invoices_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `webfinance_clients` (`id_client`) ON DELETE CASCADE;


-- vim: fileencoding=utf8
-- EOF
