-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 17 déc. 2025 à 22:52
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `strimr`
--

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

CREATE TABLE `comments` (
  `id` varchar(100) NOT NULL,
  `post_id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
('comment_1765310340_ae24f4002114ee09d30e92f3', 'post_1765310336_81a49defbd342e0517ca99a3', '1', 'uyj', '2025-12-09 20:59:00'),
('comment_1765310548_38d54975261219be0ade84c6', 'post_1765310336_81a49defbd342e0517ca99a3', '1', 'srg', '2025-12-09 21:02:28'),
('comment_1765310664_eceb479ca5be36181612e91d', 'post_1765310336_81a49defbd342e0517ca99a3', '1', 'd', '2025-12-09 21:04:24'),
('comment_1765310673_629f2b793ee4e545273ec2bb', 'post_1765310336_81a49defbd342e0517ca99a3', '1', 'p\'\'\'\'\'\'\'', '2025-12-09 21:04:33');

-- --------------------------------------------------------

--
-- Structure de la table `communities`
--

CREATE TABLE `communities` (
  `id` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `admin_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `communitymembers`
--

CREATE TABLE `communitymembers` (
  `id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `community_id` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `joined_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `marketplaceitems`
--

CREATE TABLE `marketplaceitems` (
  `id` varchar(100) NOT NULL,
  `seller_id` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` float NOT NULL,
  `image_url` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `marketplaceorders`
--

CREATE TABLE `marketplaceorders` (
  `id` varchar(100) NOT NULL,
  `buyer_id` varchar(100) NOT NULL,
  `item_id` varchar(100) NOT NULL,
  `price` float NOT NULL,
  `purchased_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `posts`
--

CREATE TABLE `posts` (
  `id` varchar(100) NOT NULL,
  `author_id` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `image_url` text DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `posts`
--

INSERT INTO `posts` (`id`, `author_id`, `content`, `image_url`, `created_at`) VALUES
('post_1764630597_5cdb472cb8336bfcb496a244', '1', 'bg', NULL, '2025-12-02 00:09:57'),
('post_1765310184_f2001b4a104f1ac579810f01', '1', 'hgf', NULL, '2025-12-09 20:56:25'),
('post_1765310192_bf911b1ef230a2917e84ecf2', '1', 'gf', NULL, '2025-12-09 20:56:32'),
('post_1765310289_9cfafddf517a5f4ce7cec1f2', '1', 'ilo', NULL, '2025-12-09 20:58:09'),
('post_1765310336_81a49defbd342e0517ca99a3', '1', 'ftg', NULL, '2025-12-09 20:58:56'),
('post_1765396324_3494000b607a35e4e02ac521', '1', 'fytrjk', NULL, '2025-12-10 20:53:59'),
('post_1765396324_6a7a9fb0ea25899e07977fcd', '1', 'fytrjk', NULL, '2025-12-10 20:53:59'),
('post_1765396324_90978984750fee78393b0e29', '1', 'fytrjk', NULL, '2025-12-10 20:53:59'),
('post_1765396324_dfb58b259a65be004e62326f', '1', 'fytrjk', NULL, '2025-12-10 20:53:59'),
('post_1765396324_e82709262a67a928219e2261', '1', 'fytrjk', NULL, '2025-12-10 20:53:59'),
('post_1765396324_ef337ef2f5decfd9f0b015b3', '1', 'fytrjk', NULL, '2025-12-10 20:53:59');

-- --------------------------------------------------------

--
-- Structure de la table `reactions`
--

CREATE TABLE `reactions` (
  `id` varchar(100) NOT NULL,
  `post_id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reactions`
--

INSERT INTO `reactions` (`id`, `post_id`, `user_id`, `type`, `created_at`) VALUES
('reaction_1764632323_7f52c21d8039de05ad8f8bfd', 'post_1764630597_5cdb472cb8336bfcb496a244', '1', 'heart', '2025-12-02 00:38:43');

-- --------------------------------------------------------

--
-- Structure de la table `streamdonations`
--

CREATE TABLE `streamdonations` (
  `id` varchar(100) NOT NULL,
  `stream_id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `amount` float NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `streams`
--

CREATE TABLE `streams` (
  `id` varchar(100) NOT NULL,
  `streamer_id` varchar(100) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `likes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `textchannels`
--

CREATE TABLE `textchannels` (
  `id` varchar(100) NOT NULL,
  `community_id` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `textmessages`
--

CREATE TABLE `textmessages` (
  `id` varchar(100) NOT NULL,
  `channel_id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `image_url` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` text NOT NULL,
  `created_at` datetime NOT NULL,
  `avatar_url` text DEFAULT NULL,
  `biography` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`, `avatar_url`, `biography`) VALUES
('1', 'testuser', 'test@example.com', 'dummyhash', '2025-11-25 23:38:23', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `voicechannels`
--

CREATE TABLE `voicechannels` (
  `id` varchar(100) NOT NULL,
  `community_id` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `voicechannelusers`
--

CREATE TABLE `voicechannelusers` (
  `id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `voice_channel_id` varchar(100) NOT NULL,
  `joined_at` datetime NOT NULL,
  `left_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `communities`
--
ALTER TABLE `communities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Index pour la table `communitymembers`
--
ALTER TABLE `communitymembers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `community_id` (`community_id`);

--
-- Index pour la table `marketplaceitems`
--
ALTER TABLE `marketplaceitems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Index pour la table `marketplaceorders`
--
ALTER TABLE `marketplaceorders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Index pour la table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Index pour la table `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `streamdonations`
--
ALTER TABLE `streamdonations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stream_id` (`stream_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `streams`
--
ALTER TABLE `streams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `streamer_id` (`streamer_id`);

--
-- Index pour la table `textchannels`
--
ALTER TABLE `textchannels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `community_id` (`community_id`);

--
-- Index pour la table `textmessages`
--
ALTER TABLE `textmessages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `channel_id` (`channel_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `voicechannels`
--
ALTER TABLE `voicechannels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `community_id` (`community_id`);

--
-- Index pour la table `voicechannelusers`
--
ALTER TABLE `voicechannelusers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `voice_channel_id` (`voice_channel_id`);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `Comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`),
  ADD CONSTRAINT `Comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `communities`
--
ALTER TABLE `communities`
  ADD CONSTRAINT `Communities_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `communitymembers`
--
ALTER TABLE `communitymembers`
  ADD CONSTRAINT `CommunityMembers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `CommunityMembers_ibfk_2` FOREIGN KEY (`community_id`) REFERENCES `communities` (`id`);

--
-- Contraintes pour la table `marketplaceitems`
--
ALTER TABLE `marketplaceitems`
  ADD CONSTRAINT `MarketplaceItems_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `marketplaceorders`
--
ALTER TABLE `marketplaceorders`
  ADD CONSTRAINT `MarketplaceOrders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `MarketplaceOrders_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `marketplaceitems` (`id`);

--
-- Contraintes pour la table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `Posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `reactions`
--
ALTER TABLE `reactions`
  ADD CONSTRAINT `Reactions_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`),
  ADD CONSTRAINT `Reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `streamdonations`
--
ALTER TABLE `streamdonations`
  ADD CONSTRAINT `StreamDonations_ibfk_1` FOREIGN KEY (`stream_id`) REFERENCES `streams` (`id`),
  ADD CONSTRAINT `StreamDonations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `streams`
--
ALTER TABLE `streams`
  ADD CONSTRAINT `Streams_ibfk_1` FOREIGN KEY (`streamer_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `textchannels`
--
ALTER TABLE `textchannels`
  ADD CONSTRAINT `TextChannels_ibfk_1` FOREIGN KEY (`community_id`) REFERENCES `communities` (`id`);

--
-- Contraintes pour la table `textmessages`
--
ALTER TABLE `textmessages`
  ADD CONSTRAINT `TextMessages_ibfk_1` FOREIGN KEY (`channel_id`) REFERENCES `textchannels` (`id`),
  ADD CONSTRAINT `TextMessages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `voicechannels`
--
ALTER TABLE `voicechannels`
  ADD CONSTRAINT `VoiceChannels_ibfk_1` FOREIGN KEY (`community_id`) REFERENCES `communities` (`id`);

--
-- Contraintes pour la table `voicechannelusers`
--
ALTER TABLE `voicechannelusers`
  ADD CONSTRAINT `VoiceChannelUsers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `VoiceChannelUsers_ibfk_2` FOREIGN KEY (`voice_channel_id`) REFERENCES `voicechannels` (`id`);

-- --------------------------------------------------------

--
-- Structure de la table `servers`
--

CREATE TABLE IF NOT EXISTS `servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Données de démonstration pour `servers`
--

INSERT INTO `servers` (`id`, `name`, `created_at`) VALUES
(1, 'General Chat', NOW()),
(2, 'Gaming', NOW()),
(3, 'Music', NOW());

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `server_id` (`server_id`),
  CONSTRAINT `message_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
