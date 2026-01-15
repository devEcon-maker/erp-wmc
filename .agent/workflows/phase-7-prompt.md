---
description: 
---

# PHASE 7 : Finalisation

Exécutez les prompts 7.1 à 7.6 dans l'ordre.

---

## Prompt 7.1 - Dashboard principal

Crée le dashboard principal de l'application :

**DashboardController ou composant Dashboard.php** :
- Accessible après login sur /dashboard
- Affiche widgets selon les permissions de l'utilisateur

**Widgets conditionnels** :

**StatCards** (haut de page) :
- CA du mois (si permission invoices.view)
- Factures en attente (si permission invoices.view)
- Commandes en cours (si permission orders.view)
- Tâches à faire (de l'utilisateur)

**OpportunityMiniPipeline** (si permission opportunities.view) :
- Version compacte du pipeline : compteur par étape, montant total, lien "Voir tout"

**UpcomingEvents** (si permission events.view) :
- Liste des 5 prochains événements
- Lien vers agenda

**RecentActivity** :
- Timeline des dernières actions : factures créées, commandes, nouveaux contacts, etc.
- Utiliser Polymorphic relation Activity ou simple log

**MyPendingApprovals** (si manager/rh/admin) :
- Demandes de congés pending
- Notes de frais à approuver
- Lien vers chaque section

**MyTasks** :
- Tâches assignées à l'utilisateur, triées par due_date
- Indicateur overdue
- Actions rapides : marquer done

**StockAlerts** (si permission stock.view) :
- Produits en alerte stock
- Bouton créer commande fournisseur

Layout responsive :
- Desktop : grille 3 colonnes
- Mobile : stack vertical

---

## Prompt 7.2 - Recherche globale

Crée la recherche globale multi-entités :

**GlobalSearchService.php** :
- search(query, user) : recherche dans contacts, products, invoices, orders, projects, employees selon permissions
- Retourne résultats groupés par type avec label, url, icône

Recherche par :
- Contacts : company_name, first_name, last_name, email
- Products : reference, name
- Invoices : reference
- Orders : reference
- Projects : name
- Employees : first_name, last_name, email, employee_number

**Composant GlobalSearch.php** :
- Input dans le header, ouvre dropdown au focus
- Debounce 300ms sur la saisie
- Dropdown avec résultats groupés par catégorie (icône + label)
- Navigation clavier (flèches + enter)
- Clic ou Enter → navigation vers la page détail
- Max 5 résultats par catégorie

Utiliser Livewire avec wire:model.live.debounce.300ms et Alpine.js pour la navigation clavier.

Optionnel : intégrer Laravel Scout avec Meilisearch pour de meilleures performances sur gros volumes. Pour commencer, simple LIKE sur les colonnes indexées suffit.

---

## Prompt 7.3 - Notifications

Crée le système de notifications in-app :

**Table notifications** (utiliser celle de Laravel) :
- php artisan notifications:table && migrate

**Types de notifications** (classes dans app/Notifications/) :
- LeaveRequestSubmitted : vers manager
- LeaveRequestApproved/Rejected : vers employé
- ExpenseReportSubmitted : vers admin
- ExpenseReportApproved/Rejected : vers employé
- InvoiceOverdue : vers comptable/admin
- StockAlert : vers gestionnaire stock
- EventReminder : vers participants
- NewJobApplication : vers RH
- TaskAssigned : vers employé

Chaque notification :
- implements ShouldQueue
- toDatabase() : stocke message, type, data (liens)
- toMail() : envoie email (optionnel selon préférence user)

**Composant NotificationDropdown.php** :
- Icône cloche dans header avec badge compteur unread
- Dropdown avec liste notifications récentes
- Clic sur notification : marque lue + navigue vers lien
- Bouton "Tout marquer comme lu"
- Lien "Voir toutes" vers page /notifications

**NotificationsList.php** :
- Page complète des notifications
- Filtres : lues/non lues, type
- Pagination

Utiliser Laravel Echo avec Pusher ou Reverb pour notifications temps réel (optionnel, ajouter dans une phase ultérieure).

---

## Prompt 7.4 - Exports et rapports

Crée le système d'exports :

**ExportService.php** :
Méthodes génériques :
- exportToExcel(collection, columns, filename) : utilise Maatwebsite/Excel
- exportToPDF(view, data, filename) : utilise DomPDF
- exportToCSV(collection, columns, filename)

**Exports spécifiques** (classes dans app/Exports/) :

**ContactsExport** :
- Colonnes : type, company_name, full_name, email, phone, city, status, created_at
- Filtres : mêmes que ContactsList

**ProductsExport** :
- Colonnes : reference, name, type, category, purchase_price, selling_price, margin, stock

**InvoicesExport** :
- Colonnes : reference, contact, date, due_date, total_ttc, paid_amount, status
- Filtres : période, status

**TimesheetsExport** :
- Colonnes : date, employee, project, task, hours, billable, description
- Filtres : période, employee, project

**LeaveBalancesExport** :
- Colonnes : employee, type, allocated, used, remaining

**Intégration UI** :
- Bouton "Exporter" dans chaque liste avec dropdown : Excel, CSV, PDF
- Export applique les filtres actifs
- Pour gros exports : dispatch Job et notification quand prêt avec lien téléchargement

Queue les exports volumineux (>1000 lignes) via Laravel Queue.

---

## Prompt 7.5 - Tests et documentation

Configure les tests et la documentation :

**Tests unitaires** (tests/Unit/) :
- InvoiceServiceTest : calcul totaux, génération référence, changements status
- StockServiceTest : mouvements, réservation, validation stock négatif
- LeaveServiceTest : calcul jours, vérification solde, approbation

**Tests fonctionnels** (tests/Feature/) :
- ContactsCrudTest : création, édition, suppression, permissions
- InvoiceWorkflowTest : création → envoi → paiement → paid
- LeaveRequestWorkflowTest : soumission → approbation → déduction solde
- AuthenticationTest : login, permissions, accès refusé

**Factories** pour tous les modèles avec états :
- InvoiceFactory : states draft, sent, paid, overdue
- OrderFactory : states draft, confirmed, delivered
- LeaveRequestFactory : states pending, approved, rejected

Utiliser Pest PHP pour une syntaxe plus lisible.

**Documentation** dans /docs :
- README.md : installation, configuration, démarrage
- ARCHITECTURE.md : structure modules, conventions
- API.md : endpoints si API exposée
- DEPLOYMENT.md : mise en production

**Seeders de démonstration** :
- DatabaseSeeder appelle tous les seeders
- Crée données réalistes : 50 contacts, 20 produits, 10 employés, factures sur 6 mois
- Utilisateur démo : demo@example.com / password

---

## Prompt 7.6 - Optimisations et sécurité

Applique les optimisations et mesures de sécurité :

**Performance** :
- Eager loading systématique : with() dans toutes les requêtes avec relations
- Cache des settings : Cache::rememberForever('settings', ...)
- Index DB sur : contacts.type, contacts.assigned_to, invoices.status, invoices.contact_id, orders.status, products.reference, stock_levels.product_id
- Pagination sur toutes les listes (25 items)

**Sécurité** :
- Policies sur tous les modèles, vérification dans chaque composant Livewire
- Form Requests avec validation stricte
- Sanitization des inputs HTML (strip_tags ou HTMLPurifier si rich text)
- Rate limiting sur login et formulaires publics (recrutement)
- CSRF sur tous les formulaires (automatique avec Livewire)
- Validation upload fichiers : types autorisés (pdf, jpg, png), taille max 10MB
- Storage privé pour documents sensibles (expenses, applications)

**Audit** :
- Installer spatie/laravel-activitylog
- Logger les actions CRUD sur : contacts, invoices, orders, employees, contracts
- Interface admin pour consulter les logs

**Backups** :
- Installer spatie/laravel-backup
- Configurer backup quotidien DB + fichiers storage
- Notification email si backup échoue

**Configuration production** :
- .env.example complet avec toutes les variables
- config/app.php : timezone Europe/Paris
- Queues : configurer Redis + Supervisor pour workers
- Scheduler : configurer cron pour artisan schedule:run

Commande finale : php artisan optimize pour cache config/routes/views

---

## Récapitulatif des commandes Artisan personnalisées

```
php artisan contracts:generate-invoices    # Génère factures récurrentes (daily 6h)
php artisan invoices:check-overdue         # Met à jour statuts overdue (daily 8h)
php artisan invoices:send-reminders        # Envoie relances (daily 9h)
php artisan events:send-reminders          # Rappels événements (every 15min)
php artisan leaves:reset-balances          # Reset soldes congés (yearly Jan 1)
php artisan stock:check-alerts             # Alertes stock bas (daily 7h)
```