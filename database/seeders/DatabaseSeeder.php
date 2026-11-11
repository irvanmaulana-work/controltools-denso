<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Partlist;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@erecla.com',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'Approver User',
            'username' => 'approver',
            'email' => 'approver@erecla.com',
            'password' => Hash::make('password'),
            'role' => 'approver'
        ]);

        User::create([
            'name' => 'Member User',
            'username' => 'member',
            'email' => 'member@erecla.com',
            'password' => Hash::make('password'),
            'role' => 'member'
        ]);

        // Sample Partlists
        Partlist::create([
            'drawing_no_supplier' => 'DN001 / ABC Supplier',
            'name' => 'Bearing 6203',
            'size_type' => '17x40x12 / Ball',
            'qty' => 10,
            'sub_assy_drawing_unit' => ' Assy-001',
            'location' => 'Warehouse A1'
        ]);

        Partlist::create([
            'drawing_no_supplier' => 'DN002 / XYZ Corp',
            'name' => 'Bolt M8x20',
            'size_type' => 'M8 / Hex',
            'qty' => 50,
            'sub_assy_drawing_unit' => 'Unit-002',
            'location' => 'Warehouse B2'
        ]);

        Partlist::create([
            'drawing_no_supplier' => 'DN003 / DEF Ltd',
            'name' => 'Nut M8',
            'size_type' => 'M8 / Standard',
            'qty' => 60,
            'sub_assy_drawing_unit' => 'Sub-003',
            'location' => 'Warehouse C3'
        ]);
    }
}
