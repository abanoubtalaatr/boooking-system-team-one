<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\SearchHistory;
use Illuminate\Database\Seeder;

class SearchHistorySeeder extends Seeder
{
    /** @var list<string> */
    private array $sampleQueries = [
        'طبيب قلب',
        'أخصائي جلدية',
        'عيادة أسنان',
        'طب أطفال',
        'جراحة عامة',
        'طبيب نفسي',
        'عظام',
        'نساء وتوليد',
    ];

    /** @var list<string> */
    private array $sources = ['search', 'chat', 'favorite'];

    public function run(): void
    {
        $patients = Patient::query()->get();

        if ($patients->isEmpty()) {
            return;
        }

        foreach ($patients as $patient) {
            $queries = collect($this->sampleQueries)->shuffle()->take(random_int(2, 6));

            foreach ($queries as $query) {
                SearchHistory::query()->updateOrCreate(
                    [
                        'user_id' => $patient->id,
                        'query' => $query,
                        'source' => fake()->randomElement($this->sources),
                    ],
                    [
                        'updated_at' => now()->subDays(random_int(0, 30)),
                    ]
                );
            }
        }
    }
}
