-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 18, 2025 at 10:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `STRIMR`
--

-- --------------------------------------------------------

--
-- Table structure for table `Comments`
--

CREATE TABLE `Comments` (
  `id` varchar(100) NOT NULL,
  `post_id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Communities`
--

CREATE TABLE `Communities` (
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
-- Table structure for table `CommunityMembers`
--

CREATE TABLE `CommunityMembers` (
  `id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `community_id` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `joined_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `MarketplaceItems`
--

CREATE TABLE `MarketplaceItems` (
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
-- Table structure for table `MarketplaceOrders`
--

CREATE TABLE `MarketplaceOrders` (
  `id` varchar(100) NOT NULL,
  `buyer_id` varchar(100) NOT NULL,
  `item_id` varchar(100) NOT NULL,
  `price` float NOT NULL,
  `purchased_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Posts`
--

CREATE TABLE `Posts` (
  `id` varchar(100) NOT NULL,
  `author_id` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `image_url` text DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Reactions`
--

CREATE TABLE `Reactions` (
  `id` varchar(100) NOT NULL,
  `post_id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `StreamDonations`
--

CREATE TABLE `StreamDonations` (
  `id` varchar(100) NOT NULL,
  `stream_id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `amount` float NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Streams`
--

CREATE TABLE `Streams` (
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
-- Table structure for table `TextChannels`
--

CREATE TABLE `TextChannels` (
  `id` varchar(100) NOT NULL,
  `community_id` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `TextMessages`
--

CREATE TABLE `TextMessages` (
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
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `id` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` text NOT NULL,
  `created_at` datetime NOT NULL,
  `avatar_url` text DEFAULT NULL,
  `biography` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `VoiceChannels`
--

CREATE TABLE `VoiceChannels` (
  `id` varchar(100) NOT NULL,
  `community_id` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `VoiceChannelUsers`
--

CREATE TABLE `VoiceChannelUsers` (
  `id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `voice_channel_id` varchar(100) NOT NULL,
  `joined_at` datetime NOT NULL,
  `left_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Comments`
--
ALTER TABLE `Comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Communities`
--
ALTER TABLE `Communities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `CommunityMembers`
--
ALTER TABLE `CommunityMembers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `community_id` (`community_id`);

--
-- Indexes for table `MarketplaceItems`
--
ALTER TABLE `MarketplaceItems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `MarketplaceOrders`
--
ALTER TABLE `MarketplaceOrders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `Posts`
--
ALTER TABLE `Posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `Reactions`
--
ALTER TABLE `Reactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `StreamDonations`
--
ALTER TABLE `StreamDonations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stream_id` (`stream_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Streams`
--
ALTER TABLE `Streams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `streamer_id` (`streamer_id`);

--
-- Indexes for table `TextChannels`
--
ALTER TABLE `TextChannels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `community_id` (`community_id`);

--
-- Indexes for table `TextMessages`
--
ALTER TABLE `TextMessages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `channel_id` (`channel_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `VoiceChannels`
--
ALTER TABLE `VoiceChannels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `community_id` (`community_id`);

--
-- Indexes for table `VoiceChannelUsers`
--
ALTER TABLE `VoiceChannelUsers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `voice_channel_id` (`voice_channel_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Comments`
--
ALTER TABLE `Comments`
  ADD CONSTRAINT `Comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `Posts` (`id`),
  ADD CONSTRAINT `Comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

--
-- Constraints for table `Communities`
--
ALTER TABLE `Communities`
  ADD CONSTRAINT `Communities_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `Users` (`id`);

--
-- Constraints for table `CommunityMembers`
--
ALTER TABLE `CommunityMembers`
  ADD CONSTRAINT `CommunityMembers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`),
  ADD CONSTRAINT `CommunityMembers_ibfk_2` FOREIGN KEY (`community_id`) REFERENCES `Communities` (`id`);

--
-- Constraints for table `MarketplaceItems`
--
ALTER TABLE `MarketplaceItems`
  ADD CONSTRAINT `MarketplaceItems_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `Users` (`id`);

--
-- Constraints for table `MarketplaceOrders`
--
ALTER TABLE `MarketplaceOrders`
  ADD CONSTRAINT `MarketplaceOrders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `Users` (`id`),
  ADD CONSTRAINT `MarketplaceOrders_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `MarketplaceItems` (`id`);

--
-- Constraints for table `Posts`
--
ALTER TABLE `Posts`
  ADD CONSTRAINT `Posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `Users` (`id`);

--
-- Constraints for table `Reactions`
--
ALTER TABLE `Reactions`
  ADD CONSTRAINT `Reactions_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `Posts` (`id`),
  ADD CONSTRAINT `Reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

--
-- Constraints for table `StreamDonations`
--
ALTER TABLE `StreamDonations`
  ADD CONSTRAINT `StreamDonations_ibfk_1` FOREIGN KEY (`stream_id`) REFERENCES `Streams` (`id`),
  ADD CONSTRAINT `StreamDonations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

--
-- Constraints for table `Streams`
--
ALTER TABLE `Streams`
  ADD CONSTRAINT `Streams_ibfk_1` FOREIGN KEY (`streamer_id`) REFERENCES `Users` (`id`);

--
-- Constraints for table `TextChannels`
--
ALTER TABLE `TextChannels`
  ADD CONSTRAINT `TextChannels_ibfk_1` FOREIGN KEY (`community_id`) REFERENCES `Communities` (`id`);

--
-- Constraints for table `TextMessages`
--
ALTER TABLE `TextMessages`
  ADD CONSTRAINT `TextMessages_ibfk_1` FOREIGN KEY (`channel_id`) REFERENCES `TextChannels` (`id`),
  ADD CONSTRAINT `TextMessages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

--
-- Constraints for table `VoiceChannels`
--
ALTER TABLE `VoiceChannels`
  ADD CONSTRAINT `VoiceChannels_ibfk_1` FOREIGN KEY (`community_id`) REFERENCES `Communities` (`id`);

--
-- Constraints for table `VoiceChannelUsers`
--
ALTER TABLE `VoiceChannelUsers`
  ADD CONSTRAINT `VoiceChannelUsers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`),
  ADD CONSTRAINT `VoiceChannelUsers_ibfk_2` FOREIGN KEY (`voice_channel_id`) REFERENCES `VoiceChannels` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
