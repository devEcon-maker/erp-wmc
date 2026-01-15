---
description: 
---

# PHASE 5 : Ressources Humaines

Exécutez les prompts 5.1 à 5.5 dans l'ordre.

---

## Prompt 5.1 - Employés et départements

Dans app/Modules/HR/, crée la gestion des employés :

**departments** :
- id, name, manager_id (FK employees nullable), parent_id (FK self nullable)
- timestamps
- Seeder avec : Direction, Commercial, Technique, Administratif, RH

**employees** :
- id, user_id (FK nullable - lié au compte utilisateur)
- employee_number (unique, auto: EMP-XXXX)
- first_name, last_name, email, phone
- birth_date, hire_date, end_date (nullable)
- job_title, department_id (FK)
- manager_id (FK employees nullable)
- salary (decimal 10,2 nullable - visible seulement admin/rh)
- contract_type (enum: cdi, cdd, interim, stage, alternance)
- status (enum: active, inactive, terminated)
- timestamps, soft_deletes

Relations :
- belongsTo User, Department, Manager (self)
- hasMany DirectReports (self), LeaveRequest, ExpenseReport, Timesheet

Accessors :
- full_name, seniority_years

**Composants Livewire** :
- EmployeesList : filtres department/status/contract_type, masque salary sauf permission
- EmployeeForm : formulaire complet, création compte user optionnelle
- EmployeeShow : fiche détaillée, onglets (infos, congés, frais, temps)
- OrgChart : organigramme visuel par départements (tree view)

---

## Prompt 5.2 - Gestion des congés

Crée le système de gestion des congés :

**leave_types** :
- id, name, days_per_year (decimal 5,2), is_paid (boolean), requires_approval (boolean default true), color
- Seeder : Congés payés (25j, vert), RTT (10j, bleu), Maladie (0j, rouge), Sans solde (0j, gris)

**leave_balances** :
- id, employee_id (FK), leave_type_id (FK), year (integer)
- allocated (decimal 5,2), used (decimal 5,2)
- Accessor : remaining = allocated - used
- unique [employee_id, leave_type_id, year]

**leave_requests** :
- id, employee_id (FK), leave_type_id (FK)
- start_date, end_date, days_count (decimal 5,2)
- reason (text nullable)
- status (enum: pending, approved, rejected, cancelled)
- approved_by (FK users nullable), approved_at, rejection_reason
- timestamps

**LeaveService.php** :
- calculateDaysCount(start, end) : calcule jours ouvrés (exclut weekends, jours fériés FR)
- canRequest(employee, leaveType, days) : vérifie solde suffisant
- submit(LeaveRequest) : status=pending, notification manager
- approve(LeaveRequest, approver) : status=approved, décrémente balance.used
- reject(LeaveRequest, reason) : status=rejected
- cancel(LeaveRequest) : status=cancelled, réincrémente balance si était approved

Règles :
- Pas de demande dans le passé
- Détection chevauchements avec autres demandes
- Notification email au manager lors de la soumission

**Composants Livewire** :
- LeaveBalances : affiche soldes par type pour l'employé connecté
- LeaveRequestForm : sélection type, dates, calcul auto days_count, affiche solde restant
- LeaveRequestsList : mes demandes + demandes à approuver (si manager/rh)
- LeaveCalendar : calendrier mensuel des absences de l'équipe, code couleur par type
- LeaveApproval : interface manager pour approuver/rejeter avec notes

Commande Artisan leaves:reset-balances : recopie days_per_year dans allocated pour nouvelle année. Planifiée 1er janvier 00:00.

---

## Prompt 5.3 - Notes de frais

Crée le système de notes de frais :

**expense_categories** :
- id, name, max_amount (decimal nullable - plafond par ligne), requires_receipt (boolean default true)
- Seeder : Transport (200€), Repas (25€, justif requis), Hébergement (150€), Fournitures (100€), Autre (null)

**expense_reports** :
- id, employee_id (FK), reference (auto: NDF-YYYYMM-XXXX)
- period_start, period_end (dates)
- status (enum: draft, submitted, approved, rejected, paid)
- total_amount (decimal 10,2)
- submitted_at, approved_by (FK), approved_at, paid_at
- timestamps, soft_deletes

