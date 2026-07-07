<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'category' => 'Appointments',
                'question' => 'How can I book an appointment?',
                'answer' => 'Select a doctor, choose an available date and time, then confirm your booking.',
            ],
            [
                'category' => 'Appointments',
                'question' => 'Can I cancel my appointment?',
                'answer' => 'Yes, you can cancel your appointment before its scheduled time.',
            ],
            [
                'category' => 'Doctors',
                'question' => 'How are doctors rated?',
                'answer' => 'Ratings are based on verified patient reviews.',
            ],
            [
                'category' => 'Payments',
                'question' => 'Which payment methods are accepted?',
                'answer' => 'Cash, Visa, Mastercard, and digital wallets are accepted.',
            ],
            [
                'category' => 'Account',
                'question' => 'How can I reset my password?',
                'answer' => 'Use the "Forgot Password" option on the login screen.',
            ],
        ];

        foreach ($faqs as $faq) {

            $category = FaqCategory::where('name', $faq['category'])->first();

            Faq::create([
                'faq_category_id' => $category->id,
                'question' => $faq['question'],
                'answer' => $faq['answer'],
            ]);
        }
    }
}
