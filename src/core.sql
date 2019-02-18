CREATE TABLE fa_AppToken (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  token CHAR(128) NOT NULL UNIQUE,
  name VARCHAR(64) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE fa_Route (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  path VARCHAR(64) NOT NULL UNIQUE,
  extension VARCHAR(64) DEFAULT NULL,
  controller VARCHAR(64) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE fa_AppToken_Route (
  token BIGINT(20) NOT NULL,
  route BIGINT(20) NOT NULL,
  PRIMARY KEY (token, route),
  FOREIGN KEY (token) REFERENCES fa_AppToken(id) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (route) REFERENCES fa_Route(id) ON UPDATE CASCADE ON DELETE CASCADE
  );

CREATE TABLE fa_User (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  loginName VARCHAR(64) NOT NULL UNIQUE,
  authType ENUM('local','ldap') NOT NULL DEFAULT 'local',
  password CHAR(128) DEFAULT NULL,
  firstName VARCHAR(32) NOT NULL,
  lastName VARCHAR(32) NOT NULL,
  displayName TEXT DEFAULT NULL,
  email VARCHAR(256) DEFAULT NULL UNIQUE,
  disabled TINYINT(1) DEFAULT 0,
  PRIMARY KEY(id)
);

CREATE TABLE fa_UserToken (
  token CHAR(128) NOT NULL,
  user BIGINT(20) NOT NULL,
  issueTime DATETIME NOT NULL,
  expireTime DATETIME NOT NULL,
  expired TINYINT(1) DEFAULT 0,
  ipAddress VARCHAR(39) NOT NULL,
  PRIMARY KEY (token),
  FOREIGN KEY (user) REFERENCES fa_User(id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE fa_Role (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  displayName VARCHAR(64) NOT NULL UNIQUE,
  PRIMARY KEY (id)
);

CREATE TABLE fa_User_Role (
  user BIGINT(20) NOT NULL,
  role BIGINT(20) NOT NULL,
  PRIMARY KEY (user, role),
  FOREIGN KEY (user) REFERENCES fa_User(id) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (role) REFERENCES fa_Role(id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE fa_Permission (
  code VARCHAR(64) NOT NULL,
  displayName VARCHAR(64) NOT NULL UNIQUE,
  description TEXT NOT NULL,
  PRIMARY KEY (code)
);

CREATE TABLE fa_Role_Permission (
  role BIGINT(20) NOT NULL,
  permission VARCHAR(64) NOT NULL,
  PRIMARY KEY (role, permission),
  FOREIGN KEY (role) REFERENCES fa_Role(id) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (permission) REFERENCES fa_Permission(code) ON UPDATE CASCADE ON DELETE CASCADE
);

INSERT INTO fa_Route (path, controller) VALUES ('authenticate', 'Authenticate'),
                                               ('users', 'User');

INSERT INTO isd_fastapps_rest.fa_Permission (code, displayName, description) VALUES ('fa-users-listuserids', 'List All User IDs', 'Allow retrieval of the list of user IDs'),
                                                                                    ('fa-users-listloginnames', 'List All Login Names', 'Allow retrieval of the list of login names.'),
                                                                                    ('fa-users-showuserdetails', 'Display User Details', 'Allows viewing full details for a user'),
                                                                                    ('fa-users-showuserroles', 'Display User Roles', 'Allow viewing roles a user belongs to'),
                                                                                    ('fa-roles-listroleids', 'List All Role IDs', 'Allow retrieval of the list of role IDs'),
                                                                                    ('fa-roles-showroledetails', 'Display Role Details', 'Allow viewing full details for a role'),
                                                                                    ('fa-roles-showrolepermissions', 'Display Role Permissions', 'Allow viewing permissions assigned to a role');