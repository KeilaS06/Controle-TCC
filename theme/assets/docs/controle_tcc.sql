CREATE DATABASE controle_tcc;
USE controle_tcc;
-- DROP DATABASE controle_tcc;

CREATE TABLE IF NOT EXISTS `usuarios` (
	`id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `username` VARCHAR(30) NOT NULL,
  `password` VARCHAR(120) NOT NULL,
  `access` CHAR(1) NOT NULL,
	`group_id` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS `composicao` (
	`id` INT PRIMARY KEY AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `grupo_id` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS `grupos` (
	`id` INT PRIMARY KEY AUTO_INCREMENT,
	`name` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `teacher_id_group` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS `entregas` (
	`id` INT PRIMARY KEY AUTO_INCREMENT,
	`name` VARCHAR(120) NOT NULL,
  `user` VARCHAR(120),
	`date` DATE NOT NULL,
	`date_delivery` DATE,
  `note` VARCHAR(5),
  `filename` VARCHAR(120),
	`grupo` INT NULL,
	`teacher_id_entregas` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

ALTER TABLE `entregas` ADD CONSTRAINT `task_teacher_fk` FOREIGN KEY (`teacher_id_entregas`) REFERENCES
`usuarios` (`id`) ON DELETE CASCADE;

ALTER TABLE `usuarios` ADD CONSTRAINT `group_user_fk` FOREIGN KEY (`group_id`) REFERENCES
`grupos` (`id`) ON DELETE CASCADE;

ALTER TABLE `grupos` ADD CONSTRAINT `group_teacher_fk` FOREIGN KEY (`teacher_id_group`) REFERENCES
`usuarios` (`id`) ON DELETE CASCADE;

ALTER TABLE `composicao` ADD CONSTRAINT `composicao_usuario_fk` FOREIGN KEY (`usuario_id`) REFERENCES
`usuarios` (`id`) ON DELETE CASCADE;

ALTER TABLE `composicao` ADD CONSTRAINT `composicao_grupo_fk` FOREIGN KEY (`grupo_id`) REFERENCES
`grupos` (`id`) ON DELETE CASCADE;
