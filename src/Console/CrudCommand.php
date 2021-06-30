<?php

namespace Tediscript\BreezeCrud\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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
                                {--t|table= : Custom table name}
                                {--d|delete : Delete Breeze CRUD by name}
                                {--r|reset : Delete and re-generate Breeze CRUD by name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CRUD based on Breeze starter kits';

    protected $tableDesc = [];
    protected $tableDescription = [];

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
        $tableName = $this->getTableName();

        try {
            $tableDesc = DB::select("describe ${tableName}");
            $this->tableDescription = $this->mapTableDescription($tableDesc);
        } catch (Exception $e) {}

        $name = $this->argument('name');

        // delete
        if ($this->option('delete')) {
            return $this->deleteCrud($name);
        }

        //delete
        if ($this->option('reset')) {
            return $this->resetCrud($name);
        }

        //create
        return $this->createCrud($name);
    }

    protected function getTableName()
    {
        $name = $this->argument('name');
        return ($table = $this->option('table')) ? $table : Str::snake(Str::plural($name));
    }

    protected function mapTableDescription($tableDesciption = [])
    {
        foreach ($tableDesciption as &$item) {
            $item->ValidationType = 'string';
            $item->InputType = 'text';
            $numerics = [
                'int', // including: 'tinyint', 'smallint', 'mediumint', 'bigint',
                'decimal',
                'numeric',
                'float',
                'double',
                'real',
            ];
            foreach ($numerics as $numeric) {
                if (str_contains($item->Type, $numeric)) {
                    $item->ValidationType = 'numeric';
                    $item->InputType = 'number';
                    break;
                }
            }

            if (Str::startsWith($item->Type, 'date')) {
                $item->ValidationType = 'date_format:Y-m-d';
                $item->InputType = 'date';
            }

            if (Str::startsWith($item->Type, 'timestamp') || Str::startsWith($item->Type, 'datetime')) {
                $item->ValidationType = 'date_format:Y-m-d H:i:s';
                $item->InputType = 'datetime-local';
            }

            //override
            $item->ValidationType = $item->Type == 'tinyint(1)' ? 'boolean' : $item->ValidationType;
            $item->InputType = $item->ValidationType == 'boolean' ? 'check' : $item->InputType;
            $item->InputType = Str::contains($item->Type, 'text') ? 'textarea' : $item->InputType;
            $item->InputType = $item->Type == 'blob' ? 'textarea' : $item->InputType;
            $item->InputType = $item->Type == 'year' ? 'year' : $item->InputType;
            
            $item->Min = Str::contains($item->Type, 'unsigned') ? 0 : null;
            $item->Max = null;
            preg_match_all('/\d+/m', $item->Type, $matches);
            if (!Str::contains($item->Type, 'decimal')) {
                $item->Max = isset($matches[0][0]) ? $matches[0][0] : null;
            }
        }
        return $tableDesciption;
    }

    protected function getValidationsStr($exclude = [])
    {
        $name = $this->argument('name');
        $tableName = ($table = $this->option('table')) ? $table : Str::snake(Str::plural($name));

        $vs = '';
        foreach ($this->tableDescription as $field) {
            if (!in_array($field->Field, $exclude)) {
                extract((array) $field);
                $vs .= "'${Field}' => '";
                $vs .= $Null == 'YES' ? "nullable" : "required";
                $vs .= "|$ValidationType";
                $vs .= is_null($Min) ? '' : "|min:${Min}";
                $vs .= is_null($Max) || $ValidationType == 'boolean' ? '' : "|max:${Max}";
                $vs .= $Key == 'UNI' ? "|unique:${tableName}" : '';
                $vs .= "',\n\t\t\t";
            }
        }
        return $vs ? $vs : "'title' => 'required|string',\n\t\t\t'description' => 'nullable|string',";
    }

    protected function getTableFieldsStr($exclude = [])
    {
        $tableFieldsStr = '';
        $fields = [];
        foreach ($this->tableDescription as $t) {
            if (!in_array($t->Field, $exclude)) {
                $fields[] = $t->Field;
            }
        }
        if (!empty($fields)) {
            $tableFieldsStr = "'" . implode("',\n\t\t'", $fields) . "',";
        }

        return empty($tableFieldsStr) ? "'title',\n\t\t'description'," : $tableFieldsStr;
    }

    protected function resetCrud($name)
    {
        Artisan::call("breeze:crud ${name} -d");
        Artisan::call("breeze:crud ${name}");
    }

    protected function createCrud($name)
    {
        //create controller
        $this->createController($name);

        //create model
        $this->createModel($name);

        //create views
        // $this->createViews($name);

        //create Route
        // $this->createRoute($name);

        return 0;
    }

    protected function createController($name)
    {
        $this->line('Create controller...');
        $exclude = ['id', 'updated_at', 'created_at'];
        $validationsStr = $this->getValidationsStr($exclude);
        $cName = Str::camel($name);
        $cpName = Str::plural($cName);
        $lName = Str::lower($name);
        $lpName = Str::plural($lName);
        $controllerPath = app_path("Http/Controllers/${name}Controller.php");
        $controllerStubPath = __DIR__ . '/../../stubs/default/app/Http/Controllers/Controller.stub';
        $controllerStubStr = file_get_contents($controllerStubPath);
        $controllerStubStr = Str::replace('__NAME__', $name, $controllerStubStr);
        $controllerStubStr = Str::replace('__LNAME__', $lName, $controllerStubStr);
        $controllerStubStr = Str::replace('__LPNAME__', $lpName, $controllerStubStr);
        $controllerStubStr = Str::replace('__CNAME__', $cName, $controllerStubStr);
        $controllerStubStr = Str::replace('__CPNAME__', $cpName, $controllerStubStr);
        $controllerStubStr = Str::replace('__VALIDATIONSSTR__', $validationsStr, $controllerStubStr);
        file_put_contents($controllerPath, $controllerStubStr);
    }

    protected function createModel($name)
    {
        $this->line('Create model...');
        $exclude = ['id', 'updated_at', 'created_at'];
        $tableFields = ($tableFieldsstr = $this->getTableFieldsStr($exclude)) ? $tableFieldsstr : "'title',\n\t\t'description',";
        $modelPath = app_path("Models/${name}.php");
        $modelStubPath = __DIR__ . '/../../stubs/default/app/Models/Model.stub';
        $modelStubStr = file_get_contents($modelStubPath);
        $modelStubStr = Str::replace('__NAME__', $name, $modelStubStr);
        $modelStubStr = Str::replace('__TABLEFIELDS__', $tableFields, $modelStubStr);
        file_put_contents($modelPath, $modelStubStr);
    }

    protected function createViews($name)
    {
        $this->line('Create views...');
        $lName = Str::lower($name);
        $lpName = Str::plural($lName);
        $pName = Str::plural($name);
        $cName = Str::camel($name);
        $viewsPath = resource_path("views/${lpName}");
        File::ensureDirectoryExists($viewsPath);
        //index
        $indexStubPath = __DIR__ . '/../../stubs/default/resources/views/default/index.blade.php';
        $indexTemplate = file_get_contents($indexStubPath);
        $indexTemplate = Str::replace('__PNAME__', $pName, $indexTemplate);
        $indexTemplate = Str::replace('__LPNAME__', $lpName, $indexTemplate);
        $indexTemplate = Str::replace('__CNAME__', $cName, $indexTemplate);
        $indexViewPath = "$viewsPath/index.blade.php";
        file_put_contents($indexViewPath, $indexTemplate);
        //create
        $createStubPath = __DIR__ . '/../../stubs/default/resources/views/default/create.blade.php';
        $createTemplate = file_get_contents($createStubPath);
        $createTemplate = Str::replace('__NAME__', $name, $createTemplate);
        $createTemplate = Str::replace('__PNAME__', $pName, $createTemplate);
        $createTemplate = Str::replace('__LPNAME__', $lpName, $createTemplate);
        $createViewPath = "$viewsPath/create.blade.php";
        file_put_contents($createViewPath, $createTemplate);
        //show
        $showStubPath = __DIR__ . '/../../stubs/default/resources/views/default/show.blade.php';
        $showTemplate = file_get_contents($showStubPath);
        $showTemplate = Str::replace('__NAME__', $name, $showTemplate);
        $showTemplate = Str::replace('__PNAME__', $pName, $showTemplate);
        $showTemplate = Str::replace('__LPNAME__', $lpName, $showTemplate);
        $showTemplate = Str::replace('__CNAME__', $cName, $showTemplate);
        $showViewPath = "$viewsPath/show.blade.php";
        file_put_contents($showViewPath, $showTemplate);
        //edit
        $editStubPath = __DIR__ . '/../../stubs/default/resources/views/default/edit.blade.php';
        $editTemplate = file_get_contents($editStubPath);
        $editTemplate = Str::replace('__NAME__', $name, $editTemplate);
        $editTemplate = Str::replace('__PNAME__', $pName, $editTemplate);
        $editTemplate = Str::replace('__LPNAME__', $lpName, $editTemplate);
        $editTemplate = Str::replace('__CNAME__', $cName, $editTemplate);
        $editViewPath = "$viewsPath/edit.blade.php";
        file_put_contents($editViewPath, $editTemplate);
    }

    protected function createRoute($name)
    {
        $this->line('Create route...');
        $lName = Str::lower($name);
        $lpName = Str::plural($lName);
        $routePath = base_path('routes/web.php');
        $controllerNamespace = "use App\Http\Controllers\\${name}Controller;";
        $this->insertToFile($controllerNamespace, $routePath, 3);
        $resourceRoute = "Route::resource('${lpName}', ${name}Controller::class);";
        $this->insertToFile($resourceRoute, $routePath, 0);
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
    protected function insertToFile($string, $path, $line = 0)
    {
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
