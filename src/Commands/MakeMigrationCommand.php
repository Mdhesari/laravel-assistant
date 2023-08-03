<?php

namespace Mdhesari\LaravelAssistant\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeMigrationCommand extends BaseGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'assistant:make-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assistant scaffold a new migration.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelName = $this->argument('model');

        $path = Str::of(database_path('migrations/'))
            ->append(date('Y_m_d_His_'))
            ->append('create_')
            ->append(Str::of($modelName)->lower()->plural())
            ->append('_table')
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents('/migrations/create.stub', [
            'TABLE'  => Str::of($modelName)->lower()->plural(),
            'FIELDS' => $this->getSchemaParser($this->option('fields') ?: '')->render(),
        ]);

        $this->createFile($path, $contents);

        return 0;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['fields', null, InputOption::VALUE_OPTIONAL, 'The specified fields table.', null],
        ];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'Model name'],
        ];
    }
}
