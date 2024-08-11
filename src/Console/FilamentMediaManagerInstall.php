<?php

namespace TomatoPHP\FilamentMediaManager\Console;

use Illuminate\Console\Command;
use TomatoPHP\ConsoleHelpers\Traits\RunCommand;

class FilamentMediaManagerInstall extends Command
{
    use RunCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'filament-media-manager:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'install package and publish assets';

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Publish Vendor Assets');

        \Artisan::call('vendor:publish', [
            '--tag' => 'medialibrary-migrations'
        ]);

        \Artisan::call('migrate');

        \Artisan::call('optimize:clear');

        $this->info('Filament Media Manager installed successfully.');
    }
}