**expense_lines** :
- id, expense_report_id (FK), category_id (FK)
- date, description, amount (decimal 10,2)
- receipt_path (string nullable)
- timestamps

**ExpenseService.php** :
- calculateTotal(ExpenseReport)
- submit(ExpenseReport) : status=submitted, notification admin/manager
- approve(ExpenseReport, approver)
- reject(ExpenseReport, reason)
- markPaid(ExpenseReport)

Validation :
- amount <= category.max_amount (si défini)
- receipt_path requis si category.requires_receipt
- Pas de modification après submitted

**Composants Livewire** :
- ExpenseReportsList : mes notes + notes à approuver (si permission)
- ExpenseReportForm : période, ajout lignes, upload justificatifs (storage/expenses/), calcul total
- ExpenseReportShow : détail, visualisation justificatifs, workflow actions
- ExpenseApproval : interface approbation avec détail des lignes

Upload via Livewire file upload, stockage dans storage/app/private/expenses/.

---

## Prompt 5.4 - Feuilles de temps

Crée le système de feuilles de temps :

**timesheets** :
- id, employee_id (FK), project_id (FK), task_id (FK nullable)
- date, hours (decimal 5,2)
- description (text nullable)
- billable (boolean default true)
- approved (boolean default false), approved_by (FK nullable), approved_at
- timestamps

Règles :
- Max 24h par jour par employé
- Pas de saisie future
- Une entrée par jour/projet/tâche (upsert)

**TimesheetService.php** :
- getTotalHoursForDay(employee, date)
- getWeeklyReport(employee, weekStart)
- approve(Timesheet, approver)
- getProjectTimeReport(project) : heures par employé, coût

**Composants Livewire** :

**TimesheetWeekly.php** :
- Vue semaine avec jours en colonnes
- Lignes par projet/tâche
- Inputs heures par cellule (format 1h30 ou 1.5)
- Total par jour, total semaine
- Navigation semaine précédente/suivante
- Auto-save au blur

**TimesheetDaily.php** :
- Vue jour avec formulaire ajout : project_id, task_id (filtré par projet), hours, description, billable
- Liste entrées du jour avec édition inline

**TimesheetReport.php** (manager/admin) :
- Filtres : période, employé, projet, billable
- Tableau heures par employé avec totaux
- Export Excel

Intégration avec module Productivity : les heures alimentent les stats projets.

---

## Prompt 5.5 - Recrutement

Crée le module recrutement :

**job_positions** :
- id, title, department_id (FK)
- description (text), requirements (text)
- type (enum: full_time, part_time, contract, internship)
- location (string nullable)
- salary_range_min, salary_range_max (decimals nullable)
- status (enum: draft, published, closed)
- published_at (timestamp nullable), closes_at (date nullable)
- timestamps, soft_deletes

**job_applications** :
- id, job_position_id (FK)
- first_name, last_name, email, phone
- resume_path, cover_letter (text nullable)
- status (enum: new, reviewing, interview, offer, hired, rejected)
- rating (integer 1-5 nullable)
- notes (text nullable)
- applied_at, timestamps

**Composants Livewire** :

**JobPositionsList.php** :
- Filtres : department, status, type
- Stats : nb candidatures par poste
- Actions : modifier, publier/dépublier, clôturer

**JobPositionForm.php** :
- Formulaire complet
- Éditeur rich text pour description/requirements (utiliser textarea simple pour commencer)

**JobPositionShow.php** :
- Détail poste
- Liste candidatures avec filtres status
- Statistiques : nb par status, temps moyen

**ApplicationsKanban.php** :
- Vue Kanban : colonnes = status
- Cartes : nom, email, date, rating (étoiles)
- Drag & drop pour changer status
- Clic ouvre modal détail

**ApplicationForm.php** :
- Page publique /careers/{position}/apply (pas d'auth requise)
- Upload CV (PDF), cover letter optionnelle
- Confirmation email au candidat

**ApplicationShow.php** (modal) :
- Infos candidat, téléchargement CV
- Notes internes, rating
- Actions : Rejeter, Planifier entretien, Faire offre, Embaucher

Si status=hired : bouton "Créer fiche employé" pré-remplie avec les infos candidat.