<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
	public function run()
	{
		for ($i = 0; $i < 100; $i++) {
			Translation::factory()->count(1000)->create();
			
			echo (($i + 1) * 1000). " records created\n";
		}
		echo "All records have been created!";
	}
}
