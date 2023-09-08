<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;



class BuildEntity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:entity {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add controller, model, route and migration for an entity';


    /**
     * Return the Plural Capitalize Name
     * @param $name
     * @return string
     */
    public function getPluralClassName($name)
    {
        return ucwords(Pluralizer::plural($name));
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $name = strtolower($this->argument('name'));
        $file = storage_path('app\\public\\entities-schemas\\' . $name . '.json');
        $content = file_get_contents($file);
        $json = json_decode($content, true);

        $this->call('make:entity-controller', [
            'name' => $this->argument('name'),
            'label' => $json['label']
        ]);

        $this->call('make:entity-request', [
            'name' => $this->argument('name'),
            'label' => $json['label']
        ]);

        $this->call('make:entity-model', [
            'name' => $this->argument('name'),
            'label' => $json['label']
        ]);

        $this->call('make:entity-migration', [
            'name' => $this->argument('name'),
            'label' => $json['label']
        ]);

        $this->call('make:entity-seeder', [
            'name' => $this->argument('name'),
            'label' => $json['label']
        ]);

        // Edit the routes file

        // Get the routes/api.php file
        $routes = file_get_contents(base_path('routes/api.php'));

        // Check if the route already exists
        if (strpos($routes, $this->getPluralClassName($this->argument('name'))) !== false) {
            $this->error('Route already exists');
            return Command::SUCCESS;
        }

        // Replace the /* Add new routes here */ with the new route
        $routes = str_replace(
            '/* Add new routes here */',
            'Route::resource(\'' . Pluralizer::plural(strtolower($this->argument('name'))) . "', 'App\\Http\\Controllers\\" . $this->getPluralClassName($this->argument('name')) . "Controller');\n\t".
            'Route::get(\'get-all-' . Pluralizer::plural(strtolower($this->argument('name'))) . "', 'App\\Http\\Controllers\\" . $this->getPluralClassName($this->argument('name')) . "Controller@getAll');\n\n\t/* Add new routes here */",
            $routes
        );

        // Save the routes/api.php file
        file_put_contents(base_path('routes/api.php'), $routes);


        // Make php artisan migrate
        // $this->call('migrate');


        return Command::SUCCESS;
    }
}
