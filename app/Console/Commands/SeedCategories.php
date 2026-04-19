<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class SeedCategories extends Command
{
    protected $signature = 'categories:seed';
    protected $description = 'Seed default categories into the database';

    public function handle()
    {
        $categories = [
            'Maintenance',
            'Rooms',
            'Technology/Internet',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }

        $this->info('Categories seeded successfully!');
        $this->table(['Category'], array_map(fn($c) => [$c], $categories));

        return 0;
    }
}
