<?php

namespace Mdhesari\LaravelAssistant\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

class MakeModelCommand extends BaseGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'assistant:make-model';

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
        $modelName = $this->argument('model');

        $studlyModelName = Str::studly($modelName);

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
