<?php
require_once 'init.php';

$db = Database::getInstance();

$users = $db->get('users', ['id', '>=', 0]); //получаем всех пользователей из таблицы

$user_id = $db->get('users', ['id', '=', 5]); //получаем пользователя по id