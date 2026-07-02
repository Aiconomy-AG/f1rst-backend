<?php
//
//namespace App\Console\Commands;
//
//use Illuminate\Console\Command;
//use Illuminate\Support\Facades\Http;
//use App\Models\Car;
//
//class SyncStreetCars extends Command
//{
//    protected $signature = 'cars:sync-street-cars';
//    protected $description = 'Fetches cars mapping data into existing make and power columns.';
//
//    public function handle()
//    {
//        $apiKey = env('CAR_API_KEY');
//
//        if (!$apiKey) {
//            $this->error('CAR_API_KEY is missing from your .env file.');
//            return;
//        }
//
//        $targets = [
//            ['make' => 'audi', 'model' => 'a4'],
//            ['make' => 'bmw', 'model' => 'm3'],
//            ['make' => 'mercedes', 'model' => 'c-class'],
//            ['make' => 'porsche', 'model' => '911'],
//            ['make' => 'ferrari', 'model' => '488']
//        ];
//
//        $totalInserted = 0;
//        $maxLimit = 100;
//
//        foreach ($targets as $target) {
//            $brand = $target['make'];
//            $modelQuery = $target['model'];
//
//            if ($totalInserted >= $maxLimit) {
//                $this->info("Global limit of {$maxLimit} reached. Stopping scan.");
//                break;
//            }
//
//            $this->info("Fetching cars for: {$brand} {$modelQuery}...");
//
//            $response = Http::withHeaders([
//                'X-Api-Key' => $apiKey
//            ])->get("https://api.api-ninjas.com/v1/cars", [
//                'make' => $brand,
//                'model' => $modelQuery
//                // The 'limit' parameter has been removed here to fix the 400 error
//            ]);
//
//            if (!$response->successful()) {
//                $this->error("Failed to fetch data for {$brand} {$modelQuery}.");
//                $this->error("HTTP Status Code: " . $response->status());
//                $this->error("Response Body: " . $response->body());
//                continue;
//            }
//
//            $carsData = $response->json();
//            $brandInserted = 0;
//
//            foreach ($carsData as $car) {
//                if ($totalInserted >= $maxLimit) {
//                    break;
//                }
//
//                $carBrand = $car['make'] ?? null;
//                $carModel = $car['model'] ?? null;
//                $cylinders = $car['cylinders'] ?? 0;
//
//                if ($carBrand && $carModel) {
//                    $wasCreated = Car::updateOrCreate(
//                        [
//                            'make'  => $carBrand,
//                            'model' => $carModel,
//                        ],
//                        [
//                            'power' => (int) $cylinders,
//                        ]
//                    );
//
//                    if ($wasCreated->wasRecentlyCreated) {
//                        $brandInserted++;
//                        $totalInserted++;
//                    }
//                }
//            }
//
//            $this->info("Inserted {$brandInserted} new variations for {$brand} {$modelQuery}.");
//            sleep(1);
//        }
//
//        $this->info("Sync complete! Total new cars inserted: {$totalInserted}");
//    }
//}


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Car;

class SyncStreetCars extends Command
{
    protected $signature = 'cars:sync-street-cars';
    protected $description = 'Picks 5 random cars from a list of 50 and syncs them to create an illusion of constant updates.';

