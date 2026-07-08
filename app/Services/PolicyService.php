<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Policy\GetPrivacyPolicyAction;
use App\Actions\Policy\GetTermsAction;
use App\Models\Policy;
use Illuminate\Database\Eloquent\Collection;

class PolicyService
{
    public function __construct(
        private readonly GetPrivacyPolicyAction $privacy,
        private readonly GetTermsAction $terms,
    ) {}

    public function privacy(): Collection
    {
        return ($this->privacy)();
    }

    public function terms(): Collection
    {
        return ($this->terms)();
    }
}
