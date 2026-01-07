# Plan de modification des rapports mensuels

## Objectif
Modifier le système de rapports pour que la sélection mensuelle utilise deux selects (mois et année) au lieu d'un input date.

## Étapes

### 1. Modifier l'interface HTML (index.php)
- [ ] Ajouter un container pour les contrôles mensuels avec select mois (1-12) et select année (2024-2026+)
- [ ] Masquer ce container par défaut, l'afficher seulement pour la période "mois"
- [ ] Garder le système existant pour "jour" et "semaine"

### 2. Modifier la logique JavaScript (app.js)
- [ ] Ajouter une fonction pour initialiser les selects mois/année
- [ ] Modifier `adjustDateForPeriod()` pour gérer les périodes mensuelles
- [ ] Modifier `loadReport()` pour envoyer les bons paramètres API (month + year au lieu de date)
- [ ] Gérer les événements sur les nouveaux selects

### 3. Vérifier la compatibilité API
- [ ] Vérifier que l'API supporte les paramètres month et year
- [ ] Tester les modifications

## Spécifications
- Mois : Select avec valeurs 1-12 (Janvier = 1)
- Années : Select avec valeurs 2024, 2025, 2026+ (dynamique)
- Transition fluide entre les modes de sélection
- Préserver la fonctionnalité existante pour jour/semaine
