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

        $studlyModelName = Str::studly($modelName);

        $name = Str::of($name);
        $className = $name->studly()->append($studlyModelName);

        $path = Str::of(app_path('Actions/'.$studlyModelName.'/'))
            ->append('/')
            ->append($className)
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents("/{$name}-action.stub", [
            'NAMESPACE' => 'App\\Actions\\'.$studlyModelName,
            'CLASS'     => $className,
            'EVENT'     => Str::of($name == 'create' ? 'Created' : 'Updated')->prepend($studlyModelName),
            'MODEL'     => $studlyModelName,
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
