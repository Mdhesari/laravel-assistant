<?php

namespace Mdhesari\LaravelAssistant\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'assistant:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install assistant package.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! config('assistant.openapi_api_key')) {
            $this->error('Please add OPENAI_API_KEY to your .env variables.');
            return 1;
        };

        if ($this->confirm(question: 'would you install the dependencies? (if not do it manually later)', default: false)) {
            exec('composer install mdhesari/api-response mdhesari/laravel-query-filter');
        }

        return 0;
    }
}
