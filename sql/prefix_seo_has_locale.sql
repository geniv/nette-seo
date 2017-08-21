-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Počítač: localhost
-- Vytvořeno: Pon 21. srp 2017, 15:42
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
-- Struktura tabulky `zdm_seo_has_locale`
--

CREATE TABLE `zdm_seo_has_locale` (
  `id` int(11) NOT NULL,
  `id_locale` int(11) DEFAULT NULL,
  `id_seo` int(11) NOT NULL,
  `id_item` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `added` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `zdm_seo_has_locale`
--
ALTER TABLE `zdm_seo_has_locale`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `locale_title_description_UNIQUE` (`id_locale`,`title`,`description`),
  ADD KEY `fk_seo_has_locale_locale_idx` (`id_locale`),
  ADD KEY `fk_seo_has_locale_seo_idx` (`id_seo`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `zdm_seo_has_locale`
--
ALTER TABLE `zdm_seo_has_locale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `zdm_seo_has_locale`
--
ALTER TABLE `zdm_seo_has_locale`
  ADD CONSTRAINT `fk_seo_has_locale_locale` FOREIGN KEY (`id_locale`) REFERENCES `zdm_locale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_seo_has_locale_seo` FOREIGN KEY (`id_seo`) REFERENCES `zdm_seo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
