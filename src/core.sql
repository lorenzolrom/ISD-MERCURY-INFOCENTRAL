CREATE TABLE rest_AppToken (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  token CHAR(128) NOT NULL UNIQUE,
  name VARCHAR(64) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE rest_Route (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  path VARCHAR(64) NOT NULL UNIQUE,
  extension VARCHAR(64) NOT NULL,
  controller VARCHAR(64) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE rest_AppToken_Route (
                                     token BIGINT(20) NOT NULL,
                                     route BIGINT(20) NOT NULL,
                                     PRIMARY KEY (token, route),
                                     FOREIGN KEY (token) REFERENCES rest_AppToken(id) ON UPDATE CASCADE ON DELETE CASCADE,
                                     FOREIGN KEY (route) REFERENCES rest_Route(id) ON UPDATE CASCADE ON DELETE CASCADE
  );