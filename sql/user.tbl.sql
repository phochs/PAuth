CREATE TABLE `user` (
	userId VARCHAR(36) NOT NULL,
	password VARCHAR(60) NOT NULL,
	salt VARCHAR(255) NOT NULL,
	username VARCHAR(255) NOT NULL,
	email VARCHAR(255) NOT NULL,
	name VARCHAR(255) NOT NULL,
	gender ENUM('m', 'f', 'c') NOT NULL,
	creationTime INT(10) NOT NULL,
	lastVisit INT(10) NOT NULL,
	PRIMARY KEY (userId)
) ENGINE=MyISAM