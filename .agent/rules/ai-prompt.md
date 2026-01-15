---
trigger: always_on
---

# Prompts AI - CRM & ERP Laravel 12 / Livewire 3

Stack technique : Laravel 12, Livewire 3, Tailwind CSS 4, Alpine.js, MySQL 8. Packages requis : spatie/laravel-permission pour rôles et permissions, barryvdh/laravel-dompdf pour génération PDF, maatwebsite/excel pour exports. Architecture modulaire dans app/Modules/ avec 7 modules : Core, CRM, HR, Inventory, Finance, Productivity, Agenda. Chaque module contient : Models/, Livewire/, Services/, Policies/, routes.php et {Module}ServiceProvider.php pour autoload automatique des routes et bindings.

---

## PHASE 1 : Fondations

**1.1 Initialisation** : Crée projet Laravel 12 avec Livewire 3, Tailwind CSS 4, Alpine.js et MySQL 8. Installe breeze stack Livewire et tous packages requis. Configure structure modulaire 7 modules avec chargement automatique des routes et providers via ModuleServiceProvider.

**1.2 Module Core** : Crée modèles avec migrations. Users : ajoute role_id FK, department_id FK nullable, avatar string nullable, is_active boolean. Companies : id, name, logo, address, phone, email, siret, tva_number, timestamps. Settings : id, key unique, value text, module string, timestamps. Crée helper global setting('key', 'default'). Seeder : company par défaut, settings invoice_prefix FAC-, order_prefix CMD-, currency EUR, default_payment_days 30.

**1.3 Permissions** : Configure spatie avec rôles super_admin, admin, manager, commercial, comptable, rh, employe. Permissions format module.action pour contacts, opportunities, orders, contracts, invoices, payments, employees, leaves avec approve, expenses avec approve, recruitment, products, stock, purchases, projects, tasks, time, events, settings. Crée RolePermissionSeeder avec attributions complètes par rôle.

**1.4 Layout UI** : Crée layout app.blade.php avec sidebar gauche navigation modules icônes, header recherche globale notifications user dropdown, zone contenu responsive. Composants Blade ui/ : card, button props type/size, badge props color, modal Alpine, table, input avec label error, select, textarea, dropdown, alert, pagination, stats-card. Style Tailwind moderne couleur indigo-600.

**1.5 Contact** : Dans CRM crée Contact avec type enum prospect/client/fournisseur, company_name, first_name, last_name, email, phone, mobile, address, city, postal_code, country, status enum active/inactive, source, assigned_to FK users, notes, converted_at, timestamps, soft_deletes. Relations hasMany Opportunity Order Contract Invoice. Scopes Prospects Clients Fournisseurs Active. Accessors full_name display_name. Factory données FR, Policy permissions.

**1.6 CRUD Contacts** : ContactsList pagination 25, recherche multi-champs, filtres type/status/assigned_to, tri colonnes, actions voir/modifier/supprimer modal, export CSV. ContactForm création édition validation complète. ContactShow fiche tabs Infos Opportunités Commandes Factures Contrats Historique. Routes middleware permission.

**1.7 Produits** : ProductCategory avec name parent_id FK self. Product avec type enum product/service, reference unique, name, description, category_id, purchase_price selling_price decimals, tax_rate default 20.00, unit, is_active, track_stock, min_stock_alert, timestamps, soft_deletes. Accessors margin margin_percent. Scopes Active Products Services. Factory CategorySeeder.

**1.8 CRUD Produits** : ProductsList recherche filtres badge couleur marge vert>30% orange10-30% rouge<10%. ProductForm reference auto marge temps réel toggle track_stock. ProductShow infos stock entrepôts. Routes middleware.

---

## PHASE 2 : CRM Complet

**2.1 Opportunités** : opportunity_stages avec name order probability 0-100 color et seeder Qualification 10% Proposition 30% Négociation 60% Gagnée 100% Perdue 0%. opportunities avec contact_id title description amount probability stage_id expected_close_date assigned_to won_at lost_at lost_reason soft_deletes. Accessors weighted_amount status. Scopes Open Won Lost ByStage.

