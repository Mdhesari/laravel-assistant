<?php

namespace Mdhesari\LaravelAssistant\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeActionCommand extends BaseGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'assistant:make-action';

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
        $className = $name->studly()->append($modelName);

        $path = Str::of(app_path('Actions/'.$modelName.'/'))
            ->append('/')
            ->append($className)
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents("/actions/{$name}-action.stub", [
            'NAMESPACE'   => 'App\\Actions\\'.$modelName,
            'CLASS'       => $className,
            'EVENT'       => $eventName = Str::of($name == 'create' ? 'Created' : 'Updated')->prepend($modelName),
            'MODEL'       => $modelName,
            'MODEL_CLASS' => 'App\\Models\\'.$modelName,
            'EVENT_CLASS' => "App\\Events\\{$modelName}\\{$eventName}",
        ]);

        $this->createFile($path, $contents);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Action name'],
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
        ];
    }
}
