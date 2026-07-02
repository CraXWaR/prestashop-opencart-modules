CREATE TABLE IF NOT EXISTS `PREFIX_inquiry` (
    `id_inquiry` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `approved` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `admin_reply` TEXT NULL,
    `id_employee` INT(11) UNSIGNED NULL DEFAULT NULL,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_inquiry`),
    KEY `approved` (`approved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_inquiry_product` (
    `id_inquiry` INT(11) UNSIGNED NOT NULL,
    `id_product` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_inquiry`, `id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_inquiry_category` (
    `id_inquiry` INT(11) UNSIGNED NOT NULL,
    `id_category` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_inquiry`, `id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;