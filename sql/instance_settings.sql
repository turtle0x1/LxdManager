CREATE TABLE `Instance_Settings` (
    `IS_ID` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `IS_Name` VARCHAR(255) NOT NULL,
    `IS_Description` TEXT NOT NULL
);

INSERT INTO `Instance_Settings` (`IS_ID`, `IS_Name`, `IS_Description`) VALUES (
    1, "Instance IP", "The connection string containers can reach this instance of LXDMosaic's node server to phone home when they are part of a deployment"
);

CREATE TABLE `Instance_Settings_Values` (
    `ISV_ID` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `ISV_Date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `ISV_IS_ID` INT(11) NOT NULL,
    `ISV_Value` VARCHAR(255) NOT NULL
);