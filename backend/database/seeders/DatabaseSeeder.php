<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Trip;
use App\Models\Booking;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un utilisateur admin/test
        $admin = User::create([
            'email' => 'admin@etudiant.cesi.fr',
            'password' => Hash::make('password'),
            'first_name' => 'Admin',
            'last_name' => 'CESI',
            'field_of_study' => 'Informatique',
            'year' => 3,
            'profile_type' => 'both',
            'smoker' => false,
            'music' => true,
            'chattiness' => 'normal',
            'vehicle_brand' => 'Peugeot',
            'vehicle_model' => '208',
            'vehicle_color' => 'Bleu',
            'vehicle_seats' => 4,
            'email_verified_at' => now(),
        ]);

        // Créer des utilisateurs conducteurs
        $drivers = [
            [
                'email' => 'marie.dupont@etudiant.cesi.fr',
                'first_name' => 'Marie',
                'last_name' => 'Dupont',
                'field_of_study' => 'Informatique',
                'year' => 3,
                'vehicle_brand' => 'Renault',
                'vehicle_model' => 'Clio',
                'vehicle_color' => 'Rouge',
                'vehicle_seats' => 4,
            ],
            [
                'email' => 'paul.martin@etudiant.cesi.fr',
                'first_name' => 'Paul',
                'last_name' => 'Martin',
                'field_of_study' => 'BTP',
                'year' => 2,
                'vehicle_brand' => 'Citroën',
                'vehicle_model' => 'C3',
                'vehicle_color' => 'Blanc',
                'vehicle_seats' => 5,
            ],
            [
                'email' => 'sophie.bernard@etudiant.cesi.fr',
                'first_name' => 'Sophie',
                'last_name' => 'Bernard',
                'field_of_study' => 'Marketing',
                'year' => 4,
                'vehicle_brand' => 'Volkswagen',
                'vehicle_model' => 'Polo',
                'vehicle_color' => 'Noir',
                'vehicle_seats' => 4,
            ],
        ];

        foreach ($drivers as $driverData) {
            User::create([
                'email' => $driverData['email'],
                'password' => Hash::make('password'),
                'first_name' => $driverData['first_name'],
                'last_name' => $driverData['last_name'],
                'field_of_study' => $driverData['field_of_study'],
                'year' => $driverData['year'],
                'profile_type' => 'both',
                'smoker' => fake()->boolean(20),
                'music' => fake()->boolean(80),
                'chattiness' => fake()->randomElement(['quiet', 'normal', 'chatty']),
                'vehicle_brand' => $driverData['vehicle_brand'],
                'vehicle_model' => $driverData['vehicle_model'],
                'vehicle_color' => $driverData['vehicle_color'],
                'vehicle_seats' => $driverData['vehicle_seats'],
                'average_rating' => fake()->randomFloat(2, 3.5, 5.0),
                'total_trips' => fake()->numberBetween(5, 50),
                'email_verified_at' => now(),
            ]);
        }

        // Créer des utilisateurs passagers
        for ($i = 0; $i < 10; $i++) {
            User::create([
                'email' => fake()->unique()->firstName() . '.' . fake()->lastName() . '@etudiant.cesi.fr',
                'password' => Hash::make('password'),
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'field_of_study' => fake()->randomElement(['Informatique', 'BTP', 'Marketing', 'RH', 'Généraliste']),
                'year' => fake()->numberBetween(1, 5),
                'profile_type' => fake()->randomElement(['passenger', 'both']),
                'smoker' => fake()->boolean(20),
                'music' => fake()->boolean(80),
                'chattiness' => fake()->randomElement(['quiet', 'normal', 'chatty']),
                'average_rating' => fake()->randomFloat(2, 3.5, 5.0),
                'total_trips' => fake()->numberBetween(0, 30),
                'email_verified_at' => now(),
            ]);
        }

        // Créer des trajets
        $allDrivers = User::whereIn('profile_type', ['driver', 'both'])
            ->whereNotNull('vehicle_brand')
            ->get();

        $locations = [
            ['address' => 'Campus CESI Lyon', 'lat' => 45.7640, 'lng' => 4.8357],
            ['address' => 'Gare Part-Dieu, Lyon', 'lat' => 45.7606, 'lng' => 4.8590],
            ['address' => 'Place Bellecour, Lyon', 'lat' => 45.7578, 'lng' => 4.8320],
            ['address' => 'Vieux Lyon', 'lat' => 45.7633, 'lng' => 4.8270],
            ['address' => 'Villeurbanne Centre', 'lat' => 45.7700, 'lng' => 4.8800],
        ];

        foreach ($allDrivers->take(5) as $driver) {
            for ($i = 0; $i < 3; $i++) {
                $departure = fake()->randomElement($locations);
                $arrival = fake()->randomElement(array_filter($locations, fn($l) => $l['address'] !== $departure['address']));
                
                $departureTime = fake()->dateTimeBetween('now', '+14 days');
                $distance = fake()->randomFloat(2, 5, 50);

                Trip::create([
                    'driver_id' => $driver->id,
                    'departure_address' => $departure['address'],
                    'departure_lat' => $departure['lat'],
                    'departure_lng' => $departure['lng'],
                    'arrival_address' => $arrival['address'],
                    'arrival_lat' => $arrival['lat'],
                    'arrival_lng' => $arrival['lng'],
                    'departure_time' => $departureTime,
                    'arrival_time' => (clone $departureTime)->modify('+' . fake()->numberBetween(15, 60) . ' minutes'),
                    'available_seats' => fake()->numberBetween(1, $driver->vehicle_seats - 1),
                    'occupied_seats' => 0,
                    'status' => 'active',
                    'smoker_allowed' => $driver->smoker,
                    'music_allowed' => $driver->music,
                    'chattiness_preference' => $driver->chattiness,
                    'auto_validation' => fake()->boolean(70),
                    'distance_km' => $distance,
                    'estimated_co2_saved' => 0,
                ]);
            }
        }

        $this->command->info('Base de données peuplée avec succès!');
        $this->command->info('Utilisateur test: admin@etudiant.cesi.fr / password');
    }
}
