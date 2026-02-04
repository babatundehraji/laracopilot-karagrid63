<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;
    public $action;
    public $description;
    public $subjectType;
    public $subjectId;

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
    public $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        ?int $userId,
        string $action,
        ?string $description = null,
        ?string $subjectType = null,
        ?int $subjectId = null
    ) {
        $this->userId = $userId;
        $this->action = $action;
        $this->description = $description;
        $this->subjectType = $subjectType;
        $this->subjectId = $subjectId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            ActivityLog::create([
                'user_id' => $this->userId,
                'action' => $this->action,
                'description' => $this->description,
                'subject_type' => $this->subjectType,
                'subject_id' => $this->subjectId,
                'ip_address' => request()->ip() ?? null,
                'user_agent' => request()->userAgent() ?? null
            ]);

            Log::info('Activity logged', [
                'user_id' => $this->userId,
                'action' => $this->action,
                'subject_type' => $this->subjectType,
                'subject_id' => $this->subjectId
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log activity', [
                'user_id' => $this->userId,
                'action' => $this->action,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Activity logging job failed after all retries', [
            'user_id' => $this->userId,
            'action' => $this->action,
            'error' => $exception->getMessage(),
            'attempts' => $this->tries
        ]);
    }
}