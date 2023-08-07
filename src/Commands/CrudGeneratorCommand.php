<?php

namespace Mdhesari\LaravelAssistant\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CrudGeneratorCommand extends BaseGenerator
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'assistant:crud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create CRUD for an entity.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $modelName = $this->argument('model');
        $fields = $this->getFields();

        if ($this->option('all')) {
            $this->input->setOption('migration', true);
            $this->input->setOption('controller', true);
            $this->input->setOption('requests', true);
            $this->input->setOption('model', true);
        }

        if ($this->option('migration')) {
            $this->call('assistant:make-migration', [
                'model'    => $modelName,
                '--fields' => $fields,
            ]);
        }

        if ($this->option('requests')) {
            $this->call('assistant:make-request', [
                'model'    => $modelName,
                '--fields' => $fields,
            ]);
        }

        if ($this->option('model')) {
            $this->call('assistant:make-model', [
                'model'    => $modelName,
                '--fields' => $fields,
            ]);
        }

        if ($this->option('controller')) {
            $this->call('assistant:make-controller', [
                'model' => $modelName,
            ]);
        }

        return 0;
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


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['all', 'a', InputOption::VALUE_NONE, 'Generate a migration, model, controller, and form request classes for the crud'],
            ['fields', 'fs', InputOption::VALUE_OPTIONAL, 'The specified fields table.', null],
            ['controller', 'c', InputOption::VALUE_NONE, 'Create a new controller for the crud'],
            ['model', 'mo', InputOption::VALUE_NONE, 'Create a new model for the crud'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the crud already exists'],
            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the crud'],
            ['requests', 'R', InputOption::VALUE_NONE, 'Create new form request classes and use them in the resource controller'],
        ];
    }
}
