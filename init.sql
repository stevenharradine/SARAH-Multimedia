-- phpMyAdmin SQL Dump
-- version 4.2.6deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 18, 2015 at 03:06 AM
-- Server version: 5.5.43-0ubuntu0.14.10.1
-- PHP Version: 5.5.12-2ubuntu4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `sarah`
--

-- --------------------------------------------------------

--
-- Table structure for table `music`
--

CREATE TABLE IF NOT EXISTS `music` (
`MUSIC_ID` int(11) NOT NULL,
  `path` text NOT NULL,
  `artist` text NOT NULL,
  `track` text NOT NULL,
  `album` text NOT NULL,
  `year` text NOT NULL,
  `track_no` int(11) NOT NULL DEFAULT '-1',
  `cover` text,
  `md5` text NOT NULL,
  `last_md5_pass` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `md5_checked` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11576 ;

-- --------------------------------------------------------

--
-- Table structure for table `music_playlist`
--

CREATE TABLE IF NOT EXISTS `music_playlist` (
`PLAYLIST_ID` int(11) NOT NULL,
  `MUSIC_ID` int(11) NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `playlist_name` text NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=407 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `music`
--
ALTER TABLE `music`
 ADD PRIMARY KEY (`MUSIC_ID`);

--
-- Indexes for table `music_playlist`
--
ALTER TABLE `music_playlist`
 ADD PRIMARY KEY (`PLAYLIST_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `music`
--
ALTER TABLE `music`
MODIFY `MUSIC_ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11576;
--
-- AUTO_INCREMENT for table `music_playlist`
--
ALTER TABLE `music_playlist`
MODIFY `PLAYLIST_ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=407;