---
description: 
---

# PHASE 1 : Fondations

Exécutez les prompts 1.1 à 1.8 dans l'ordre.

---

## Prompt 1.1 - Initialisation du projet

Crée un projet Laravel 11 avec cette configuration :

Stack : Laravel 11, Livewire 3, Tailwind CSS 3, Alpine.js
Base de données : MySQL 8

Installe et configure :
- laravel/breeze (Livewire stack)
- spatie/laravel-permission
- barryvdh/laravel-dompdf
- maatwebsite/excel
- livewire/volt

Structure modulaire dans app/Modules/ avec ce pattern pour chaque module :
```
app/Modules/{Module}/
├── Models/
├── Livewire/
├── Services/
├── Policies/
├── routes.php
└── Providers/{Module}ServiceProvider.php
```

Configure le ModuleServiceProvider pour charger automatiquement les routes et providers de chaque module.

Crée le fichier config/modules.php listant les modules actifs : Core, CRM, HR, Inventory, Finance, Productivity, Agenda.

---

## Prompt 1.2 - Module Core : Modèles de base

Dans app/Modules/Core/, crée les modèles avec migrations, factories et seeders :

**users** (étend le User Laravel existant) :
- Ajoute : role_id (FK), department_id (FK nullable), avatar (string nullable), is_active (boolean default true)
- Relations : belongsTo Role, belongsTo Department, hasOne Employee

**companies** :
- id, name, logo, address, phone, email, siret, tva_number, timestamps
- Une seule entreprise (single tenant)

**settings** :
- id, key (unique), value (text), module (string), timestamps
- Crée un helper global setting('key', 'default')

Crée un seeder pour :
- 1 company par défaut
- Settings de base : company_name, invoice_prefix (FAC-), order_prefix (CMD-), currency (EUR)

Utilise spatie/laravel-permission pour roles et permissions. Ne recrée pas ces tables.

---

## Prompt 1.3 - Module Core : Rôles et Permissions

Configure spatie/laravel-permission avec ces rôles et permissions :

**Rôles** : super_admin, admin, manager, commercial, comptable, rh, employe

**Permissions par module** (format: module.action) :

CRM : contacts.view, contacts.create, contacts.edit, contacts.delete, opportunities.view, opportunities.create, opportunities.edit, orders.view, orders.create, orders.edit, contracts.view, contracts.create, contracts.edit

Finance : invoices.view, invoices.create, invoices.edit, invoices.send, payments.view, payments.create

HR : employees.view, employees.create, employees.edit, leaves.view, leaves.create, leaves.approve, expenses.view, expenses.create, expenses.approve, recruitment.view, recruitment.manage

Inventory : products.view, products.create, products.edit, stock.view, stock.manage, purchases.view, purchases.create

Productivity : projects.view, projects.create, projects.edit, tasks.view, tasks.create, time.view, time.create

Agenda : events.view, events.create, events.edit

Config : settings.manage

Crée un seeder RolePermissionSeeder qui attribue :
- super_admin : toutes les permissions
- admin : toutes sauf settings.manage
- manager : view/create/edit sur son périmètre + approve sur leaves/expenses
- commercial : CRM complet
- comptable : Finance complet + contacts.view
- rh : HR complet
- employe : *.view limité + leaves.create, expenses.create, time.create

---

## Prompt 1.4 - Layout et composants UI

Crée le layout principal et les composants Livewire réutilisables :

**Layout resources/views/components/layouts/app.blade.php** :
- Sidebar gauche fixe avec navigation par module (icônes + labels)
- Header : logo, recherche globale, notifications, dropdown user
- Zone de contenu principale
- Responsive (sidebar masquée sur mobile avec toggle)

