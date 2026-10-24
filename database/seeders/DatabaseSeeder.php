<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessNotification;
use App\Models\BusinessSetting;
use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Party;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Staff;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect([
            ['name' => 'owner', 'label' => 'Owner'],
            ['name' => 'admin', 'label' => 'Admin'],
            ['name' => 'accountant', 'label' => 'Accountant'],
            ['name' => 'cashier', 'label' => 'Cashier'],
            ['name' => 'salesman', 'label' => 'Salesman'],
        ])->map(fn ($r) => Role::firstOrCreate(['name' => $r['name']], $r));

        foreach (['Cash', 'Bank Transfer', 'Easypaisa', 'JazzCash', 'Card', 'Cheque', 'Other'] as $pm) {
            PaymentMethod::firstOrCreate(['name' => $pm]);
        }

        foreach (['Rent', 'Salary', 'Electricity', 'Internet', 'Transport', 'Food', 'Marketing', 'Office Supplies', 'Maintenance', 'Miscellaneous'] as $cat) {
            ExpenseCategory::firstOrCreate(['name' => $cat]);
        }

        foreach (['Piece', 'Kg', 'Gram', 'Liter', 'Meter', 'Box', 'Pack', 'Dozen'] as $unit) {
            Unit::firstOrCreate(['name' => $unit]);
        }

        $ownerRole = $roles->firstWhere('name', 'owner');

        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo Owner',
                'phone' => '+92 300 0000000',
                'password' => Hash::make('password'),
            ]
        );

        $businessNames = ['Asad Traders', 'Skyline Store', 'Demo Wholesale'];
        $businessIds = [];

        foreach ($businessNames as $name) {
            $business = Business::firstOrCreate(
                ['name' => $name],
                [
                    'owner_name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'category' => 'General Store',
                    'currency' => 'PKR',
                ]
            );
            $business->users()->syncWithoutDetaching([
                $user->id => ['role_id' => $ownerRole->id, 'status' => 'active'],
            ]);
            BusinessSetting::firstOrCreate(['business_id' => $business->id]);
            CashAccount::firstOrCreate(['business_id' => $business->id], ['opening_balance' => 50000]);
            $businessIds[] = $business->id;
        }

        $user->update(['current_business_id' => $businessIds[0]]);

        $customers = [
            'Ali Traders', 'Ahmed General Store', 'Usman Enterprises', 'Khan Super Mart',
            'Bilal Wholesale', 'Rehman Store', 'Hassan Mart', 'Saeed Traders',
        ];
        $suppliers = ['Metro Supplier', 'Pakistan Wholesale', 'Khyber Distributors', 'National Traders'];

        $primaryBusinessId = $businessIds[0];

        foreach ($customers as $name) {
            Party::firstOrCreate(
                ['business_id' => $primaryBusinessId, 'name' => $name, 'type' => 'customer'],
                [
                    'phone' => '03'.random_int(100000000, 999999999),
                    'opening_balance' => random_int(0, 50000),
                    'balance_type' => 'receive',
                ]
            );
        }

        foreach ($suppliers as $name) {
            Party::firstOrCreate(
                ['business_id' => $primaryBusinessId, 'name' => $name, 'type' => 'supplier'],
                [
                    'phone' => '03'.random_int(100000000, 999999999),
                    'opening_balance' => random_int(0, 30000),
                    'balance_type' => 'give',
                ]
            );
        }

        $cat = ProductCategory::firstOrCreate(['business_id' => $primaryBusinessId, 'name' => 'Groceries']);
        $unit = Unit::where('name', 'Piece')->first();
        $products = ['Sugar 1kg', 'Cooking Oil 5L', 'Rice 5kg', 'Tea Pack', 'Milk 1L', 'Flour 10kg'];

        foreach ($products as $pname) {
            Product::firstOrCreate(
                ['business_id' => $primaryBusinessId, 'name' => $pname],
                [
                    'category_id' => $cat->id,
                    'unit_id' => $unit->id,
                    'sku' => strtoupper(substr(str_replace(' ', '', $pname), 0, 8)),
                    'purchase_price' => random_int(100, 500),
                    'selling_price' => random_int(600, 1200),
                    'current_stock' => random_int(5, 200),
                    'minimum_stock' => 10,
                ]
            );
        }

        $customer = Party::where('business_id', $primaryBusinessId)->where('type', 'customer')->first();

        for ($i = 0; $i < 15; $i++) {
            Transaction::create([
                'business_id' => $primaryBusinessId,
                'party_id' => $customer->id,
                'type' => $i % 2 === 0 ? 'receive' : 'sale',
                'amount' => random_int(1000, 25000),
                'date' => now()->subDays($i)->toDateString(),
                'description' => 'Seed transaction '.$i,
                'created_by' => $user->id,
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            Invoice::firstOrCreate(
                ['business_id' => $primaryBusinessId, 'invoice_number' => 'INV-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT)],
                [
                    'party_id' => $customer->id,
                    'invoice_date' => now()->subDays($i)->toDateString(),
                    'subtotal' => 16500,
                    'grand_total' => 16500,
                    'paid_amount' => $i % 3 === 0 ? 16500 : 5000,
                    'balance_amount' => $i % 3 === 0 ? 0 : 11500,
                    'status' => $i % 3 === 0 ? 'paid' : 'partial',
                ]
            );
        }

        $rentCat = ExpenseCategory::where('name', 'Rent')->first();
        for ($i = 0; $i < 10; $i++) {
            Expense::create([
                'business_id' => $primaryBusinessId,
                'expense_category_id' => $rentCat->id,
                'amount' => random_int(5000, 30000),
                'date' => now()->subDays($i * 3)->toDateString(),
                'description' => 'Operating expense',
                'created_by' => $user->id,
            ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            Purchase::firstOrCreate(
                ['business_id' => $primaryBusinessId, 'purchase_number' => 'PO-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT)],
                [
                    'party_id' => Party::where('type', 'supplier')->value('id'),
                    'date' => now()->subDays($i)->toDateString(),
                    'subtotal' => 45000,
                    'grand_total' => 45000,
                    'amount_paid' => 20000,
                    'balance' => 25000,
                    'payment_status' => 'partial',
                    'created_by' => $user->id,
                ]
            );
        }

        $product = Product::first();
        $sale = Sale::firstOrCreate(
            ['business_id' => $primaryBusinessId, 'sale_number' => 'SALE-000001'],
            [
                'party_id' => $customer->id,
                'date' => now()->toDateString(),
                'subtotal' => 10000,
                'grand_total' => 10000,
                'amount_paid' => 5000,
                'balance' => 5000,
                'payment_status' => 'partial',
                'created_by' => $user->id,
            ]
        );
        SaleItem::firstOrCreate(
            ['sale_id' => $sale->id, 'product_id' => $product?->id],
            [
                'description' => $product?->name,
                'quantity' => 2,
                'rate' => 5000,
                'amount' => 10000,
            ]
        );

        for ($i = 1; $i <= 5; $i++) {
            Staff::firstOrCreate(
                ['business_id' => $primaryBusinessId, 'name' => 'Staff Member '.$i],
                [
                    'job_title' => $i === 1 ? 'Manager' : 'Salesman',
                    'salary' => 35000 + ($i * 2000),
                    'phone' => '03'.random_int(100000000, 999999999),
                ]
            );
        }

        BusinessNotification::create([
            'business_id' => $primaryBusinessId,
            'user_id' => $user->id,
            'title' => 'Payment received',
            'body' => 'Payment received from Ali Traders',
            'type' => 'payment',
        ]);
    }
}
