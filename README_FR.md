# Gestionnaire de Temps d'Étude

Une application web PHP moderne pour suivre et analyser le temps d'étude par catégories et matières. Elle dispose d'une interface élégante avec un design luxueux, des statistiques en temps réel, la génération de rapports PDF et un support multilingue complet.

## Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [Schéma de la base de données](#schéma-de-la-base-de-données)
- [Points de terminaison API](#points-de-terminaison-api)
- [Support multilingue](#support-multilingue)
- [Sécurité](#sécurité)
- [Structure du projet](#structure-du-projet)
- [Technologies utilisées](#technologies-utilisées)
- [Licence](#licence)

## Fonctionnalités

- **Suivi du temps d'étude** - Enregistrez des sessions d'étude avec matière, durée, date et notes optionnelles
- **Gestion des catégories et matières** - Organisez vos études en catégories avec des matières codées par couleur
- **Tableau de bord interactif** - Statistiques en temps réel avec graphiques circulaires et histogrammes
- **Génération de rapports** - Rapports quotidiens, hebdomadaires et mensuels avec export PDF
- **Support multilingue** - Interface complète en français, anglais, allemand et italien
- **Thème sombre/clair** - Détection automatique du thème avec possibilité de basculer manuellement
- **Authentification utilisateur** - Connexion par session avec capacité de réinitialisation du mot de passe
- **Panneau d'administration** - Gestion des utilisateurs pour les administrateurs
- **Design responsive** - Fonctionne sur ordinateur, tablette et mobile
- **Base de données SQLite** - Base de données légère et autonome sans dépendances externes

## Prérequis

- **PHP 7.4+** ou **PHP 8.x** avec extensions :
  - `pdo_sqlite`
  - `sqlite3`
  - `session`
  - `json`
- **Serveur web** - Apache, Nginx, ou tout serveur capable de PHP
- **Composer** - Pour gérer les dépendances PHP
- **Navigateur web moderne** - Avec JavaScript activé

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/davidperroud/school-time-tracker.git
cd school-time-tracker
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer le serveur web

**Pour Apache (avec .htaccess) :**
- Pointez la racine de votre document vers le répertoire du projet
- Assurez-vous que `AllowOverride All` est activé pour que `.htaccess` fonctionne
- Vérifiez que PHP a accès en écriture au répertoire `data/`

**Pour Nginx :**
```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    root /chemin/vers/school-time-tracker/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 4. Initialiser la base de données

Accédez à la page de configuration dans votre navigateur :
```
http://votre-domaine.com/public/setup.php
```

Ou initialisez manuellement en exécutant :
```bash
sqlite3 data/study_tracker.db < init.sql
```

### 5. Définir les permissions

```bash
chmod 755 data/
chmod 644 data/study_tracker.db
```

## Configuration

### Identifiants par défaut

Après l'installation, connectez-vous avec :

| Nom d'utilisateur | Mot de passe |
|------------------|--------------|
| admin | admin123 |

**Important :** Modifiez immédiatement ces identifiants après la première connexion.

### Configuration e-mail (pour la réinitialisation du mot de passe)

Pour que les e-mails de réinitialisation de mot de passe fonctionnent, configurez vos paramètres SMTP dans `src/User.php` ou créez un fichier `.env` :

```env
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USERNAME=votre-email@example.com
SMTP_PASSWORD=votre-mot-de-passe
SMTP_FROM=noreply@example.com
```

## Utilisation

### Navigation

L'application possède 5 sections principales :

1. **Tableau de bord** - Aperçu des statistiques d'étude avec graphiques interactifs
2. **Nouvelle entrée** - Formulaire rapide pour enregistrer le temps d'étude
3. **Entrées** - Voir, rechercher, filtrer et gérer toutes les entrées
4. **Gestion** - Créer et modifier les catégories et matières
5. **Rapports** - Générer et exporter les rapports d'étude

### Ajouter du temps d'étude

1. Allez dans l'onglet **Nouvelle entrée**
2. Sélectionnez une **Matière** dans la liste déroulante
3. Entrez la **Durée** en minutes
4. Sélectionnez la **Date** (par défaut aujourd'hui)
5. Ajoutez optionnellement des **Notes**
6. Cliquez sur **Enregistrer**

### Créer des catégories et matières

1. Allez dans l'onglet **Gestion**
2. Ajoutez une **Catégorie** (ex: "Mathématiques", "Langues")
3. Ajoutez des **Matières** sous chaque catégorie
4. Assignez des couleurs aux catégories pour une distinction visuelle

### Générer des rapports

1. Allez dans l'onglet **Rapports**
2. Sélectionnez la **Période** (jour, semaine ou mois)
3. Choisissez la **Plage de dates**
4. Cliquez sur **Générer le rapport**
5. Utilisez **Exporter en PDF** pour télécharger le rapport

### Changer de langue

- Utilisez le **menu déroulant de langue** dans l'en-tête
- Ou ajoutez `?lang=XX` à l'URL (ex: `?lang=fr`, `?lang=de`)

### Réinitialisation du mot de passe

1. Cliquez sur **Mot de passe oublié** sur la page de connexion
2. Entrez votre **nom d'utilisateur**
3. Vérifiez votre e-mail pour le **lien de réinitialisation**
4. Cliquez sur le lien et entrez le **code PIN** de l'e-mail
5. Définissez votre **nouveau mot de passe**

## Schéma de la base de données

```
users
├── id (INTEGER PRIMARY KEY)
├── username (TEXT UNIQUE)
├── password_hash (TEXT)
├── language_preference (TEXT)
├── is_admin (INTEGER)
├── created_at (DATETIME)
└── last_login (DATETIME)

password_resets
├── id (INTEGER PRIMARY KEY)
├── user_id (INTEGER FK)
├── token (TEXT UNIQUE)
├── pin (TEXT)
├── expires_at (DATETIME)
├── used_at (DATETIME)
└── created_at (DATETIME)

categories
├── id (INTEGER PRIMARY KEY)
├── name (TEXT)
├── color (TEXT)
└── created_at (DATETIME)

category_translations
├── id (INTEGER PRIMARY KEY)
├── category_id (INTEGER FK)
├── lang (TEXT)
└── name (TEXT)

subjects
├── id (INTEGER PRIMARY KEY)
├── category_id (INTEGER FK)
├── name (TEXT)
├── description (TEXT)
└── created_at (DATETIME)

subject_translations
├── id (INTEGER PRIMARY KEY)
├── subject_id (INTEGER FK)
├── lang (TEXT)
├── name (TEXT)
└── description (TEXT)

time_entries
├── id (INTEGER PRIMARY KEY)
├── subject_id (INTEGER FK)
├── duration_minutes (INTEGER)
├── entry_date (DATE)
├── notes (TEXT)
└── created_at (DATETIME)
```

## Points de terminaison API

| Point de terminaison | Méthode | Description |
|---------------------|---------|-------------|
| `/api.php?action=summary` | GET | Obtenir les statistiques récapitulatives |
| `/api.php?action=categories` | GET | Lister toutes les catégories |
| `/api.php?action=subjects` | GET | Lister les matières (optionnel : `category_id`) |
| `/api.php?action=entries` | GET | Obtenir les entrées (optionnel : `date`, `subject_id`) |
| `/api.php?action=all_entries` | GET | Obtenir toutes les entrées avec filtre de date optionnel |
| `/api.php?action=stats` | GET | Obtenir des statistiques détaillées (`subject_id`, `days`) |
| `/api.php?action=users` | GET | Lister les utilisateurs (admin uniquement) |
| `/api.php?action=progress` | GET | Obtenir les données de progression |
| `/api.php?action=recent_entries` | GET | Obtenir les entrées récentes |
| `/export_pdf.php` | GET | Exporter le rapport en PDF |

### Paramètres de requête

- `period` - `day`, `week` ou `month`
- `date` - Date au format `YYYY-MM-DD`
- `month` - Mois au format `MM`
- `year` - Année au format `YYYY`
- `category_id` - Filtrer par catégorie
- `subject_id` - Filtrer par matière
- `filter_date` - Filtrer les entrées par date

## Support multilingue

L'application est entièrement traduite en 4 langues :

| Code | Langue | Nom natif | Statut |
|------|--------|-----------|--------|
| `fr` | Français | Français | Par défaut |
| `en` | Anglais | English | Complet |
| `de` | Allemand | Deutsch | Complet |
| `it` | Italien | Italiano | Complet |

Les fichiers de traduction sont situés dans le répertoire `/lang/` :
- `lang/fr.json` - Traductions françaises
- `lang/en.json` - Traductions anglaises
- `lang/de.json` - Traductions allemandes
- `lang/it.json` - Traductions italiennes

## Sécurité

- **Hachage des mots de passe** - Utilise `password_hash()` avec l'algorithme `PASSWORD_DEFAULT`
- **Instructions préparées** - Toutes les requêtes de base de données utilisent des instructions préparées PDO
- **Sécurité des sessions** - Cookies sécurisés avec drapeaux `httponly` et `samesite`
- **Protection CSRF** - Soumissions de formulaires validées avec jetons
- **Prévention des injections SQL** - Requêtes paramétrées partout
- **Prévention XSS** - Sortie échappée avec `htmlspecialchars()`
- **Accès basé sur les rôles** - Panneau d'administration réservé aux administrateurs

## Structure du projet

```
school-time-tracker/
├── index.php                 # Redirection racine vers public/
├── init.php                  # Script d'initialisation de la base de données
├── init.sql                  # Fichier de schéma SQL
├── auth.php                  # Authentification HTTP Basic
├── composer.json             # Dépendances PHP
├── .gitignore                # Règles d'exclusion Git
├── .htaccess                 # Configuration Apache
├── LICENSE                   # Licence MIT
├── README.md                 # Documentation en anglais
├── README_FR.md             # Documentation en français
├── src/
│   ├── Database.php          # Classe singleton de base de données
│   ├── Auth.php              # Classe d'authentification
│   ├── User.php              # Classe de gestion des utilisateurs
│   ├── Session.php           # Classe de gestion des sessions
│   ├── Translation.php       # Classe de traduction/i18n
│   └── ApiController.php     # Contrôleur API REST
├── public/
│   ├── index.php             # Point d'entrée principal de l'application
│   ├── login.php             # Page de connexion
│   ├── logout.php            # Gestionnaire de déconnexion
│   ├── setup.php             # Assistant de configuration de l'application
│   ├── reset_request.php     # Demande de réinitialisation du mot de passe
│   ├── reset_password.php    # Formulaire de réinitialisation du mot de passe
│   ├── init.php              # Point d'entrée d'initialisation de la base de données
│   ├── api.php               # Point d'entrée de l'API
│   ├── export_pdf.php        # Gestionnaire d'export PDF
│   ├── css/
│   │   └── style.css         # Styles personnalisés
│   └── js/
│       └── app.js            # JavaScript frontend
├── lang/
│   ├── fr.json               # Traductions françaises
│   ├── en.json               # Traductions anglaises
│   ├── de.json               # Traductions allemandes
│   └── it.json               # Traductions italiennes
└── data/
    └── study_tracker.db       # Base de données SQLite (créée automatiquement)
```

## Technologies utilisées

| Catégorie | Technologie |
|-----------|-------------|
| Backend | PHP 7.4+ / 8.x |
| Base de données | SQLite 3 avec PDO |
| Frontend | HTML5, CSS3, JavaScript (ES6+) |
| Framework UI | Tailwind CSS (CDN) |
| Graphiques | Chart.js |
| Icônes | Lucide Icons |
| Génération PDF | TCPDF |
| Authentification | Basée sur les sessions avec hachage de mot de passe |

## Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

**Développé avec ❤️ pour optimiser le temps d'étude**
