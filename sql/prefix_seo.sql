-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Počítač: localhost
-- Vytvořeno: Stř 23. srp 2017, 17:16
-- Verze serveru: 10.0.31-MariaDB-0ubuntu0.16.04.2
-- Verze PHP: 7.0.22-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `netteweb`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `prefix_seo`
--

CREATE TABLE `prefix_seo` (
  `id` int(11) NOT NULL,
  `id_locale` int(11) DEFAULT NULL COMMENT 'vazba na jazyk',
  `id_ident` int(11) NOT NULL COMMENT 'vazba na ident',
  `id_item` int(11) DEFAULT NULL COMMENT 'id polozky',
  `title` varchar(255) DEFAULT NULL COMMENT 'titulek',
  `description` varchar(255) DEFAULT NULL COMMENT 'popisek'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='seo';

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `prefix_seo`
--
ALTER TABLE `prefix_seo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_seo_locale_idx` (`id_locale`),
  ADD KEY `fk_seo_seo_ident_idx` (`id_ident`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `prefix_seo`
--
ALTER TABLE `prefix_seo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `prefix_seo`
--
ALTER TABLE `prefix_seo`
  ADD CONSTRAINT `fk_seo_locale` FOREIGN KEY (`id_locale`) REFERENCES `prefix_locale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_seo_seo_ident` FOREIGN KEY (`id_ident`) REFERENCES `prefix_seo_ident` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
