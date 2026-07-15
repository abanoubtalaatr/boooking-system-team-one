<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DoctorAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $doctor;
    public string $password;

    public function __construct(User $doctor, string $password)
    {
        $this->doctor = $doctor;
        $this->password = $password;
    }

    public function build()
    {
        return $this
            ->subject('تم إنشاء حسابك على منصة الأطباء')
            ->view('emails.doctor-account-created');
    }
}