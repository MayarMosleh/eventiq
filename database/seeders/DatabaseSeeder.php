<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Event;
use App\Models\CompanyEvent;
use App\Models\Service;
use App\Models\Venue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('local')->put('test_users.txt', '');

        $providers = [];

        for ($i = 1; $i <= 3; $i++) {
            $providers[] = User::create([
                'name' => 'Provider ' . $i,
                'email' => 'provider' . $i . '@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('provider123'),
                'role' => 'provider',
                'remember_token' => Str::random(10),
            ]);

            Storage::append('test_users.txt', "provider - Email: provider{$i}@example.com - Password: provider123");
        }

        $companies = [];

        foreach ($providers as $provider) {
            $imageName = Str::random(10) . '.jpg';

            Storage::disk('public')->put(
                'Company Photos/' . $imageName,
                file_get_contents(base_path('database/seeders/images company/default.jpg'))
            );

            $company = Company::factory()->create([
                'user_id' => $provider->id,
                'company_image' => 'Company Photos/' . $imageName,
            ]);

            $companies[] = $company;

            for ($v = 1; $v <= 3; $v++) {
                $venue = Venue::create([
                    'venue_name' => 'Venue ' . $v . ' - ' . $company->company_name,
                    'address' => 'Sample address ' . $v,
                    'capacity' => rand(100, 500),
                    'venue_price' => rand(500, 1500),
                    'company_id' => $company->id,
                ]);

                for ($vi = 0; $vi < 3; $vi++) {
                    $venueImageName = Str::random(10) . '.jpg';

                    Storage::disk('public')->put(
                        'Venue Photos/' . $venueImageName,
                        file_get_contents(base_path('database/seeders/images venues/default.jpg'))
                    );

                    $venue->venueImages()->create([
                        'image_url' => 'Venue Photos/' . $venueImageName,
                    ]);
                }
            }
        }

        $events = [];

        for ($i = 1; $i <= 6; $i++) {
            $eventImageName = Str::random(10) . '.jpg';

            Storage::disk('public')->put(
                'Event Photos/' . $eventImageName,
                file_get_contents(base_path('database/seeders/images events/default.jpg'))
            );

            $events[] = Event::create([
                'event_name' => 'Event ' . $i,
                'description' => 'Description for event ' . $i,
                'image_url' => 'Event Photos/' . $eventImageName,
            ]);
        }

        $companyEvents = [];

        for ($i = 0; $i < count($companies); $i++) {
            $company = $companies[$i];
            $event1 = $events[$i * 2];
            $event2 = $events[$i * 2 + 1];

            $companyEvent1 = CompanyEvent::create([
                'company_id' => $company->id,
                'event_id' => $event1->id,
            ]);

            $companyEvent2 = CompanyEvent::create([
                'company_id' => $company->id,
                'event_id' => $event2->id,
            ]);

            $companyEvents[] = $companyEvent1;
            $companyEvents[] = $companyEvent2;
        }

        foreach ($companyEvents as $companyEvent) {
            for ($j = 0; $j < 4; $j++) {
                $service = Service::create([
                    'company_events_id' => $companyEvent->id,
                    'service_name' => 'Service ' . ($j + 1),
                    'service_description' => 'Description for service ' . ($j + 1),
                    'service_price' => rand(10, 100),
                    'service_quantity' => rand(1, 10),
                ]);

                for ($k = 0; $k < 2; $k++) {
                    $imageName = Str::random(10) . '.jpg';

                    Storage::disk('public')->put(
                        'Service Photos/' . $imageName,
                        file_get_contents(base_path('database/seeders/images services/default.jpg'))
                    );

                    $service->serviceImages()->create([
                        'image_url' => 'Service Photos/' . $imageName,
                    ]);
                }
            }
        }

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'remember_token' => Str::random(10),
        ]);
        Storage::append('test_users.txt', "admin - Email: admin@example.com - Password: admin123");

        $user = User::create([
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('user123'),
            'role' => 'user',
            'remember_token' => Str::random(10),
        ]);
        Storage::append('test_users.txt', "user - Email: user@example.com - Password: user123");

        $profileImageName = Str::random(10) . '.jpg';
        Storage::disk('public')->put(
            'Profile Photos/' . $profileImageName,
            file_get_contents(base_path('database/seeders/images profiles/default.jpg'))
        );

        $user->profile()->create([
            'phone' => '0000000000',
            'img' => 'Profile Photos/' . $profileImageName,
            'birthDate' => '0001-01-01',
        ]);

    }
}
