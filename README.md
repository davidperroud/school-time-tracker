# Study Time Tracker

Une application web moderne pour suivre et analyser votre temps d'étude. Développée en PHP avec une interface utilisateur élégante utilisant Tailwind CSS et Chart.js.

## ✨ Fonctionnalités

- 📊 **Dashboard interactif** avec statistiques en temps réel et graphiques
- 📝 **Suivi du temps** par sujet et catégorie
- 📈 **Rapports détaillés** (journalier, hebdomadaire, mensuel)
- 🏷️ **Gestion des catégories** et sujets d'étude
- 🌙 **Thème sombre/clair** avec sauvegarde des préférences
- 📱 **Design responsive** adapté à tous les appareils
- 🔐 **Authentification sécurisée** par HTTP Basic Auth
- 🗃️ **Base de données SQLite** légère et autonome
- 📄 **Export PDF** des rapports d'étude
- 🔄 **Modales d'édition** pour une modification en ligne

## 🚀 Installation

### Prérequis

- **Serveur web** (Apache, Nginx) avec support PHP
- **PHP 7.4+** avec extension PDO SQLite activée
- **Navigateur web moderne** avec JavaScript activé
- **Composer** pour la gestion des dépendances

### Installation automatique (recommandée)

1. **Clonez le repository :**
   ```bash
   git clone https://github.com/votre-nom-utilisateur/study-tracker-claude.git
   cd study-tracker-claude
   ```

2. **Installez les dépendances :**
   ```bash
   composer install
   ```

3. **Déployez sur votre serveur web :**
   - Copiez tous les fichiers dans le répertoire web de votre serveur
   - Assurez-vous que PHP peut écrire dans le dossier `data/`

4. **Initialisez la base de données :**
   - Accédez à `http://votre-domaine.com/public/init.php`
   - La base de données sera créée automatiquement avec des données d'exemple

5. **Accédez à l'application :**
   - URL principale : `http://votre-domaine.com/`
   - Interface utilisateur : `http://votre-domaine.com/public/`

### Installation manuelle

Si vous préférez une installation manuelle :

1. **Créez la structure de dossiers :**
   ```bash
   mkdir -p data
   chmod 755 data
   ```

2. **Initialisez la base de données :**
   ```bash
   sqlite3 data/study_tracker.db < init.sql
   ```

3. **Configurez les permissions :**
   ```bash
   chmod 644 data/study_tracker.db
   ```

## 🔐 Configuration de l'authentification

L'application utilise l'authentification HTTP Basic. Par défaut :

- **Utilisateur :** `username`
- **Mot de passe :** `password`

Pour modifier ces informations, éditez le fichier `auth.php` :

```php
$valid_username = 'votre-utilisateur';
$valid_password = 'votre-mot-de-passe';
```

## 📖 Utilisation

### Premiers pas

1. **Connectez-vous** avec les identifiants configurés
2. **Créez des catégories** (ex: Mathématiques, Sciences, Langues)
3. **Ajoutez des sujets** dans chaque catégorie
4. **Commencez à suivre** votre temps d'étude !

### Interface principale

L'application propose 5 onglets principaux :

#### 🏠 Dashboard
- Statistiques du jour (heures totales, catégories actives, sessions)
- Graphiques en camembert et barres pour la visualisation des données
- Vue d'ensemble de votre activité récente

#### ➕ Nouvelle entrée
- Formulaire rapide pour ajouter du temps d'étude
- Sélection du sujet et durée en minutes
- Ajout de notes optionnelles
- Liste des entrées récentes

#### 📋 Entrées
- Vue complète de toutes vos entrées
- Recherche par sujet, catégorie ou contenu des notes
- Filtrage par date
- Modification et suppression des entrées

#### ⚙️ Gestion
- Création et modification des catégories
- Gestion des sujets par catégorie
- Suppression des éléments non utilisés

#### 📊 Rapports
- Rapports journaliers, hebdomadaires ou mensuels
- Statistiques détaillées par catégorie
- Export PDF des rapports
- Analyse des données d'étude

## 🛠️ Architecture technique

```
study-tracker-claude/
├── .gitignore                # Fichiers à ignorer par Git
├── auth.php                  # Authentification HTTP Basic
├── composer.json             # Dépendances PHP
├── composer.lock             # Verrouillage des versions
├── index.php                 # Point d'entrée principal (redirection)
├── init.sql                  # Script d'initialisation de la base de données
├── README.md                 # Documentation du projet
├── src/
│   ├── Database.php          # Classe de gestion de la base de données
│   └── ApiController.php     # API REST pour les données
├── public/
│   ├── index.php             # Interface utilisateur principale
│   ├── api.php               # Point d'entrée de l'API
│   ├── init.php              # Initialisation de la base de données
│   ├── export_pdf.php        # Export PDF des rapports
│   ├── css/
│   │   └── style.css         # Styles CSS personnalisés
│   └── js/
│       └── app.js            # Logique JavaScript frontend
└── data/
    └── study_tracker.db      # Base de données SQLite
```

