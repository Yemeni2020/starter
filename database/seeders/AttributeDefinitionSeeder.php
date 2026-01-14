<?php

namespace Database\Seeders;

use App\Models\AttributeDefinition;
use App\Models\InventoryLocation;
use Illuminate\Database\Seeder;

class AttributeDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'key' => 'material',
                'label_translations' => ['en' => 'Material'],
                'type' => 'TEXT',
                'unit' => null,
                'description' => 'Primary material used in the product.',
                'sort_order' => 10,
            ],
            [
                'key' => 'warranty_months',
                'label_translations' => ['en' => 'Warranty (months)'],
                'type' => 'NUMBER',
                'unit' => 'months',
                'description' => 'Length of manufacturer warranty.',
                'sort_order' => 20,
            ],
            [
                'key' => 'country_of_origin',
                'label_translations' => ['en' => 'Country of origin'],
                'type' => 'TEXT',
                'description' => 'Where the product was manufactured.',
                'sort_order' => 30,
            ],
            [
                'key' => 'power_source',
                'label_translations' => ['en' => 'Power source'],
                'type' => 'SELECT',
                'options' => ['Battery', 'USB', 'Hardwired'],
                'description' => 'How the product is powered.',
                'sort_order' => 40,
            ],
            [
                'key' => 'sensor_type',
                'label_translations' => ['en' => 'Sensor type'],
                'type' => 'TEXT',
                'description' => 'Type of sensor fitted to the product.',
                'sort_order' => 50,
            ],
            [
                'key' => 'dimensions',
                'label_translations' => ['en' => 'Dimensions'],
                'type' => 'DIMENSIONS',
                'description' => 'Product size in millimeters.',
                'sort_order' => 60,
            ],
            [
                'key' => 'weight',
                'label_translations' => ['en' => 'Weight'],
                'type' => 'WEIGHT',
                'unit' => 'g',
                'description' => 'Product weight in grams.',
                'sort_order' => 70,
            ],
            [
                'key' => 'battery_capacity',
                'label_translations' => ['en' => 'Battery capacity'],
                'type' => 'NUMBER',
                'unit' => 'mAh',
                'description' => 'Battery capacity for portable items.',
                'sort_order' => 80,
            ],
            [
                'key' => 'connectivity',
                'label_translations' => ['en' => 'Connectivity'],
                'type' => 'MULTISELECT',
                'options' => ['Wi-Fi', 'Bluetooth', 'NFC', 'Zigbee'],
                'description' => 'Available connectivity channels.',
                'sort_order' => 90,
            ],
        ];

        foreach ($definitions as $definition) {
            AttributeDefinition::updateOrCreate(
                ['key' => $definition['key']],
                $definition
            );
        }

        InventoryLocation::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main warehouse', 'address' => 'Primary fulfillment center']
        );
    }
}
