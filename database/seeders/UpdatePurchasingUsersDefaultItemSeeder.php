<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdatePurchasingUsersDefaultItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all purchasing users
        $purchasingUsers = DB::table('users')
            ->where('role', 'purchasing')
            ->get();

        if ($purchasingUsers->isEmpty()) {
            $this->command->warn('No purchasing users found in the database.');
            return;
        }

        $this->command->info('Found ' . $purchasingUsers->count() . ' purchasing user(s).');

        // Set default preferences - alternate between medis and non medis
        foreach ($purchasingUsers as $index => $user) {
            $preference = ($index % 2 === 0) ? 'medis' : 'non medis';

            DB::table('users')
                ->where('id', $user->id)
                ->update(['default_item_purchasing' => $preference]);

            $this->command->info("Set {$user->name} ({$user->email}) to: {$preference}");
        }

        $this->command->info('Purchasing users default item preferences updated successfully!');
    }
}
