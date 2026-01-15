<?php

namespace App\Modules\Core\Services;

use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Models\Order;
use App\Modules\Finance\Models\Invoice;
use App\Modules\HR\Models\Employee;
use App\Modules\Inventory\Models\Product;
use App\Modules\Productivity\Models\Project;
use Illuminate\Support\Facades\Auth;

class GlobalSearchService
{
    public function search(string $query, $user = null): array
    {
        $user = $user ?? Auth::user();
        $results = [];

        if (strlen($query) < 2) {
            return $results;
        }

        // Contacts (si permission contacts.view)
        if ($user->can('contacts.view')) {
            $contacts = Contact::where(function ($q) use ($query) {
                $q->where('company_name', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'label' => $contact->display_name,
                    'subtitle' => ucfirst($contact->type) . ($contact->email ? " - {$contact->email}" : ''),
                    'url' => route('crm.contacts.show', $contact),
                    'icon' => 'person',
                ];
            });

            if ($contacts->isNotEmpty()) {
                $results['contacts'] = [
                    'label' => 'Contacts',
                    'icon' => 'perm_contact_calendar',
                    'items' => $contacts->toArray(),
                ];
            }
        }

        // Products (si permission products.view)
        if ($user->can('products.view')) {
            $products = Product::where(function ($q) use ($query) {
                $q->where('reference', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'label' => $product->name,
                    'subtitle' => $product->reference . ' - ' . number_format($product->selling_price, 2, ',', ' ') . ' FCFA',
                    'url' => route('inventory.products.show', $product),
                    'icon' => 'inventory_2',
                ];
            });

            if ($products->isNotEmpty()) {
                $results['products'] = [
                    'label' => 'Produits',
                    'icon' => 'category',
                    'items' => $products->toArray(),
                ];
            }
        }

        // Invoices (si permission invoices.view)
        if ($user->can('invoices.view')) {
            $invoices = Invoice::with('contact')
                ->where('reference', 'like', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'label' => $invoice->reference,
                        'subtitle' => $invoice->contact?->display_name . ' - ' . number_format($invoice->total_ttc, 2, ',', ' ') . ' FCFA',
                        'url' => route('finance.invoices.show', $invoice),
                        'icon' => 'receipt',
                    ];
                });

            if ($invoices->isNotEmpty()) {
                $results['invoices'] = [
                    'label' => 'Factures',
                    'icon' => 'payments',
                    'items' => $invoices->toArray(),
                ];
            }
        }

        // Orders (si permission orders.view)
        if ($user->can('orders.view')) {
            $orders = Order::with('contact')
                ->where('reference', 'like', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'label' => $order->reference,
                        'subtitle' => $order->contact?->display_name . ' - ' . ucfirst($order->status),
                        'url' => route('crm.orders.show', $order),
                        'icon' => 'shopping_cart',
                    ];
                });

            if ($orders->isNotEmpty()) {
                $results['orders'] = [
                    'label' => 'Commandes',
                    'icon' => 'shopping_cart',
                    'items' => $orders->toArray(),
                ];
            }
        }

        // Projects (si permission projects.view)
        if ($user->can('projects.view')) {
            $projects = Project::with('contact')
                ->where('name', 'like', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'label' => $project->name,
                        'subtitle' => $project->contact?->display_name ?? 'Projet interne',
                        'url' => route('productivity.projects.show', $project),
                        'icon' => 'folder',
                    ];
                });

            if ($projects->isNotEmpty()) {
                $results['projects'] = [
                    'label' => 'Projets',
                    'icon' => 'folder',
                    'items' => $projects->toArray(),
                ];
            }
        }

        // Employees (si permission employees.view)
        if ($user->can('employees.view')) {
            $employees = Employee::where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('employee_number', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'label' => $employee->full_name,
                    'subtitle' => $employee->employee_number . ' - ' . ($employee->position ?? 'N/A'),
                    'url' => route('hr.employees.show', $employee),
                    'icon' => 'badge',
                ];
            });

            if ($employees->isNotEmpty()) {
                $results['employees'] = [
                    'label' => 'Employes',
                    'icon' => 'badge',
                    'items' => $employees->toArray(),
                ];
            }
        }

        return $results;
    }
}
