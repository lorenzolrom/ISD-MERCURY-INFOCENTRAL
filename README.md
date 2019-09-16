# MERLOT Technical Manual  
LLR Technologies & Associated Services  
Information Systems Development

## About LLR Technologies

LLR Technologies is an organization that provides technology services to organizations and individuals.  The Information
Systems and Development division designs and implements software solutions to satisfy business needs.

## About Mercury

The Mercury Application Platform (MAP, or Mercury for short) is a family of interoperable applications aimed at 
consolidating and streamlining the storage and processing of business data.  The Mercury Platform is made up of 
four software packages:

* MAP InfoCentral, a data processing layer, taking instructions and providing information through a REST application 
programming interface

* MAP InfoScape, a web based data browsing and manipulation interface

* MAP CenterView, itself a platform for creating dashboards linked to InfoCentral data

* MAP DoorWay, an interface for web browsers to be re-directed to other sites based on information in InfoCentral

## About MERLOT

MERLOT stands for "Mercury Logistics & Operations Tools"; it is an ever expanding suite of information management 
utilities programmed into the MAP applications.   MERLOT is presently divided into five different modules:

1. Network Central (NetCenter)

2. Service Center

3. Facilities Management

4. Configuration

5. Self-Service

The specific use-cases and functions of each will be covered in a later section.

## Installation

### Database

MERLOT was written for MySQL-compatible databases, including MariaDB.  The 'docs' folder in the InfoCentral source code 
contains several SQL scripts that need to be executed in a database, either newly created or existing.  It is recommended 
to run MERLOT in its own database.

The SQL script named '_init.sql' __must__ be run first.  This scripts creates the following object tables:

* User

* Token

* Secret

* Role

* Permission

* Notification

* Bulletin

* Attribute

* History

* HistoryItem

the following associative tables:

* User_Role

* Role_Permission

* Secret_Permission

and imports a list of Permission objects that MERLOT will need to operate.  

You will need to configure a user, or have one configured already, in the database to allow InfoCentral (and whatever host 
it will be running on) to perform create, insert, update, and delete operations on any table in this database.

### Web Applications

All MAP applications are laid out with a single 'public' in the 'src' directory of the source code intended to be the 
publicly accessible web root. Other directories above 'public' contain class files and configurations that should not be 
exposed to the end user. The recommended configuration for MAP is with each application running in its own virtual host; 
each virtual host can have the 'public' folder defined as its 'DocumentRoot', eliminating the possibility of access to 
unintended parts of the application.  If one virtual host is desired, with different MAP applications as sub-directories, 
it is recommended to use symbolic links from the single DocumentRoot to the 'public' directory of the MAP applications, 
which should be installed outside this folder.  

With this setup in place, both applications can now be configured.

## Configuration

### InfoCentral

Locate 'Config-Generic.class.php' in the 'src' directory.  This file must be renamed to 'Config.class.php'.  
This file contains the following options in the OPTIONS constant array:  

* baseURL, the __full__ URL that InfoCentral is running on, e.x. 'https://mercury.test.com/infocentral'

* baseURI, any part after the specified baseURL that needs to be added to access InfoCentral, this can usually be left 
as '/'

* databaseHost, Name, User, Password all refer to your database server, database name, and user account

* salt, a SALT that will be used when encrypting passwords

* allowMultipleSessions, if true, logging in from another location will not invalidate a User session

* ldapEnabled, allow LDAP accounts to be created

* ldapDomainController, the FQDN of your authoritative domain controller

* ldapDomain, the common name for your domain, e.x. 'TEST'

* ldapDomainDn, the DN of your domain, e.x. 'dc=test, dc=local'

* ldapUsername, Password refer to a Domain Administrator account that can change passwords and perform queries

* emailEnabled, allow email notifications to be sent

* emailHost, Port, Auth, Username, Password refer to host names and credentials from your email provider

* emailFromAddress, the address emails will be marked as sent from

* emailFromName, the name that will show up for the 'from' address

* validWebRootPaths, an array of allowed paths for the web root of a VHost object

* validWebLogPaths, an array of allowed paths for the log file of a VHost

* serviceCenterAgentURL, the URL to be attached to an email when notifying a ticket agent of an update

* serviceCenterRequestURL, the URL to be attached to an email when notifying a ticket contact of an update

## Glossary

### Objects

1. User  
The entity that will be accessing the application and performing tasks.

1. Token  
The session of a user who is currently, or has been, logged in.  May be in an active or expired state.

1. Secret  
A key used to access the InfoCentral API without requiring a User.

1. Permission  
A static string that is used to determine access to parts of the application.

1. Role  
A set of Permission objects with a human-readable name.

1. Notification  
A message received by a User.

1. Bulletin  
A scheduled message tied to one or more Roles.

1. Attribute  
A state or descriptor tied to many different kinds of objects.

1. History  
A record of state change of an object, tied to the User who performed it.

1. HistoryItem  
An entry in a History object detailing the change in a specific attribute of an object.