**2.2 Pipeline** : OpportunityPipeline vue Kanban colonnes stages, cartes infos clés, drag drop Sortable.js update stage_id, modal si Gagnée option créer commande, modal saisie raison si Perdue, filtres stats. OpportunityForm modal contact searchable probability auto. OpportunityShow détail timeline propositions.

**2.3 Propositions** : proposals avec reference auto PROP-YYYYMM-XXXX, opportunity_id contact_id dates, status enum draft/sent/accepted/refused, totaux HT/TVA/TTC, notes conditions pdf_path sent_at soft_deletes. proposal_lines product_id description quantity unit_price discount tax_rate total order. ProposalService calculateTotals generatePDF DomPDF send email PDF. ProposalBuilder formulaire en-tête tableau lignes inline calculs temps réel actions brouillon/prévisualiser/envoyer.

**2.4 Commandes** : orders avec type enum client/fournisseur, contact_id, reference auto CMD- ou ACH-, date, status workflow draft/confirmed/processing/shipped/delivered/cancelled, totaux, shipping_address, proposal_id soft_deletes. order_lines avec delivered_qty. OrderService calculateTotals, confirm réserve stock, deliver déduit stock, cancel libère stock, createFromProposal, generateInvoice. Composants List Form Show workflow badges couleur.

**2.5 Contrats** : contracts avec contact_id reference auto CTR-, type enum contract/subscription, dates, renewal_type, billing_frequency enum monthly/quarterly/yearly, amount, status draft/active/suspended/terminated, next_billing_date soft_deletes. contract_lines. ContractService activate calcule next_billing_date suspend terminate generateRecurringInvoice. Commande contracts:generate-invoices planifiée daily 6h génère factures abonnements.

---

## PHASE 3 : Finance

**3.1 Factures** : invoices avec type enum client/fournisseur/avoir, contact_id order_id contract_id, reference auto FAC-YYYY-XXXXX séquentiel annuel sans rupture, dates, status draft/sent/partial/paid/overdue/cancelled, totaux, paid_amount, pdf_path sent_at soft_deletes. invoice_lines complètes. payments invoice_id date amount method enum bank_transfer/check/cash/card/other reference. payment_reminders type enum first/second/final sent_at. InvoiceService generateReference calculateTotals generatePDF send recordPayment checkOverdue createAvoir. Règle envoyée non supprimable.

**3.2 Livewire Factures** : InvoicesList filtres stats totaux indicateur rouge retard actions. InvoiceForm création zéro ou depuis commande/contrat due_date auto. InvoiceShow détail paiements inline historique relances barre progression. InvoicePreview PDF mentions légales coordonnées bancaires.

**3.3 Relances** : Settings reminder_first_days 7 second 15 final 30 templates email. ReminderService getInvoicesForReminder sendReminder processAllReminders. Templates email courtois ferme mise en demeure. Commandes invoices:check-overdue daily 8h invoices:send-reminders daily 9h.

---

## PHASE 4 : Stock Achats

**4.1 Stocks** : warehouses name address is_default seeder Principal. stock_levels product_id warehouse_id quantity reserved_quantity unique. stock_movements product_id warehouse_id type enum in/out/transfer/adjustment quantity reference_type reference_id from_warehouse_id date notes created_by. StockService getAvailableStock reserve release addStock removeStock transfer adjust. Validation stock non négatif.

**4.2 Interface** : StockDashboard stats tableau produits badge rouge alerte filtres. StockMovementForm modal création types. StockHistory historique filtres. Intégration commandes confirm→reserve deliver→removeStock+release cancel→release. Commande stock:check-alerts daily 7h notification produits sous seuil.

**4.3 Achats** : purchase_orders supplier_id FK contacts fournisseur reference auto ACH- dates status draft/sent/partial/received/cancelled totaux soft_deletes. purchase_order_lines received_qty. PurchaseOrderService calculateTotals send email PDF receivePartial mouvements stock receiveAll cancel. Composants List Form Show réception. ReorderSuggestions produits à commander.

