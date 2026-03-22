-- Utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    language_preference TEXT DEFAULT 'fr',
    is_admin INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL
);

-- Mot de passe oublié - Jetons de réinitialisation
CREATE TABLE IF NOT EXISTS password_resets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token TEXT NOT NULL UNIQUE,
    pin TEXT NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Index pour les recherches rapides
CREATE INDEX IF NOT EXISTS idx_password_resets_token ON password_resets(token);
CREATE INDEX IF NOT EXISTS idx_password_resets_user ON password_resets(user_id);
CREATE INDEX IF NOT EXISTS idx_password_resets_expires ON password_resets(expires_at);

-- Catégories
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    color TEXT NOT NULL DEFAULT '#3b82f6',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Traductions des catégories
CREATE TABLE IF NOT EXISTS category_translations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    lang TEXT NOT NULL,
    name TEXT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE(category_id, lang)
);

-- Matières/Sujets
CREATE TABLE IF NOT EXISTS subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    description TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Traductions des sujets
CREATE TABLE IF NOT EXISTS subject_translations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    subject_id INTEGER NOT NULL,
    lang TEXT NOT NULL,
    name TEXT NOT NULL,
    description TEXT DEFAULT '',
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE(subject_id, lang)
);

-- Entrées de temps
CREATE TABLE IF NOT EXISTS time_entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    subject_id INTEGER NOT NULL,
    duration_minutes INTEGER NOT NULL,
    entry_date DATE NOT NULL,
    notes TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Index pour les recherches rapides
CREATE INDEX IF NOT EXISTS idx_subjects_category ON subjects(category_id);
CREATE INDEX IF NOT EXISTS idx_time_entries_subject ON time_entries(subject_id);
CREATE INDEX IF NOT EXISTS idx_time_entries_date ON time_entries(entry_date);

-- Données de démonstration (catégories)
INSERT INTO categories (id, name, color) VALUES 
(1, 'Mathematics', '#ef4444'),
(2, 'Languages', '#22c55e'),
(3, 'Sciences', '#3b82f6');

-- Traductions catégories: Français
INSERT INTO category_translations (category_id, lang, name) VALUES 
(1, 'fr', 'Mathématiques'),
(2, 'fr', 'Langues'),
(3, 'fr', 'Sciences');

-- Traductions catégories: Allemand
INSERT INTO category_translations (category_id, lang, name) VALUES 
(1, 'de', 'Mathematik'),
(2, 'de', 'Sprachen'),
(3, 'de', 'Naturwissenschaften');

-- Traductions catégories: Italien
INSERT INTO category_translations (category_id, lang, name) VALUES 
(1, 'it', 'Matematica'),
(2, 'it', 'Lingue'),
(3, 'it', 'Scienze');

-- Données de démonstration (sujets)
INSERT INTO subjects (id, category_id, name, description) VALUES 
(1, 1, 'Algebra', 'Algebra and linear equations'),
(2, 1, 'Geometry', 'Plane and solid geometry'),
(3, 2, 'English', 'English language learning'),
(4, 2, 'German', 'German language learning'),
(5, 3, 'Physics', 'Classical physics and mechanics'),
(6, 3, 'Chemistry', 'General chemistry basics');

-- Traductions sujets: Français
INSERT INTO subject_translations (subject_id, lang, name, description) VALUES 
(1, 'fr', 'Algèbre', 'Algèbre et équations linéaires'),
(2, 'fr', 'Géométrie', 'Géométrie plane et dans l''espace'),
(3, 'fr', 'Anglais', 'Apprentissage de la langue anglaise'),
(4, 'fr', 'Allemand', 'Apprentissage de la langue allemande'),
(5, 'fr', 'Physique', 'Physique classique et mécanique'),
(6, 'fr', 'Chimie', 'Bases de la chimie générale');

-- Traductions sujets: Allemand
INSERT INTO subject_translations (subject_id, lang, name, description) VALUES 
(1, 'de', 'Algebra', 'Algebra und lineare Gleichungen'),
(2, 'de', 'Geometrie', 'Ebenen- und Raumgeometrie'),
(3, 'de', 'Englisch', 'Englisch lernen'),
(4, 'de', 'Deutsch', 'Deutsch lernen'),
(5, 'de', 'Physik', 'Klassische Physik und Mechanik'),
(6, 'de', 'Chemie', 'Grundlagen der allgemeinen Chemie');

-- Traductions sujets: Italien
INSERT INTO subject_translations (subject_id, lang, name, description) VALUES 
(1, 'it', 'Algebra', 'Algebra ed equazioni lineari'),
(2, 'it', 'Geometria', 'Geometria piana e solida'),
(3, 'it', 'Inglese', 'Apprendimento della lingua inglese'),
(4, 'it', 'Tedesco', 'Apprendimento della lingua tedesca'),
(5, 'it', 'Fisica', 'Fisica classica e meccanica'),
(6, 'it', 'Chimica', 'Fondamenti di chimica generale');

-- Quelques entrées de démonstration
INSERT INTO time_entries (subject_id, duration_minutes, entry_date, notes) VALUES 
(1, 60, date('now'), 'Exercises on linear equations'),
(3, 45, date('now', '-1 day'), 'Grammar review'),
(5, 90, date('now', '-2 days'), 'Newton laws homework'),
(4, 30, date('now', '-3 days'), 'Vocabulary list 5'),
(2, 45, date('now', '-4 days'), 'Area calculations practice');
