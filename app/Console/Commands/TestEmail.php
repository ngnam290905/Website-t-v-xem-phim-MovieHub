<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'email:test {email?}';

    protected $description = 'Test email configuration by sending a test email';

    public function handle()
    {
        $email = $this->argument('email') ?? $this->ask('Enter email address to send test email');
        
        if (!$email) {
            $this->error('Email address is required');
            return 1;
        }

        $this->info('Sending test email to: ' . $email);
        $this->info('Mail Configuration:');
        $this->line('  MAILER: ' . config('mail.default'));
        $this->line('  HOST: ' . config('mail.mailers.smtp.host'));
        $this->line('  PORT: ' . config('mail.mailers.smtp.port'));
        $this->line('  USERNAME: ' . config('mail.mailers.smtp.username'));
        $this->line('  ENCRYPTION: ' . config('mail.mailers.smtp.encryption'));
        $this->line('  FROM: ' . config('mail.from.address'));

        try {
            Mail::raw('This is a test email from Laravel application. If you receive this, your email configuration is working correctly!', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email from Laravel');
            });

            $this->info('✅ Test email sent successfully!');
            $this->info('Please check your inbox (and spam folder) at: ' . $email);
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email:');
            $this->error($e->getMessage());
            $this->newLine();
            $this->warn('Troubleshooting tips:');
            $this->line('1. Check if you are using App Password (not regular Gmail password)');
            $this->line('2. Ensure 2-Step Verification is enabled');
            $this->line('3. Run: php artisan config:clear');
            $this->line('4. See GMAIL_SETUP_GUIDE.md for detailed instructions');
            return 1;
        }
    }
}
