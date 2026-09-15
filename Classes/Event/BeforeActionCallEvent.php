<?php

namespace Nitsan\BlogSystem\Event;

class BeforeActionCallEvent
{
    protected string $controllerClassName;
    protected string $actionMethodName;
    protected array $preparedArguments;

    public function __construct(string $controllerClassName, string $actionMethodName, array $preparedArguments)
    {
        $this->controllerClassName = $controllerClassName;
        $this->actionMethodName = $actionMethodName;
        $this->preparedArguments = $preparedArguments;
    }

    public function getControllerClassName(): string
    {
        return $this->controllerClassName;
    }

    public function getActionMethodName(): string
    {
        return $this->actionMethodName;
    }

    public function getPreparedArguments(): array
    {
        return $this->preparedArguments;
    }
}