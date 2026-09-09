<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'email:test {email}';
    protected $description = 'Test SMTP email configuration';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('Testing SMTP configuration...');
        $this->info('SMTP Host: ' . config('mail.mailers.smtp.host'));
        $this->info('SMTP Port: ' . config('mail.mailers.smtp.port'));
        $this->info('SMTP Username: ' . config('mail.mailers.smtp.username'));
        $this->info('SMTP From: ' . config('mail.from.address'));
        
        try {
            Mail::raw('This is a test email from CampFix.', function ($message) use ($email) {
                $message->to($email)
                        ->subject('CampFix SMTP Test Email');
            });
            
            $this->info('✅ Email sent successfully to: ' . $email);
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            $this->error('Error trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
