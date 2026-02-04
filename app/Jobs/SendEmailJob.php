<?php

namespace App\Jobs;

use App\Mail\GenericMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $to;
    public $subject;
    public $msg;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(string $to, string $subject, string $msg)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->msg = $msg;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->to)->send(new GenericMail($this->subject, $this->msg));
            
            Log::info('Email sent successfully', [
                'to' => $this->to,
                'subject' => $this->subject,
                'queued' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'to' => $this->to,
                'subject' => $this->subject,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);
            
            // Re-throw exception to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Email job failed after all retries', [
            'to' => $this->to,
            'subject' => $this->subject,
            'error' => $exception->getMessage(),
            'attempts' => $this->tries
        ]);
    }
}