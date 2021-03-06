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
            return $this->deleteCrud();
        }

        //delete
        if ($this->option('reset')) {
            return $this->resetCrud();
        }

        //create
        return $this->createCrud();
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
            $item->InputType = $item->ValidationType == 'boolean' ? 'checkbox' : $item->InputType;
            $item->InputType = Str::contains($item->Type, 'text') ? 'textarea' : $item->InputType;
            $item->InputType = $item->Type == 'blob' ? 'textarea' : $item->InputType;
            $item->InputType = $item->Type == 'time' ? 'time' : $item->InputType;

            $item->Min = Str::contains($item->Type, 'unsigned') ? 0 : null;
            $item->Max = null;
            preg_match_all('/\d+/m', $item->Type, $matches);
            if (!Str::contains($item->Type, 'decimal')) {
                $item->Max = isset($matches[0][0]) ? $matches[0][0] : null;
            }
            //options for select or radio
            $item->Options = null;
            if (Str::startsWith($item->Type, 'enum(') || Str::startsWith($item->Type, 'set(')) {
                $optionsStr = Str::between($item->Type, '(', ')');
                $item->Options = array_map(function ($i) {
                    return substr(trim($i), 1, -1);
                }, explode(',', $optionsStr));
            }
            //radio if less than 7
            if (!is_null($item->Options) && count($item->Options) < 7) {
                $item->InputType = 'radio';
            }
            //select if more than 6
            if (!is_null($item->Options) && count($item->Options) > 6) {
                $item->InputType = 'select';
            }
            //email (temporary)
            if (Str::contains($item->Type, 'email')) {
                $item->InputType = 'email';
                $item->ValidationType = 'email';
            }
            //password (temporary)
            if (Str::contains($item->Type, 'password')) {
                $item->InputType = 'password';
                $item->ValidationType = 'password';
            }
        }
        return $tableDesciption;
    }

    protected function getTableDescription($exclude = [], $limit = 0)
    {
        $res = [];
        foreach ($this->tableDescription as $t) {
            if (!in_array($t->Field, $exclude)) {
                $res[] = $t;
                if ($limit > 0 && count($res) == $limit) {
                    break;
                }
            }
        }
        return $res;
    }

    protected function getValidations($exclude = [])
    {
        $name = $this->argument('name');
        $tableName = ($table = $this->option('table')) ? $table : Str::snake(Str::plural($name));

        $validations = [];
        foreach ($this->tableDescription as $field) {
            if (!in_array($field->Field, $exclude)) {
                extract((array) $field);
                $vs = $Null == 'YES' ? "nullable" : "required";
                $vs .= "|$ValidationType";
                $vs .= is_null($Min) ? '' : "|min:${Min}";
                $vs .= is_null($Max) || $ValidationType == 'boolean' ? '' : "|max:${Max}";
                $vs .= $Key == 'UNI' ? "|unique:${tableName}" : '';
                $validations[$Field] = $vs;
            }
        }
        return $validations;
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

    protected function getTableFields($exclude = [])
    {
        $fields = [];
        foreach ($this->tableDescription as $t) {
            if (!in_array($t->Field, $exclude)) {
                $fields[] = $t->Field;
            }
        }
        return $fields;
    }

    protected function resetCrud()
    {
        $this->line('Reset crud...');

        $name = $this->argument('name');
        Artisan::call("breeze:crud ${name} -d");
        Artisan::call("breeze:crud ${name}");
    }

    protected function createCrud()
    {
        //create model
        $this->createModel();

        //create controller
        $this->createController();

        //create views
        $this->createViews();

        //create Route
        $this->createRoute();

        return 0;
    }

    protected function createController()
    {
        $this->line('Create controller...');

        $name = $this->argument('name');
        $data['name'] = $name;
        $exclude = ['id', 'updated_at', 'created_at'];
        $data['tableFields'] = !empty($tableFields = $this->getTableFields($exclude))
        ? $tableFields : ['title', 'description'];
        $data['instanceName'] = Str::camel($name);
        $data['instanceCollectionName'] = Str::plural(Str::camel($name));
        $data['resourceName'] = Str::plural(Str::lower($name));
        $data['validations'] = $this->getValidations($exclude);
        $template = $this->renderStub('controller.stub', $data);
        $path = app_path("Http/Controllers/${name}Controller.php");
        file_put_contents($path, $template);
    }

    protected function createModel()
    {
        $this->line('Create model...');

        $name = $this->argument('name');
        $data['name'] = $name;
        $exclude = ['id', 'updated_at', 'created_at'];
        $data['tableFields'] = !empty($tableFields = $this->getTableFields($exclude))
        ? $tableFields : ['title', 'description'];
        $template = $this->renderStub('model.stub', $data);
        $path = app_path("Models/${name}.php");
        file_put_contents($path, $template);
    }

    protected function createViews()
    {
        $this->line('Create views...');

        $name = $this->argument('name');
        $data['name'] = $name;
        $data['pluralName'] = Str::plural($name);
        $data['instanceName'] = Str::camel($name);
        $data['instanceCollectionName'] = Str::plural(Str::camel($name));
        $exclude = ['id', 'created_at', 'updated_at'];
        $data['tableDescription'] = $this->getTableDescription($exclude);
        $data['limit'] = 3;
        $resourceName = Str::plural(Str::lower($name));
        $data['resourceName'] = $resourceName;
        $viewsPath = resource_path("views/${resourceName}");
        File::ensureDirectoryExists($viewsPath);

        //create
        $createTemplate = $this->renderStub('create.stub', $data);
        $createViewPath = "$viewsPath/create.blade.php";
        file_put_contents($createViewPath, $createTemplate);

        //edit
        $editTemplate = $this->renderStub('edit.stub', $data);
        $editViewPath = "$viewsPath/edit.blade.php";
        file_put_contents($editViewPath, $editTemplate);

        //index
        $indexTemplate = $this->renderStub('index.stub', $data);
        $indexViewPath = "$viewsPath/index.blade.php";
        file_put_contents($indexViewPath, $indexTemplate);

        //show
        $showTemplate = $this->renderStub('show.stub', $data);
        $showViewPath = "$viewsPath/show.blade.php";
        file_put_contents($showViewPath, $showTemplate);
    }

    protected function renderStub($filename, $data = [])
    {
        $stubPath = $this->getStub($filename);
        $stub = file_get_contents($stubPath);

        $keywords = [
            'php',
            'endphp',
            'if',
            'elseif',
            'else',
            'endif',
            'unless',
            'endunless',
            'isset',
            'endisset',
            'empty',
            'endempty',
            'switch',
            'case',
            'break',
            'default',
            'endswitch',
            'for',
            'endfor',
            'foreach',
            'endforeach',
            'forelse',
            'endforelse',
            'while',
            'endwhile',
            'continue',
        ];

        $encodes = [
            '/<\?php/m' => '[PHP_OPEN_TAG]',
            '/\?>/m' => '[PHP_CLOSE_TAG]',
            '/<x-/m' => '[BC_OPEN_TAG]',
            '/<\/x-/m' => '[BC_CLOSE_TAG]',
            '/{{--/m' => '[COMMENT_OPEN_TAG]',
            '/--}}/m' => '[COMMENT_CLOSE_TAG]',
            '/(?<!@)({{ *)(\w+?)( *}})/m' => '[STUBVAR_OPEN_TAG]$2[STUBVAR_CLOSE_TAG]',
            '/(?<!@)({{ *)(.+?)( *}})/m' => '[VAR_OPEN_TAG]$2[VAR_CLOSE_TAG]',
            '/(@?{{ *)(.+?)( *}})/m' => '@$1$2$3',
            '/(@?{!! *)(.+?)( *!!})/m' => '@$1$2$3',
            '/(@\w+)/m' => '@$1',
            '/^\s+$/m' => '',
            '/([ \t]*)(#)(' . implode('|', $keywords) . ')/m' => '$2$3',
            '/(#)(' . implode('|', $keywords) . ')/m' => '@$2',
        ];

        foreach ($encodes as $key => $value) {
            $stub = preg_replace($key, $value, $stub);
        }

        $stub = preg_replace('/\[STUBVAR_OPEN_TAG\](\w+?)\[STUBVAR_CLOSE_TAG\]/m', '{{ \$$1 }}', $stub);

        $tempBlade = '__render_stub_temp__';
        $tempPath = resource_path("views/${tempBlade}.blade.php");
        file_put_contents($tempPath, $stub);
        $tmpl = view($tempBlade, $data)->render();

        // unlink(resource_path("views/${tempBlade}.blade.php"));

        $decodes = [
            '[PHP_OPEN_TAG]' => '<?php',
            '[PHP_CLOSE_TAG]' => '?>',
            '[BC_OPEN_TAG]' => '<x-',
            '[BC_CLOSE_TAG]' => '</x-',
            '[COMMENT_OPEN_TAG]' => '{{--',
            '[COMMENT_CLOSE_TAG]' => '--}}',
            '[STUBVAR_OPEN_TAG]' => '{{ ',
            '[STUBVAR_CLOSE_TAG]' => ' }}',
            '[VAR_OPEN_TAG]' => '{{ ',
            '[VAR_CLOSE_TAG]' => ' }}',
        ];

        foreach ($decodes as $key => $value) {
            $tmpl = str_replace($key, $value, $tmpl);
        }

        return $tmpl;
    }

    protected function createRoute()
    {
        $this->line('Create route...');

        $name = $this->argument('name');
        $resourceName = Str::plural(Str::lower($name));
        $routePath = base_path('routes/web.php');
        $controllerNamespace = "use App\Http\Controllers\\${name}Controller;";
        $this->insertToFile($controllerNamespace, $routePath, 3);
        $resourceRoute = "Route::resource('${resourceName}', ${name}Controller::class);";
        $this->insertToFile($resourceRoute, $routePath, 0);
    }

    protected function deleteCrud()
    {
        $this->line('Delete crud...');

        $name = $this->argument('name');
        $resourceName = Str::lower(Str::plural($name));

        //Delete route
        $this->line('Delete route...');
        $routePath = base_path('routes/web.php');
        $controllerNamespace = "\nuse App\Http\Controllers\\${name}Controller;";
        $this->replaceInFile($controllerNamespace, '', $routePath);
        $resourceRoute = "\nRoute::resource('${resourceName}', ${name}Controller::class);";
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
        $viewsPath = resource_path("views/${resourceName}");
        $this->deleteIfExists($viewsPath);

        return 0;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub($filename)
    {
        $layout = 'default';
        $relativePath = "/stubs/make-crud/${layout}/${filename}";
        return $this->resolveStubPath($relativePath);
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = base_path(trim($stub, '/')))
        ? $customPath : __DIR__ . '/../..' . $stub;
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
