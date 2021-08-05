-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.1.29-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win32
-- HeidiSQL Versão:              9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Copiando estrutura para tabela zetta_vault.credentials
DROP TABLE IF EXISTS `credentials`;
CREATE TABLE IF NOT EXISTS `credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` smallint(6) NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FA05280EA76ED395` (`user_id`),
  CONSTRAINT `FK_FA05280EA76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela zetta_vault.credentials: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `credentials` DISABLE KEYS */;
INSERT INTO `credentials` (`id`, `user_id`, `type`, `value`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 1, '$argon2i$v=19$m=131072,t=4,p=3$ay9jblBYWERWb2tDSXFabQ$dtJE2YkiW8H5PH6OzocS8kXosy5RH6TmanqgGcuIzsE', '2018-01-01 00:00:01', '2018-01-01 00:00:01', NULL);
/*!40000 ALTER TABLE `credentials` ENABLE KEYS */;

-- Copiando estrutura para tabela zetta_vault.roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela zetta_vault.roles: ~2 rows (aproximadamente)
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'Admin', '2015-10-17 11:27:35', '2015-10-17 11:27:36', NULL),
	(2, 'Member', '2015-10-17 11:29:10', '2015-10-17 11:29:10', NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;

-- Copiando estrutura para tabela zetta_vault.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` smallint(6) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `bio` longtext COLLATE utf8mb4_unicode_ci,
  `status` smallint(6) NOT NULL,
  `confirmed_email` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1483A5E9D60322AC` (`role_id`),
  CONSTRAINT `FK_1483A5E9D60322AC` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela zetta_vault.users: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `role_id`, `username`, `name`, `email`, `avatar`, `gender`, `birthday`, `bio`, `status`, `confirmed_email`, `active`, `token`, `token_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 1, 'admin', 'Admin', 'admin@z.com', './public/img/thumb-boy.svg', 2, '1992-08-30', NULL, 2, 1, 1, 'cd807e79fdd149412919de5ef780f864', '2018-06-28 15:54:50', '2018-01-01 00:00:01', '2018-06-28 15:54:50', NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

-- Copiando estrutura para tabela zetta_vault.zv_accounts
DROP TABLE IF EXISTS `zv_accounts`;
CREATE TABLE IF NOT EXISTS `zv_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8E5C812632C8A3DE` (`organization_id`),
  CONSTRAINT `FK_8E5C812632C8A3DE` FOREIGN KEY (`organization_id`) REFERENCES `zv_organizations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela zetta_vault.zv_accounts: ~2 rows (aproximadamente)
/*!40000 ALTER TABLE `zv_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `zv_accounts` ENABLE KEYS */;

-- Copiando estrutura para tabela zetta_vault.zv_credentials
DROP TABLE IF EXISTS `zv_credentials`;
CREATE TABLE IF NOT EXISTS `zv_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2C9FEFDF9B6B5FBA` (`account_id`),
  KEY `IDX_2C9FEFDFD823E37A` (`section_id`),
  CONSTRAINT `FK_2C9FEFDF9B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `zv_accounts` (`id`),
  CONSTRAINT `FK_2C9FEFDFD823E37A` FOREIGN KEY (`section_id`) REFERENCES `zv_sections` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela zetta_vault.zv_credentials: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `zv_credentials` DISABLE KEYS */;
/*!40000 ALTER TABLE `zv_credentials` ENABLE KEYS */;

-- Copiando estrutura para tabela zetta_vault.zv_organizations
DROP TABLE IF EXISTS `zv_organizations`;
CREATE TABLE IF NOT EXISTS `zv_organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela zetta_vault.zv_organizations: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `zv_organizations` DISABLE KEYS */;
/*!40000 ALTER TABLE `zv_organizations` ENABLE KEYS */;

-- Copiando estrutura para tabela zetta_vault.zv_sections
DROP TABLE IF EXISTS `zv_sections`;
CREATE TABLE IF NOT EXISTS `zv_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6F025C129B6B5FBA` (`account_id`),
  CONSTRAINT `FK_6F025C129B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `zv_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela zetta_vault.zv_sections: ~3 rows (aproximadamente)
/*!40000 ALTER TABLE `zv_sections` DISABLE KEYS */;
/*!40000 ALTER TABLE `zv_sections` ENABLE KEYS */;

-- Copiando estrutura para tabela zetta_vault.zv_tags
DROP TABLE IF EXISTS `zv_tags`;
CREATE TABLE IF NOT EXISTS `zv_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `encrypted` tinyint(1) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela zetta_vault.zv_tags: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `zv_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `zv_tags` ENABLE KEYS */;

-- Copiando estrutura para tabela zetta_vault.zv_tag_accounts
DROP TABLE IF EXISTS `zv_tag_accounts`;
CREATE TABLE IF NOT EXISTS `zv_tag_accounts` (
  `tag_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  PRIMARY KEY (`tag_id`,`account_id`),
  KEY `IDX_DFB9DF2BAD26311` (`tag_id`),
  KEY `IDX_DFB9DF29B6B5FBA` (`account_id`),
  CONSTRAINT `FK_DFB9DF29B6B5FBA` FOREIGN KEY (`account_id`) REFERENCES `zv_accounts` (`id`),
  CONSTRAINT `FK_DFB9DF2BAD26311` FOREIGN KEY (`tag_id`) REFERENCES `zv_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela zetta_vault.zv_tag_accounts: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `zv_tag_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `zv_tag_accounts` ENABLE KEYS */;

-- Copiando estrutura para tabela zetta_vault.zv_tag_credentials
DROP TABLE IF EXISTS `zv_tag_credentials`;
CREATE TABLE IF NOT EXISTS `zv_tag_credentials` (
  `tag_id` int(11) NOT NULL,
  `credential_id` int(11) NOT NULL,
  PRIMARY KEY (`tag_id`,`credential_id`),
  KEY `IDX_8DA19A6BAD26311` (`tag_id`),
  KEY `IDX_8DA19A62558A7A5` (`credential_id`),
  CONSTRAINT `FK_8DA19A62558A7A5` FOREIGN KEY (`credential_id`) REFERENCES `zv_credentials` (`id`),
  CONSTRAINT `FK_8DA19A6BAD26311` FOREIGN KEY (`tag_id`) REFERENCES `zv_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela zetta_vault.zv_tag_credentials: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `zv_tag_credentials` DISABLE KEYS */;
/*!40000 ALTER TABLE `zv_tag_credentials` ENABLE KEYS */;

-- Copiando estrutura para tabela zetta_vault.zv_tag_sections
DROP TABLE IF EXISTS `zv_tag_sections`;
CREATE TABLE IF NOT EXISTS `zv_tag_sections` (
  `tag_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  PRIMARY KEY (`tag_id`,`section_id`),
  KEY `IDX_ECA540C6BAD26311` (`tag_id`),
  KEY `IDX_ECA540C6D823E37A` (`section_id`),
  CONSTRAINT `FK_ECA540C6BAD26311` FOREIGN KEY (`tag_id`) REFERENCES `zv_tags` (`id`),
  CONSTRAINT `FK_ECA540C6D823E37A` FOREIGN KEY (`section_id`) REFERENCES `zv_sections` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela zetta_vault.zv_tag_sections: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `zv_tag_sections` DISABLE KEYS */;
/*!40000 ALTER TABLE `zv_tag_sections` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
