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
-- Create Etudiant table
CREATE TABLE IF NOT EXISTS Etudiant (
    etudiant_id INT AUTO_INCREMENT PRIMARY KEY,
    nom_etudiant VARCHAR(100) NOT NULL,
    prenom_etudiant VARCHAR(100) NOT NULL,
    n_inscription_etudiant VARCHAR(100) NOT NULL,
    birthday_etudiant Date Not NULL,
    email_etudiant VARCHAR(100) NOT NULL,
    speciality_id INT,
    FOREIGN KEY (speciality_id) REFERENCES Speciality(speciality_id)
);
-- Create Binome table
CREATE TABLE IF NOT EXISTS Binome (
    binome_id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant1_id INT NOT NULL,
    etudiant2_id INT NOT NULL,
    FOREIGN KEY (etudiant1_id) REFERENCES Etudiant(etudiant_id),
    FOREIGN KEY (etudiant2_id) REFERENCES Etudiant(etudiant_id)
);
-- Create Enseignant table
CREATE TABLE IF NOT EXISTS Enseignant (
    enseignant_id INT AUTO_INCREMENT PRIMARY KEY,
    nom_enseignant VARCHAR(100) NOT NULL,
    prenom_enseignant VARCHAR(100) NOT NULL,
    email_enseignant VARCHAR(100) NOT NULL,
    N_telephone_enseignant VARCHAR(100) NOT NULL,
    type ENUM(
        'enseignant',
        'chef_specialite',
        'chef_departement'
    ) NOT NULL,
    speciality_id INT,
    FOREIGN KEY (speciality_id) REFERENCES Speciality(speciality_id)
);
-- Create Theme table
CREATE TABLE IF NOT EXISTS Theme (
    theme_id INT AUTO_INCREMENT PRIMARY KEY,
    title_theme VARCHAR(100) NOT NULL,
    description_theme TEXT NOT NULL,
    status ENUM(
        'non_valide',
        'en_attente',
        'attribue',
        'termine'
    ) NOT NULL,
    speciality_id INT,
    enseignant_id INT,
    binome_id INT,
    FOREIGN KEY (speciality_id) REFERENCES Speciality(speciality_id),
    FOREIGN KEY (enseignant_id) REFERENCES Enseignant(enseignant_id),
    FOREIGN KEY (binome_id) REFERENCES Binome(binome_id)
);