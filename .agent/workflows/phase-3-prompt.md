---
description: 
---

# PHASE 3 : Finance

Exécutez les prompts 3.1 à 3.3 dans l'ordre.

---

## Prompt 3.1 - Factures : Modèles

Dans app/Modules/Finance/, crée le système de facturation :

**invoices** :
- id, type (enum: client, fournisseur, avoir)
- contact_id (FK), order_id (FK nullable), contract_id (FK nullable)
- reference (unique, auto: FAC-YYYY-XXXXX numérotation séquentielle annuelle)
- date, due_date
- status (enum: draft, sent, partial, paid, overdue, cancelled)
- total_ht, total_tva, total_ttc (decimals 12,2)
- paid_amount (decimal 12,2 default 0)
- notes (text nullable)
- pdf_path (string nullable)
- sent_at (timestamp nullable)
- timestamps, soft_deletes

**invoice_lines** :
- id, invoice_id (FK), product_id (FK nullable)
- description, quantity, unit_price, discount, tax_rate, total
- order (integer)
- timestamps

**payments** :
- id, invoice_id (FK), date, amount (decimal 12,2)
- method (enum: bank_transfer, check, cash, card, other)
- reference (string nullable), notes (text nullable)
- timestamps

**payment_reminders** :
- id, invoice_id (FK)
- type (enum: first, second, final)
- sent_at (timestamp)
- timestamps

**InvoiceService.php** :
- generateReference(year) : FAC-YYYY-XXXXX séquentiel sans rupture
- calculateTotals(Invoice)
- generatePDF(Invoice)
- send(Invoice, $email) : envoie mail avec PDF, status=sent
- recordPayment(Invoice, amount, method, reference) : crée Payment, met à jour paid_amount et status
- checkOverdue() : passe en overdue les factures sent avec due_date < today
- createAvoir(Invoice) : crée avoir (type=avoir, montants négatifs)

Règle : une facture envoyée ne peut pas être supprimée, seulement annulée via avoir.

---

## Prompt 3.2 - Factures : Composants Livewire

Crée les composants Livewire pour la facturation :

**InvoicesList.php** :
- Filtres : type, status, contact_id, période (date range)
- Colonnes : reference, contact, date, échéance, total TTC, payé, reste dû, status (badge couleur)
- Indicateur visuel factures en retard (rouge)
- Stats en haut : total facturé, total payé, total en attente, total en retard
- Actions : voir, modifier (si draft), envoyer, enregistrer paiement, créer avoir

**InvoiceForm.php** :
- Création depuis zéro ou depuis commande/contrat
- Formulaire en-tête + lignes (même pattern que ProposalBuilder)
- Calcul automatique due_date selon paramètre (setting: default_payment_days = 30)
- Validation : reference unique

**InvoiceShow.php** :
- Détail complet avec lignes
- Section paiements : liste des paiements reçus, formulaire ajout paiement inline
- Historique relances envoyées
- Actions selon status : Envoyer, Télécharger PDF, Enregistrer paiement, Créer avoir
- Progression visuelle : barre de progression paid_amount / total_ttc

**InvoicePreview.php** :
- Affichage du PDF inline (iframe ou viewer)
- Boutons : Télécharger, Envoyer par email

Template PDF resources/views/pdf/invoice.blade.php : mentions légales obligatoires, numéro TVA, coordonnées bancaires (depuis settings).

---

## Prompt 3.3 - Relances automatiques

Crée le système de relances automatiques :

**Settings à ajouter** :
- reminder_first_days (default: 7) : jours après échéance pour 1ère relance
- reminder_second_days (default: 15)
- reminder_final_days (default: 30)
- reminder_first_template, reminder_second_template, reminder_final_template : templates email (text)

**ReminderService.php** :
- getInvoicesForReminder(type) : factures overdue sans relance de ce type, selon délai configuré
- sendReminder(Invoice, type) : envoie email selon template, crée PaymentReminder
- processAllReminders() : traite toutes les relances dues

**Templates email** resources/views/emails/reminders/ :
- first.blade.php : rappel courtois
- second.blade.php : rappel ferme
- final.blade.php : mise en demeure

**Commandes Artisan** :
- php artisan invoices:check-overdue : met à jour status des factures en retard
- php artisan invoices:send-reminders : envoie les relances dues

Planification dans Kernel.php :
- invoices:check-overdue → daily at 8:00
- invoices:send-reminders → daily at 9:00

**Composant RemindersList.php** :
- Historique de toutes les relances envoyées
- Filtres par période, contact