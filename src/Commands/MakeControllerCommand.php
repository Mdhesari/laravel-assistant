<?php

namespace Mdhesari\LaravelAssistant\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeControllerCommand extends BaseGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'assistant:make-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assistant scaffold a new request.';

    /**
     * The model name.
     *
     * @var string
     */
    private string $modelName;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelName = $this->argument('model');

        $this->modelName = $modelName;

        $this->makeEvents();

        $this->makeActions();

        $path = $this->getPath();

        $namespace = $this->getNamespace($path);

        $path = $path->append($modelName)
            ->append('Controller')
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents('/controller.stub', [
            'NAMESPACE'           => $namespace,
            'CLASS'               => $modelName.'Controller',
            'MODEL'               => $modelName,
            'MODEL_REQUEST'       => $request = $modelName.'Request',
            'MODEL_REQUEST_CLASS' => $this->getRequestNamespace($request),
            'MODEL_CLASS'         => $this->getModelNamespace($modelName),
            'ACTION_CREATE_CLASS' => $this->getActionNamespace('Create'.$modelName),
            'ACTION_DELETE_CLASS' => $this->getActionNamespace('Delete'.$modelName),
            'ACTION_UPDATE_CLASS' => $this->getActionNamespace('Update'.$modelName),
        ]);


        $this->createFile($path, $contents);

        return self::SUCCESS;
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
            ['module', null, InputOption::VALUE_OPTIONAL, 'Create for Nwidart-modules.', null],
        ];
    }

    private function makeActions()
    {
        $this->makeAction('create');
        $this->makeAction('update');
        $this->makeAction('delete');
    }

    private function makeEvents()
    {
        $this->makeEvent('created');
        $this->makeEvent('updated');
    }

    private function makeAction(string $name)
    {
        $this->call('assistant:make-action', [
            '--model'  => $this->modelName,
            '--module' => $this->option('module'),
            'name'     => $name,
        ]);
    }

    private function makeEvent(string $name)
    {
        $this->call('assistant:make-event', [
            '--model'  => $this->modelName,
            '--module' => $this->option('module'),
            'name'     => $name,
        ]);
    }

    private function getPath(): \Illuminate\Support\Stringable
    {
        if (($module = $this->option('module')) && function_exists('module_path'))
            return Str::of(module_path($module).'/Http/Controllers/');

        return Str::of(app_path('/Http/Controllers/'));
    }
}
