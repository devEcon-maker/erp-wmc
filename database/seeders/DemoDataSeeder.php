<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Models\Opportunity;
use App\Modules\CRM\Models\OpportunityStage;
use App\Modules\CRM\Models\Proposal;
use App\Modules\CRM\Models\ProposalLine;
use App\Modules\CRM\Models\Order;
use App\Modules\CRM\Models\OrderLine;
use App\Modules\CRM\Models\Contract;
use App\Modules\CRM\Models\ContractLine;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoiceLine;
use App\Modules\Finance\Models\Payment;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\LeaveType;
use App\Modules\HR\Models\LeaveBalance;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\ExpenseCategory;
use App\Modules\HR\Models\ExpenseReport;
use App\Modules\HR\Models\ExpenseLine;
use App\Modules\HR\Models\JobPosition;
use App\Modules\HR\Models\JobApplication;
use App\Modules\Inventory\Models\ProductCategory;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Models\StockLevel;
use App\Modules\Productivity\Models\Project;
use App\Modules\Productivity\Models\Task;
use App\Modules\Productivity\Models\TimeEntry;
use App\Modules\Agenda\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating demo users...');
        $users = $this->createUsers();

        $this->command->info('Creating departments and employees...');
        $employees = $this->createDepartmentsAndEmployees($users);

        $this->command->info('Creating product categories and products...');
        $products = $this->createProducts();

        $this->command->info('Creating contacts...');
        $contacts = $this->createContacts($users);

        $this->command->info('Creating opportunities...');
        $this->createOpportunities($contacts, $users);

        $this->command->info('Creating proposals (devis et proforma)...');
        $this->createProposals($contacts, $products, $users);

        $this->command->info('Creating orders...');
        $this->createOrders($contacts, $products, $users);

        $this->command->info('Creating invoices...');
        $this->createInvoices($contacts, $products, $users);

        $this->command->info('Creating contracts...');
        $this->createContracts($contacts, $products, $users);

        $this->command->info('Creating leave balances and requests...');
        $this->createLeaveData($employees);

        $this->command->info('Creating expense reports...');
        $this->createExpenseReports($employees);

        $this->command->info('Creating projects and tasks...');
        $projects = $this->createProjects($contacts, $employees, $users);

        $this->command->info('Creating time entries...');
        $this->createTimeEntries($employees, $projects);

        $this->command->info('Creating events...');
        $this->createEvents($users, $contacts, $projects);

        $this->command->info('Creating job positions and applications...');
        $this->createRecruitment();

        $this->command->info('Demo data created successfully!');
    }

    private function createUsers(): array
    {
        $users = [];

        $users['admin'] = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $users['admin']->assignRole('super_admin');

        $users['commercial'] = User::firstOrCreate(
            ['email' => 'commercial@example.com'],
            [
                'name' => 'Jean Commercial',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $users['commercial']->assignRole('commercial');

        $users['comptable'] = User::firstOrCreate(
            ['email' => 'comptable@example.com'],
            [
                'name' => 'Marie Comptable',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $users['comptable']->assignRole('comptable');

        $users['rh'] = User::firstOrCreate(
            ['email' => 'rh@example.com'],
            [
                'name' => 'Sophie RH',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $users['rh']->assignRole('rh');

        $users['manager'] = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Pierre Manager',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $users['manager']->assignRole('manager');

        return $users;
    }

    private function createDepartmentsAndEmployees(array $users): array
    {
        $departments = ['Direction', 'Commercial', 'Comptabilite', 'RH', 'Technique', 'Support'];

        foreach ($departments as $name) {
            Department::firstOrCreate(['name' => $name]);
        }

        $employees = [];
        $employeeData = [
            ['first_name' => 'Admin', 'last_name' => 'Demo', 'user' => $users['admin'], 'dept' => 'Direction', 'job_title' => 'Directeur General'],
            ['first_name' => 'Jean', 'last_name' => 'Dupont', 'user' => $users['commercial'], 'dept' => 'Commercial', 'job_title' => 'Responsable Commercial'],
            ['first_name' => 'Marie', 'last_name' => 'Martin', 'user' => $users['comptable'], 'dept' => 'Comptabilite', 'job_title' => 'Chef Comptable'],
            ['first_name' => 'Sophie', 'last_name' => 'Bernard', 'user' => $users['rh'], 'dept' => 'RH', 'job_title' => 'DRH'],
            ['first_name' => 'Pierre', 'last_name' => 'Durand', 'user' => $users['manager'], 'dept' => 'Technique', 'job_title' => 'Directeur Technique'],
            ['first_name' => 'Lucas', 'last_name' => 'Petit', 'user' => null, 'dept' => 'Technique', 'job_title' => 'Developpeur Senior'],
            ['first_name' => 'Emma', 'last_name' => 'Robert', 'user' => null, 'dept' => 'Commercial', 'job_title' => 'Commercial'],
            ['first_name' => 'Hugo', 'last_name' => 'Richard', 'user' => null, 'dept' => 'Support', 'job_title' => 'Support Client'],
            ['first_name' => 'Lea', 'last_name' => 'Simon', 'user' => null, 'dept' => 'Technique', 'job_title' => 'Developpeur Junior'],
            ['first_name' => 'Thomas', 'last_name' => 'Laurent', 'user' => null, 'dept' => 'Comptabilite', 'job_title' => 'Comptable'],
            ['first_name' => 'Affou', 'last_name' => 'Diakite', 'user' => null, 'dept' => 'Technique', 'job_title' => 'Chef de Projet'],
        ];

        $counter = 1;
        foreach ($employeeData as $data) {
            $dept = Department::where('name', $data['dept'])->first();
            $emp = Employee::firstOrCreate(
                ['email' => strtolower($data['first_name']) . '.' . strtolower($data['last_name']) . '@wmc.fr'],
                [
                    'employee_number' => 'EMP-' . str_pad($counter++, 4, '0', STR_PAD_LEFT),
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => '06' . rand(10000000, 99999999),
                    'department_id' => $dept->id,
                    'job_title' => $data['job_title'],
                    'user_id' => $data['user']?->id,
                    'hire_date' => Carbon::now()->subMonths(rand(6, 48)),
                    'birth_date' => Carbon::now()->subYears(rand(25, 55)),
                    'contract_type' => 'cdi',
                    'status' => 'active',
                ]
            );
            $employees[] = $emp;
        }

        return $employees;
    }

    private function createProducts(): array
    {
        $categories = ['Logiciels', 'Services', 'Materiel', 'Formation', 'Maintenance'];

        $categoryModels = [];
        foreach ($categories as $name) {
            $categoryModels[$name] = ProductCategory::firstOrCreate(['name' => $name]);
        }

        $products = [];
        $productData = [
            ['ref' => 'SOFT-001', 'name' => 'Licence ERP Standard', 'cat' => 'Logiciels', 'type' => 'service', 'purchase' => 500, 'selling' => 1200],
            ['ref' => 'SOFT-002', 'name' => 'Licence ERP Premium', 'cat' => 'Logiciels', 'type' => 'service', 'purchase' => 1000, 'selling' => 2500],
            ['ref' => 'SOFT-003', 'name' => 'Module CRM', 'cat' => 'Logiciels', 'type' => 'service', 'purchase' => 200, 'selling' => 500],
            ['ref' => 'SOFT-004', 'name' => 'Module Comptabilite', 'cat' => 'Logiciels', 'type' => 'service', 'purchase' => 300, 'selling' => 750],
            ['ref' => 'SERV-001', 'name' => 'Developpement sur mesure (jour)', 'cat' => 'Services', 'type' => 'service', 'purchase' => 400, 'selling' => 800],
            ['ref' => 'SERV-002', 'name' => 'Conseil strategique (jour)', 'cat' => 'Services', 'type' => 'service', 'purchase' => 500, 'selling' => 1200],
            ['ref' => 'SERV-003', 'name' => 'Integration API', 'cat' => 'Services', 'type' => 'service', 'purchase' => 1500, 'selling' => 3500],
            ['ref' => 'MAT-001', 'name' => 'Serveur Dell PowerEdge', 'cat' => 'Materiel', 'type' => 'product', 'purchase' => 3000, 'selling' => 4500, 'stock' => 5],
            ['ref' => 'MAT-002', 'name' => 'PC Portable Pro', 'cat' => 'Materiel', 'type' => 'product', 'purchase' => 800, 'selling' => 1200, 'stock' => 15],
            ['ref' => 'MAT-003', 'name' => 'Ecran 27 pouces', 'cat' => 'Materiel', 'type' => 'product', 'purchase' => 200, 'selling' => 350, 'stock' => 20],
            ['ref' => 'FORM-001', 'name' => 'Formation ERP (1 jour)', 'cat' => 'Formation', 'type' => 'service', 'purchase' => 200, 'selling' => 600],
            ['ref' => 'FORM-002', 'name' => 'Formation avancee (3 jours)', 'cat' => 'Formation', 'type' => 'service', 'purchase' => 500, 'selling' => 1800],
            ['ref' => 'MAINT-001', 'name' => 'Maintenance annuelle Standard', 'cat' => 'Maintenance', 'type' => 'service', 'purchase' => 500, 'selling' => 1500],
            ['ref' => 'MAINT-002', 'name' => 'Maintenance annuelle Premium', 'cat' => 'Maintenance', 'type' => 'service', 'purchase' => 1000, 'selling' => 3000],
            ['ref' => 'MAINT-003', 'name' => 'Hebergement cloud (mois)', 'cat' => 'Maintenance', 'type' => 'service', 'purchase' => 50, 'selling' => 150],
        ];

        $warehouse = Warehouse::first();

        foreach ($productData as $data) {
            $product = Product::firstOrCreate(
                ['reference' => $data['ref']],
                [
                    'name' => $data['name'],
                    'category_id' => $categoryModels[$data['cat']]->id,
                    'type' => $data['type'],
                    'purchase_price' => $data['purchase'],
                    'selling_price' => $data['selling'],
                    'tax_rate' => 20,
                    'track_stock' => $data['type'] === 'product',
                    'min_stock_alert' => $data['type'] === 'product' ? 5 : 0,
                    'is_active' => true,
                ]
            );
            $products[] = $product;

            if (isset($data['stock']) && $warehouse) {
                StockLevel::firstOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                    ['quantity' => $data['stock'], 'reserved_quantity' => 0]
                );
            }
        }

        return $products;
    }

    private function createContacts(array $users): array
    {
        $commercial = $users['commercial'];
        $contacts = [];

        $contactData = [
            ['type' => 'client', 'company' => 'TechCorp France', 'first' => 'Marc', 'last' => 'Leblanc', 'city' => 'Paris'],
            ['type' => 'client', 'company' => 'Digital Solutions', 'first' => 'Claire', 'last' => 'Moreau', 'city' => 'Lyon'],
            ['type' => 'client', 'company' => 'InnovateTech', 'first' => 'Philippe', 'last' => 'Girard', 'city' => 'Marseille'],
            ['type' => 'client', 'company' => 'DataPro Services', 'first' => 'Isabelle', 'last' => 'Roux', 'city' => 'Toulouse'],
            ['type' => 'client', 'company' => 'CloudFirst', 'first' => 'Antoine', 'last' => 'Mercier', 'city' => 'Bordeaux'],
            ['type' => 'client', 'company' => 'E-Commerce Plus', 'first' => 'Nathalie', 'last' => 'Blanc', 'city' => 'Nantes'],
            ['type' => 'prospect', 'company' => 'FutureTech', 'first' => 'Vincent', 'last' => 'Garcia', 'city' => 'Nice'],
            ['type' => 'prospect', 'company' => 'GreenEnergy Corp', 'first' => 'Amelie', 'last' => 'Martinez', 'city' => 'Strasbourg'],
            ['type' => 'prospect', 'company' => 'HealthTech Solutions', 'first' => 'David', 'last' => 'Lopez', 'city' => 'Rennes'],
            ['type' => 'prospect', 'company' => 'FinanceFirst', 'first' => 'Caroline', 'last' => 'Wilson', 'city' => 'Paris'],
            ['type' => 'fournisseur', 'company' => 'Dell France', 'first' => 'Michel', 'last' => 'Leroy', 'city' => 'Paris'],
            ['type' => 'fournisseur', 'company' => 'HP Business', 'first' => 'Christine', 'last' => 'Morel', 'city' => 'Lyon'],
        ];

        foreach ($contactData as $data) {
            $email = strtolower($data['first']) . '.' . strtolower($data['last']) . '@' . Str::slug($data['company']) . '.com';
            $contact = Contact::firstOrCreate(
                ['email' => $email],
                [
                    'type' => $data['type'],
                    'company_name' => $data['company'],
                    'first_name' => $data['first'],
                    'last_name' => $data['last'],
                    'phone' => '01' . rand(10000000, 99999999),
                    'mobile' => '06' . rand(10000000, 99999999),
                    'address' => rand(1, 200) . ' avenue des Champs-Elysees',
                    'city' => $data['city'],
                    'postal_code' => rand(10000, 95000),
                    'country' => 'France',
                    'status' => 'active',
                    'assigned_to' => $commercial->id,
                ]
            );
            $contacts[] = $contact;
        }

        return $contacts;
    }

    private function createOpportunities(array $contacts, array $users): void
    {
        $stages = OpportunityStage::orderBy('order')->get();
        if ($stages->isEmpty())
            return;

        $prospects = collect($contacts)->where('type', 'prospect')->values();

        foreach ($prospects as $index => $contact) {
            $stage = $stages[$index % $stages->count()];
            Opportunity::firstOrCreate(
                ['contact_id' => $contact->id, 'title' => 'Projet ' . $contact->company_name],
                [
                    'stage_id' => $stage->id,
                    'amount' => rand(5, 50) * 1000,
                    'probability' => $stage->probability ?? rand(20, 80),
                    'expected_close_date' => Carbon::now()->addMonths(rand(1, 6)),
                    'assigned_to' => $users['commercial']->id,
                    'description' => 'Opportunite commerciale avec ' . $contact->company_name,
                ]
            );
        }
    }

    private function createProposals(array $contacts, array $products, array $users): void
    {
        $clients = collect($contacts)->where('type', 'client')->values();
        $prospects = collect($contacts)->where('type', 'prospect')->values();
        $allContacts = $clients->merge($prospects);
        $serviceProducts = collect($products)->where('type', 'service')->values();

        $proposalData = [
            // Devis en brouillon
            ['status' => 'draft', 'days_ago' => 2, 'valid_days' => 30],
            ['status' => 'draft', 'days_ago' => 5, 'valid_days' => 30],
            // Devis envoyés
            ['status' => 'sent', 'days_ago' => 10, 'valid_days' => 30, 'sent_days_ago' => 8],
            ['status' => 'sent', 'days_ago' => 15, 'valid_days' => 30, 'sent_days_ago' => 12],
            ['status' => 'sent', 'days_ago' => 7, 'valid_days' => 30, 'sent_days_ago' => 5],
            // Devis acceptés (prêts à convertir)
            ['status' => 'accepted', 'days_ago' => 20, 'valid_days' => 30, 'sent_days_ago' => 18, 'accepted_days_ago' => 14],
            ['status' => 'accepted', 'days_ago' => 25, 'valid_days' => 30, 'sent_days_ago' => 22, 'accepted_days_ago' => 18],
            ['status' => 'accepted', 'days_ago' => 30, 'valid_days' => 30, 'sent_days_ago' => 28, 'accepted_days_ago' => 25],
            // Devis refusés
            ['status' => 'refused', 'days_ago' => 35, 'valid_days' => 30, 'sent_days_ago' => 33, 'rejected_days_ago' => 30],
            ['status' => 'refused', 'days_ago' => 40, 'valid_days' => 30, 'sent_days_ago' => 38, 'rejected_days_ago' => 35],
        ];

        $counter = Proposal::count();

        foreach ($proposalData as $index => $data) {
            $contact = $allContacts[$index % $allContacts->count()];
            $createdAt = Carbon::now()->subDays($data['days_ago']);

            $counter++;
            $reference = sprintf('DEV-%s-%05d', $createdAt->format('Y'), $counter);

            $proposal = Proposal::firstOrCreate(
                ['reference' => $reference],
                [
                    'contact_id' => $contact->id,
                    'status' => $data['status'],
                    'valid_until' => $createdAt->copy()->addDays($data['valid_days']),
                    'sent_at' => isset($data['sent_days_ago']) ? Carbon::now()->subDays($data['sent_days_ago']) : null,
                    'accepted_at' => isset($data['accepted_days_ago']) ? Carbon::now()->subDays($data['accepted_days_ago']) : null,
                    'rejected_at' => isset($data['rejected_days_ago']) ? Carbon::now()->subDays($data['rejected_days_ago']) : null,
                    'notes' => $this->getProposalNotes($data['status']),
                    'terms' => "Conditions de paiement: 30 jours fin de mois\nValidité du devis: 30 jours\nTVA: 19.25%",
                    'total_amount' => 0,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount_ttc' => 0,
                    'created_by' => $users['commercial']->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );

            // Ajouter des lignes de produits
            $totalHt = 0;
            $totalDiscount = 0;
            $selectedProducts = $serviceProducts->random(min(rand(2, 4), $serviceProducts->count()));

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 10);
                $discountRate = rand(0, 1) ? rand(0, 15) : 0; // 50% chance d'avoir une remise
                $lineTotal = $product->selling_price * $qty;
                $discount = $lineTotal * ($discountRate / 100);
                $lineTotalAfterDiscount = $lineTotal - $discount;
                $totalHt += $lineTotalAfterDiscount;
                $totalDiscount += $discount;

                ProposalLine::firstOrCreate(
                    ['proposal_id' => $proposal->id, 'product_id' => $product->id],
                    [
                        'description' => $product->name,
                        'quantity' => $qty,
                        'unit_price' => $product->selling_price,
                        'tax_rate' => 19.25, // TVA Cameroun
                        'discount_rate' => $discountRate,
                        'total_amount' => $lineTotalAfterDiscount,
                    ]
                );
            }

            $taxAmount = $totalHt * 0.1925; // 19.25% TVA
            $proposal->update([
                'total_amount' => $totalHt,
                'tax_amount' => $taxAmount,
                'discount_amount' => $totalDiscount,
                'total_amount_ttc' => $totalHt + $taxAmount,
            ]);
        }
    }

    private function getProposalNotes(string $status): string
    {
        return match ($status) {
            'draft' => 'Devis en cours de préparation.',
            'sent' => 'Devis envoyé au client, en attente de réponse.',
            'accepted' => 'Devis accepté par le client. Prêt pour conversion en facture.',
            'refused' => 'Devis refusé par le client. Motif: budget insuffisant ou choix d\'un concurrent.',
            default => 'Devis commercial.',
        };
    }

    private function createOrders(array $contacts, array $products, array $users): void
    {
        $clients = collect($contacts)->where('type', 'client')->values();
        $serviceProducts = collect($products)->where('type', 'service')->values();
        $statuses = ['draft', 'confirmed', 'processing', 'shipped', 'delivered'];

        foreach ($clients->take(8) as $client) {
            $status = $statuses[array_rand($statuses)];
            $orderDate = Carbon::now()->subDays(rand(1, 90));

            $order = Order::firstOrCreate(
                ['contact_id' => $client->id, 'reference' => 'CMD-' . date('Y') . '-' . str_pad(Order::count() + 1, 5, '0', STR_PAD_LEFT)],
                [
                    'status' => $status,
                    'order_date' => $orderDate,
                    'delivery_date' => $orderDate->copy()->addDays(rand(5, 30)),
                    'notes' => 'Commande demo',
                    'total_amount' => 0,
                    'tax_amount' => 0,
                    'total_amount_ttc' => 0,
                    'created_by' => $users['commercial']->id,
                ]
            );

            $totalHt = 0;
            $selectedProducts = $serviceProducts->random(min(3, $serviceProducts->count()));
            foreach ($selectedProducts as $product) {
                $qty = rand(1, 5);
                $lineTotal = $product->selling_price * $qty;
                $totalHt += $lineTotal;

                OrderLine::firstOrCreate(
                    ['order_id' => $order->id, 'product_id' => $product->id],
                    [
                        'description' => $product->name,
                        'quantity' => $qty,
                        'unit_price' => $product->selling_price,
                        'tax_rate' => $product->tax_rate,
                        'total_amount' => $lineTotal,
                    ]
                );
            }

            $taxAmount = $totalHt * 0.2;
            $order->update([
                'total_amount' => $totalHt,
                'tax_amount' => $taxAmount,
                'total_amount_ttc' => $totalHt + $taxAmount,
            ]);
        }
    }

    private function createInvoices(array $contacts, array $products, array $users): void
    {
        $clients = collect($contacts)->where('type', 'client')->values();
        $serviceProducts = collect($products)->where('type', 'service')->values();
        $statuses = ['draft', 'sent', 'paid', 'partial', 'overdue'];

        foreach ($clients->take(6) as $client) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                $date = Carbon::now()->subMonths(rand(0, 4))->subDays(rand(0, 20));
                $status = $statuses[array_rand($statuses)];

                $invoice = Invoice::firstOrCreate(
                    ['reference' => 'FAC-' . $date->format('Y') . '-' . str_pad(Invoice::count() + 1, 5, '0', STR_PAD_LEFT)],
                    [
                        'contact_id' => $client->id,
                        'type' => 'invoice',
                        'status' => $status,
                        'order_date' => $date,
                        'due_date' => $date->copy()->addDays(30),
                        'total_amount' => 0,
                        'tax_amount' => 0,
                        'total_amount_ttc' => 0,
                        'paid_amount' => 0,
                        'notes' => 'Facture demo',
                        'created_by' => $users['comptable']->id,
                    ]
                );

                $totalHt = 0;
                $selectedProducts = $serviceProducts->random(min(3, $serviceProducts->count()));
                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 5);
                    $lineTotal = $product->selling_price * $qty;
                    $totalHt += $lineTotal;

                    InvoiceLine::firstOrCreate(
                        ['invoice_id' => $invoice->id, 'product_id' => $product->id],
                        [
                            'description' => $product->name,
                            'quantity' => $qty,
                            'unit_price' => $product->selling_price,
                            'tax_rate' => $product->tax_rate,
                            'total_amount' => $lineTotal,
                        ]
                    );
                }

                $taxAmount = $totalHt * 0.2;
                $totalTtc = $totalHt + $taxAmount;
                $paidAmount = 0;

                if ($status === 'paid') {
                    $paidAmount = $totalTtc;
                } elseif ($status === 'partial') {
                    $paidAmount = $totalTtc * (rand(30, 70) / 100);
                }

                $invoice->update([
                    'total_amount' => $totalHt,
                    'tax_amount' => $taxAmount,
                    'total_amount_ttc' => $totalTtc,
                    'paid_amount' => $paidAmount,
                    'paid_at' => $paidAmount > 0 ? $date->copy()->addDays(rand(5, 25)) : null,
                ]);
            }
        }
    }

    private function createContracts(array $contacts, array $products, array $users): void
    {
        $clients = collect($contacts)->where('type', 'client')->values()->take(4);
        $maintenanceProducts = collect($products)->filter(fn($p) => str_starts_with($p->reference, 'MAINT'))->values();

        if ($maintenanceProducts->isEmpty())
            return;

        foreach ($clients as $client) {
            $startDate = Carbon::now()->subMonths(rand(1, 12));

            $contract = Contract::firstOrCreate(
                ['contact_id' => $client->id, 'reference' => 'CTR-' . date('Y') . '-' . str_pad(Contract::count() + 1, 5, '0', STR_PAD_LEFT)],
                [
                    'type' => fake()->randomElement(['subscription', 'contract']),
                    'status' => 'active',
                    'start_date' => $startDate,
                    'end_date' => $startDate->copy()->addYear(),
                    'billing_frequency' => fake()->randomElement(['monthly', 'quarterly', 'yearly']),
                    'total_amount' => 0,
                    'tax_amount' => 0,
                    'total_amount_ttc' => 0,
                    'notes' => 'Contrat de maintenance demo',
                    'created_by' => $users['commercial']->id,
                ]
            );

            $totalHt = 0;
            $selectedProducts = $maintenanceProducts->random(min(2, $maintenanceProducts->count()));
            foreach ($selectedProducts as $product) {
                $qty = 12;
                $lineTotal = $product->selling_price * $qty;
                $totalHt += $lineTotal;

                ContractLine::firstOrCreate(
                    ['contract_id' => $contract->id, 'product_id' => $product->id],
                    [
                        'description' => $product->name,
                        'quantity' => $qty,
                        'unit_price' => $product->selling_price,
                        'tax_rate' => $product->tax_rate,
                        'total_amount' => $lineTotal,
                    ]
                );
            }

            $taxAmount = $totalHt * 0.2;
            $contract->update([
                'total_amount' => $totalHt,
                'tax_amount' => $taxAmount,
                'total_amount_ttc' => $totalHt + $taxAmount,
            ]);
        }
    }

    private function createLeaveData(array $employees): void
    {
        $leaveTypes = LeaveType::all();
        if ($leaveTypes->isEmpty())
            return;

        $year = Carbon::now()->year;

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                LeaveBalance::firstOrCreate(
                    ['employee_id' => $employee->id, 'leave_type_id' => $leaveType->id, 'year' => $year],
                    [
                        'allocated' => $leaveType->default_days ?? 25,
                        'used' => rand(0, 10),
                    ]
                );
            }

            if (rand(0, 1)) {
                $leaveType = $leaveTypes->random();
                $startDate = Carbon::now()->addDays(rand(10, 60));

                LeaveRequest::firstOrCreate(
                    ['employee_id' => $employee->id, 'start_date' => $startDate],
                    [
                        'leave_type_id' => $leaveType->id,
                        'end_date' => $startDate->copy()->addDays(rand(1, 5)),
                        'days_count' => rand(1, 5),
                        'reason' => 'Conges annuels',
                        'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
                    ]
                );
            }
        }
    }

    private function createExpenseReports(array $employees): void
    {
        $categories = ExpenseCategory::all();
        if ($categories->isEmpty())
            return;

        foreach (collect($employees)->take(5) as $employee) {
            $report = ExpenseReport::firstOrCreate(
                ['employee_id' => $employee->id, 'reference' => 'NDF-' . Carbon::now()->format('Ym') . '-' . str_pad(ExpenseReport::count() + 1, 4, '0', STR_PAD_LEFT)],
                [
                    'period_start' => Carbon::now()->startOfMonth(),
                    'period_end' => Carbon::now()->endOfMonth(),
                    'status' => fake()->randomElement(['draft', 'submitted', 'approved']),
                    'total_amount' => 0,
                ]
            );

            $total = 0;
            for ($i = 0; $i < rand(2, 4); $i++) {
                $category = $categories->random();
                $amount = rand(20, 150);
                $total += $amount;

                ExpenseLine::firstOrCreate(
                    ['expense_report_id' => $report->id, 'date' => Carbon::now()->subDays(rand(1, 20)), 'category_id' => $category->id],
                    [
                        'description' => 'Depense ' . $category->name,
                        'amount' => $amount,
                    ]
                );
            }

            $report->update(['total_amount' => $total]);
        }
    }

    private function createProjects(array $contacts, array $employees, array $users): array
    {
        $clients = collect($contacts)->where('type', 'client')->values();
        $projects = [];

        $projectData = [
            ['name' => 'Implementation ERP TechCorp', 'type' => 'fixed', 'budget' => 50000],
            ['name' => 'Migration Cloud Digital Solutions', 'type' => 'hourly', 'budget' => 30000],
            ['name' => 'Developpement Module Custom', 'type' => 'hourly', 'budget' => 25000],
            ['name' => 'Formation equipe InnovateTech', 'type' => 'fixed', 'budget' => 8000],
            ['name' => 'Refonte Interface CloudFirst', 'type' => 'fixed', 'budget' => 15000],
        ];

        foreach ($projectData as $index => $data) {
            $client = $clients[$index % $clients->count()];
            $startDate = Carbon::now()->subMonths(rand(0, 3));

            $project = Project::firstOrCreate(
                ['name' => $data['name']],
                [
                    'contact_id' => $client->id,
                    'manager_id' => $users['manager']->id,
                    'description' => 'Projet demo: ' . $data['name'],
                    'status' => fake()->randomElement(['planning', 'active', 'on_hold', 'completed']),
                    'billing_type' => $data['type'],
                    'budget' => $data['budget'],
                    'hourly_rate' => $data['type'] === 'hourly' ? 85 : null,
                    'start_date' => $startDate,
                    'end_date' => $startDate->copy()->addMonths(rand(2, 6)),
                ]
            );
            $projects[] = $project;

            $taskTitles = [
                'Analyse des besoins',
                'Specification technique',
                'Developpement Phase 1',
                'Tests unitaires',
                'Developpement Phase 2',
                'Mise en production',
            ];

            $statuses = ['todo', 'in_progress', 'review', 'done'];
            foreach ($taskTitles as $taskIndex => $title) {
                $status = $statuses[min($taskIndex, 3)];

                Task::firstOrCreate(
                    ['project_id' => $project->id, 'title' => $title],
                    [
                        'description' => 'Tache: ' . $title,
                        'status' => $status,
                        'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
                        'assigned_to' => $employees[array_rand($employees)]->id,
                        'estimated_hours' => rand(4, 40),
                        'due_date' => $startDate->copy()->addDays($taskIndex * 7 + rand(1, 7)),
                        'order' => $taskIndex,
                    ]
                );
            }
        }

        return $projects;
    }

    private function createTimeEntries(array $employees, array $projects): void
    {
        foreach ($projects as $project) {
            $tasks = Task::where('project_id', $project->id)->get();

            foreach ($tasks->take(4) as $task) {
                for ($i = 0; $i < rand(1, 3); $i++) {
                    $employee = $employees[array_rand($employees)];
                    $date = Carbon::now()->subDays(rand(1, 20));

                    TimeEntry::firstOrCreate(
                        ['task_id' => $task->id, 'employee_id' => $employee->id, 'date' => $date],
                        [
                            'project_id' => $project->id,
                            'hours' => rand(1, 8),
                            'description' => 'Travail sur ' . $task->title,
                            'billable' => $project->billing_type !== 'non_billable',
                        ]
                    );
                }
            }
        }
    }

    private function createEvents(array $users, array $contacts, array $projects): void
    {
        $eventTypes = ['meeting', 'call', 'task', 'reminder'];
        $admin = $users['admin'];

        for ($i = 0; $i < 12; $i++) {
            $type = $eventTypes[array_rand($eventTypes)];
            $startAt = Carbon::now()->addDays(rand(0, 25))->setHour(rand(9, 17))->setMinute(0);
            $allDay = rand(0, 10) > 8;

            Event::firstOrCreate(
                ['title' => fake()->sentence(3), 'start_at' => $startAt],
                [
                    'description' => fake()->paragraph(),
                    'type' => $type,
                    'color' => fake()->randomElement(['#E76F51', '#2196F3', '#4CAF50', '#FF9800', '#9C27B0']),
                    'all_day' => $allDay,
                    'end_at' => $allDay ? $startAt->copy()->endOfDay() : $startAt->copy()->addHours(rand(1, 3)),
                    'location' => $type === 'meeting' ? 'Salle ' . rand(1, 5) : null,
                    'created_by' => $admin->id,
                    'project_id' => rand(0, 1) && count($projects) > 0 ? $projects[array_rand($projects)]->id : null,
                    'contact_id' => rand(0, 1) && count($contacts) > 0 ? $contacts[array_rand($contacts)]->id : null,
                    'reminder_minutes' => fake()->randomElement([null, 15, 30, 60]),
                ]
            );
        }
    }

    private function createRecruitment(): void
    {
        $positions = [
            ['title' => 'Developpeur Full Stack', 'dept' => 'Technique', 'type' => 'full_time'],
            ['title' => 'Commercial B2B', 'dept' => 'Commercial', 'type' => 'full_time'],
        ];

        foreach ($positions as $posData) {
            $dept = Department::where('name', $posData['dept'])->first();
            $position = JobPosition::firstOrCreate(
                ['title' => $posData['title']],
                [
                    'department_id' => $dept?->id,
                    'description' => 'Nous recherchons un(e) ' . $posData['title'],
                    'requirements' => "- Experience requise\n- Competences techniques\n- Esprit d'equipe",
                    'type' => $posData['type'],
                    'location' => 'Paris',
                    'salary_range_min' => rand(30, 45) * 1000,
                    'salary_range_max' => rand(50, 80) * 1000,
                    'status' => 'published',
                    'published_at' => Carbon::now()->subDays(rand(5, 30)),
                ]
            );

            for ($i = 0; $i < rand(2, 5); $i++) {
                JobApplication::firstOrCreate(
                    ['job_position_id' => $position->id, 'email' => fake()->unique()->safeEmail()],
                    [
                        'first_name' => fake()->firstName(),
                        'last_name' => fake()->lastName(),
                        'phone' => '06' . rand(10000000, 99999999),
                        'cover_letter' => fake()->paragraphs(2, true),
                        'status' => fake()->randomElement(['new', 'reviewing', 'interview', 'rejected']),
                        'applied_at' => Carbon::now()->subDays(rand(1, 20)),
                    ]
                );
            }
        }
    }
}
