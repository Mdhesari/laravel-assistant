<?php

namespace Mdhesari\LaravelAssistant\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
        $fillable = $this->getFillable();

        if (($module = $this->option('module')) && function_exists('module_path')) {
            $path = Str::of($namespace = module_path($module).'/Entities/');
        } else {
            $path = Str::of($namespace = app_path('Models/'));
        }

        $namespace = $this->getNamespace($namespace);

        $path = $path->append($modelName)
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents('/model.stub', [
            'NAMESPACE' => $namespace,
            'CLASS'     => $modelName,
            'FILLABLE'  => $fillable,
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

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['fields', null, InputOption::VALUE_OPTIONAL, 'The specified fields table.', null],
            ['module', null, InputOption::VALUE_OPTIONAL, 'Create for Nwidart-modules.', null],
        ];
    }

    private function getFillable()
    {
        $fields = $this->getFields();

        $fields = explode(',', $fields);

        $fillable = '';

        foreach ($fields as $field) {
            $field = explode(':', $field)[0];
            $fillable .= "\t\t'".trim($field)."',".PHP_EOL;
        }

        return Str::replaceLast(PHP_EOL, '', $fillable);
    }
}
