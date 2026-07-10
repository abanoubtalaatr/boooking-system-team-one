<?php
namespace Database\Factories;

use App\Models\Hospital;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'specialization_id'  => Specialization::query()->inRandomOrder()->value('id'),//Specialization::factory(),
            'hospital_id' => Hospital::query()->inRandomOrder()->value('id'),//'hospital_id'       => Hospital::factory(),
            'latitude'          => $this->faker->latitude(29.9, 30.2), // حدود القاهرة تقريبًا
            'longitude'         => $this->faker->longitude(31.1, 31.4),
            'bio'               => $this->faker->paragraph(3),
            'avatar'            => $this->faker->imageUrl(200, 200, 'people'),
            'price'             => $this->faker->randomFloat(2, 100, 800),
            'experience_years'  => $this->faker->numberBetween(1, 30),
            'certificates'      => $this->generateCertificates(),
            'education'         => $this->faker->randomElement([
                'Cairo University - Faculty of Medicine',
                'Ain Shams University - Faculty of Medicine',
                'Alexandria University - Faculty of Medicine',
                'Harvard Medical School',
            ]),
            'gender'            => $this->faker->randomElement(['male', 'female']),
            'language'          => $this->faker->randomElement(['english', 'arabic']),
            'is_active'         => true,
        ];
    }

    /**
     * توليد شهادات عشوائية بنفس الشكل المطلوب في العمود JSON.
     */
    protected function generateCertificates(): array
    {
        $count = $this->faker->numberBetween(1, 3);

        return collect(range(1, $count))->map(function () {
            return [
                'title'  => $this->faker->randomElement([
                    'Laravel Professional',
                    'PHP Advanced',
                    'Board Certified Cardiologist',
                    'Advanced Cardiac Life Support',
                    'Pediatric Emergency Medicine',
                ]),
                'issuer' => $this->faker->randomElement(['Udemy', 'Coursera', 'AMA', 'Egyptian Medical Syndicate']),
                'year'   => $this->faker->numberBetween(2015, 2024),
            ];
        })->toArray();
    }

    /**
     * حالة: دكتور غير مفعل.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * حالة: دكتور من غير lat/lng (لاختبار سيناريو عدم وجود إحداثيات).
     */
    public function withoutLocation(): static
    {
        return $this->state(fn(array $attributes) => [
            'latitude'  => null,
            'longitude' => null,
        ]);
    }
}
