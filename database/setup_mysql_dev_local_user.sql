-- Dev-MySQL-Benutzer `local` und Datenbank `luca` anlegen.
-- Als Administrator ausführen, z. B.:
--   mysql -h 127.0.0.1 -P 3307 -u root < database/setup_mysql_dev_local_user.sql   (Herd Pro, Port ggf. prüfen)
--   mysql -h 127.0.0.1 -P 3306 -u root -p < database/setup_mysql_dev_local_user.sql   (eigenes MySQL)
--
-- Passwort unten bei Bedarf ändern (und in .env DB_PASSWORD eintragen).
-- Wenn `local` noch existiert, aber das Passwort falsch ist, zuerst auskommentiert ausführen:
-- DROP USER IF EXISTS `local`@`127.0.0.1`;
-- DROP USER IF EXISTS `local`@`localhost`;

CREATE DATABASE IF NOT EXISTS `luca` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS `local`@`127.0.0.1` IDENTIFIED BY 'local';
CREATE USER IF NOT EXISTS `local`@`localhost` IDENTIFIED BY 'local';

GRANT ALL PRIVILEGES ON `luca`.* TO `local`@`127.0.0.1`;
GRANT ALL PRIVILEGES ON `luca`.* TO `local`@`localhost`;

FLUSH PRIVILEGES;
