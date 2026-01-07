# Plan de modification des rapports mensuels - TODO

## Objectif
Modifier le système de rapports pour que la sélection mensuelle utilise deux selects (mois et année) au lieu d'un input date.

## Étapes

### 1. Modifier l'interface HTML (index.php)
- [x] Ajouter un container pour les contrôles mensuels avec select mois (1-12) et select année (2024-2026+)
- [x] Masquer ce container par défaut, l'afficher seulement pour la période "mois"
- [x] Garder le système existant pour "jour" et "semaine"

### 2. Modifier la logique JavaScript (app.js)
- [x] Ajouter une fonction pour initialiser les selects mois/année
- [x] Modifier `adjustDateForPeriod()` pour gérer les périodes mensuelles (renommé en adjustControlsForPeriod)
- [x] Modifier `loadReport()` pour envoyer les bons paramètres API (month + year au lieu de date)
- [x] Gérer les événements sur les nouveaux selects

### 3. Vérifier la compatibilité API
- [x] Vérifier que l'API supporte les paramètres month et year
- [x] Modifier l'API pour supporter month et year (fait)

### 4. Corriger les erreurs
- [ ] Corriger l'erreur de syntaxe JavaScript (fichier tronqué)
- [ ] Tester les modifications

## Spécifications
- Mois : Select avec valeurs 1-12 (Janvier = 1)
- Années : Select avec valeurs 2024, 2025, 2026+ (dynamique)
- Transition fluide entre les modes de sélection
- Préserver la fonctionnalité existante pour jour/semaine
