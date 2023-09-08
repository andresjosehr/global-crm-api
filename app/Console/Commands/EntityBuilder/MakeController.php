<?php

namespace App\Console\Commands\EntityBuilder;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;
use Illuminate\Console\Command;

class MakeController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * *
     * @var string
     */
    protected $signature = 'make:entity-controller {name} {label}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add controller for especific entity';


    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath()
    {
        return __DIR__ . '/../../../../stubs/entity-controller.stub';
    }

    /**
    **
    * Map the stub variables present in stub to its value
    *
    * @return array
    *
    */
    public function getStubVariables()
    {
        return [
            'namespace'        => 'App\\Http\\Controllers',
            'class'            => $this->getPluralClassName($this->argument('name')),
            'name'             => $this->argument('name'),
            'label'            => $this->argument('label'),
            'camelName'        => $this->getCamelCaseName($this->argument('name')),
            'editableFields'   => $this->getEditableFieldsSetring(),
            'searchableFields' => $this->getSearchableFieldsSetring(),
        ];
    }


    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public function getSourceFile()
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }


    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public function getStubContents($stub , $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace)
        {
            $contents = str_replace('{{ '.$search.' }}' , $replace, $contents);
        }

        return $contents;

    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public function getSourceFilePath()
    {
        return base_path('App\\Http\\Controllers') .'\\' .$this->getPluralClassName($this->argument('name')) . 'Controller.php';
    }

    /**
     * Return the Singular Capitalize Name
     * @param $name
     * @return string
     */
    public function getSingularClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

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
     * Return the camelCase Name
     * @param $name
     * @return string
     */
    public function getCamelCaseName($name)
    {
        return lcfirst($this->getSingularClassName($name));
    }


    /**
     * Return the string used to edit or create fields of the entity
     * @param $name
     * @return string
     */
    public function getEditableFieldsSetring()
    {
        $name = strtolower($this->argument('name'));
        $file = storage_path('app\\public\\entities-schemas\\' . $name . '.json');
        $content = file_get_contents($file);
        $json = json_decode($content, true);

        $string = '';
        foreach ($json['fields'] as $field) {
            if($field['editable']) {
                if($field['inputType'] == 'date'){
                    $string .= "$$name->$field[name] = explode('T', \$request->$field[name])[0];\n\t\t";
                    continue;
                }
                $string .= "$$name->$field[name] = \$request->$field[name];\n\t\t";
            }
        }
        return $string;
    }

    /**
     * Return the string used to search fields of the entity
     * @param $name
     * @return string
     */
    public function getSearchableFieldsSetring()
    {
        $name = strtolower($this->argument('name'));
        $file = storage_path('app\\public\\entities-schemas\\' . $name . '.json');
        $content = file_get_contents($file);
        $json = json_decode($content, true);

        $string = '';
        if($json['searchableList']){
            $string = 'when(($request->input("searchString")!=""), function($q) use ($request){'."\n\t\t\t";
            $string .= '$q';
            foreach ($json['fields'] as $field) {
                if(isset($field['searchable'])) {
                    if($field['searchable']) {
                        $string .= "\n\t\t\t".'->orWhere("'.$field['name'].'", "like", "%".$request->searchString."%")';
                    }
                }
            }
            $string .= ';'."\n\t\t".'})->';

            foreach ($json['fields'] as $field) {
                if(isset($field['searchable'])) {
                    if($field['searchable']) {
                        $string .= "\n\t\t".'when(($request->input("'.$field['name'].'")!=""), function($q) use ($request){'."\n\t\t\t";
                        $string .= '$q->where("'.$field['name'].'", "like", "%".$request->'.$field['name'].'."%");';
                        $string .= "\n\t\t".'})->';
                    }
                }
            }
        }
        return $string;
    }



    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->getSourceFilePath();

        $this->makeDirectory(dirname($path));

        $contents = $this->getSourceFile();

        if (!$this->files->exists($path)) {
            $this->files->put($path, $contents);
            $this->info("File : {$path} created");
        } else {
            $this->error('Controller already exists');
        }

    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    // public function handle()
    // {
    //     $this->info('Hello World');
    //     // to lowercase
    //     $name = strtolower($this->argument('name'));
    //     // Get file in storage/app/public/entities-schema/people.json
    //     $file = storage_path('app\\public\\entities-schemas\\' . $name . '.json');
    //     // Get file content
    //     $content = file_get_contents($file);
    //     // Decode json
    //     $json = json_decode($content, true);

    //     $this->info(print_r($json, true));


    //     return Command::SUCCESS;
    // }
}
