---
created: 2026-02-09T15:06
title: Ajouter système de récupération de mot de passe
area: auth
files:
  - src/User.php:89-96 (updatePassword method)
  - public/login.php
  - public/setup.php
---

## Problème

L'application n'a actuellement aucun moyen de récupérer un mot de passe oublié. Les utilisateurs doivent passer par SQLite directement ou supprimer la base de données pour réinitialiser un mot de passe.

## Solution

Implémenter un système de "mot de passe oublié" avec:
- Page de demande d'email avec saisie du username
- Génération d'un token de resetuniq + expiré
- Envoi d'email avec lien de reset (ou code PIN si pas d'email configuré)
- Page de saisie nouveau mot de passe avec token validation
- Table `password_resets` dans SQLite

Considérations:
- Pas de serveur SMTP configuré actuellement — prévoir fallback (afficher code PIN à la place d'email)
- Token avec expiration (1 heure)
- Token usage unique
