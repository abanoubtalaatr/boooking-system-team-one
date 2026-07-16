<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id'       => $this->sender_id,
            'sender_type'     => class_basename($this->sender_type), // Patient / User
            'type'            => $this->type,
            'body'            => $this->body,
            'attachment_url'  =>  $this->attachment_url,
            'read_at'         => $this->read_at,
            'created_at'      => $this->created_at,
        ];
    }
}