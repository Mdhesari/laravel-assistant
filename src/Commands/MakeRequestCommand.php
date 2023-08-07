<?php

namespace Mdhesari\LaravelAssistant\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeRequestCommand extends BaseGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'assistant:make-request';

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
        $fields = $this->getValidationRules();

        $path = Str::of(app_path('Http/Requests/'.$modelName.'/'))
            ->append($modelName)
            ->append('Request')
            ->append('.php');

        $path = $this->getCompletePath($path);

        $contents = $this->getTemplateContents('/request.stub', [
            'NAMESPACE' => 'App\\Http\\'.$modelName.'\\Requests',
            'CLASS'     => $modelName.'Request',
            'RULES'     => $fields,
        ]);

        $this->createFile($path, $contents);

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
            ['fields', null, InputOption::VALUE_OPTIONAL, 'The specified fields table.', null],
        ];
    }

    private function getValidationRules()
    {
        $fields = $this->getFields();

        $response = $this->assistant()->chat([
            'messages' => [
                [
                    "role"    => "user",
                    "content" => "I have this laravel database scheme : title:string, content:text. please give me the laravel validation rules"
                ],
                [
                    "role"    => "assistant",
                    "content" => '{"title":"required|string", "content" :"required|string", "user_id":"nullable|exists:users,id"}',
                ],
                [
                    "role"    => "user",
                    "content" => "I have this laravel database scheme : first_name:string, email:string. please give me the laravel validation rules"
                ],
                [
                    "role"    => "assistant",
                    "content" => '{"first_name":"required|string", "email" :"required|string"}',
                ],
                [
                    "role"    => "user",
                    "content" => "I have this laravel database scheme : {$fields}. please give me the laravel validation rules"
                ],
            ],
        ]);

        $fields = json_decode($response['choices'][0]['message']['content'], true);

        $rules = '';

        foreach ($fields as $key => $value) {
            $rules .= "\t\t\t'$key'\t=>\t'$value',".PHP_EOL;
        }

        return Str::replaceLast(PHP_EOL, '', $rules);
    }
}
