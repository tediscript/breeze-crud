<?php

namespace Tediscript\BreezeCrud\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'breeze:crud
                                {name : The name of the model class}
                                {--d|delete : Delete Breeze CRUD by name}
                                {--m|migration : Create a new migration file for the model}
                                {--s|seed : Create a new seeder file for the model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CRUD based on Breeze starter kit';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');

        //delete
        if ($this->option('delete')) {
            return $this->deleteCrud($name);
        }

        //create controller
        $this->line('Create controller...');
        $controllerCommand = "make:controller ${name}Controller --resource";
        Artisan::call($controllerCommand);

        //create model
        $this->line('Create model...');
        $modelCommand = "make:model ${name}";
        if ($this->option('migration')) {
            $modelCommand .= ' --migration';
        }
        if ($this->option('migration') && $this->option('seed')) {
            $modelCommand .= ' --migration --seed';
        }
        Artisan::call($modelCommand);

        //create views
        $this->line('Create views...');
        $lName = Str::lower($name);
        $lpName = Str::plural($lName);
        $viewsPath = resource_path("views/${lpName}");
        File::ensureDirectoryExists($viewsPath);
        //index
        $indexStubPath = resource_path("stubs/index.blade.php");
        $indexTemplate = file_get_contents($indexStubPath);
        $indexViewPath = "$viewsPath/index.blade.php";
        file_put_contents($indexViewPath, $indexTemplate);
        //create
        $createStubPath = resource_path("stubs/create.blade.php");
        $createTemplate = file_get_contents($createStubPath);
        $createViewPath = "$viewsPath/create.blade.php";
        file_put_contents($createViewPath, $createTemplate);
        //show
        $showStubPath = resource_path("stubs/show.blade.php");
        $showTemplate = file_get_contents($showStubPath);
        $showViewPath = "$viewsPath/show.blade.php";
        file_put_contents($showViewPath, $showTemplate);
        //edit
        $editStubPath = resource_path("stubs/edit.blade.php");
        $editTemplate = file_get_contents($editStubPath);
        $editViewPath = "$viewsPath/edit.blade.php";
        file_put_contents($editViewPath, $editTemplate);

        //create Route
        $this->line('Create route...');
        $routePath = base_path('routes/web.php');
        $controllerNamespace = "use App\Http\Controllers\\${name}Controller;";
        $this->insertToFile($controllerNamespace, $routePath, 3);
        $resourceRoute = "Route::resource('${lpName}', ${name}Controller::class);";
        $this->insertToFile($resourceRoute, $routePath, 0);

        return 0;
    }

    protected function deleteCrud($name)
    {
        $lName = Str::lower($name);
        $lpName = Str::plural($lName);

        //Delete route
        $this->line('Delete route...');
        $routePath = base_path('routes/web.php');
        $controllerNamespace = "\nuse App\Http\Controllers\\${name}Controller;";
        $this->replaceInFile($controllerNamespace, '', $routePath);
        $resourceRoute = "\nRoute::resource('${lpName}', ${name}Controller::class);";
        $this->replaceInFile($resourceRoute, '', $routePath);

        //delete controller
        $this->line('Delete controller...');
        $controllerPath = app_path("Http/Controllers/${name}Controller.php");
        $this->deleteIfExists($controllerPath);

        //delete model
        $this->line('Delete model...');
        $modelPath = app_path("Models/${name}.php");
        $this->deleteIfExists($modelPath);

        //delete views
        $this->line('Delete views...');
        $viewsPath = resource_path("views/${lpName}");
        $this->deleteIfExists($viewsPath);

        return 0;
    }

    /**
     * Delete file or folder if exists and writable
     * 
     * @param string $path
     * @return void
     */
    protected function deleteIfExists($path) 
    {
        if (File::isDirectory($path) && File::isWritable($path)) {
            File::deleteDirectory($path);
        }

        if (File::isFile($path) && File::isWritable($path)) {
            File::delete($path);
        }
    }

    /**
     * Replace a given string within a given file.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $path
     * @return void
     */
    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    /**
     * Insert a given string to line number of file. 
     * Line number starts from 1 and default is 0 (last line).
     * 
     * @param string $string
     * @param string $path
     * @param int $line
     * @return void
     */
    protected function insertToFile($string, $path, $line = 0) {
        $contents = file_get_contents($path);
        $arr = explode("\n", $contents);
        $count = count($arr);
        $line = $line > 0 ? $line - 1 : $count + $line;

        $first = array_slice($arr, 0, $line);
        $second = array_slice($arr, $line);
        $merged = array_merge($first, [$string], $second);
        $data = implode("\n", $merged);
        file_put_contents($path, $data);
    }
}
