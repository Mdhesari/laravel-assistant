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

        $path = Str::of(app_path('Models/'))
            ->append($modelName)
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents('/model.stub', [
            'NAMESPACE' => 'App\\Models',
            'CLASS'     => $modelName,
            'FILLABLE'  => $fillable,
        ]);

        $this->createFile($path, $contents);
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
        ];
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
