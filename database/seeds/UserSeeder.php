<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use App\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    private function getUsers() {
        $json = file_get_contents('./user.json');
        $data = json_decode($json, true);

        
    }

    private function users() {
        return collect([
            [
                'name' => 'Software Developer',
                'username' => 'developer',
                'password' => bcrypt('developer123'),
                'role' => 'developer'
            ],
            // [
            //     'name' => 'Administrator',
            //     'username' => 'admin',
            //     'password' => bcrypt('12345678'),
            //     'role' => 'admin'
            // ],
            // [
            //     'name' => 'Ruben Alpredo Tampubolon',
            //     'username' => 'ben',
            //     'password' => bcrypt('77'),
            //     'role' => 'admin'
            // ],

            // //Employees
            // [
            //     'name' => 'Susanti Shanty',
            //     'username' => 'resepsionis',
            //     'password' => bcrypt('12345678'),
            //     'role' => 'receptionis'
            // ],
            // [
            //     'name' => 'Razor Colombias',
            //     'username' => 'razor',
            //     'password' => bcrypt('12345678'),
            //     'role' => 'customer'
            // ],
            // [
            //     'name' => 'Risco Arizona',
            //     'username' => 'rn',
            //     'password' => bcrypt('12'),
            //     'role' => 'customer'
            // ],

            // //Technicians
            // [
            //     'name' => 'Anton',
            //     'username' => 'anton',
            //     'password' => bcrypt('12345678'),
            //     'role' => 'teknisi'
            // ],
            // [
            //     'name' => 'Ardi Wijaya',
            //     'username' => 'ardi',
            //     'password' => bcrypt('12'),
            //     'role' => 'teknisi'
            // ],

            // //Cleaning Services
            // [
            //     'name' => 'Moria Anita',
            //     'username' => 'moria',
            //     'password' => bcrypt('12345678'),
            //     'role' => 'cleaning-service'
            // ],
            // [
            //     'name' => 'Rona Sena',
            //     'username' => 'rona',
            //     'password' => bcrypt('12345678'),
            //     'role' => 'cleaning-service'
            // ],


            // //Other Support
            // [
            //     'name' => 'Afdan Roy',
            //     'username' => 'afdan',
            //     'password' => bcrypt('12345678'),
            //     'role' => 'security'
            // ],
            // [
            //     'name' => 'Sumarwan',
            //     'username' => 'sumarwan',
            //     'password' => bcrypt('12345678'),
            //     'role' => 'security'
            // ],

            // //Gardener
            // [
            //     'name' => 'Franco Aledry',
            //     'username' => 'franco',
            //     'password' => bcrypt('12345678'),
            //     'role' => 'gardener'
            // ],
            // [
            //     'name' => 'Sylviana Horiza',
            //     'username' => 'sylviana',
            //     'password' => bcrypt('12345678'),
            //     'role' => 'gardener'
            // ],
        ]);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->users()->each(function($value) {
            $user = User::create([
                'name' => $value['name'],
                'username' => $value['username'],
                'password' => $value['password'],
            ]);
            $user->roles()->attach(Role::where('slug',$value['role'])->first());
        });
    }
}
