-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC. All rights reserved.                        |
-- |                                                                    |
-- | This work is published under the GNU AGPLv3 license with some      |
-- | permitted exceptions and without any warranty. For full license    |
-- | and copyright information, see https://civicrm.org/licensing       |
-- +--------------------------------------------------------------------+
--
-- Generated from schema.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--
-- /*******************************************************
-- *
-- * Clean up the existing tables - this section generated from drop.tpl
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_campagnodon_transaction_link`;
DROP TABLE IF EXISTS `civicrm_campagnodon_transaction`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * civicrm_campagnodon_transaction
-- *
-- * Campagnodon transaction. Groups contact/contributions/payments/... related to a donation or adhesion coming from SPIP Campagnodon plugin.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_campagnodon_transaction` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique CampagnodonTransaction ID',
  `idx` varchar(255) NULL COMMENT 'The campagnodon key as given by the origin system (SPIP, ...). A string like: spip/12345.',
  `operation_type` varchar(255) NOT NULL COMMENT 'The operation type given by the origin system. Example: donation, membership, ... Can be any string, only used to filter transactions.',
  `start_date` datetime NOT NULL DEFAULT NOW() COMMENT 'The datetime at which this transaction started.',
  `status` varchar(20) NOT NULL DEFAULT 'init' COMMENT 'The status of the transaction.',
  `tax_receipt` tinyint NOT NULL DEFAULT false COMMENT 'True if the user want a tax receipt',
  `payment_url` varchar(255) COMMENT 'The url to pay the subscriptions.',
  `transaction_url` varchar(255) COMMENT 'The url to the original transaction.',
  `payment_instrument_id` int unsigned COMMENT 'FK vers Instrument de Paiement',
  `contact_id` int unsigned COMMENT 'FK de contact',
  `original_contact_id` int unsigned COMMENT 'The contact id when this transaction was created. So we can know if there was a deduplication afterward.',
  `new_contact` tinyint COMMENT 'True if the contact was created for this transaction.',
  `campaign_id` int unsigned COMMENT 'The campaign for which this Campagnodon transaction is attached.',
  `email` varchar(254) COMMENT 'Courriel',
  `source` varchar(255) NULL COMMENT 'Origin of this Transaction.',
  `prefix_id` int unsigned COMMENT 'Préfixe ou Titre du nom (M., Mme...). FK de l\'Id du préfixe',
  `first_name` varchar(64) COMMENT 'Prénom.',
  `last_name` varchar(64) COMMENT 'Nom de famille.',
  `birth_date` date COMMENT 'Date de naissance',
  `street_address` varchar(96) COMMENT 'Concaténation de tous les composants d\'adresses routables (préfixe, numéro de rue, nom de rue, suffixe, unité\nnuméro OU P.O. Boîte). Les applications doivent pouvoir déterminer l\'emplacement physique avec ces données (pour la cartographie, le courrier\nlivraison, etc.).  ',
  `supplemental_address_1` varchar(96) COMMENT 'Informations d\'adresse supplémentaires, ligne 1',
  `supplemental_address_2` varchar(96) COMMENT 'Informations d\'adresse supplémentaires, ligne 2',
  `postal_code` varchar(64) COMMENT 'Stockez les codes postaux américains (zip5) ET internationaux. L\'application est responsable de la validation appropriée du pays / de la région.',
  `city` varchar(64) COMMENT 'Nom de la ville, de la capitale ou du village.',
  `country_id` int unsigned COMMENT 'A quel pays cette adresse appartient.',
  `phone` varchar(32) COMMENT 'Numéro de téléphone complet.',
  `merged` tinyint NOT NULL DEFAULT false COMMENT 'True if transaction information were merged into the contact',
  `cleaned` tinyint NOT NULL DEFAULT false COMMENT 'True if personnal information were deleted from the transaction',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `index_idx`(idx),
  INDEX `index_operation_type`(operation_type),
  INDEX `start_date`(start_date),
  INDEX `cleaned_start_date_idx`(cleaned, start_date),
  CONSTRAINT FK_civicrm_campagnodon_transaction_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE SET NULL,
  CONSTRAINT FK_civicrm_campagnodon_transaction_campaign_id FOREIGN KEY (`campaign_id`) REFERENCES `civicrm_campaign`(`id`) ON DELETE SET NULL,
  CONSTRAINT FK_civicrm_campagnodon_transaction_country_id FOREIGN KEY (`country_id`) REFERENCES `civicrm_country`(`id`) ON DELETE SET NULL
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_campagnodon_transaction_link
-- *
-- * Link between CampagnodonTransaction and other tables (contributions, ...)
-- *
-- *******************************************************/
CREATE TABLE `civicrm_campagnodon_transaction_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique CampagnodonTransactionLink ID',
  `campagnodon_tid` int unsigned NOT NULL COMMENT 'FK to CampagnodonTransaction',
  `parent_id` int unsigned DEFAULT NULL COMMENT 'Optional parent id for this CampagnodonTransactionLink. Used to find the contribution link associated to a membership subscription.',
  `entity_table` varchar(64) NOT NULL COMMENT 'Table of the linked object',
  `entity_id` int unsigned NULL DEFAULT NULL COMMENT 'ID of the linked object. Can be null if the object is not created in pending state.',
  `on_complete` tinyint DEFAULT false COMMENT 'Only when entity_table=\'group\' or \'contact\' or \'tag\'. If true, the contact will be added in group only when transaction is complete.',
  `total_amount` decimal(20,2) NULL DEFAULT NULL COMMENT 'Only when entity_table=\'contribution\'. Total amount of this contribution.',
  `currency` varchar(3) DEFAULT NULL COMMENT 'Only when entity_table=\'contribution\'. 3 character string, value from config setting or input via user.',
  `financial_type_id` int unsigned NULL DEFAULT NULL COMMENT 'Only when entity_table=\'contribution\'. FK to Financial Type.',
  `membership_type_id` int unsigned NULL DEFAULT NULL COMMENT 'Only when entity_table=\'membership\'. FK to Membership Type.',
  `opt_in` varchar(25) DEFAULT NULL COMMENT 'An opt-in action to do on the contact (or membership).',
  `cancelled` varchar(20) DEFAULT NULL COMMENT 'Some links can be cancelled. This field contains a keyword to describe the reason. Example: membership already exists.',
  PRIMARY KEY (`id`),
  INDEX `index_entity_table_entity_id`(entity_table, entity_id),
  INDEX `index_cancelled`(cancelled),
  CONSTRAINT FK_civicrm_campagnodon_transaction_link_campagnodon_tid FOREIGN KEY (`campagnodon_tid`) REFERENCES `civicrm_campagnodon_transaction`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_campagnodon_transaction_link_parent_id FOREIGN KEY (`parent_id`) REFERENCES `civicrm_campagnodon_transaction_link`(`id`) ON DELETE SET NULL
)
ENGINE=InnoDB;