**Composants Blade dans resources/views/components/** :

ui/card.blade.php : conteneur avec titre optionnel, padding
ui/button.blade.php : props type (primary/secondary/danger), size (sm/md/lg), icon
ui/badge.blade.php : props color (gray/green/red/yellow/blue)
ui/modal.blade.php : modal Livewire-compatible avec Alpine.js
ui/table.blade.php : table stylée avec header, slots pour rows
ui/input.blade.php : input avec label, error, props type/name/placeholder
ui/select.blade.php : select avec label, error, options
ui/textarea.blade.php : textarea avec label, error
ui/dropdown.blade.php : dropdown menu avec Alpine.js
ui/alert.blade.php : alerte type success/error/warning/info
ui/pagination.blade.php : pagination Tailwind
ui/stats-card.blade.php : carte statistique avec icône, valeur, label, variation

Utilise Tailwind CSS. Style moderne, épuré, couleur primaire indigo-600.

---

## Prompt 1.5 - Module CRM : Modèle Contacts

Dans app/Modules/CRM/, crée le modèle Contact avec migration :

**contacts** :
- id, type (enum: prospect, client, fournisseur)
- company_name, first_name, last_name
- email, phone, mobile
- address, city, postal_code, country
- status (enum: active, inactive), source (string nullable)
- assigned_to (FK users nullable)
- notes (text nullable)
- converted_at (timestamp nullable)
- timestamps, soft_deletes

**Relations** :
- belongsTo User (assigned_to)
- hasMany Opportunity, Order, Contract, Invoice

**Scopes** :
- scopeProspects(), scopeClients(), scopeFournisseurs()
- scopeActive(), scopeAssignedTo($userId)

**Accessors** :
- full_name : first_name + last_name
- display_name : company_name ?: full_name

Crée ContactFactory avec des données FR réalistes (Faker fr_FR).
Crée ContactPolicy avec les règles basées sur les permissions spatie.

---

## Prompt 1.6 - CRUD Contacts Livewire

Crée les composants Livewire pour le CRUD Contacts dans app/Modules/CRM/Livewire/ :

**ContactsList.php** :
- Pagination 25 items
- Recherche (company_name, first_name, last_name, email)
- Filtres : type, status, assigned_to
- Tri par colonne (company_name, created_at)
- Actions : voir, modifier, supprimer (avec confirmation modal)
- Bouton "Nouveau contact"
- Export CSV des résultats filtrés

**ContactForm.php** :
- Mode création et édition (prop ?Contact $contact)
- Validation complète avec Form Request rules
- Select assigned_to (liste users avec permission contacts.create)
- Select type et status
- Boutons : Annuler, Enregistrer
- Redirect vers show après save
- Flash message succès

**ContactShow.php** :
- Affichage fiche complète
- Tabs : Infos, Opportunités, Commandes, Factures, Contrats, Historique
- Timeline des activités (à implémenter plus tard)
- Actions : Modifier, Convertir en client (si prospect), Supprimer
- Bouton retour liste

Crée les routes dans app/Modules/CRM/routes.php :
- GET /crm/contacts → ContactsList
- GET /crm/contacts/create → ContactForm
- GET /crm/contacts/{contact} → ContactShow  
- GET /crm/contacts/{contact}/edit → ContactForm

Applique le middleware permission sur chaque route.

---

## Prompt 1.7 - Module Inventory : Produits

Dans app/Modules/Inventory/, crée les modèles avec migrations :

**product_categories** :
- id, name, parent_id (FK self nullable), timestamps
- Relations : belongsTo parent, hasMany children, hasMany products

**products** :
- id, type (enum: product, service)
- reference (string unique), name, description (text nullable)
- category_id (FK nullable)
- purchase_price (decimal 10,2), selling_price (decimal 10,2)
- tax_rate (decimal 5,2 default 20.00)
- unit (string default 'unité')
- is_active (boolean default true)
- track_stock (boolean default true)
- min_stock_alert (integer nullable)
- timestamps, soft_deletes

**Relations** :
- belongsTo ProductCategory
- hasMany StockLevel, OrderLine, InvoiceLine

**Accessors** :
- margin : selling_price - purchase_price
- margin_percent : (margin / purchase_price) * 100

**Scopes** :
- scopeActive(), scopeProducts(), scopeServices(), scopeTrackStock()

Crée ProductFactory et ProductCategorySeeder avec catégories de base.

---

## Prompt 1.8 - CRUD Produits Livewire

Crée les composants Livewire pour Produits dans app/Modules/Inventory/Livewire/ :

**ProductsList.php** :
- Pagination 25 items
- Recherche (reference, name)
- Filtres : type, category_id, is_active, track_stock
- Colonnes : reference, name, type, category, prix achat, prix vente, marge %, stock (si track_stock), statut
- Badge couleur pour marge (vert >30%, orange 10-30%, rouge <10%)
- Actions : voir, modifier, dupliquer, désactiver/activer

**ProductForm.php** :
- Champs : reference (auto-généré si vide), name, description, type, category_id, purchase_price, selling_price, tax_rate, unit, track_stock, min_stock_alert
- Affichage dynamique marge en temps réel
- track_stock masque/affiche min_stock_alert
- Validation : reference unique, selling_price >= 0, purchase_price >= 0

**ProductShow.php** :
- Infos produit
- Si track_stock : tableau des niveaux de stock par entrepôt
- Historique des mouvements de stock (à venir)

Routes dans app/Modules/Inventory/routes.php :
- /inventory/products/*
- /inventory/categories/* (CRUD simple)

Middleware permission products.*.