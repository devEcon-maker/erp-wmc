---
description: 
---

# Prompt de Débogage Universel - CRM & ERP Laravel/Livewire

## Instructions d'utilisation

Copiez ce prompt dans Cursor ou Claude Code lorsque vous rencontrez un problème. Remplacez les sections entre crochets par vos informations spécifiques.

---

## Prompt à utiliser

```
CONTEXTE PROJET
Projet : CRM & ERP Laravel 11 + Livewire 3 + Tailwind CSS + Alpine.js + MySQL 8
Structure : Architecture modulaire dans app/Modules/{Core|CRM|HR|Inventory|Finance|Productivity|Agenda}
Packages : spatie/laravel-permission, barryvdh/laravel-dompdf, maatwebsite/excel

PROBLÈME RENCONTRÉ
Module concerné : [Nom du module]
Fichier(s) impliqué(s) : [Chemin complet du/des fichier(s)]
Action tentée : [Ce que tu essayais de faire]
Comportement attendu : [Ce qui devrait se passer]
Comportement actuel : [Ce qui se passe réellement]

MESSAGE D'ERREUR
[Coller le message d'erreur exact ici, incluant la stack trace si disponible]

CODE CONCERNÉ
[Coller le code pertinent ici - uniquement les parties liées au problème]

CE QUE J'AI DÉJÀ ESSAYÉ
[Liste des tentatives de résolution déjà effectuées]

INSTRUCTIONS DE DÉBOGAGE
1. Analyse le message d'erreur et identifie la cause racine
2. Vérifie la cohérence avec l'architecture modulaire du projet
3. Propose une solution qui respecte les conventions :
   - Services pour la logique métier
   - Policies pour les autorisations
   - Form Requests pour la validation
   - Soft deletes sur les modèles métier
4. Fournis le code corrigé complet, pas de fragments
5. Explique brièvement pourquoi l'erreur se produisait
6. Indique les fichiers à modifier avec leurs chemins exacts
```

---

## Variantes selon le type de problème

### Erreur Livewire

```
PROBLÈME LIVEWIRE
Composant : [App\Modules\{Module}\Livewire\{Composant}]
Vue : [resources/views/livewire/{module}/{vue}.blade.php]
Erreur : [Message d'erreur]
Action déclencheur : [Clic bouton, wire:model, mount, etc.]

Vérifie :
- Propriétés publiques correctement déclarées
- Méthodes wire:click existantes
- Bindings wire:model sur propriétés existantes
- Règles de validation dans rules()
- Dispatch d'événements correctement nommés
```

### Erreur Migration/Base de données

```
PROBLÈME BASE DE DONNÉES
Migration : [Nom du fichier migration]
Table(s) concernée(s) : [Nom des tables]
Erreur : [Message d'erreur]

Vérifie :
- Clés étrangères référencent des tables existantes
- Ordre des migrations (dépendances)
- Types de colonnes compatibles
- Contraintes uniques et index
- Commande à exécuter pour corriger
```

### Erreur Permissions/Policies

```
PROBLÈME AUTORISATION
Policy : [App\Modules\{Module}\Policies\{Policy}]
Permission testée : [module.action]
Utilisateur/Rôle : [Rôle de l'utilisateur]
Résultat : [403, false, etc.]

Vérifie :
- Policy enregistrée dans AuthServiceProvider
- Méthode policy correspond à l'action
- Permission existe dans la base
- Rôle a la permission assignée
- Middleware appliqué sur la route
```

### Erreur Relations Eloquent

```
PROBLÈME RELATION
Modèle source : [App\Modules\{Module}\Models\{Model}]
Relation : [Nom de la relation]
Type : [belongsTo, hasMany, belongsToMany, etc.]
Erreur : [Message d'erreur]

Vérifie :
- Clé étrangère existe dans la table
- Nom de la relation correspond à la méthode
- Modèle cible correctement importé
- Eager loading avec with() si nécessaire
```

### Erreur Service/Logique métier

```
PROBLÈME SERVICE
Service : [App\Modules\{Module}\Services\{Service}]
Méthode : [Nom de la méthode]
Entrées : [Paramètres passés]
Erreur : [Message d'erreur ou comportement incorrect]

Vérifie :
- Injection de dépendances correcte
- Transactions DB si opérations multiples
- Validation des entrées
- Gestion des cas limites
- Retour attendu vs retour réel
```

### Erreur PDF/Export

```
PROBLÈME GÉNÉRATION
Type : [PDF DomPDF / Excel Maatwebsite]
Template : [Chemin de la vue]
Données : [Structure des données passées]
Erreur : [Message d'erreur]

Vérifie :
- Vue existe au chemin indiqué
- Variables passées à la vue
- Syntaxe Blade correcte
- Styles inline pour PDF
- Mémoire suffisante pour gros fichiers
```

---

## Checklist de débogage rapide

Avant de soumettre le prompt, vérifiez :

1. **Cache Laravel** : `php artisan cache:clear && php artisan config:clear && php artisan view:clear && php artisan route:clear`

2. **Autoload** : `composer dump-autoload`

3. **Migrations** : `php artisan migrate:status`

4. **Logs** : Consulter `storage/logs/laravel.log`

5. **Livewire** : `php artisan livewire:discover`

6. **Permissions** : Vérifier que le seeder a été exécuté

7. **Assets** : `npm run build` si problème CSS/JS

---

## Format de réponse attendu

Demandez à l'IA de répondre avec cette structure :

```
DIAGNOSTIC
[Explication de la cause du problème]

SOLUTION
[Fichier 1 : chemin/complet/fichier.php]
[Code complet corrigé]

[Fichier 2 si nécessaire]
[Code complet corrigé]

COMMANDES À EXÉCUTER
[Liste des commandes artisan/composer/npm si nécessaires]

VÉRIFICATION
[Comment tester que le problème est résolu]
```