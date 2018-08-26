-- --------------------------------------------------------

--
-- Structure de la table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `grade` int(11) NOT NULL,
  `sign_in_ip` varchar(50) NOT NULL,
  `last_log_in_ip` varchar(50) NOT NULL,
  `sign_in_timestamp` varchar(50) NOT NULL,
  `last_log_in_timestamp` varchar(50) NOT NULL,
  `last_activity_timestamp` varchar(50) NOT NULL,
  `last_messenger_activity_timestamp` varchar(50) NOT NULL,
  `sign_in_user_agent` text NOT NULL,
  `last_log_in_user_agent` text NOT NULL,
  `token` varchar(50) NOT NULL,
  `banned_until` varchar(50) NOT NULL,
  `bonus` int(11) NOT NULL,
  `last_retreive_password_timestamp` varchar(50) NOT NULL,
  `cheat` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `actions`
--

CREATE TABLE `actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `command` text NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `full_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `applications_by_players`
--

CREATE TABLE `applications_by_players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `ban_ip`
--

CREATE TABLE `ban_ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `bind`
--

CREATE TABLE `bind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) NOT NULL,
  `shortcut` varchar(50) NOT NULL,
  `command` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `bonus_logs`
--

CREATE TABLE `bonus_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(50) NOT NULL,
  `date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `decryptlab_rooms`
--

CREATE TABLE `decryptlab_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator_id` int(11) NOT NULL,
  `bet` int(11) NOT NULL,
  `revelation_timestamp` varchar(50) NOT NULL,
  `decrypted` varchar(100) NOT NULL,
  `encrypted` varchar(100) NOT NULL,
  `encryption_method` varchar(100) NOT NULL,
  `participants` varchar(5000) NOT NULL,
  `won_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `infiltrations_history`
--

CREATE TABLE `infiltrations_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `slug` varchar(500) NOT NULL,
  `owner` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` varchar(50) NOT NULL,
  `pseudo` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `account_session` text NOT NULL,
  `server_session` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `matrice`
--

CREATE TABLE `matrice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `tokens` int(11) NOT NULL,
  `won_by` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `messenger`
--

CREATE TABLE `messenger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `message` varchar(500) NOT NULL,
  `mpto` varchar(50) NOT NULL,
  `timestamp` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `mp`
--

CREATE TABLE `mp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `has_been_read` int(11) NOT NULL,
  `timestamp` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `players`
--

CREATE TABLE `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `level` int(11) NOT NULL COMMENT 'nbRams + (nbServs)Carré - 6',
  `tokens` int(11) NOT NULL,
  `servers` int(11) NOT NULL COMMENT 'Total des serveurs',
  `rams` int(11) NOT NULL COMMENT 'Total des rams',
  `average_servers_security` int(11) NOT NULL,
  `mission_id` int(11) NOT NULL,
  `hacked_servers` int(11) NOT NULL,
  `last_hacked_timestamp` varchar(50) NOT NULL,
  `last_hacked_player_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `pub_logs`
--

CREATE TABLE `pub_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `servers`
--

CREATE TABLE `servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `code` varchar(500) NOT NULL,
  `slug` varchar(500) NOT NULL,
  `rams` int(11) NOT NULL,
  `ram_containers` int(11) NOT NULL,
  `time_worked` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `sites`
--

CREATE TABLE `sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) NOT NULL,
  `adress` varchar(25) NOT NULL,
  `content` text NOT NULL,
  `hosted_on` varchar(50) NOT NULL,
  `nb_links` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
