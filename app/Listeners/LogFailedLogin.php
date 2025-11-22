<?php

namespace App\Listeners;

use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\SecurityAlert;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class LogFailedLogin implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;
        $ip = request()->ip();

        AuditLogger::log('failed_login', null, null, null, [
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
        ]);

        // Check for suspicious activity
        $recentFailures = AuditLog::where('action', 'failed_login')
            ->where('ip_address', $ip)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($recentFailures >= 5) {
            // Send alert to all admins
            $admins = User::where('role', User::ROLE_ADMIN)->get();
            Notification::send($admins, new SecurityAlert('Multiple Failed Login Attempts', [
                'IP Address' => $ip,
                'Email Attempted' => $email,
                'Failure Count' => $recentFailures,
                'Time Window' => 'Last 10 minutes',
            ]));
        }
    }
}
