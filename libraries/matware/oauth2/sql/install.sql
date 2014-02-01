DROP TABLE IF EXISTS `#__webservices_clients`;
CREATE TABLE IF NOT EXISTS `#__webservices_clients` (
  `client_id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `secret` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`client_id`),
  UNIQUE KEY `idx_services_clients_key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


DROP TABLE IF EXISTS `#__webservices_credentials`;
CREATE TABLE IF NOT EXISTS `#__webservices_credentials` (
  `credentials_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` varchar(255) NOT NULL DEFAULT '',
  `client_secret` varchar(255) NOT NULL DEFAULT '',
  `client_ip` varchar(255) NOT NULL,
  `temporary_token` varchar(255) NOT NULL,
  `access_token` varchar(255) NOT NULL DEFAULT '',
  `refresh_token` varchar(255) NOT NULL,
  `resource_uri` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT '',
  `callback_url` varchar(255) NOT NULL DEFAULT '',
  `resource_owner_id` int(11) NOT NULL DEFAULT '0',
  `expiration_date` datetime DEFAULT NULL,
  `temporary_expiration_date` datetime DEFAULT NULL,
  PRIMARY KEY (`credentials_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `#__webservices_tokens`;
CREATE TABLE IF NOT EXISTS `#__webservices_tokens` (
  `tokens_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` varchar(255) NOT NULL,
  `client_secret` varchar(255) NOT NULL,
  `access_token` varchar(255) NOT NULL DEFAULT '',
  `refresh_token` varchar(255) NOT NULL,
  `resource_uri` varchar(255) NOT NULL,
  `signature_method` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `expires_in` datetime NOT NULL,
  PRIMARY KEY (`tokens_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
