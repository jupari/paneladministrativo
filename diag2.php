<?php
$count = DB::table('parametrizacion as p')
    ->join('categorias as cat', 'cat.id', '=', 'p.categoria_id')
    ->join('cargos as c', 'c.id', '=', 'p.cargo_id')
    ->join('novedades_detalle as nd', 'nd.id', '=', 'p.novedad_detalle_id')
    ->join('novedades as n', 'n.id', '=', 'nd.novedad_id')
    ->where('p.active', 1)
    ->where('c.active', 1)
    ->whereRaw("UPPER(cat.nombre) = 'NOMINA'")
    ->count();

$cargos = DB::table('parametrizacion as p')
    ->join('categorias as cat', 'cat.id', '=', 'p.categoria_id')
    ->join('cargos as c', 'c.id', '=', 'p.cargo_id')
    ->join('novedades_detalle as nd', 'nd.id', '=', 'p.novedad_detalle_id')
    ->join('novedades as n', 'n.id', '=', 'nd.novedad_id')
    ->where('p.active', 1)
    ->where('c.active', 1)
    ->whereRaw("UPPER(cat.nombre) = 'NOMINA'")
    ->select(['c.id', 'c.nombre'])
    ->distinct()
    ->get();

echo "Total filas NOMINA: {$count}\n";
echo "Cargos distintos: {$cargos->count()}\n";
foreach ($cargos as $c) {
    echo "  - [{$c->id}] {$c->nombre}\n";
}

// Verificar categorías disponibles
echo "\nCategorías activas:\n";
DB::table('categorias')->where('active',1)->get(['id','nombre'])->each(fn($c) => print("  [{$c->id}] {$c->nombre}\n"));

// Verificar cargos activos
echo "\nCargos activos:\n";
DB::table('cargos')->where('active',1)->get(['id','nombre','salario_base'])->each(fn($c) => print("  [{$c->id}] {$c->nombre} sal_base={$c->salario_base}\n"));