    public function handle()
    {
        $apiKey = env('CAR_API_KEY');

        if (!$apiKey) {
            $this->error('CAR_API_KEY is missing from your .env file.');
            return;
        }

        // Hardcoded list of 50 diverse cars
        $masterList = [
            ['make' => 'audi', 'model' => 'a3'], ['make' => 'audi', 'model' => 'a4'], ['make' => 'audi', 'model' => 'q5'], ['make' => 'audi', 'model' => 'r8'],
            ['make' => 'bmw', 'model' => 'm3'], ['make' => 'bmw', 'model' => 'x5'], ['make' => 'bmw', 'model' => '330i'], ['make' => 'bmw', 'model' => 'm5'],
            ['make' => 'mercedes', 'model' => 'c-class'], ['make' => 'mercedes', 'model' => 'e-class'], ['make' => 'mercedes', 'model' => 'g-class'], ['make' => 'mercedes', 'model' => 'gle'],
            ['make' => 'porsche', 'model' => '911'], ['make' => 'porsche', 'model' => 'cayenne'], ['make' => 'porsche', 'model' => 'macan'], ['make' => 'porsche', 'model' => 'panamera'],
            ['make' => 'ferrari', 'model' => '488'], ['make' => 'ferrari', 'model' => 'f8'], ['make' => 'ferrari', 'model' => 'roma'], ['make' => 'ferrari', 'model' => 'portofino'],
            ['make' => 'toyota', 'model' => 'camry'], ['make' => 'toyota', 'model' => 'corolla'], ['make' => 'toyota', 'model' => 'rav4'], ['make' => 'toyota', 'model' => 'supra'],
            ['make' => 'honda', 'model' => 'civic'], ['make' => 'honda', 'model' => 'accord'], ['make' => 'honda', 'model' => 'cr-v'], ['make' => 'honda', 'model' => 'pilot'],
            ['make' => 'ford', 'model' => 'mustang'], ['make' => 'ford', 'model' => 'f-150'], ['make' => 'ford', 'model' => 'explorer'], ['make' => 'ford', 'model' => 'bronco'],
            ['make' => 'chevrolet', 'model' => 'corvette'], ['make' => 'chevrolet', 'model' => 'camaro'], ['make' => 'chevrolet', 'model' => 'silverado'], ['make' => 'chevrolet', 'model' => 'tahoe'],
            ['make' => 'nissan', 'model' => 'altima'], ['make' => 'nissan', 'model' => 'sentra'], ['make' => 'nissan', 'model' => 'rogue'], ['make' => 'nissan', 'model' => 'gt-r'],
            ['make' => 'volkswagen', 'model' => 'golf'], ['make' => 'volkswagen', 'model' => 'jetta'], ['make' => 'volkswagen', 'model' => 'tiguan'], ['make' => 'volkswagen', 'model' => 'gti'],
            ['make' => 'subaru', 'model' => 'outback'], ['make' => 'subaru', 'model' => 'wrx'], ['make' => 'subaru', 'model' => 'forester'], ['make' => 'subaru', 'model' => 'crosstrek'],
            ['make' => 'hyundai', 'model' => 'elantra'], ['make' => 'hyundai', 'model' => 'tucson']
        ];

        // Randomize the list and pick the first 5
        shuffle($masterList);
        $targets = array_slice($masterList, 0, 5);

        $this->info("Picked 5 random cars for this sync...");

        $totalInserted = 0;

        foreach ($targets as $target) {
            $brand = $target['make'];
            $modelQuery = $target['model'];

            $this->info("Fetching data for: {$brand} {$modelQuery}...");

            $response = Http::withHeaders([
                'X-Api-Key' => $apiKey
            ])->get("https://api.api-ninjas.com/v1/cars", [
                'make' => $brand,
                'model' => $modelQuery
            ]);

            if (!$response->successful()) {
                $this->error("Failed to fetch data for {$brand} {$modelQuery}.");
                continue;
            }

            $carsData = $response->json();
            $brandInserted = 0;

            foreach ($carsData as $car) {
                $carBrand = $car['make'] ?? null;
                $carModel = $car['model'] ?? null;
                $cylinders = $car['cylinders'] ?? 0;

                if ($carBrand && $carModel) {
                    $wasCreated = Car::updateOrCreate(
                        [
                            'make' => $carBrand,
                            'model' => $carModel,
                        ],
                        [
                            'power' => (int)$cylinders,
                        ]
                    );

                    if ($wasCreated->wasRecentlyCreated) {
                        $brandInserted++;
                        $totalInserted++;
                    }
                }
            }

            $this->info("Inserted {$brandInserted} variations for {$brand} {$modelQuery}.");
            sleep(1);
        }

        $this->info("Sync complete! Total new cars inserted this run: {$totalInserted}");
    }
}
