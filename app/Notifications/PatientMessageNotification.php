<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PatientMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Message $message)
    {
    }

    public function via(object $notifiable): array
    {
        return ["database"];
    }

    public function toArray(object $notifiable): array
    {
        return [
            "conversation_id" => $this->message->conversation_id,
            "message_id" => $this->message->id,
            "sender_id" => $this->message->sender_id,
            "preview" => str($this->message->content ?? "[" . $this->message->type->value . "]")->limit(80)->toString(),
        ];
    }
}
