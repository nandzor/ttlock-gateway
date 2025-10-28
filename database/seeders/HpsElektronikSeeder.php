<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HpsElektronik;
use Illuminate\Support\Facades\Storage;

class HpsElektronikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = base_path('table HPS Elektronik.csv');

        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $handle = fopen($csvFile, 'r');
        if (!$handle) {
            $this->command->error('Could not open CSV file');
            return;
        }

        // Skip header row
        $header = fgetcsv($handle);

        $batchSize = 1000;
        $batch = [];
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // Convert price from string with commas to decimal
            $harga = str_replace(',', '', $row[5]);
            $harga = floatval($harga);

            $batch[] = [
                'kdwilayah' => $row[0],
                'jenis_barang' => $row[1],
                'merek' => $row[2],
                'barang' => $row[3],
                'tahun' => intval($row[4]),
                'harga' => $harga,
                'active' => $row[6] === 't',
                'grade' => $row[7],
                'kondisi' => $row[8],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $count++;

            // Insert in batches for better performance
            if (count($batch) >= $batchSize) {
                HpsElektronik::insert($batch);
                $batch = [];
                $this->command->info("Processed {$count} records...");
            }
        }

        // Insert remaining records
        if (!empty($batch)) {
            HpsElektronik::insert($batch);
        }

        fclose($handle);
        $this->command->info("Successfully imported {$count} records from HPS Elektronik CSV");
    }
}
