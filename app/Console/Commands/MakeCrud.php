<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeCrud extends Command
{
    protected $signature = 'make:crud {name} {--datatable}';
    protected $description = 'Genera modelo, requests, servicio y controlador para un CRUD con opción de DataTables';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $this->info("🚀 Generando CRUD para: {$name}");

        // Modelo + migración
        $this->call('make:model', [
            'name' => $name,
            '-m'   => true,
        ]);

        // Controlador REST
        $this->call('make:controller', [
            'name' => "{$name}Controller",
            '--api' => true,
        ]);

        // Requests
        $this->call('make:request', ['name' => "Store{$name}Request"]);
        $this->call('make:request', ['name' => "Update{$name}Request"]);

        // Crear servicio
        $servicePath = app_path("Services/{$name}Service.php");
        if (!file_exists(dirname($servicePath))) {
            mkdir(dirname($servicePath), 0755, true);
        }

        $datatableLogic = $this->option('datatable') ? <<<PHP
        use Yajra\DataTables\Facades\DataTables;
        use DateTime;

        public function getDataTable(\$request)
        {
            \$items = {$name}::with('estado')->get();

            if (\$request->ajax()) {
                return DataTables::of(\$items)
                    ->addIndexColumn()
                    ->addColumn('id', fn(\$td) => \$td->id)
                    ->addColumn('nombre', fn(\$td) => \$td->nombre ?? '')
                    ->addColumn('fecha', function (\$td) {
                        \$date = new DateTime(\$td->fecha);
                        return \$date->format('d/m/Y');
                    })
                    ->addColumn('acciones', function (\$td) {
                        return '<button type="button" onclick="edit(' . \$td->id . ')"
                                    class="btn btn-secondary btn-circle btn-sm"
                                    title="Editar"><i class="fas fa-pencil-alt"></i></button>';
                    })
                    ->rawColumns(['acciones'])
                    ->make(true);
            }

            return null;
        }
        PHP : '';

        $serviceTemplate = <<<PHP
        <?php

        namespace App\Services;

        use App\Models\\{$name};
        use Exception;

        class {$name}Service
        {
            public function getAll()
            {
                return {$name}::all();
            }

            public function getById(\$id)
            {
                return {$name}::findOrFail(\$id);
            }

            public function create(array \$data)
            {
                return {$name}::create(\$data);
            }

            public function update(\$id, array \$data)
            {
                \$model = {$name}::findOrFail(\$id);
                \$model->update(\$data);
                return \$model;
            }

            public function delete(\$id)
            {
                \$model = {$name}::findOrFail(\$id);
                return \$model->delete();
            }

            {$datatableLogic}
        }
        PHP;

        file_put_contents($servicePath, $serviceTemplate);
        $this->info("✅ Servicio creado en: {$servicePath}");

        $this->info("🎉 CRUD generado con éxito para {$name}");
        return Command::SUCCESS;
    }
}
