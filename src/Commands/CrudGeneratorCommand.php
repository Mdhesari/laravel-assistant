<?php

namespace Mdhesari\LaravelAssistant\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Mdhesari\LaravelAssistant\Exceptions\FileAlreadyExistException;
use Mdhesari\LaravelAssistant\Generators\FileGenerator;
use Mdhesari\LaravelAssistant\Support\Migrations\SchemaParser;
use Mdhesari\LaravelAssistant\Support\Stub;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CrudGeneratorCommand extends Command
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
            $this->createMigration();
        }

        if ($this->option('requests')) {
            $this->createRequests();
        }

        if ($this->option('model')) {
            $this->createModel();
        }

        if ($this->option('controller')) {
            $this->createController();
        }

        // Ask AI for possible fields and data structure

        // Add data to generated stubs & create files

        return 0;
    }

    /**
     * Get controller name.
     *
     * @param $entity
     * @return string
     */
    public function getDestinationFilePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * @return array|string
     */
    protected function getControllerName(string $entity)
    {
        $controller = Str::studly($entity);

        if (Str::contains(strtolower($controller), 'controller') === false) {
            $controller .= 'Controller';
        }

        return $controller;
    }

    /**
     * @return string
     */
    protected function getTemplateContents(string $name, array $replaces)
    {
        return (new Stub($name, $replaces))->render();
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

    private function createMigration()
    {
        $studlyModelName = Str::studly($this->modelName);

        $path = Str::of(database_path('migrations/'))
            ->append(date('Y_m_d_His_'))
            ->append('create_')
            ->append(Str::of($this->modelName)->lower()->plural())
            ->append('_table')
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents('/migrations/create.stub', [
            'TABLE'  => Str::of($studlyModelName)->lower()->plural(),
            'FIELDS' => $this->getSchemaParser()->render(),
        ]);

        $this->createFile($path, $contents);
    }

    private function createActions()
    {
        $this->createAction('create');
        $this->createAction('update');
    }

    private function createEvents()
    {
        $this->createEvent('created');
        $this->createEvent('updated');
    }

    private function createModel()
    {
        $studlyModelName = Str::studly($this->modelName);

        $path = Str::of(app_path('Models/'))
            ->append($studlyModelName)
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents('/model.stub', [
            'CLASS_NAMESPACE' => 'App\\Models',
            'CLASS'           => $studlyModelName,
        ]);

        $this->createFile($path, $contents);
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

        $path = Str::of(app_path('Actions'))
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

    private function createRequests()
    {
        $studlyModelName = Str::studly($this->modelName);

        $path = Str::of(app_path('Http/Requests/'))
            ->append($studlyModelName)
            ->append('Request')
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents('/request.stub', [
            'CLASS_NAMESPACE' => 'App\\Http\\Requests',
            'CLASS'           => $studlyModelName.'Request',
        ]);

        $this->createFile($path, $contents);
    }

    private function createFile(string $path, string $contents): void
    {
        try {
            $this->components->task("Generating file {$path}", function () use ($path, $contents) {
                $overwriteFile = $this->hasOption('force') ? $this->option('force') : false;
                (new FileGenerator($path, $contents))->withFileOverwrite($overwriteFile)->generate();
            });

        } catch (FileAlreadyExistException $e) {
            $this->components->error("File : {$path} already exists.");

            return;
        }
    }

    private function getCompletePath(string $path): string
    {
        $path = $this->getDestinationFilePath($path);

        if (! $this->laravel['files']->isDirectory($dir = dirname($path))) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }

        return $path;
    }

    /**
     * Get schema parser.
     *
     * @return SchemaParser
     */
    public function getSchemaParser()
    {
        return new SchemaParser($this->option('fields'));
    }
}
