<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $statusRu;

    public function __construct(Application $application)
    {
        $this->application = $application;
        
        // Классический switch вместо match (работает на PHP 7.1+)
        switch ($application->status) {
            case 'approved':
                $this->statusRu = '✅ одобрена';
                break;
            case 'rejected':
                $this->statusRu = '❌ отклонена';
                break;
            default:
                $this->statusRu = 'обновлена';
        }
    }

    public function build()
    {
        return $this->subject('Заявка на фестиваль KIFF: ' . $this->statusRu)
                    ->markdown('emails.application_status');
    }
}