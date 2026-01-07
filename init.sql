-- Catégories de sujets
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    color TEXT DEFAULT '#3b82f6',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Sujets d'étude
CREATE TABLE IF NOT EXISTS subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE(category_id, name)
);

-- Entrées de temps
CREATE TABLE IF NOT EXISTS time_entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    subject_id INTEGER NOT NULL,
    duration_minutes INTEGER NOT NULL,
    entry_date DATE NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Index pour optimiser les requêtes
CREATE INDEX IF NOT EXISTS idx_subjects_category ON subjects(category_id);
CREATE INDEX IF NOT EXISTS idx_time_entries_subject ON time_entries(subject_id);
CREATE INDEX IF NOT EXISTS idx_time_entries_date ON time_entries(entry_date);

-- Données de démonstration
INSERT OR IGNORE INTO categories (name, color) VALUES
    ('Mathématiques', '#ef4444'),
    ('Sciences', '#10b981'),
    ('Langues', '#f59e0b'),
    ('Programmation', '#8b5cf6');

INSERT OR IGNORE INTO subjects (category_id, name, description) VALUES
    (1, 'Algèbre', 'Équations et fonctions'),
    (1, 'Géométrie', 'Formes et espaces'),
    (2, 'Physique', 'Mécanique et thermodynamique'),
    (3, 'Anglais', 'Grammaire et vocabulaire'),
    (4, 'Python', 'Programmation orientée objet');