### Base de données

Le schéma de base de données comprend 3 tables principales :

- **`categories`** : Catégories d'étude (Mathématiques, Sciences, etc.)
  - `id`, `name`, `color`, `created_at`
- **`subjects`** : Sujets spécifiques dans chaque catégorie
  - `id`, `category_id`, `name`, `description`, `created_at`
- **`time_entries`** : Entrées de temps avec durée et date
  - `id`, `subject_id`, `duration_minutes`, `entry_date`, `notes`, `created_at`

### API Endpoints

L'application expose une API REST complète :

- `GET /public/api.php?action=summary&period=day|week|month[&date=YYYY-MM-DD][&month=MM][&year=YYYY]` - Résumé des données
- `GET /public/api.php?action=categories` - Liste des catégories avec statistiques
- `GET /public/api.php?action=subjects[&category_id=ID]` - Liste des sujets avec statistiques
- `GET /public/api.php?action=entries[&date=YYYY-MM-DD][&subject_id=ID]` - Entrées par date/sujet
- `GET /public/api.php?action=all_entries[&filter_date=YYYY-MM-DD]` - Toutes les entrées
- `GET /public/api.php?action=stats&subject_id=ID[&days=30]` - Statistiques détaillées par sujet

### Export PDF

- `GET /public/export_pdf.php?period=day|week|month[&date=YYYY-MM-DD][&month=MM][&year=YYYY]` - Export PDF du rapport

## 🎨 Personnalisation

### Thème

L'application supporte nativement les thèmes sombre et clair :
- Bascule automatique selon les préférences système
- Sauvegarde du choix utilisateur dans le localStorage
- Thème appliqué aux graphiques Chart.js

### Styles

Les styles utilisent une combinaison de :
- **Tailwind CSS** (CDN) pour les utilitaires
- **CSS personnalisé** pour les composants spécifiques
- Variables CSS pour la gestion des thèmes

### Couleurs

Les couleurs par défaut peuvent être modifiées dans le fichier `public/index.php` :
```javascript
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: '#3b82f6',
                secondary: '#8b5cf6',
            }
        }
    }
}
```

## 🔧 Développement

### Structure du projet

- **Backend PHP** : Classes orientées objet avec séparation des responsabilités
- **Frontend** : Vanilla JavaScript avec fetch API
- **Base de données** : SQLite avec PDO pour la sécurité
- **UI/UX** : Design moderne et responsive

### Scripts disponibles

- `public/init.php` : Initialisation de la base de données avec données d'exemple
- `public/export_pdf.php` : Génération de rapports PDF

### Technologies utilisées

- **Backend :** PHP 7.4+, SQLite, PDO
- **Frontend :** HTML5, CSS3, JavaScript ES6+
- **UI Framework :** Tailwind CSS
- **Graphiques :** Chart.js
- **Authentification :** HTTP Basic Auth
- **Export PDF :** TCPDF

## 📊 Fonctionnalités avancées

- **Calculs automatiques** des statistiques en temps réel
- **Filtres dynamiques** pour la recherche d'entrées
- **Modales d'édition** pour une modification en ligne
- **Validation côté client** des formulaires
- **Gestion d'erreurs** avec messages utilisateur
- **Performance optimisée** avec index de base de données
- **Export PDF** des rapports avec TCPDF
- **Gestion des préférences** utilisateur (thème, etc.)

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Forkez le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commitez vos changements (`git commit -m 'Ajout de la nouvelle fonctionnalité'`)
4. Poussez vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Ouvrez une Pull Request

### Règles de contribution

- Respectez le style de code existant
- Ajoutez des commentaires pour les fonctionnalités complexes
- Mettez à jour la documentation si nécessaire
- Testez vos modifications avant de soumettre

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 🐛 Signaler un problème

Si vous rencontrez un problème ou avez une suggestion :

1. Vérifiez les [issues existantes](https://github.com/votre-nom-utilisateur/study-tracker-claude/issues)
2. Créez une nouvelle issue avec :
   - Description détaillée du problème
   - Étapes pour reproduire
   - Informations sur votre environnement
   - Captures d'écran si applicable

## 🙏 Remerciements

- [Tailwind CSS](https://tailwindcss.com/) pour le framework CSS
- [Chart.js](https://www.chartjs.org/) pour les graphiques
- [SQLite](https://www.sqlite.org/) pour la base de données
- [TCPDF](https://tcpdf.org/) pour la génération de PDF

---

**Développé avec ❤️ pour optimiser votre temps d'étude**

## 📬 Contact

Pour toute question ou suggestion, vous pouvez me contacter via GitHub ou ouvrir une issue dans le repository.
