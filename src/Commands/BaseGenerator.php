<?php

namespace Mdhesari\LaravelAssistant\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Mdhesari\LaravelAssistant\Exceptions\FileAlreadyExistException;
use Mdhesari\LaravelAssistant\Generators\FileGenerator;
use Mdhesari\LaravelAssistant\LaravelAssistant;
use Mdhesari\LaravelAssistant\Support\Migrations\SchemaParser;
use Mdhesari\LaravelAssistant\Support\Stub;

abstract class BaseGenerator extends Command
{
    /**
     * Get controller name.
     *
     * @param string $path
     * @return string
     */
    protected function getDestinationFilePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * @param string $name
     * @param array $replaces
     * @return string
     */
    protected function getTemplateContents(string $name, array $replaces): string
    {
        return (new Stub($name, $replaces))->render();
    }

    /**
     * @param string $path
     * @param string $contents
     */
    protected function createFile(string $path, string $contents): void
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

    /**
     * @param string $path
     * @return string
     */
    protected function getCompletePath(string $path): string
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
     * @param array $fields
     * @return SchemaParser
     */
    public function getSchemaParser(?string $fields = null): SchemaParser
    {
        return new SchemaParser($fields);
    }

    protected function getFields()
    {
        $fields = $this->option('fields') ?: null;

        is_null($fields) && $fields = $this->guessFields($this->argument('model'));

        return $fields;
    }

    protected function guessFields(string $model)
    {
        $response = $this->assistant()->chat([
            'messages' => [
                [
                    "role"    => "user",
                    "content" => "please give me fields for designing users laravel database"
                ],
                [
                    "role"    => "assistant",
                    "content" => 'first_name:string:nullable,last_name:string:nullable,email:string:unique,password:string:nullable,address:string',
                ],
                [
                    "role"    => "user",
                    "content" => "please give me fields for designing posts laravel database"
                ],
                [
                    "role"    => "assistant",
                    "content" => 'title:string:nullable,body:string:nullable,author_id:foreignId:nullable,meta:json:nullable,reading_time:string,nullable',
                ],
                [
                    "role"    => "user",
                    "content" => "please give me fields for designing ${model} laravel database"
                ],
            ],
        ]);

        return $response['choices'][0]['message']['content'] ?? '';
    }

    protected function assistant()
    {
        return app(LaravelAssistant::class);
    }

    protected function getNamespace(string $namespace): array|string
    {
        $namespace = Str::replace('/', '\\', $namespace);

        $projectDirName = explode('/', app_path());
        $projectDirName = $projectDirName[count($projectDirName) - 2];

        $namespace = substr($namespace, strpos($namespace, $projectDirName) + strlen($projectDirName));

        if ($namespace[0] === '\\') {
            $namespace = substr($namespace, 1);
        }

        if ($namespace[strlen($namespace) - 1] === '\\') {
            $namespace = substr($namespace, 0, strlen($namespace) - 1);
        }

        return ucfirst($namespace);
    }

    /**
     * @param string $modelName
     * @return string
     */
    protected function getModelNamespace(string $modelName): string
    {
        try {
            $namespace = get_class(app($modelName));

//            $namespace = substr($namespace, 0, strpos($namespace, $modelName) - 1);
        } catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
            if ($module = $this->option('module'))
                $namespace = "Modules\\{$module}\\Entities\\{$modelName}";
            else
                $namespace = 'App\\Models\\'.$modelName;
        }

        return $namespace;
    }

    /**
     * @param string $eventName
     * @return string
     */
    protected function getRequestNamespace(string $requestName): string
    {
        try {
            $namespace = get_class(app($requestName));
        } catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
            if ($module = $this->option('module'))
                $namespace = "Modules\\{$module}\\Http\\Requests\\".$requestName;
            else
                $namespace = 'App\\Http\\Requests\\'.$requestName;
        }

        return $namespace;
    }

    /**
     * @param string $actionName
     * @return string
     */
    protected function getActionNamespace(string $actionName): string
    {
        try {
            $namespace = get_class(app($actionName));
        } catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
            if ($module = $this->option('module'))
                $namespace = "Modules\\{$module}\\Actions\\".$actionName;
            else
                $namespace = 'App\\Actions\\'.$actionName;
        }

        return $namespace;
    }

    /**
     * @param string $eventName
     * @return string
     */
    protected function getEventNamespace(string $eventName): string
    {
        try {
            $namespace = get_class(app($eventName));
        } catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
            if ($module = $this->option('module'))
                $namespace = "Modules\\{$module}\\Events\\".$eventName;
            else
                $namespace = 'App\\Events\\'.$eventName;
        }

        return $namespace;
    }
}
