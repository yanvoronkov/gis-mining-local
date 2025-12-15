<?php
/* Ansible managed */
$connection = \Bitrix\Main\Application::getConnection();

$connection->queryExecute("SET NAMES 'utf8mb4'");
$connection->queryExecute("SET collation_connection = 'utf8mb4_0900_ai_ci'");

