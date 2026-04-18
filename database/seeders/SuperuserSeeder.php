<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\CategorySeederService;
class SuperuserSeeder extends Seeder {
    public function run(): void {
        $admin = User::firstOrCreate(['email'=>'admin@edgeledger.local'],[
            'name'=>'Super Admin','password'=>Hash::make('password'),
            'role'=>'superuser','is_active'=>true,
        ]);
        CategorySeederService::seedForUser($admin->id);

        $trader = User::firstOrCreate(['email'=>'trader@edgeledger.local'],[
            'name'=>'Demo Trader','password'=>Hash::make('password'),
            'role'=>'user','is_active'=>true,
        ]);
        CategorySeederService::seedForUser($trader->id);
    }
}
