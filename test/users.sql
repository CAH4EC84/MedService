-- phpMyAdmin SQL Dump
-- version 2.10.1
-- http://www.phpmyadmin.net
-- 
-- Хост: localhost
-- Время создания: Май 21 2009 г., 20:59
-- Версия сервера: 5.0.45
-- Версия PHP: 5.2.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- База данных: `jqGridDB`
-- 

-- --------------------------------------------------------

-- 
-- Структура таблицы `users`
-- 

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `surname` varchar(30) collate utf8_unicode_ci NOT NULL,
  `fname` varchar(30) collate utf8_unicode_ci NOT NULL,
  `lname` varchar(30) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

-- 
-- Дамп данных таблицы `users`
-- 

INSERT INTO `users` (`id`, `surname`, `fname`, `lname`) VALUES 
(1, 'Иванов', 'Иван', 'Иванович'),
(2, 'Петров', 'Петр', 'Петрович'),
(3, 'Сидоров', 'Сидор', 'Сидорович'),
(4, 'Сергеев', 'Сергей', 'Сергеевич'),
(5, 'Васильев', 'Василий', 'Васильевич'),
(6, 'Тарасов', 'Тарас', 'Тарасович');