---

## PHASE 5 : RH

**5.1 Employés** : departments name manager_id parent_id seeder Direction Commercial Technique Administratif RH. employees user_id employee_number auto EMP-XXXX infos personnelles dates job_title department_id manager_id salary visible admin/rh contract_type enum cdi/cdd/interim/stage/alternance status soft_deletes. Composants EmployeesList masquage salary EmployeeForm création user optionnelle EmployeeShow onglets OrgChart visuel.

**5.2 Congés** : leave_types name days_per_year is_paid requires_approval color seeder CP 25j RTT 10j Maladie Sans solde. leave_balances employee_id leave_type_id year allocated used unique. leave_requests dates days_count reason status pending/approved/rejected/cancelled approved_by rejection_reason. LeaveService calculateDaysCount jours ouvrés FR canRequest submit approve décrémente reject cancel réincrémente. Composants LeaveBalances LeaveRequestForm LeaveRequestsList LeaveCalendar équipe LeaveApproval. Commande leaves:reset-balances yearly Jan 1.

**5.3 Frais** : expense_categories name max_amount requires_receipt seeder Transport Repas Hébergement Fournitures Autre. expense_reports employee_id reference auto NDF- période status draft/submitted/approved/rejected/paid total_amount approved_by paid_at soft_deletes. expense_lines category_id date description amount receipt_path. ExpenseService calculateTotal submit approve reject markPaid. Upload storage private. Composants List Form Show Approval.

**5.4 Temps** : timesheets employee_id project_id task_id date hours description billable approved approved_by. Règles max 24h jour pas futur upsert. TimesheetService getTotalHoursForDay getWeeklyReport approve. Composants TimesheetWeekly auto-save TimesheetDaily TimesheetReport export Excel.

**5.5 Recrutement** : job_positions title department_id description requirements type full_time/part_time/contract/internship location salary_range status draft/published/closed dates soft_deletes. job_applications job_position_id infos candidat resume_path cover_letter status new/reviewing/interview/offer/hired/rejected rating notes. Composants JobPositionsList stats JobPositionForm JobPositionShow. ApplicationsKanban drag drop. ApplicationForm page publique /careers/{id}/apply sans auth. ApplicationShow modal. Conversion hired création Employee.

---

## PHASE 6 : Productivité Agenda

**6.1 Projets** : projects name description contact_id client manager_id dates budget status planning/active/on_hold/completed/cancelled billing_type fixed/hourly/non_billable hourly_rate soft_deletes. project_members project_id employee_id role hourly_rate unique. Accessors total_hours total_cost revenue profit. Composants ProjectsList colonnes profit ProjectForm section membres ProjectShow tabs Vue ensemble Tâches Temps Équipe Finances.

**6.2 Tâches** : tasks project_id parent_id title description assigned_to priority low/medium/high/urgent status todo/in_progress/review/done dates estimated_hours order soft_deletes. Accessors actual_hours is_overdue. Composants TasksKanban Sortable drag drop TasksList groupement TaskModal création édition TaskShow détail TimeTracker timer Alpine Play/Pause/Stop.

**6.3 Stats** : ProjectStatsService getProjectSummary getProjectBurndown getTeamUtilization getProfitByProject getTimeByClient. Composants ProjectStats graphiques Chart.js tab Finances ProductivityDashboard filtres période top 5 projets employés répartition facturable.

**6.4 Agenda** : events title description start/end_datetime all_day location type meeting/call/task/reminder/other color recurrence_rule RRULE contact_id project_id invoice_id created_by soft_deletes. event_attendees status pending/accepted/declined. event_reminders minutes_before type email/notification sent_at. EventService getEventsForPeriod expandRecurring sendReminders. Composants Calendar FullCalendar.js vues mois/semaine/jour EventModal complet EventShow liens. Commande events:send-reminders toutes 15min.