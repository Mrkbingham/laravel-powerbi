<?php

namespace InterWorks\PowerBI;

use InterWorks\PowerBI\Commands\PowerBICommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PowerBIServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-powerbi')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_powerbi_table')
            ->hasCommand(PowerBICommand::class);
    }

    /**
     * Register the package services.
     *
     * Registers the PowerBI factory class as a singleton in the container.
     * This allows the facade to resolve the factory and maintain state
     * across multiple calls.
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton(PowerBI::class, function () {
            return new PowerBI;
        });
    }
}
