-- =============================================================
-- Smallproject Schema kept simple. Comments seem to have been 
-- deleted when done through PuTTy -> Mysql so I have taken
-- the schema dump from mysql and added them.
-- =============================================================

-- Create the database (no-op if it already exists), keep your server defaults.
CREATE DATABASE IF NOT EXISTS `Smallproject`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

-- Set database.
USE `Smallproject`;

-- Table: Users
-- Stores application user accounts
-- Each user has one unique login/username

CREATE TABLE `Users` (
  `ID` int NOT NULL AUTO_INCREMENT,                     -- Primary key for each user
  `FirstName` varchar(50) NOT NULL DEFAULT '',          -- User's first name
  `LastName`  varchar(50) NOT NULL DEFAULT '',          -- User's last name
  `Login`     varchar(50) NOT NULL,                     -- Username used at login
  `Password`  varchar(64) NOT NULL,                     -- Hashed password string
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uq_users_login` (`Login`)                 -- Enforces unique usernames
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- Table: Contacts
-- Stores contacts that belong to a specific user.
-- A user can have many contacts; contacts are deleted automatically
-- if their owning user is deleted.

CREATE TABLE `Contacts` (
  `ID` int NOT NULL AUTO_INCREMENT,                     -- Primary key for each contact
  `UserID` int NOT NULL,                                -- FK to Users.ID
  `FirstName` varchar(50) NOT NULL DEFAULT '',          -- Contact's first name
  `LastName`  varchar(50) NOT NULL DEFAULT '',          -- Contact's last name
  `Email`     varchar(100) NOT NULL DEFAULT '',         -- Contact's email 
  `Phone`     varchar(50) NOT NULL DEFAULT '',          -- Contact's phone number
  `DateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, 
                                                        -- When the contact was created
  `UpdatedAt`   timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 
                     ON UPDATE CURRENT_TIMESTAMP,       -- Auto updated when the row changes
  PRIMARY KEY (`ID`),
  UNIQUE KEY `uq_user_email` (`UserID`,`Email`),        -- Prevents duplicate emails for same user
  KEY `ix_contacts_user`  (`UserID`),                   -- Speeds up lookups by owner
  KEY `ix_contacts_name`  (`LastName`,`FirstName`),     -- Speeds up name searches
  KEY `ix_contacts_email` (`Email`),                    -- Speeds up email searches
  KEY `ix_contacts_phone` (`Phone`),                    -- Speeds up phone searches
  CONSTRAINT `fk_contacts_user`
    FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`)
    ON DELETE CASCADE                                   -- Delete contacts when user is deleted
    ON UPDATE CASCADE                                   -- Keep FK in sync if user ID changes
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

