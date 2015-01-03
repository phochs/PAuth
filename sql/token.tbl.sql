CREATE TABLE `authToken` (
	token VARCHAR(255) NOT NULL,
	userId VARCHAR(255) NOT NULL,
	IP VARCHAR(50) NOT NULL,
	IPVia VARCHAR(50) NULL,
	IPForward VARCHAR(50) NULL,
	userAgent VARCHAR(255) NOT NULL,
	userLanguage VARCHAR(255) NOT NULL,
	HTTPAccept VARCHAR(255) NOT NULL,
	expires INT(10) NOT NULL,
	PRIMARY KEY (token)
) ENGINE=MyISAM