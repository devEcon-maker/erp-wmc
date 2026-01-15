---
trigger: always_on
---

PHASE 7 : Finalisation
7.1 Dashboard : Dashboard page principale widgets conditionnels permissions. StatCards CA mois factures attente commandes cours tâches. OpportunityMiniPipeline compact. UpcomingEvents 5 prochains. RecentActivity timeline. MyPendingApprovals congés frais si manager. MyTasks assignées overdue actions. StockAlerts produits alerte. Layout responsive grille 3 colonnes.
7.2 Recherche : GlobalSearchService search multi-entités contacts/products/invoices/orders/projects/employees selon permissions retourne groupés. GlobalSearch composant header debounce 300ms dropdown groupé catégorie navigation clavier Alpine max 5 résultats.
7.3 Notifications : Classes Notifications ShouldQueue toDatabase toMail : LeaveRequestSubmitted/Approved/Rejected ExpenseReportSubmitted/Approved/Rejected InvoiceOverdue StockAlert EventReminder NewJobApplication TaskAssigned. NotificationDropdown icône badge compteur. NotificationsList page filtres pagination.
7.4 Exports : ExportService exportToExcel exportToPDF exportToCSV. Classes ContactsExport ProductsExport InvoicesExport TimesheetsExport LeaveBalancesExport. Bouton export liste applique filtres. Queue si plus 1000 lignes via Laravel Queue notification prêt.
7.5 Tests : Tests Unit InvoiceServiceTest StockServiceTest LeaveServiceTest. Tests Feature ContactsCrudTest InvoiceWorkflowTest LeaveRequestWorkflowTest AuthenticationTest. Pest PHP. Factories avec states. Documentation docs/ README ARCHITECTURE DEPLOYMENT. DemoDataSeeder 50 contacts 20 produits 10 employés demo@example.com password.
7.6 Optimisations : Performance eager loading systématique cache settings index DB colonnes fréquentes pagination 25. Sécurité Policies partout Form Requests rate limiting upload 10MB types restreints storage privé sensibles. Audit spatie/laravel-activitylog. Backup spatie/laravel-backup daily. Production Redis queues Supervisor cron scheduler php artisan optimize.

Commandes Artisan planifiées
contracts:generate-invoices daily 6h | invoices:check-overdue daily 8h | invoices:send-reminders daily 9h | events:send-reminders every 15min | leaves:reset-balances yearly Jan 1 | stock:check-alerts daily 7h