---
description: 
---

# PHASE 6 : Productivité & Agenda

Exécutez les prompts 6.1 à 6.4 dans l'ordre.

---

## Prompt 6.1 - Projets

Dans app/Modules/Productivity/, crée la gestion de projets :

**projects** :
- id, name, description (text nullable)
- contact_id (FK nullable - client lié)
- manager_id (FK employees)
- start_date, end_date (nullable)
- budget (decimal 12,2 nullable)
- status (enum: planning, active, on_hold, completed, cancelled)
- billing_type (enum: fixed, hourly, non_billable)
- hourly_rate (decimal 8,2 nullable - pour billing_type=hourly)
- timestamps, soft_deletes

**project_members** :
- id, project_id (FK), employee_id (FK)
- role (string nullable - ex: développeur, chef de projet)
- hourly_rate (decimal 8,2 nullable - surcharge du rate projet)
- timestamps
- unique [project_id, employee_id]

Relations :
- belongsTo Contact, Manager (Employee)
- belongsToMany Employee via project_members
- hasMany Task, Timesheet, Opportunity

Accessors :
- total_hours : sum timesheets.hours
- total_cost : sum(timesheets.hours * rate)
- revenue : sum invoices liées ou budget si fixed
- profit : revenue - total_cost

**Composants Livewire** :

**ProjectsList.php** :
- Filtres : status, manager_id, contact_id
- Colonnes : name, client, manager, dates, status, heures, budget, profit (badge couleur)

**ProjectForm.php** :
- Formulaire projet
- Section membres : ajout/suppression employés avec rôle et rate optionnel

**ProjectShow.php** :
- Tabs : Vue d'ensemble, Tâches, Temps, Équipe, Finances
- Vue d'ensemble : progress bar (si end_date), stats clés
- Finances : graphique heures/coût, comparaison budget

---

## Prompt 6.2 - Tâches

Crée la gestion des tâches :

**tasks** :
- id, project_id (FK), parent_id (FK self nullable - sous-tâches)
- title, description (text nullable)
- assigned_to (FK employees nullable)
- priority (enum: low, medium, high, urgent)
- status (enum: todo, in_progress, review, done)
- start_date (nullable), due_date (nullable)
- estimated_hours (decimal 5,2 nullable)
- order (integer)
- timestamps, soft_deletes

Relations :
- belongsTo Project, Employee (assigned_to), Parent (self)
- hasMany Children (self), Timesheet

Accessors :
- actual_hours : sum timesheets.hours
- is_overdue : due_date < today && status != done

**Composants Livewire** :

**TasksKanban.php** (dans ProjectShow) :
- Colonnes : todo, in_progress, review, done
- Cartes : title, assigned_to avatar, due_date (rouge si overdue), priority badge
- Drag & drop entre colonnes
- Clic ouvre TaskModal

**TasksList.php** :
- Vue liste alternative avec filtres
- Groupement par projet

**TaskModal.php** :
- Modal création/édition
- Champs : title, description, assigned_to, priority, dates, estimated_hours, parent_id
- Section temps : liste entrées temps sur cette tâche, total vs estimé

**TaskShow.php** (page complète optionnelle) :
- Détail complet
- Sous-tâches si applicable
- Timer pour saisie temps : bouton Start/Stop, auto-save entrée

**Timer temps réel** :
- Composant TimeTracker avec Alpine.js
- Bouton Play/Pause, compteur
- Au Stop : crée timesheet avec durée, description optionnelle

---

## Prompt 6.3 - Statistiques projets

Crée les rapports et statistiques de productivité :

**ProjectStatsService.php** :
- getProjectSummary(project) : heures totales, coût, revenue, profit, marge %
- getProjectBurndown(project) : heures estimées vs réelles par semaine
- getTeamUtilization(period) : heures par employé, % facturable
- getProfitByProject(period) : classement projets par rentabilité
- getTimeByClient(period) : heures par client

**Composants Livewire** :

**ProjectStats.php** (dans ProjectShow tab Finances) :
- Cards : heures totales, coût total, revenue, profit (couleur), marge %
- Graphique temps par membre (bar chart)
- Graphique burndown estimé vs réel (line chart)
- Tableau détail par membre : heures, coût, % du total

**ProductivityDashboard.php** :
- Filtres : période (semaine/mois/trimestre/année)
- Stats globales : heures totales, revenue, profit global
- Top 5 projets rentables
- Top 5 employés (heures)
- Répartition facturable vs non-facturable (pie chart)
- Heures par semaine (trend line)

Utiliser Chart.js via CDN. Livewire component avec données calculées côté serveur, JSON passé à Alpine pour rendu Chart.js.

---

## Prompt 6.4 - Agenda

Dans app/Modules/Agenda/, crée le système d'agenda :

**events** :
- id, title, description (text nullable)
- start_datetime, end_datetime
- all_day (boolean default false)
- location (string nullable)
- type (enum: meeting, call, task, reminder, other)
- color (string nullable)
- recurrence_rule (string nullable - format RRULE iCal)
- contact_id (FK nullable), project_id (FK nullable), invoice_id (FK nullable)
- created_by (FK users)
- timestamps, soft_deletes

**event_attendees** :
- id, event_id (FK), user_id (FK)
- status (enum: pending, accepted, declined)
- timestamps

**event_reminders** :
- id, event_id (FK)
- minutes_before (integer)
- type (enum: email, notification)
- sent_at (timestamp nullable)
- timestamps

**EventService.php** :
- getEventsForPeriod(start, end, userId?) : retourne événements dans la période
- expandRecurring(event, start, end) : génère occurrences selon RRULE
- sendReminders() : envoie rappels dont datetime - minutes_before <= now && sent_at is null

**Composants Livewire** :

**Calendar.php** :
- Vue calendrier avec FullCalendar.js (via CDN)
- Vues : mois, semaine, jour
- Filtres : type d'événement, mes événements uniquement
- Clic sur date → ouvre EventModal en création
- Clic sur événement → ouvre EventModal en édition
- Drag & drop pour déplacer (met à jour dates)
- Couleur par type d'événement

**EventModal.php** :
- Formulaire : title, type, dates (datetime picker), all_day toggle, location, description
- Liaisons optionnelles : contact_id, project_id
- Section participants : ajout users, invitation envoyée par email
- Section rappels : ajout rappels avec délai en minutes/heures/jours

**EventShow.php** (page complète optionnelle) :
- Détail complet
- Liens cliquables vers contact/projet/facture liés
- Liste participants avec statuts

Commande Artisan events:send-reminders planifiée toutes les 15 minutes.

Template notification email : resources/views/emails/event-reminder.blade.php