-- Create database
CREATE DATABASE IF NOT EXISTS Suivi_pfe;
-- Use the created database
USE Suivi_pfe;
-- Create Faculte table
CREATE TABLE IF NOT EXISTS Faculte (
    faculte_id INT AUTO_INCREMENT PRIMARY KEY,
    nom_faculte VARCHAR(100) NOT NULL
);
-- Create Departement table
CREATE TABLE IF NOT EXISTS Departement (
    departement_id INT AUTO_INCREMENT PRIMARY KEY,
    nom_departement VARCHAR(100) NOT NULL,
    faculte_id INT,
    FOREIGN KEY (faculte_id) REFERENCES Faculte(faculte_id)
);
-- Create Filieres table
CREATE TABLE IF NOT EXISTS Filieres (
    filiere_id INT AUTO_INCREMENT PRIMARY KEY,
    nom_filiere VARCHAR(100) NOT NULL,
    departement_id INT,
    FOREIGN KEY (departement_id) REFERENCES Departement(departement_id)
);
-- Create Speciality table
CREATE TABLE IF NOT EXISTS Speciality (
    speciality_id INT AUTO_INCREMENT PRIMARY KEY,
    nom_speciality VARCHAR(100) NOT NULL,
    filiere_id INT,
    FOREIGN KEY (filiere_id) REFERENCES Filieres(filiere_id)
);
-- Create Enseignant table
CREATE TABLE IF NOT EXISTS Enseignant (
    enseignant_id INT AUTO_INCREMENT PRIMARY KEY,
    nom_enseignant VARCHAR(100) NOT NULL,
    prenom_enseignant VARCHAR(100) NOT NULL,
    email_enseignant VARCHAR(100) NOT NULL,
    N_telephone_enseignant VARCHAR(100) NOT NULL,
    type ENUM('enseignant', 'chef_specialite') NOT NULL,
    departement_id INT,
    speciality_id INT,
    FOREIGN KEY (departement_id) REFERENCES Departement(departement_id),
    FOREIGN KEY (speciality_id) REFERENCES Speciality(speciality_id)
);
-- Create Etudiant table
CREATE TABLE IF NOT EXISTS Etudiant (
    etudiant_id INT AUTO_INCREMENT PRIMARY KEY,
    nom_etudiant VARCHAR(100) NOT NULL,
    prenom_etudiant VARCHAR(100) NOT NULL,
    n_inscription_etudiant VARCHAR(100) NOT NULL,
    birthday_etudiant DATE NOT NULL,
    email_etudiant VARCHAR(100) NOT NULL,
    speciality_id INT,
    FOREIGN KEY (speciality_id) REFERENCES Speciality(speciality_id)
);
-- Create Theme table
CREATE TABLE IF NOT EXISTS Theme (
    theme_id INT AUTO_INCREMENT PRIMARY KEY,
    title_theme VARCHAR(100) NOT NULL,
    description_theme TEXT NOT NULL,
    objectif_theme TEXT NOT NULL,
    outils_theme TEXT NOT NULL,
    stage ENUM('oui', 'non') NOT NULL,
    connaissances_theme TEXT NOT NULL,
    status ENUM(
        'non_valide',
        'en_attente',
        'attribue',
        'termine'
    ) NOT NULL,
    speciality_id INT,
    enseignant_id INT,
    FOREIGN KEY (speciality_id) REFERENCES Speciality(speciality_id),
    FOREIGN KEY (enseignant_id) REFERENCES Enseignant(enseignant_id)
);
-- Create Binome table
CREATE TABLE IF NOT EXISTS Binome (
    binome_id INT AUTO_INCREMENT PRIMARY KEY,
    taux_memoire DECIMAL(5, 2) DEFAULT NULL,
    taux_logiciel DECIMAL(5, 2) DEFAULT NULL,
    etudiant1_id INT NOT NULL,
    etudiant2_id INT NOT NULL,
    enseignant_id INT,
    status ENUM('en_attente', 'attribue') NOT NULL,
    theme_id INT,
    date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant1_id) REFERENCES Etudiant(etudiant_id),
    FOREIGN KEY (etudiant2_id) REFERENCES Etudiant(etudiant_id),
    FOREIGN KEY (theme_id) REFERENCES Theme(theme_id),
    FOREIGN KEY (enseignant_id) REFERENCES Enseignant(enseignant_id)
);
-- Create Planning table
CREATE TABLE IF NOT EXISTS Planning (
    planning_id INT AUTO_INCREMENT PRIMARY KEY,
    date_planning DATE NOT NULL,
    heure_debut TIME NOT NULL,
    jury_01 INT NOT NULL,
    jury_02 INT NOT NULL,
    salle VARCHAR(100) NOT NULL,
    theme_id INT,
    enseignant_id INT,
    binome_id INT,
    FOREIGN KEY (theme_id) REFERENCES Theme(theme_id),
    FOREIGN KEY (enseignant_id) REFERENCES Enseignant(enseignant_id),
    FOREIGN KEY (binome_id) REFERENCES Binome(binome_id),
    FOREIGN KEY (jury_01) REFERENCES Enseignant(enseignant_id),
    FOREIGN KEY (jury_02) REFERENCES Enseignant(enseignant_id)
);
-- Create Triggers
DELIMITER // CREATE TRIGGER before_insert_enseignant BEFORE
INSERT ON Enseignant FOR EACH ROW BEGIN IF NEW.type = 'enseignant' THEN IF NEW.departement_id IS NULL THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = "Enseignant must have a departement_id";
END IF;
IF NEW.speciality_id IS NOT NULL THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Enseignant cannot have a speciality_id';
END IF;
ELSEIF NEW.type = 'chef_specialite' THEN IF NEW.speciality_id IS NULL THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Chef_specialite must have a speciality_id';
END IF;
IF NEW.departement_id IS NOT NULL THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Chef_specialite cannot have a departement_id';
END IF;
END IF;
END;
// DELIMITER;
DELIMITER // CREATE TRIGGER before_update_enseignant BEFORE
UPDATE ON Enseignant FOR EACH ROW BEGIN IF NEW.type = 'enseignant' THEN IF NEW.departement_id IS NULL THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Enseignant must have a departement_id';
END IF;
IF NEW.speciality_id IS NOT NULL THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Enseignant cannot have a speciality_id';
END IF;
ELSEIF NEW.type = 'chef_specialite' THEN IF NEW.speciality_id IS NULL THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Chef_specialite must have a speciality_id';
END IF;
IF NEW.departement_id IS NOT NULL THEN SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Chef_specialite cannot have a departement_id';
END IF;
END IF;
END;
// DELIMITER;