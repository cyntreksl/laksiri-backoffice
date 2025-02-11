<?php

namespace App\Classes;

class WhatsAppTemplate
{
    protected string $name;

    protected string $language = 'en';

    protected array $components = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function addHeader(array $parameters): self
    {
        $headerParams = array_map(fn ($param) => [
            'type' => 'text',
            'parameter_name' => $param['name'],
            'text' => $param['value'],
        ], $parameters);

        $this->components[] = [
            'type' => 'header',
            'parameters' => $headerParams,
        ];

        return $this;
    }

    public function addBody(array $parameters): self
    {
        $bodyParams = array_map(fn ($param) => [
            'type' => 'text',
            'parameter_name' => $param['name'],
            'text' => $param['value'],
        ], $parameters);

        $this->components[] = [
            'type' => 'body',
            'parameters' => $bodyParams,
        ];

        return $this;
    }

    public function addUrlButton(int $index, string $text): self
    {
        $this->components[] = [
            'type' => 'button',
            'sub_type' => 'url',
            'index' => $index,
            'parameters' => [[
                'type' => 'text',
                'text' => $text,
            ]],
        ];

        return $this;
    }

    public function getTemplate(): array
    {
        return [
            'name' => $this->name,
            'language' => ['code' => $this->language],
            'components' => $this->components,
        ];
    }
}
