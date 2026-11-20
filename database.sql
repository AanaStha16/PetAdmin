CREATE DATABASE IF NOT EXISTS PetDatabase CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE PetDatabase;
CREATE TABLE Pets (
  PetID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  Name VARCHAR(255) NOT NULL, Age INT UNSIGNED NOT NULL,
  Street VARCHAR(255) NULL, City VARCHAR(255) NULL,
  ZipCode VARCHAR(50) NULL, State VARCHAR(50) NULL,
  TypeofPet VARCHAR(255) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE Owners (
  OID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  LastName VARCHAR(255) NOT NULL,
  Street VARCHAR(255) NULL, City VARCHAR(255) NULL, ZipCode VARCHAR(50) NULL,
  State VARCHAR(50) NULL, Age INT UNSIGNED NULL,
  AnnualIncome DECIMAL(15,2) NULL
) ENGINE=InnoDB;
CREATE TABLE Owns (
  PetID INT UNSIGNED NOT NULL, `Year` INT NOT NULL, OID INT UNSIGNED NOT NULL,
  PetAgeatOwnership INT UNSIGNED NULL, PricePaid DECIMAL(10,2) NULL,
  PRIMARY KEY (PetID, OID),
  CONSTRAINT fk_owns_pet FOREIGN KEY (PetID) REFERENCES Pets(PetID) ON DELETE CASCADE,
  CONSTRAINT fk_owns_owner FOREIGN KEY (OID) REFERENCES Owners(OID) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE Likes (
  PetID INT UNSIGNED NOT NULL, TypeofFood VARCHAR(255) NOT NULL,
  PRIMARY KEY(PetID,TypeofFood),
  CONSTRAINT fk_likes_pet FOREIGN KEY(PetID) REFERENCES Pets(PetID) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE Foods (
  FoodID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  Name VARCHAR(255) NOT NULL, Brand VARCHAR(255) NOT NULL,
  TypeofFood VARCHAR(255) NOT NULL, Price DECIMAL(10,2) NOT NULL,
  ItemWeight DECIMAL(10,2) NULL, ClassofFood VARCHAR(255) NULL
) ENGINE=InnoDB;
CREATE TABLE Purchases (
  OID INT UNSIGNED NOT NULL, FoodID INT UNSIGNED NOT NULL, PetID INT UNSIGNED NOT NULL,
  `Month` INT NOT NULL, `Year` INT NOT NULL, Quantity INT UNSIGNED NOT NULL,
  PRIMARY KEY(OID, FoodID, PetID, `Month`, `Year`),
  CONSTRAINT fk_purchase_owner FOREIGN KEY(OID) REFERENCES Owners(OID) ON DELETE CASCADE,
  CONSTRAINT fk_purchase_food FOREIGN KEY(FoodID) REFERENCES Foods(FoodID) ON DELETE CASCADE,
  CONSTRAINT fk_purchase_pet FOREIGN KEY(PetID) REFERENCES Pets(PetID) ON DELETE CASCADE,
  CONSTRAINT fk_purchase_ownership FOREIGN KEY(PetID,OID) REFERENCES Owns(PetID,OID) ON DELETE CASCADE
) ENGINE=InnoDB;
INSERT INTO Pets (Name,Age,Street,City,ZipCode,State,TypeofPet) VALUES
 ('Buddy',3,'123 Oak St','Boston','02118','MA','Dog'),
 ('Mittens',2,'45 Pine St','Boston','02120','MA','Cat'),
 ('Charlie',5,'78 Maple Ave','Cambridge','02139','MA','Dog'),
 ('Polly',1,'12 Bird Ln','Boston','02125','MA','Bird'),
 ('Nemo',1,'90 River Rd','Brookline','02445','MA','Fish');
INSERT INTO Owners (LastName,Street,City,ZipCode,State,Age,AnnualIncome) VALUES
 ('Smith','123 Oak St','Boston','02118','MA',35,75000),
 ('Johnson','45 Pine St','Boston','02120','MA',29,65000),
 ('Williams','78 Maple Ave','Cambridge','02139','MA',42,90000);
INSERT INTO Owns(PetID,`Year`,OID,PetAgeatOwnership,PricePaid) VALUES
 (1,2020,1,1,300),(2,2021,2,1,0),(3,2019,3,2,500),(4,2022,1,1,100),(5,2023,2,1,0);
INSERT INTO Likes(PetID,TypeofFood) VALUES
 (1,'Dog Food'),(2,'Cat Food'),(3,'Dog Food'),(4,'Bird Food'),(5,'Fish Food');
INSERT INTO Foods(Name,Brand,TypeofFood,Price,ItemWeight,ClassofFood) VALUES
 ('Bone','MeatLovers','Dog Food',5.99,1,'Dry'),
 ('Kibble','Pawfect','Dog Food',15.49,5,'Dry'),
 ('Canned Tuna','MeowMix','Cat Food',1.29,0.2,'Wet'),
 ('Seeds','FeatherMix','Bird Food',4.49,1,'Dry'),
 ('Fish Flakes','AquaCare','Fish Food',3.99,0.5,'Dry');
INSERT INTO Purchases(OID,FoodID,PetID,`Month`,`Year`,Quantity) VALUES
 (1,1,1,5,2022,2),(2,3,2,6,2022,5),(3,2,3,4,2021,1),
 (1,4,4,7,2022,3),(2,5,5,8,2023,2);
