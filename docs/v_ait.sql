-- Application TODO

-- App - VHOST TODO

-- App - Host TODO

-- App Dev Update TODO

--
-- DEFAULT DATA
--

-- Attributes (aitt = App Type, aitd = App Data Capacity, aitl = App Lifespan, aits = App Status, aita = App Auth Type)
INSERT INTO `Attribute` (`extension`, `type`, `code`, `name`) VALUES
('itsm', 'aitt', 'infr', 'Infrastructure Service'),
('itsm', 'aitt', 'weba', 'Web Application'),
('itsm', 'aitt', 'osap', 'O.S. Application'),
('itsm', 'aitd', 'lt1g', '<1 GB'),
('itsm', 'aitd', '110g', '1-10 GB'),
('itsm', 'aitd', '1150', '11-50 GB'),
('itsm', 'aitd', '5110', '51-100 GB'),
('itsm', 'aitd', '1015', '101-500 GB'),
('itsm', 'aitd', '501t', '501 GB - 1 TB'),
('itsm', 'aitd', 'gt1t', '>1 TB'),
('itsm', 'aitl', '1t5y', '1-5 Years'),
('itsm', 'aitl', '5t10', '5-10 Years'),
('itsm', 'aitl', 'gt10', '>10 Years'),
('itsm', 'aita', 'loca', 'Local D.B.'),
('itsm', 'aita', 'extn', 'External D.B.'),
('itsm', 'aita', 'ldap', 'LDAP'),
('itsm', 'aita', 'sson', 'Single Sign-On'),
('itsm', 'aita', 'none', 'None'),
('itsm', 'aits', 'addd', 'Added'),
('itsm', 'aits', 'inde', 'In Development'),
('itsm', 'aits', 'pror', 'Project Rejected'),
('itsm', 'aits', 'inpr', 'In Production'),
('itsm', 'aits', 'prjr', 'Projected Retire'),
('itsm', 'aits', 'read', 'Retain App & Data'),
('itsm', 'aits', 'rdao', 'Retain Data Only'),
('itsm', 'aits', 'reti', 'Retired'),
('itsm', 'aits', 'deco', 'Decomissioned');