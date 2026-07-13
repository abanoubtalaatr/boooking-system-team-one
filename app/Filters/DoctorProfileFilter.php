<?php

namespace App\Filters;

class DoctorProfileFilter extends QueryFilter
{
    public function search($value): void
    {
        $this->builder->whereHas('user', function ($query) use ($value) {
            $query->where('name', 'like', "%{$value}%");
        });
    }

    public function specialist_id($value): void
    {
        $this->builder->where('specialization_id', $value);
    }

    public function price_from($value): void
    {
        $this->builder->where('price', '>=', $value);
    }

    public function price_to($value): void
    {
        $this->builder->where('price', '<=', $value);
    }

    public function is_active($value): void
    {
        $this->builder->where('is_active', (bool) $value);
    }

    public function gender($value): void
    {
        $this->builder->where('gender', (bool) $value);
    }

    public function experience_from($value): void
    {
        $this->builder->where('experience_years', '>=', $value);
    }

    public function rating_from($value): void
    {
        $this->builder->having('rating_avg', '>=', $value);
    }
}