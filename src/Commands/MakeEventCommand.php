<?php

namespace Mdhesari\LaravelAssistant\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeEventCommand extends BaseGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'assistant:make-event';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assistant scaffold a new request.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $modelName = $this->option('model');

        $name = Str::of($name);
        $class = $name->studly()->prepend($modelName);

        if (($module = $this->option('module')) && function_exists('module_path'))
            $path = Str::of(module_path($module).'/Events/');
        else
            $path = Str::of(app_path('Events/'.$modelName.'/'));

        $namespace = $this->getNamespace($path);

        $path = $path->append($class)
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents('/event.stub', [
            'NAMESPACE'   => $namespace,
            'CLASS'       => $class,
            'MODEL'       => $modelName,
            'MODEL_CLASS' => $this->getModelNamespace($modelName),
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
            ['name', InputArgument::REQUIRED, 'Event name'],
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
            ['model', null, InputOption::VALUE_REQUIRED, 'Model name', null],
            ['module', null, InputOption::VALUE_OPTIONAL, 'Create for Nwidart-modules.', null],
        ];
    }
}
