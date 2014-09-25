SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `capsules` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `capsules` ;

-- -----------------------------------------------------
-- Table `capsules`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `capsules`.`users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `token` TEXT NULL DEFAULT NULL,
  `ctag_capsules` VARCHAR(255) NOT NULL,
  `ctag_discoveries` VARCHAR(255) NOT NULL,
  `created` DATETIME NOT NULL,
  `modified` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `capsules`.`capsules`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `capsules`.`capsules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `lat` FLOAT(10,6) NOT NULL,
  `lng` FLOAT(10,6) NOT NULL,
  `point` POINT NOT NULL,
  `etag` VARCHAR(255) NOT NULL,
  `created` DATETIME NOT NULL,
  `modified` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `capsules_users_idx` (`user_id` ASC),
  CONSTRAINT `capsules_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `capsules`.`users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `capsules`.`memoirs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `capsules`.`memoirs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `capsule_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NULL DEFAULT NULL,
  `file` TEXT NOT NULL,
  `order` INT UNSIGNED NOT NULL,
  `created` DATETIME NOT NULL,
  `modified` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `memoirs_capsules_idx` (`capsule_id` ASC),
  CONSTRAINT `memoirs_capsules`
    FOREIGN KEY (`capsule_id`)
    REFERENCES `capsules`.`capsules` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `capsules`.`discoveries`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `capsules`.`discoveries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `capsule_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `favorite` TINYINT(1) NOT NULL DEFAULT 0,
  `rating` TINYINT(2) NOT NULL DEFAULT 0,
  `etag` VARCHAR(255) NOT NULL,
  `created` DATETIME NOT NULL,
  `modified` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `discoveries_capsules_idx` (`capsule_id` ASC),
  INDEX `discoveries_users_idx` (`user_id` ASC),
  CONSTRAINT `discoveries_capsules`
    FOREIGN KEY (`capsule_id`)
    REFERENCES `capsules`.`capsules` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `discoveries_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `capsules`.`users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
