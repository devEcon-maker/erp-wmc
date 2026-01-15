---
trigger: always_on
---

# PRD - CRM & ERP Laravel/Livewire

## 1. Aperçu du projet

**Stack technique :** Laravel 12, Livewire 3, Tailwind CSS 3, Alpine.js, MySQL 8
**Packages requis :** spatie/laravel-permission, barryvdh/laravel-dompdf, maatwebsite/excel
**Objectif principal :** Système de gestion intégré couvrant la relation client, les ressources humaines, la gestion des stocks, la facturation, la productivité et l'agenda de l'entreprise.
**Structure du projet :** Architecture modulaire dans app/Modules/ avec 7 modules distincts : Core, CRM, HR, Inventory, Finance, Productivity, Agenda. Chaque module contient ses propres Models, Livewire, Services, Policies, routes.php et un ServiceProvider dédié pour l'enregistrement automatique.

## 2. Modules et fonctionnalités détaillées

### 2.1 Module Core
Gestion complète des utilisateurs avec système de rôles et permissions via spatie/laravel-permission. Rôles disponibles dans le système : super_admin, admin, manager, commercial, comptable, rh, employe. Paramètres système configurables incluant les préfixes des documents, les délais de paiement par défaut et la devise. Entreprise unique en mode single-tenant avec informations légales complètes. Dashboard principal avec widgets conditionnels selon les permissions de l'utilisateur connecté. Recherche globale multi-entités avec résultats groupés par catégorie. Système de notifications in-app avec support email optionnel.

### 2.2 Module CRM

**Contacts :** Gestion unifiée des prospects, clients et fournisseurs avec conversion automatique de prospect vers client lors de la première commande. Attribution à un commercial responsable, suivi des interactions via timeline chronologique, import et export au format CSV, recherche avancée avec filtres multiples combinables, détection et fusion des doublons. Soft delete systématique pour conserver l'historique des relations commerciales.

**Opportunités :** Pipeline commercial présenté en vue Kanban avec étapes entièrement configurables (Qualification 10%, Proposition 30%, Négociation 60%, Gagnée 100%, Perdue 0%). Fonctionnalité drag and drop entre colonnes avec mise à jour automatique de la probabilité héritée de l'étape. Génération de propositions commerciales professionnelles au format PDF avec envoi direct par email au prospect. Calcul automatique du chiffre d'affaires pondéré selon la probabilité de conversion de chaque opportunité.

**Commandes :** Gestion des commandes clients et fournisseurs avec workflow complet personnalisable (brouillon, confirmée, en cours de traitement, expédiée, livrée, annulée). Impact automatique sur le stock : réservation des quantités à la confirmation de commande, déduction effective à la livraison. Génération automatique de facture depuis une commande entièrement livrée. Suivi précis des livraisons partielles ligne par ligne avec quantités livrées distinctes.

**Contrats et abonnements :** Gestion des contrats ponctuels et des abonnements récurrents avec fréquence de facturation configurable (mensuelle, trimestrielle, annuelle). Renouvellement automatique ou manuel selon le paramétrage choisi par le commercial. Génération automatique des factures récurrentes via une tâche planifiée quotidienne à six heures. Alertes proactives avant échéance des contrats pour anticipation commerciale.

### 2.3 Module RH

**Employés :** Fiches employés complètes avec numéro matricule auto-généré au format EMP-XXXX, informations personnelles détaillées, poste occupé, département de rattachement, manager direct. Organigramme visuel interactif par départements avec navigation hiérarchique. Liaison optionnelle avec un compte utilisateur pour permettre l'accès au système. Historique complet des modifications et documents attachés sécurisés.

**Congés :** Types de congés entièrement configurables avec quota annuel personnalisable (congés payés vingt-cinq jours, RTT dix jours, maladie, sans solde). Soldes calculés par employé et par année avec affichage automatique des jours restants disponibles. Workflow d'approbation structuré avec notification automatique au manager concerné. Déduction automatique du solde immédiatement après approbation de la demande. Calendrier des absences de l'équipe avec code couleur distinctif par type de congé. Détection automatique des chevauchements entre demandes concurrentes.

**Notes de frais :** Catégories de dépenses avec plafonds configurables et justificatif obligatoire selon la catégorie sélectionnée. Création de notes de frais par période avec upload sécurisé des justificatifs scannés. Workflow complet de validation : brouillon, soumise, approuvée, rejetée, payée. Suivi détaillé du remboursement et exports comptables standardisés.

**Feuilles de temps :** Saisie hebdomadaire ou quotidienne avec liaison obligatoire aux projets et optionnelle aux tâches spécifiques. Distinction claire entre heures facturables et heures non facturables pour chaque entrée. Validation par le manager avec historique des approbations. Maximum vingt-quatre heures par jour avec interdiction de saisie dans le futur. Calcul automatique de l'impact financier sur la rentabilité des projets concernés.

**Recrutement :** Création de fiches de poste détaillées avec description complète et prérequis attendus. Publication et dépublication des offres selon les besoins. Réception des candidatures via formulaire public accessible sans authentification. Workflow de suivi présenté en Kanban : nouvelle, en revue, entretien planifié, offre émise, embauchée, rejetée. Conversion automatique en fiche employé complète lors de la finalisation de l'embauche.

### 2.4 Module Stock

**Produits et services :** Catalogue unifié avec distinction claire entre produits physiques et services immatériels. Catégorisation hiérarchique à plusieurs niveaux. Prix d'achat et prix de vente avec calcul automatique de la marge brute en euros et en pourcentage. Taux de TVA configurable individuellement par produit. Suivi de stock optionnel activable avec seuil d'alerte minimum paramétrable.

