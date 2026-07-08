<?php

namespace App\Actions\Chat;

use App\Models\Conversation;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class StartConversationAction
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversations,
    ) {
    }

    /**
     * Idempotent: reuse an existing conversation between this patient/doctor pair.
     * The unique index on (patient_id, doctor_id) is the safety net for races.
     */
    public function handle(string $patientId, string $doctorId): Conversation
    {
        $existing = $this->conversations->findBetween($patientId, $doctorId);

        if ($existing) {
            return $existing;
        }

        try {
            return DB::transaction(fn () => $this->conversations->create([
                "patient_id" => $patientId,
                "doctor_id" => $doctorId,
            ]));
        } catch (QueryException $e) {
            // Unique constraint hit by a concurrent request: fetch the winner.
            return $this->conversations->findBetween($patientId, $doctorId);
        }
    }
}
