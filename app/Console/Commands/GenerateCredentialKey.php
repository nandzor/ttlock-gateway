<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateCredentialKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encrypt:generate-key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new ENCRYPT_CREDENTIAL_KEY for credential encryption';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $key = base64_encode(random_bytes(32));

        $this->info('Generated ENCRYPT_CREDENTIAL_KEY:');
        $this->line($key);
        $this->newLine();

        $this->warn('Add this to your .env file:');
        $this->line("ENCRYPT_CREDENTIAL_KEY={$key}");
        $this->newLine();

        $this->info('After adding to .env, run: php artisan config:clear');

        return Command::SUCCESS;
    }
}
