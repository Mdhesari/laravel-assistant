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

        if ($this->option('modules') && function_exists('module_path'))
            $path = Str::of(module_path($modelName).'/');
        else
            $path = Str::of(app_path('/'));

        $path = $path->append('Http/Controllers/')
            ->append($modelName)
            ->append('Controller')
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents('/controller.stub', [
            'NAMESPACE'           => 'App\\Http\\Controllers',
            'CLASS'               => $modelName.'Controller',
            'MODEL'               => $modelName,
            'MODEL_REQUEST'       => $request = $modelName.'Request',
            'MODEL_REQUEST_CLASS' => "App\\Http\\Requests\\{$modelName}\\{$request}",
            'MODEL_CLASS'         => 'App\\Models\\'.$modelName,
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
            ['modules', null, InputOption::VALUE_OPTIONAL, 'Create for Nwidart-modules.', null],
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
            '--model'   => $this->modelName,
            '--modules' => $this->option('modules'),
            'name'      => $name,
        ]);
    }

    private function makeEvent(string $name)
    {
        $this->call('assistant:make-event', [
            '--model'   => $this->modelName,
            '--modules' => $this->option('modules'),
            'name'      => $name,
        ]);
    }
}
