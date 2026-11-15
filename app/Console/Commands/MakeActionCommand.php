<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Override;

#[Description('Create a new action class')]
#[Signature('make:action {name : The name of the action class}')]
final class MakeActionCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $type = 'Action';

    #[Override]
    public function handle(): ?bool
    {
        $actionName = $this->getNameInput();
        $actionPath = app_path("Actions/{$actionName}.php");

        if (File::exists($actionPath)) {
            $this->fail("The action {$actionName} already exists.");
        }

        return parent::handle();
    }

    protected function getStub(): string
    {
        return base_path('/stubs/action.stub');
    }

    #[Override]
    protected function getNameInput(): string
    {
        return Str::of($this->argument('name'))
            ->trim()
            ->before('.php')
            ->before('Action')
            ->append('Action')
            ->toString();
    }

    /**
     * @param  string  $rootNamespace
     */
    #[Override]
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Actions';
    }
}
