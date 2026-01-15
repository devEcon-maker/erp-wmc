<?php

namespace App\Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\ProductCategory;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer les catégories
        $categories = [
            'geolocalisation' => ProductCategory::where('name', 'Géolocalisation')->first()?->id,
            'telecom' => ProductCategory::where('name', 'Télécommunication et Solutions Digitales')->first()?->id,
            'securite' => ProductCategory::where('name', 'Sécurité Digitale')->first()?->id,
            'logiciels' => ProductCategory::where('name', 'Applications & Logiciels')->first()?->id,
            'multimedia' => ProductCategory::where('name', 'Multimédia')->first()?->id,
            'bureau' => ProductCategory::where('name', 'Matériels de Bureau')->first()?->id,
            'autres' => ProductCategory::where('name', 'Autres Produits')->first()?->id,
        ];

        $products = [
            // ========== GÉOLOCALISATION ==========
            ['name' => 'Balise GPS', 'reference' => 'GPS-001', 'selling_price' => 150000, 'category_id' => $categories['geolocalisation'], 'type' => 'product', 'description' => 'Balise GPS de tracking véhicule', 'unit' => 'PCS'],
            ['name' => 'Carte RFID', 'reference' => 'GPS-002', 'selling_price' => 100000, 'category_id' => $categories['geolocalisation'], 'type' => 'product', 'description' => 'Carte RFID pour identification', 'unit' => 'PCS'],
            ['name' => 'Clé d\'identification chauffeur-Dallas', 'reference' => 'GPS-003', 'selling_price' => 100000, 'category_id' => $categories['geolocalisation'], 'type' => 'product', 'description' => 'Clé Dallas pour identification chauffeur', 'unit' => 'PCS'],
            ['name' => 'Jauge de carburant sans fil 1m', 'reference' => 'GPS-004', 'selling_price' => 200000, 'category_id' => $categories['geolocalisation'], 'type' => 'product', 'description' => 'Jauge de carburant capacitive 1 mètre', 'unit' => 'PCS'],
            ['name' => 'Jauge de carburant sans fil 3m', 'reference' => 'GPS-005', 'selling_price' => 600000, 'category_id' => $categories['geolocalisation'], 'type' => 'product', 'description' => 'Jauge de carburant capacitive 3 mètres', 'unit' => 'PCS'],
            ['name' => 'Bouton SOS', 'reference' => 'GPS-006', 'selling_price' => 200000, 'category_id' => $categories['geolocalisation'], 'type' => 'product', 'description' => 'Bouton d\'urgence SOS', 'unit' => 'PCS'],
            ['name' => 'Lecteur d\'identification chauffeur-RFID', 'reference' => 'GPS-007', 'selling_price' => 100000, 'category_id' => $categories['geolocalisation'], 'type' => 'product', 'description' => 'Lecteur RFID pour identification chauffeur', 'unit' => 'PCS'],
            ['name' => 'Lecteur d\'identification chauffeur-Dallas', 'reference' => 'GPS-008', 'selling_price' => 100000, 'category_id' => $categories['geolocalisation'], 'type' => 'product', 'description' => 'Lecteur Dallas pour identification chauffeur', 'unit' => 'PCS'],
            ['name' => 'Module CAN-BUS LVCAN', 'reference' => 'GPS-009', 'selling_price' => 200000, 'category_id' => $categories['geolocalisation'], 'type' => 'product', 'description' => 'Module CAN-BUS pour lecture données véhicule', 'unit' => 'PCS'],

            // Services Géolocalisation
            ['name' => 'Frais d\'installation balise GPS', 'reference' => 'SRV-GPS-001', 'selling_price' => 25000, 'category_id' => $categories['geolocalisation'], 'type' => 'service', 'description' => 'Installation et configuration de balise GPS', 'unit' => 'ENS', 'track_stock' => false],
            ['name' => 'Frais d\'installation et étalonnage jauge', 'reference' => 'SRV-GPS-002', 'selling_price' => 65000, 'category_id' => $categories['geolocalisation'], 'type' => 'service', 'description' => 'Installation et étalonnage jauge de carburant', 'unit' => 'ENS', 'track_stock' => false],
            ['name' => 'Abonnement récurrent mensuel GPS', 'reference' => 'SRV-GPS-003', 'selling_price' => 5000, 'category_id' => $categories['geolocalisation'], 'type' => 'service', 'description' => 'Abonnement mensuel plateforme de géolocalisation', 'unit' => 'MOIS', 'track_stock' => false],
            ['name' => 'Abonnement récurrent annuel GPS', 'reference' => 'SRV-GPS-004', 'selling_price' => 20000, 'category_id' => $categories['geolocalisation'], 'type' => 'service', 'description' => 'Abonnement annuel plateforme de géolocalisation', 'unit' => 'AN', 'track_stock' => false],
            ['name' => 'Maintenance préventive GPS', 'reference' => 'SRV-GPS-005', 'selling_price' => 15000, 'category_id' => $categories['geolocalisation'], 'type' => 'service', 'description' => 'Maintenance préventive équipements GPS', 'unit' => 'ENS', 'track_stock' => false],

            // ========== TÉLÉCOMMUNICATION ==========
            ['name' => 'Point d\'accès Ubiquiti Unify intérieur', 'reference' => 'TEL-001', 'selling_price' => 300000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Point d\'accès WiFi Ubiquiti indoor', 'unit' => 'PCS'],
            ['name' => 'Point d\'accès Ubiquiti extérieur', 'reference' => 'TEL-002', 'selling_price' => 320000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Point d\'accès WiFi Ubiquiti outdoor', 'unit' => 'PCS'],
            ['name' => 'Switch 24 ports GB PoE', 'reference' => 'TEL-003', 'selling_price' => 400000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Switch 24 ports Gigabit PoE', 'unit' => 'PCS'],
            ['name' => 'Switch 16 ports cat 6', 'reference' => 'TEL-004', 'selling_price' => 210000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Switch 16 ports catégorie 6', 'unit' => 'PCS'],
            ['name' => 'IP7WW-000U-C1', 'reference' => 'TEL-005', 'selling_price' => 125000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Module NEC téléphonie IP', 'unit' => 'PCS'],
            ['name' => 'IP7WW-3COIDB-C1', 'reference' => 'TEL-006', 'selling_price' => 120000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Carte CO NEC SL2100', 'unit' => 'PCS'],
            ['name' => 'SL2100 IP CHANEL-16 LIC', 'reference' => 'TEL-007', 'selling_price' => 129000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Licence 16 canaux IP NEC SL2100', 'unit' => 'PCS'],
            ['name' => 'ITX-7PUC-TEL (UT880) Directeur', 'reference' => 'TEL-008', 'selling_price' => 300000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Téléphone IP NEC UT880 Directeur', 'unit' => 'PCS'],
            ['name' => 'GT210-ITX-1615-1W(BK)TEL', 'reference' => 'TEL-009', 'selling_price' => 90000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Téléphone IP NEC standard', 'unit' => 'PCS'],
            ['name' => 'IP7WW-8IPLD-C1 TEL(BK)', 'reference' => 'TEL-010', 'selling_price' => 225000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Téléphone IP NEC 8 boutons', 'unit' => 'PCS'],
            ['name' => 'AT-50P(BK) TEL', 'reference' => 'TEL-011', 'selling_price' => 35000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Téléphone analogique NEC', 'unit' => 'PCS'],
            ['name' => 'Logicom VEGA 150', 'reference' => 'TEL-012', 'selling_price' => 45000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Téléphone sans fil Logicom', 'unit' => 'PCS'],
            ['name' => 'Pack kit Yealink', 'reference' => 'TEL-013', 'selling_price' => 8500000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Kit complet visioconférence Yealink', 'unit' => 'ENS'],
            ['name' => 'SL2100 NEC SIP License', 'reference' => 'TEL-014', 'selling_price' => 75000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Licence SIP NEC SL2100', 'unit' => 'PCS'],
            ['name' => 'IP7WW-VOIPDB-C1', 'reference' => 'TEL-015', 'selling_price' => 525000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Carte VOIP NEC SL2100', 'unit' => 'PCS'],
            ['name' => 'IP7WW-4KSU-C1 w/o C', 'reference' => 'TEL-016', 'selling_price' => 250000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'Unité principale NEC SL2100', 'unit' => 'PCS'],
            ['name' => 'IP7EU-CPU-C1-A', 'reference' => 'TEL-017', 'selling_price' => 460000, 'category_id' => $categories['telecom'], 'type' => 'product', 'description' => 'CPU NEC SL2100', 'unit' => 'PCS'],

            // Services Télécom
            ['name' => 'Installation réseau informatique', 'reference' => 'SRV-TEL-001', 'selling_price' => 150000, 'category_id' => $categories['telecom'], 'type' => 'service', 'description' => 'Installation et configuration réseau', 'unit' => 'ENS', 'track_stock' => false],
            ['name' => 'Configuration téléphonie IP', 'reference' => 'SRV-TEL-002', 'selling_price' => 100000, 'category_id' => $categories['telecom'], 'type' => 'service', 'description' => 'Configuration système téléphonie IP', 'unit' => 'ENS', 'track_stock' => false],
            ['name' => 'Maintenance réseau', 'reference' => 'SRV-TEL-003', 'selling_price' => 75000, 'category_id' => $categories['telecom'], 'type' => 'service', 'description' => 'Maintenance préventive réseau', 'unit' => 'ENS', 'track_stock' => false],

            // ========== SÉCURITÉ DIGITALE ==========
            ['name' => 'Sirène intérieure', 'reference' => 'SEC-001', 'selling_price' => 60750, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Sirène d\'alarme intérieure', 'unit' => 'PCS'],
            ['name' => 'Sirène extérieure + Batterie', 'reference' => 'SEC-002', 'selling_price' => 101250, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Sirène d\'alarme extérieure avec batterie', 'unit' => 'PCS'],
            ['name' => 'Sirène', 'reference' => 'SEC-003', 'selling_price' => 45000, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Sirène d\'alarme standard', 'unit' => 'PCS'],
            ['name' => 'Indicateur d\'action lumineux', 'reference' => 'SEC-004', 'selling_price' => 67500, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Flash lumineux d\'alarme', 'unit' => 'PCS'],
            ['name' => 'Détecteur de chaleur', 'reference' => 'SEC-005', 'selling_price' => 33750, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Détecteur thermique incendie', 'unit' => 'PCS'],
            ['name' => 'Détecteur de fumée', 'reference' => 'SEC-006', 'selling_price' => 47250, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Détecteur de fumée optique', 'unit' => 'PCS'],
            ['name' => 'ZKBioHLMS', 'reference' => 'SEC-007', 'selling_price' => 28500, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Logiciel de gestion ZKTeco', 'unit' => 'PCS'],
            ['name' => 'Centrale WIZARD', 'reference' => 'SEC-008', 'selling_price' => 300000, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Centrale d\'alarme WIZARD', 'unit' => 'PCS'],
            ['name' => 'Centrale incendie conventionnel', 'reference' => 'SEC-009', 'selling_price' => 735750, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Centrale de détection incendie conventionnelle', 'unit' => 'PCS'],
            ['name' => 'Buzzer', 'reference' => 'SEC-010', 'selling_price' => 60000, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Buzzer d\'alarme', 'unit' => 'PCS'],
            ['name' => 'Relais Anti-démarrage 24V', 'reference' => 'SEC-011', 'selling_price' => 100000, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Relais anti-démarrage 24V', 'unit' => 'PCS'],
            ['name' => 'Relais Anti-démarrage 12V', 'reference' => 'SEC-012', 'selling_price' => 80000, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Relais anti-démarrage 12V', 'unit' => 'PCS'],
            ['name' => 'MDVR avec 2 Caméras + GPS', 'reference' => 'SEC-013', 'selling_price' => 450000, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Enregistreur vidéo mobile avec 2 caméras', 'unit' => 'ENS'],
            ['name' => 'MDVR avec 4 Caméras + GPS', 'reference' => 'SEC-014', 'selling_price' => 550000, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Enregistreur vidéo mobile avec 4 caméras', 'unit' => 'ENS'],
            ['name' => 'Antivirus Norton', 'reference' => 'SEC-015', 'selling_price' => 65000, 'category_id' => $categories['securite'], 'type' => 'product', 'description' => 'Licence antivirus Norton 1 an', 'unit' => 'PCS'],

            // Services Sécurité
            ['name' => 'Installation système d\'alarme', 'reference' => 'SRV-SEC-001', 'selling_price' => 150000, 'category_id' => $categories['securite'], 'type' => 'service', 'description' => 'Installation complète système d\'alarme', 'unit' => 'ENS', 'track_stock' => false],
            ['name' => 'Installation vidéosurveillance', 'reference' => 'SRV-SEC-002', 'selling_price' => 200000, 'category_id' => $categories['securite'], 'type' => 'service', 'description' => 'Installation système de vidéosurveillance', 'unit' => 'ENS', 'track_stock' => false],
            ['name' => 'Maintenance système sécurité', 'reference' => 'SRV-SEC-003', 'selling_price' => 50000, 'category_id' => $categories['securite'], 'type' => 'service', 'description' => 'Maintenance préventive sécurité', 'unit' => 'ENS', 'track_stock' => false],

            // ========== APPLICATIONS & LOGICIELS ==========
            ['name' => 'Pack Windows 11', 'reference' => 'LOG-001', 'selling_price' => 120000, 'category_id' => $categories['logiciels'], 'type' => 'product', 'description' => 'Licence Windows 11 Pro', 'unit' => 'PCS'],
            ['name' => 'Pack Microsoft Office 365', 'reference' => 'LOG-002', 'selling_price' => 85000, 'category_id' => $categories['logiciels'], 'type' => 'product', 'description' => 'Licence Microsoft Office 365 1 an', 'unit' => 'PCS'],
            ['name' => 'Licence ERP WMC', 'reference' => 'LOG-003', 'selling_price' => 500000, 'category_id' => $categories['logiciels'], 'type' => 'product', 'description' => 'Licence annuelle ERP WMC', 'unit' => 'AN'],
            ['name' => 'Plateforme de géolocalisation', 'reference' => 'LOG-004', 'selling_price' => 300000, 'category_id' => $categories['logiciels'], 'type' => 'product', 'description' => 'Accès plateforme web de géolocalisation', 'unit' => 'AN'],

            // Services Logiciels
            ['name' => 'Développement application sur mesure', 'reference' => 'SRV-LOG-001', 'selling_price' => 1500000, 'category_id' => $categories['logiciels'], 'type' => 'service', 'description' => 'Développement application personnalisée', 'unit' => 'ENS', 'track_stock' => false],
            ['name' => 'Formation logiciel', 'reference' => 'SRV-LOG-002', 'selling_price' => 100000, 'category_id' => $categories['logiciels'], 'type' => 'service', 'description' => 'Formation utilisateur logiciel', 'unit' => 'JOUR', 'track_stock' => false],
            ['name' => 'Support technique annuel', 'reference' => 'SRV-LOG-003', 'selling_price' => 250000, 'category_id' => $categories['logiciels'], 'type' => 'service', 'description' => 'Contrat support technique annuel', 'unit' => 'AN', 'track_stock' => false],

            // ========== MULTIMÉDIA ==========
            ['name' => 'Amplificateur de son', 'reference' => 'MED-001', 'selling_price' => 235000, 'category_id' => $categories['multimedia'], 'type' => 'product', 'description' => 'Amplificateur audio professionnel', 'unit' => 'PCS'],
            ['name' => 'Écran tactile Dahua 65"', 'reference' => 'MED-002', 'selling_price' => 3000000, 'category_id' => $categories['multimedia'], 'type' => 'product', 'description' => 'Écran interactif Dahua 65 pouces tactile', 'unit' => 'PCS'],
            ['name' => 'Télévision Samsung 85" Ultra HD 4K', 'reference' => 'MED-003', 'selling_price' => 2500000, 'category_id' => $categories['multimedia'], 'type' => 'product', 'description' => 'TV Samsung 85 pouces 4K UHD', 'unit' => 'PCS'],
            ['name' => 'Télévision Samsung 55" Ultra HD 4K', 'reference' => 'MED-004', 'selling_price' => 750000, 'category_id' => $categories['multimedia'], 'type' => 'product', 'description' => 'TV Samsung 55 pouces 4K UHD', 'unit' => 'PCS'],
            ['name' => 'Télévision Samsung 43" Ultra 4K', 'reference' => 'MED-005', 'selling_price' => 480000, 'category_id' => $categories['multimedia'], 'type' => 'product', 'description' => 'TV Samsung 43 pouces 4K', 'unit' => 'PCS'],
            ['name' => 'Écran LED 2m3 X 4', 'reference' => 'MED-006', 'selling_price' => 19500000, 'category_id' => $categories['multimedia'], 'type' => 'product', 'description' => 'Mur LED grand format 2.3m x 4m', 'unit' => 'ENS'],

            // Services Multimédia
            ['name' => 'Installation écran et configuration', 'reference' => 'SRV-MED-001', 'selling_price' => 75000, 'category_id' => $categories['multimedia'], 'type' => 'service', 'description' => 'Installation et configuration écran', 'unit' => 'ENS', 'track_stock' => false],
            ['name' => 'Installation mur LED', 'reference' => 'SRV-MED-002', 'selling_price' => 500000, 'category_id' => $categories['multimedia'], 'type' => 'service', 'description' => 'Installation mur LED grand format', 'unit' => 'ENS', 'track_stock' => false],

            // ========== MATÉRIELS DE BUREAU ==========
            ['name' => 'Placard de rangement 1,63m x 2,10m', 'reference' => 'BUR-001', 'selling_price' => 550000, 'category_id' => $categories['bureau'], 'type' => 'product', 'description' => 'Armoire de rangement bureau', 'unit' => 'PCS'],
            ['name' => 'Bureau avec retour 2m x 80cm', 'reference' => 'BUR-002', 'selling_price' => 480000, 'category_id' => $categories['bureau'], 'type' => 'product', 'description' => 'Bureau direction avec retour', 'unit' => 'PCS'],
            ['name' => 'Comptoir de réception', 'reference' => 'BUR-003', 'selling_price' => 550000, 'category_id' => $categories['bureau'], 'type' => 'product', 'description' => 'Comptoir accueil réception', 'unit' => 'PCS'],
            ['name' => 'Table de réunion en U 25 places', 'reference' => 'BUR-004', 'selling_price' => 3500000, 'category_id' => $categories['bureau'], 'type' => 'product', 'description' => 'Table de réunion U 5m x 2,4m', 'unit' => 'ENS'],
            ['name' => 'Bureau Open Space 1,30m x 2,40m', 'reference' => 'BUR-005', 'selling_price' => 750000, 'category_id' => $categories['bureau'], 'type' => 'product', 'description' => 'Bureau open space avec passe-câble', 'unit' => 'PCS'],
            ['name' => 'Ordinateur HP Core I3', 'reference' => 'BUR-006', 'selling_price' => 350000, 'category_id' => $categories['bureau'], 'type' => 'product', 'description' => 'PC HP Core I3 12GB 500GB SSD + 1TB HDD', 'unit' => 'PCS'],
            ['name' => 'Table de réunion', 'reference' => 'BUR-007', 'selling_price' => 750000, 'category_id' => $categories['bureau'], 'type' => 'product', 'description' => 'Table de réunion standard', 'unit' => 'PCS'],
            ['name' => 'Étagère', 'reference' => 'BUR-008', 'selling_price' => 20000, 'category_id' => $categories['bureau'], 'type' => 'product', 'description' => 'Étagère de bureau', 'unit' => 'PCS'],

            // ========== AUTRES PRODUITS ==========
            ['name' => 'Batterie 12V', 'reference' => 'AUT-001', 'selling_price' => 20250, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Batterie 12V standard', 'unit' => 'PCS'],
            ['name' => 'Batterie 12V/7AH', 'reference' => 'AUT-002', 'selling_price' => 27000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Batterie 12V 7AH', 'unit' => 'PCS'],
            ['name' => 'Bandeau électrique', 'reference' => 'AUT-003', 'selling_price' => 30000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Bandeau électrique alimentation', 'unit' => 'PCS'],
            ['name' => 'Gainage', 'reference' => 'AUT-004', 'selling_price' => 750, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Gainage protection câble (mètre)', 'unit' => 'ML'],
            ['name' => 'Panneau de brassage', 'reference' => 'AUT-005', 'selling_price' => 65000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Panneau de brassage 24 ports', 'unit' => 'PCS'],
            ['name' => 'Panneau de brassage vide', 'reference' => 'AUT-006', 'selling_price' => 20000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Panneau de brassage vide', 'unit' => 'PCS'],
            ['name' => 'Bobines', 'reference' => 'AUT-007', 'selling_price' => 450, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Bobine câble', 'unit' => 'PCS'],
            ['name' => 'Guide câble', 'reference' => 'AUT-008', 'selling_price' => 9000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Guide câble vertical/horizontal', 'unit' => 'PCS'],
            ['name' => 'Câble incendie', 'reference' => 'AUT-009', 'selling_price' => 1553, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Câble incendie (mètre)', 'unit' => 'ML'],
            ['name' => 'Câble Cat7', 'reference' => 'AUT-010', 'selling_price' => 2025, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Câble réseau catégorie 7 (mètre)', 'unit' => 'ML'],
            ['name' => 'Câble Cat6', 'reference' => 'AUT-011', 'selling_price' => 650, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Câble réseau catégorie 6 (mètre)', 'unit' => 'ML'],
            ['name' => 'Câble réseau', 'reference' => 'AUT-012', 'selling_price' => 650, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Câble réseau standard (mètre)', 'unit' => 'ML'],
            ['name' => 'Câble HT', 'reference' => 'AUT-013', 'selling_price' => 1100, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Câble haute tension (mètre)', 'unit' => 'ML'],
            ['name' => 'Câble courant 3x2,5', 'reference' => 'AUT-014', 'selling_price' => 1500, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Câble électrique 3x2,5mm² (mètre)', 'unit' => 'ML'],
            ['name' => 'Câble inoxydable', 'reference' => 'AUT-015', 'selling_price' => 1450, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Câble inoxydable (mètre)', 'unit' => 'ML'],
            ['name' => 'Cordon de brassage 1m', 'reference' => 'AUT-016', 'selling_price' => 2000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Cordon de brassage RJ45 1 mètre', 'unit' => 'PCS'],
            ['name' => 'Cordon de brassage 3m', 'reference' => 'AUT-017', 'selling_price' => 5000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Cordon de brassage RJ45 3 mètres', 'unit' => 'PCS'],
            ['name' => 'Base plafonnée', 'reference' => 'AUT-018', 'selling_price' => 30000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Base de détecteur plafond', 'unit' => 'PCS'],
            ['name' => 'Base simple', 'reference' => 'AUT-019', 'selling_price' => 350000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Base détecteur simple', 'unit' => 'PCS'],
            ['name' => 'Face plate', 'reference' => 'AUT-020', 'selling_price' => 650, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Plaque murale RJ45', 'unit' => 'PCS'],
            ['name' => 'Noyau RJ45', 'reference' => 'AUT-021', 'selling_price' => 2000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Noyau RJ45 catégorie 6', 'unit' => 'PCS'],
            ['name' => 'Obturateur double', 'reference' => 'AUT-022', 'selling_price' => 650, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Obturateur double prise', 'unit' => 'PCS'],
            ['name' => 'Obturateur mono', 'reference' => 'AUT-023', 'selling_price' => 650, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Obturateur simple prise', 'unit' => 'PCS'],
            ['name' => 'Support', 'reference' => 'AUT-024', 'selling_price' => 5500, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Support de fixation', 'unit' => 'PCS'],
            ['name' => 'Ressort', 'reference' => 'AUT-025', 'selling_price' => 280, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Ressort de fixation', 'unit' => 'PCS'],
            ['name' => 'Bague', 'reference' => 'AUT-026', 'selling_price' => 1200, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Bague de fixation', 'unit' => 'PCS'],
            ['name' => 'Piquet de terre', 'reference' => 'AUT-027', 'selling_price' => 35000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Piquet de mise à la terre', 'unit' => 'PCS'],
            ['name' => 'Panneaux de signalisation', 'reference' => 'AUT-028', 'selling_price' => 8500, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Panneau de signalisation incendie', 'unit' => 'PCS'],
            ['name' => 'MF Thin Card', 'reference' => 'AUT-029', 'selling_price' => 1300, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Carte fine RFID Mifare', 'unit' => 'PCS'],
            ['name' => 'Armoire 15U', 'reference' => 'AUT-030', 'selling_price' => 230000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Armoire rack 15U', 'unit' => 'PCS'],
            ['name' => 'B.B.G', 'reference' => 'AUT-031', 'selling_price' => 33750, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Boîtier de brassage général', 'unit' => 'PCS'],
            ['name' => 'LH3600', 'reference' => 'AUT-032', 'selling_price' => 300000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Équipement LH3600', 'unit' => 'PCS'],
            ['name' => 'SLG410', 'reference' => 'AUT-033', 'selling_price' => 75000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Module SLG410', 'unit' => 'PCS'],
            ['name' => 'DIA47-H', 'reference' => 'AUT-034', 'selling_price' => 65000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Détecteur DIA47-H', 'unit' => 'PCS'],
            ['name' => 'Accessoire pour installation', 'reference' => 'AUT-035', 'selling_price' => 100000, 'category_id' => $categories['autres'], 'type' => 'product', 'description' => 'Kit accessoires installation', 'unit' => 'ENS'],

            // Services généraux
            ['name' => 'Frais annexes (Transport, Hébergement & Restauration)', 'reference' => 'SRV-GEN-001', 'selling_price' => 100000, 'category_id' => $categories['autres'], 'type' => 'service', 'description' => 'Frais de déplacement et logistique', 'unit' => 'ENS', 'track_stock' => false],
            ['name' => 'Main d\'oeuvre installation', 'reference' => 'SRV-GEN-002', 'selling_price' => 50000, 'category_id' => $categories['autres'], 'type' => 'service', 'description' => 'Main d\'oeuvre installation journalière', 'unit' => 'JOUR', 'track_stock' => false],
        ];

        foreach ($products as $productData) {
            Product::firstOrCreate(
                ['reference' => $productData['reference']],
                $productData
            );
        }

        $this->command->info(count($products) . ' produits et services créés avec succès !');
    }
}
