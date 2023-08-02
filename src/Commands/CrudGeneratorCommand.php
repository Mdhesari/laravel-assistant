<?php

namespace Mdhesari\LaravelAssistant\Commands;

use Illuminate\Support\Str;
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
     * The model name passed as an argument
     *
     * @var string
     */
    private string $modelName;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->modelName = $this->argument('model');

        if ($this->option('all')) {
            $this->input->setOption('migration', true);
            $this->input->setOption('controller', true);
            $this->input->setOption('requests', true);
            $this->input->setOption('model', true);
        }

        $this->createEvents();

        $this->createActions();

        if ($this->option('migration')) {
            $this->call('assistant:make-migration', [
                'model' => $this->modelName,
            ]);
        }

        if ($this->option('requests')) {
            $this->call('assistant:make-request', [
                'model' => $this->modelName,
            ]);
        }

        if ($this->option('model')) {
            $this->call('assistant:make-model', [
                'model' => $this->modelName,
            ]);
        }

        if ($this->option('controller')) {
            $this->createController();
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
            ['fields', null, InputOption::VALUE_OPTIONAL, 'The specified fields table.', null],
            ['controller', 'c', InputOption::VALUE_NONE, 'Create a new controller for the crud'],
            ['model', 'mo', InputOption::VALUE_NONE, 'Create a new model for the crud'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the crud already exists'],
            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the crud'],
            ['requests', 'R', InputOption::VALUE_NONE, 'Create new form request classes and use them in the resource controller'],
        ];
    }

    private function createActions()
    {
        $this->createAction('create');
        $this->createAction('update');
        $this->createAction('delete');
    }

    private function createEvents()
    {
        $this->createEvent('created');
        $this->createEvent('updated');
    }

    private function createController()
    {
        $studlyModelName = Str::studly($this->modelName);

        $path = Str::of(app_path('Http/Controllers/'))
            ->append($studlyModelName)
            ->append('Controller')
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents('/controller.stub', [
            'CLASS_NAMESPACE' => 'App\\Http\\Controllers',
            'CLASS'           => $studlyModelName.'Controller',
            'MODEL'           => $studlyModelName,
            'MODEL_REQUEST'   => $studlyModelName.'Request',
        ]);


        $this->createFile($path, $contents);
    }

    private function createAction(string $name)
    {
        $studlyModelName = Str::studly($this->modelName);

        $name = Str::of($name);
        $className = $name->studly()->append($studlyModelName);

        $path = Str::of(app_path('Actions/'.$studlyModelName.'/'))
            ->append('/')
            ->append($className)
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents("/{$name}-action.stub", [
            'NAMESPACE' => 'App\\Actions',
            'CLASS'     => $className,
            'EVENT'     => Str::of($name == 'create' ? 'Created' : 'Updated')->prepend($studlyModelName),
            'MODEL'     => $studlyModelName,
        ]);

        $this->createFile($path, $contents);
    }

    private function createEvent(string $name)
    {
        $studlyModelName = Str::studly($this->modelName);

        $name = Str::of($name);
        $class = $name->studly()->prepend($studlyModelName);

        $path = Str::of(app_path('Events'))
            ->append('/')
            ->append($class)
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents('/event.stub', [
            'NAMESPACE' => 'App\\Events',
            'CLASS'     => $class,
            'MODEL'     => $studlyModelName,
        ]);

        $this->createFile($path, $contents);
    }
}
