---
description: 
---

# PHASE 4 : Stock & Achats

Exécutez les prompts 4.1 à 4.3 dans l'ordre.

---

## Prompt 4.1 - Gestion des stocks

Dans app/Modules/Inventory/, crée la gestion des stocks :

**warehouses** :
- id, name, address (text nullable), is_default (boolean default false)
- timestamps
- Seeder : un entrepôt "Principal" par défaut

**stock_levels** :
- id, product_id (FK), warehouse_id (FK)
- quantity (decimal 12,3 default 0)
- reserved_quantity (decimal 12,3 default 0)
- unique [product_id, warehouse_id]
- timestamps

**stock_movements** :
- id, product_id (FK), warehouse_id (FK)
- type (enum: in, out, transfer, adjustment)
- quantity (decimal 12,3) - positif pour in, négatif pour out
- reference_type (string nullable) - Order, PurchaseOrder, etc.
- reference_id (integer nullable)
- from_warehouse_id (FK nullable) - pour transfers
- date, notes (text nullable)
- created_by (FK users)
- timestamps

**StockService.php** :
- getAvailableStock(product, warehouse?) : quantity - reserved_quantity
- reserve(product, warehouse, quantity, reference) : incrémente reserved_quantity
- release(product, warehouse, quantity) : décrémente reserved_quantity
- addStock(product, warehouse, quantity, reference, notes) : crée mouvement in, incrémente quantity
- removeStock(product, warehouse, quantity, reference, notes) : crée mouvement out, décrémente quantity
- transfer(product, fromWarehouse, toWarehouse, quantity) : crée mouvement transfer
- adjust(product, warehouse, newQuantity, notes) : ajustement inventaire

Validation : stock ne peut pas être négatif (sauf setting allow_negative_stock = false par défaut)

---

## Prompt 4.2 - Interface Stock

Crée les composants Livewire pour le stock :

**StockDashboard.php** :
- Vue d'ensemble : stats globales (valeur stock, produits en alerte, mouvements du jour)
- Tableau produits avec colonnes : reference, name, stock total, stock dispo, seuil alerte, status
- Filtre : warehouse_id, catégorie, produits en alerte uniquement
- Badge rouge si quantity <= min_stock_alert
- Lien vers détail produit

**StockMovementForm.php** :
- Modal création mouvement manuel
- Type : entrée, sortie, transfert, ajustement
- Champs : product_id (autocomplete), warehouse_id, quantity, notes
- Pour transfert : from_warehouse_id, to_warehouse_id
- Pour ajustement : affiche stock actuel, saisie nouveau stock

**StockHistory.php** :
- Historique des mouvements
- Filtres : product_id, warehouse_id, type, période
- Colonnes : date, produit, entrepôt, type (badge), quantité (+/-), référence, créé par

**Intégration avec commandes** :
- Order confirmed → StockService::reserve()
- Order delivered → StockService::removeStock() + release()
- Order cancelled → StockService::release()

**Alertes stock** :
- Commande Artisan stock:check-alerts : identifie produits sous seuil, envoie notification/email
- Planifiée daily at 7:00

---

## Prompt 4.3 - Commandes fournisseurs

Crée le système de commandes fournisseurs (achats) :

**purchase_orders** :
- id, supplier_id (FK contacts où type=fournisseur)
- reference (unique, auto: ACH-YYYYMM-XXXX)
- date, expected_date (date nullable)
- status (enum: draft, sent, partial, received, cancelled)
- total_ht, total_tva, total_ttc
- notes (text nullable)
- timestamps, soft_deletes

**purchase_order_lines** :
- id, purchase_order_id (FK), product_id (FK)
- description, quantity, unit_price, tax_rate
- received_qty (decimal 10,3 default 0)
- timestamps

**PurchaseOrderService.php** :
- calculateTotals(PurchaseOrder)
- send(PurchaseOrder) : status=sent, envoie email fournisseur avec PDF
- receivePartial(PurchaseOrder, array $quantities) : met à jour received_qty, crée mouvements stock in
- receiveAll(PurchaseOrder) : reçoit tout, status=received
- cancel(PurchaseOrder)
- createFromStockAlerts() : génère commandes d'achat suggérées depuis produits en alerte

**Composants Livewire** :
- PurchaseOrdersList : filtres status/supplier/période
- PurchaseOrderForm : formulaire avec lignes, autocomplete fournisseur et produits
- PurchaseOrderShow : détail, section réception partielle avec inputs quantités, workflow actions
- ReorderSuggestions : liste produits à recommander avec quantités suggérées, bouton "Créer commande"

Template PDF resources/views/pdf/purchase_order.blade.php