**Gestion des stocks :** Support multi-entrepôts avec niveaux de stock distincts par emplacement géographique. Traçabilité complète de tous les mouvements : entrée de marchandise, sortie pour livraison, transfert inter-entrepôts, ajustement suite à inventaire. Stock disponible calculé dynamiquement : quantité totale moins réservations en cours. Alertes automatiques envoyées lorsque le stock passe sous le seuil minimum défini.

**Achats et approvisionnement :** Commandes fournisseurs avec workflow dédié (brouillon, envoyée au fournisseur, réception partielle en cours, entièrement réceptionnée). Réception partielle supportée avec mise à jour du stock ligne par ligne. Suggestions intelligentes de réapprovisionnement basées sur les alertes de stock actives. Possibilité de génération automatique de commandes fournisseurs groupées.

### 2.5 Module Finance

**Facturation :** Gestion des factures clients, factures fournisseurs et avoirs correctifs. Numérotation séquentielle annuelle strictement sans rupture au format FAC-YYYY-XXXXX. Génération possible depuis les commandes livrées ou les contrats actifs. Template PDF entièrement personnalisable incluant toutes les mentions légales obligatoires. Envoi direct par email avec pièce jointe PDF générée automatiquement.

**Paiements :** Enregistrement flexible des paiements partiels ou totaux sur chaque facture. Méthodes de paiement multiples supportées : virement bancaire, chèque, espèces, carte bancaire, autre. Mise à jour automatique du statut de la facture selon le montant total payé versus le montant dû. Historique complet des paiements par client avec rapprochement bancaire automatique.

**Relances automatiques :** Trois niveaux de relance entièrement configurables avec délais personnalisables en jours après la date d'échéance. Templates email distincts et personnalisables pour chaque niveau de relance : rappel courtois initial, rappel ferme intermédiaire, mise en demeure finale. Historique détaillé des relances envoyées conservé par facture. Tâches planifiées quotidiennes pour vérification des échéances et envoi automatique des relances dues.

### 2.6 Module Productivité

**Projets :** Création de projets avec liaison optionnelle à un client et équipe assignée via table pivot. Budget prévisionnel et dates de début et fin planifiées. Types de facturation supportés : forfait fixe, régie horaire, non facturable. Taux horaire configurable au niveau du projet avec surcharge possible par membre individuel. Calcul automatique de la rentabilité : revenus générés moins coûts calculés (heures passées multipliées par taux horaire applicable).

**Tâches :** Vue Kanban par projet avec colonnes personnalisées : à faire, en cours, en revue, terminée. Support des sous-tâches via relation hiérarchique parent-enfant. Attribution à un responsable, priorité graduée (basse, moyenne, haute, urgente), dates d'échéance. Estimation prévisionnelle en heures et comparaison visuelle avec le temps réel passé. Fonctionnalité drag and drop pour réorganisation libre des tâches entre colonnes et au sein des colonnes.

**Suivi du temps :** Timer intégré avec boutons démarrer, pause et arrêter ou alternative de saisie manuelle directe. Liaison obligatoire à un projet existant et optionnelle à une tâche spécifique du projet. Rapports de temps détaillés filtrables par période, par projet ou par employé. Export formaté pour facturation client en régie.

### 2.7 Module Agenda

Calendrier interactif avec vues mois, semaine et jour implémentées via FullCalendar.js. Types d'événements distincts : réunion, appel téléphonique, tâche, rappel, autre. Liaisons optionnelles vers les contacts, les projets ou les factures concernés pour navigation contextuelle. Support de la récurrence au format RRULE standard iCal. Invitations aux participants internes avec gestion du statut de réponse (en attente, accepté, décliné). Rappels configurables par email ou notification in-app avec délai personnalisable en minutes, heures ou jours avant l'événement.

## 3. Base de données

**Core :** users, companies, settings avec paramètres système
**CRM :** contacts, opportunity_stages, opportunities, proposals, proposal_lines, orders, order_lines, contracts, contract_lines
**HR :** departments, employees, leave_types, leave_balances, leave_requests, expense_categories, expense_reports, expense_lines, timesheets, job_positions, job_applications
**Inventory :** product_categories, products, warehouses, stock_levels, stock_movements, purchase_orders, purchase_order_lines
**Finance :** invoices, invoice_lines, payments, payment_reminders
**Productivity :** projects, project_members, tasks, time_entries
**Agenda :** events, event_attendees, event_reminders

## 4. Tâches planifiées

Les commandes Artisan suivantes sont planifiées : contracts:generate-invoices quotidiennement à six heures pour les factures d'abonnements, invoices:check-overdue quotidiennement à huit heures pour les statuts en retard, invoices:send-reminders quotidiennement à neuf heures pour les relances, events:send-reminders toutes les quinze minutes pour les rappels, leaves:reset-balances le premier janvier pour les soldes de congés, stock:check-alerts quotidiennement à sept heures pour les alertes de stock.

## 5. Planning

Phase 1 quatre semaines : architecture, auth, contacts, produits. Phase 2 quatre semaines : opportunités, propositions, commandes, contrats. Phase 3 trois semaines : factures, paiements, relances. Phase 4 trois semaines : stocks, achats. Phase 5 quatre semaines : employés, congés, frais, temps, recrutement. Phase 6 trois semaines : projets, tâches, agenda. Phase 7 deux semaines : dashboard, recherche, tests. Total : 23 semaines de dév.