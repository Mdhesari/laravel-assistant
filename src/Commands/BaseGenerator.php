<?php

namespace Mdhesari\LaravelAssistant\Commands;

use Illuminate\Console\Command;
use Mdhesari\LaravelAssistant\Exceptions\FileAlreadyExistException;
use Mdhesari\LaravelAssistant\Generators\FileGenerator;
use Mdhesari\LaravelAssistant\Support\Migrations\SchemaParser;
use Mdhesari\LaravelAssistant\Support\Stub;

abstract class BaseGenerator extends Command
{
    /**
     * Get controller name.
     *
     * @param $entity
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
    public function getSchemaParser(string $fields = ''): SchemaParser
    {
        return new SchemaParser($fields);
    }
}
