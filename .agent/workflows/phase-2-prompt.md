---
description: 
---

# PHASE 2 : CRM Complet

Exécutez les prompts 2.1 à 2.5 dans l'ordre.

---

## Prompt 2.1 - Opportunités : Modèles

Dans app/Modules/CRM/, crée les modèles pour les opportunités :

**opportunity_stages** :
- id, name, order (integer), probability (integer 0-100), color (string), timestamps
- Seeder avec : Qualification (10%, gray), Proposition (30%, blue), Négociation (60%, yellow), Gagnée (100%, green), Perdue (0%, red)

**opportunities** :
- id, contact_id (FK), title, description (text nullable)
- amount (decimal 12,2), probability (integer)
- stage_id (FK opportunity_stages)
- expected_close_date (date nullable)
- assigned_to (FK users nullable)
- won_at (timestamp nullable), lost_at (timestamp nullable)
- lost_reason (string nullable)
- timestamps, soft_deletes

**Relations** :
- belongsTo Contact, Stage, User (assigned_to)
- hasMany Proposal
- hasOne Order (généré si gagnée)

**Accessors** :
- weighted_amount : amount * (probability / 100)
- status : 'open' | 'won' | 'lost' (basé sur won_at/lost_at)

**Scopes** :
- scopeOpen(), scopeWon(), scopeLost()
- scopeByStage($stageId)

---

## Prompt 2.2 - Opportunités : Pipeline Kanban

Crée le composant Livewire OpportunityPipeline dans app/Modules/CRM/Livewire/ :

**OpportunityPipeline.php** :
- Vue Kanban avec colonnes = stages (triées par order)
- Cartes opportunités dans chaque colonne avec : title, contact.display_name, amount formaté, expected_close_date, avatar assigned_to
- Drag & drop entre colonnes (Alpine.js + Sortable.js via CDN)
- Au drop : mise à jour stage_id et probability (hérite du stage)
- Si drop sur "Gagnée" : modal pour confirmer et option créer commande
- Si drop sur "Perdue" : modal pour saisir lost_reason
- Filtres en haut : assigned_to, période expected_close_date
- Stats en haut : nombre total, montant total, montant pondéré

**OpportunityForm.php** :
- Modal ou page création/édition
- Champs : contact_id (searchable select), title, description, amount, stage_id, expected_close_date, assigned_to
- probability auto-rempli selon stage mais modifiable

**OpportunityShow.php** :
- Détail complet
- Timeline des changements de stage
- Propositions liées
- Boutons : Modifier, Marquer gagnée, Marquer perdue

Route GET /crm/opportunities → OpportunityPipeline

---

## Prompt 2.3 - Propositions commerciales

Crée le système de propositions commerciales :

**proposals** :
- id, opportunity_id (FK nullable), contact_id (FK)
- reference (string unique, auto: PROP-YYYYMM-XXXX)
- date, validity_date
- status (enum: draft, sent, accepted, refused)
- total_ht, total_tva, total_ttc (decimals 12,2)
- notes (text nullable), conditions (text nullable)
- pdf_path (string nullable)
- sent_at (timestamp nullable)
- timestamps, soft_deletes

**proposal_lines** :
- id, proposal_id (FK), product_id (FK nullable)
- description, quantity (decimal 10,3), unit_price (decimal 10,2)
- discount (decimal 5,2 default 0), tax_rate (decimal 5,2)
- total (decimal 12,2)
- order (integer)
- timestamps

**ProposalService.php** :
- calculateTotals(Proposal) : recalcule total_ht, tva, ttc depuis les lignes
- generatePDF(Proposal) : génère PDF avec DomPDF, stocke dans storage/proposals/
- send(Proposal, $email) : envoie par mail avec PDF attaché, met status=sent

**Composant ProposalBuilder.php** :
- Formulaire en-tête : contact_id, date, validity_date, notes, conditions
- Tableau lignes éditable inline : product_id (autocomplete), description, qté, prix, remise, TVA
- Ajout ligne vide, suppression ligne, réordonnancement drag
- Calculs en temps réel (ligne total, sous-totaux, TVA, TTC)
- Actions : Enregistrer brouillon, Prévisualiser PDF, Envoyer

Template PDF resources/views/pdf/proposal.blade.php : professionnel, logo entreprise, coordonnées, tableau lignes, totaux, conditions.

---

## Prompt 2.4 - Commandes clients

Crée le système de commandes dans app/Modules/CRM/ :

**orders** :
- id, type (enum: client, fournisseur)
- contact_id (FK), reference (unique, auto: CMD-YYYYMM-XXXX pour client, ACH-YYYYMM-XXXX pour fournisseur)
- date, status (enum: draft, confirmed, processing, shipped, delivered, cancelled)
- total_ht, total_tva, total_ttc (decimals 12,2)
- shipping_address (text nullable), notes (text nullable)
- proposal_id (FK nullable), opportunity_id (FK nullable)
- timestamps, soft_deletes

**order_lines** :
- id, order_id (FK), product_id (FK nullable)
- description, quantity, unit_price, discount, tax_rate, total
- delivered_qty (decimal 10,3 default 0)
- order (integer)
- timestamps

**OrderService.php** :
- calculateTotals(Order)
- confirm(Order) : passe status=confirmed, réserve stock si type=client
- ship(Order) : passe status=shipped
- deliver(Order, array $quantities) : met à jour delivered_qty, déduit stock, passe status=delivered si tout livré
- cancel(Order) : annule, libère stock réservé
- createFromProposal(Proposal) : crée commande depuis proposition acceptée
- generateInvoice(Order) : crée facture depuis commande livrée

**Composants Livewire** :
- OrdersList : filtres type/status/contact, pagination
- OrderForm : similaire à ProposalBuilder, sélection depuis proposition existante possible
- OrderShow : détail, workflow actions selon status, lignes avec colonnes qté commandée/livrée

Workflow visuel : draft → confirmed → processing → shipped → delivered (badges couleur)

---

## Prompt 2.5 - Contrats et abonnements

Crée le système de contrats dans app/Modules/CRM/ :

**contracts** :
- id, contact_id (FK), reference (unique, auto: CTR-YYYYMM-XXXX)
- type (enum: contract, subscription)
- start_date, end_date (nullable pour subscription sans fin)
- renewal_type (enum: none, auto, manual)
- billing_frequency (enum: monthly, quarterly, yearly) - pour subscriptions
- amount (decimal 12,2), status (enum: draft, active, suspended, terminated)
- next_billing_date (date nullable)
- notes (text nullable)
- timestamps, soft_deletes

**contract_lines** :
- id, contract_id (FK), product_id (FK nullable)
- description, quantity, unit_price
- timestamps

**ContractService.php** :
- activate(Contract) : passe status=active, calcule next_billing_date
- suspend(Contract) : passe status=suspended
- terminate(Contract) : passe status=terminated, set end_date
- generateRecurringInvoice(Contract) : génère facture, avance next_billing_date selon frequency
- getContractsDueForBilling(date) : retourne contrats avec next_billing_date <= date

**Commande Artisan** :
- php artisan contracts:generate-invoices : appelle generateRecurringInvoice pour chaque contrat dû
- Planifiée quotidiennement à 6h dans Console/Kernel.php

**Composants Livewire** :
- ContractsList : filtres type/status, alertes contrats arrivant à échéance (30 jours)
- ContractForm : formulaire complet avec lignes
- ContractShow : détail, historique factures générées, actions workflow