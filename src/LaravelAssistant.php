<?php

namespace Mdhesari\LaravelAssistant;

class LaravelAssistant
{
    public function chat(array $args, $stream = null)
    {
        $args = [
            'model'             => 'gpt-3.5-turbo-16k',
            'temperature'       => 1.0,
//            'max_tokens'        => 4000,
            'frequency_penalty' => 0,
            'presence_penalty'  => 0,
            ...$args
        ];

        return $this->parse($this->ai()->chat($args));
    }

    private function ai()
    {
        return app('ai');
    }

    private function parse(string $completed)
    {
        return json_decode($completed, true);
    }
}
