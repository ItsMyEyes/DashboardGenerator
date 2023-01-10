<?php

namespace KiyoraDashboard\Commands;

use KiyoraDashboard\Models\Permission;
use KiyoraDashboard\Models\PermissionGroup;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeCrud extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud
                            {name : Table name}
                            {--route= : Custom route name}
                            {--route-only= : Only Route y/n}
                            {--permission-only= : Only permission y/n}
                            {--relayouts= : relayouts y/n}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create bootstrap CRUD operations';

    protected $controller = '';

    protected $routeName = '';

    /**
     * Execute the console command.
     *
     * @return bool|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     */
    public function handle()
    {
        $this->banner();
        $this->info('Running Crud Generator ...');

        $this->table = $this->getNameInput();
        $relayouts = $this->option('relayouts');
        if ($relayouts != "" && in_array($relayouts, array("y", 'Y')) || !(view()->exists($this->layout))) {
            $layout = $this->ask('Whats Layout you want? ', 'adminlte');
            if ($layout == "") {
                $this->error('Error: layout not found');
                return false;
            }
            $this->layout = $layout;
            $this->buildLayout();
            return true;
        }

        // If table not exist in DB return
        if (!$this->tableExists()) {
            $this->error("`{$this->table}` table not exist");

            return false;
        }

        // Build the class name from table name
        $this->name = $this->_buildClassName();
        $this->routeName = str_replace('_', '-', Str::kebab(Str::plural($this->table)));



        $route = $this->option('route-only');
        if ($route != '') {
            $this->buildRoute();
            return true;
        }

        $permission = $this->option('permission-only');
        if ($permission != '') {
            $this->buildPermission();
            return true;
        }

        // Generate the crud
        $this->buildOptions()
            ->buildController()
            ->buildModel()
            ->buildViews()
            ->buildRoute();
        $this->buildPermission();
        $this->buildNav();

        $this->info('Created Successfully.');

        return true;
    }

    /**
     * Build the Controller Class and save in app/Http/Controllers.
     *
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     */
    protected function buildController()
    {
        $controllerPath = $this->_getControllerPath($this->name);

        if ($this->files->exists($controllerPath) && $this->ask('Already exist Controller. Do you want overwrite (y/n)?', 'y') == 'n') {
            return $this;
        }

        $this->info('Creating Controller ...');

        $replace = $this->buildReplacements();

        $controllerTemplate = str_replace(
            array_keys($replace),
            array_values($replace),
            $this->getStub('Controller')
        );

        $this->write($controllerPath, $controllerTemplate);

        return $this;
    }

    /**
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     */
    protected function buildModel()
    {
        $modelPath = $this->_getModelPath($this->name);

        if ($this->files->exists($modelPath) && $this->ask('Already exist Model. Do you want overwrite (y/n)?', 'y') == 'n') {
            return $this;
        }

        $this->info('Creating Model ...');

        // Make the models attributes and replacement
        $model = $this->modelReplacements();
        $model['{{table}}'] = $this->table;
        $replace = array_merge($this->buildReplacements(), $model);

        $modelTemplate = str_replace(
            array_keys($replace),
            array_values($replace),
            $this->getStub('Model')
        );

        $this->write($modelPath, $modelTemplate);

        return $this;
    }

    /**
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @throws \Exception
     */
    protected function buildViews()
    {
        $this->info('Creating Views ...');

        $tableHead = "\n";
        $tableBody = "[";
        $viewRows = "\n";
        $form = "\n";

        foreach ($this->getFilteredColumns() as $column) {
            $title = Str::title(str_replace('_', ' ', $column));

            $tableHead .= $this->getHead($title);
            $tableBody .= $this->getBody($column);
            $viewRows .= $this->getField($title, $column, 'view-field');
            $form .= $this->getField($title, $column, 'form-field');
        }
        $tableBody .= "{data: 'action'},";
        $tableBody .= "]";

        $replace = array_merge($this->buildReplacements(), [
            '{{tableHeader}}' => $tableHead,
            '{{tableBody}}' => $tableBody,
            '{{viewRows}}' => $viewRows,
            '{{routeAjax}}' => Str::kebab(Str::plural($this->name)),
            '{{form}}' => $form,
        ]);

        // $this->buildLayout();

        foreach (['index', 'create', 'edit', 'form', 'show'] as $view) {
            $viewTemplate = str_replace(
                array_keys($replace),
                array_values($replace),
                $this->getStub("views/{$view}")
            );

            $this->write($this->_getViewPath($view), $viewTemplate);
        }

        return $this;
    }

    /**
     * Make the class name from table name.
     *
     * @return string
     */
    private function _buildClassName()
    {
        return Str::studly(Str::singular($this->table));
    }

    private function buildNav()
    {
        $routeFile = base_path('resources/view/layouts/nav.blade.php');
        $this->controller = $this->_getNameSpaceController($this->name);
        $haveRoute = strpos(file_get_contents($routeFile), $this->routeName);
        if (!$haveRoute && file_exists($routeFile) && (strtolower($this->ask('Dou you want write nav y/n', 'y')) === 'y')) {
            $isAdded = File::append($routeFile, "\n" . "\n" . implode("\n", $this->addNav()));
            if ($isAdded) {
                $this->info('Crud/Resource route added to ' . $routeFile);
            } else {
                $this->info('Unable to add the route to ' . $routeFile);
            }
        } else {
            $this->info("Skipp to create route, already exist route");
        }
    }

    private function addNav()
    {
        return [
            "@can('list_" . $this->routeName . "')<li class='nav-item'> <a href='/' class='nav-link{{ \KiyoraDashboard\Models\General::IsActiveRoute('" . $this->routeName . "*') }}'> <i class='nav-icon fas fa-th'></i> <p> Simple Link </p> </a> </li>@endcan",
        ];
    }


    private function buildRoute()
    {
        $routeFile = base_path('routes/web.php');
        $this->controller = $this->_getNameSpaceController($this->name);
        $haveRoute = strpos(file_get_contents($routeFile), $this->controller);
        if (!$haveRoute && file_exists($routeFile) && (strtolower($this->ask('Dou you want write route y/n', 'y')) === 'y')) {
            $isAdded = File::append($routeFile, "\n" . "\n" . implode("\n", $this->addRoutes()));
            if ($isAdded) {
                $this->info('Crud/Resource route added to ' . $routeFile);
            } else {
                $this->info('Unable to add the route to ' . $routeFile);
            }
        } else {
            $this->info("Skipp to create route, already exist route");
        }
    }

    private function buildPermission()
    {
        $this->info('Creating Permission ...');
        $name = Str::title(str_replace('_', ' ', $this->table));
        $createGroup = PermissionGroup::create(['title' => "Manage $name", 'description' => "managing all $name"]);
        Permission::firstOrCreate(['name' => 'list_' . $this->routeName, 'guard_name' => "web", "permission_group_id" => $createGroup->id]);
        Permission::firstOrCreate(['name' => 'create_' . $this->routeName, 'guard_name' => "web", "permission_group_id" => $createGroup->id]);
        Permission::firstOrCreate(['name' => 'edit_' . $this->routeName, 'guard_name' => "web", "permission_group_id" => $createGroup->id]);
        Permission::firstOrCreate(['name' => 'delete_' . $this->routeName, 'guard_name' => "web", "permission_group_id" => $createGroup->id]);
        Permission::firstOrCreate(['name' => 'show_' . $this->routeName, 'guard_name' => "web", "permission_group_id" => $createGroup->id]);
        Artisan::call('cache:clear');
        $this->info('Permission created');
    }

    protected function addRoutes()
    {
        $lower = Str::camel($this->name);
        return [
            "Route::get('" . $this->routeName . "', [" . $this->controller . ", 'index'])->name('" . $this->routeName . ".index')->middleware('permission:list_" . $this->routeName . "');",
            "Route::get('" . $this->routeName . "/create', [" . $this->controller . ", 'create'])->name('" . $this->routeName . ".create')->middleware('permission:create_" . $this->routeName . "');",
            "Route::post('" . $this->routeName . "', [" . $this->controller . ", 'store'])->name('" . $this->routeName . ".store')->middleware('permission:create_" . $this->routeName . "');",
            "Route::get('" . $this->routeName . "/{" . $lower . "}', [" . $this->controller . ", 'show'])->name('" . $this->routeName . ".show')->middleware('permission:show_" . $this->routeName . "');",
            "Route::get('" . $this->routeName . "/{" . $lower . "}/edit', [" . $this->controller . ", 'edit'])->name('" . $this->routeName . ".edit')->middleware('permission:edit_" . $this->routeName . "');",
            "Route::patch('" . $this->routeName . "/{" . $lower . "}', [" . $this->controller . ", 'update'])->name('" . $this->routeName . ".update')->middleware('permission:edit_" . $this->routeName . "');",
            "Route::delete('" . $this->routeName . "/{" . $lower . "}', [" . $this->controller . ", 'destroy'])->name('" . $this->routeName . ".destroy')->middleware('permission:edit_" . $this->routeName . "');",
            "Route::post('" . $this->routeName . "-ajax', [" . $this->controller . ", 'ajax'])->name('" . $this->routeName . ".ajax')->middleware('permission:list_" . $this->routeName . "');"
        ];
    }
}
