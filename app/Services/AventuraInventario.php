<?php

namespace App\Services;

use App\Models\AventuraExpedicion;
use Illuminate\Support\Facades\DB;

class AventuraInventario
{
    /** Called only under the adventure's user lock. Never changes its original investment. */
    public static function recibir(AventuraExpedicion $run, string $clave): bool
    {
        $item = (new AventuraCatalogo())->items()[$clave] ?? null;
        abort_unless($item,422);
        if ($item['desafio']) abort_unless(DB::table('desafio_recompensas')->where('usuario_id',$run->usuario_id)->where('nivel_desafio',$item['desafio'])->where('completado_primera_vez',true)->exists(),422);
        $id = DB::table('items')->where('clave_catalogo',$clave)->value('id');
        abort_unless($id,503,'El catálogo está en mantenimiento.');
        $fila = DB::table('inventario')->where('usuario_id',$run->usuario_id)->where('item_id',$id)->first();
        if ($fila && (int)$fila->expedicion_id !== $run->id) return false;
        if ($item['tipo'] !== 'pocion' && $fila && $fila->cantidad > 0) return false;
        if ($item['tipo'] === 'pocion' && $run->inventario()->where('posicion','pocion')->sum('cantidad') >= 20) return false;
        $datos = ['expedicion_id'=>$run->id,'posicion'=>$item['tipo'] === 'pocion' ? 'pocion' : null,'cantidad'=>($fila->cantidad ?? 0)+1,'updated_at'=>now()];
        if ($fila) DB::table('inventario')->where('id',$fila->id)->update($datos);
        else DB::table('inventario')->insert($datos+['usuario_id'=>$run->usuario_id,'item_id'=>$id,'created_at'=>now()]);
        return true;
    }

    public static function equiparBotin(AventuraExpedicion $run,string $clave,?string $reemplazar): void
    {
        $item = (new AventuraCatalogo())->items()[$clave] ?? null;
        abort_unless($item && $item['tipo'] !== 'pocion',422,'Ese objeto no requiere equiparse.');
        if ($item['desafio']) abort_unless(DB::table('desafio_recompensas')->where('usuario_id',$run->usuario_id)->where('nivel_desafio',$item['desafio'])->where('completado_primera_vez',true)->exists(),422,'La reliquia aún está bloqueada.');
        $fila = $run->inventario()->whereHas('item',fn($q)=>$q->where('clave_catalogo',$clave))->where('cantidad','>',0)->first();
        abort_unless($fila && $fila->posicion === null,409,'El objeto ya está equipado o no está en tu mochila.');
        if ($item['tipo'] === 'hechizo') {
            $equipados = $run->equipo['hechizos'];
            $nivel = (int) DB::table('aventura_personajes')->where('usuario_id', $run->usuario_id)->value('nivel');
            if (count($equipados) >= AventuraCatalogo::limiteHechizos($nivel)) {
                abort_unless(in_array($reemplazar,$equipados,true),422,'Elige qué hechizo reemplazar.');
                $run->inventario()->whereHas('item',fn($q)=>$q->where('clave_catalogo',$reemplazar))->update(['posicion'=>null]);
            }
        } else $run->inventario()->where('posicion',$item['tipo'])->update(['posicion'=>null]);
        $fila->posicion = $item['tipo']; $fila->save();
    }

    /** Only during preparation, inside the transaction holding the user lock. */
    public static function guardar(AventuraExpedicion $run, array $equipo): void
    {
        $seleccion = [];
        foreach (['arma', 'armadura'] as $posicion) {
            if ($equipo[$posicion]) $seleccion[] = [$equipo[$posicion], $posicion, 1];
        }
        foreach ($equipo['hechizos'] as $clave) $seleccion[] = [$clave, 'hechizo', 1];
        foreach ($equipo['pociones'] as $pocion) $seleccion[] = [$pocion['id'], 'pocion', $pocion['cantidad']];

        $items = DB::table('items')->whereNotNull('clave_catalogo')->pluck('id', 'clave_catalogo');
        foreach ($seleccion as [$clave]) {
            if (!isset($items[$clave])) throw new \LogicException('El catálogo de inventario no está actualizado.');
            // Never appropriate an item belonging to another scope or expedition.
            $existente = DB::table('inventario')->where('usuario_id', $run->usuario_id)->where('item_id', $items[$clave])->first();
            if ($existente && (int) $existente->expedicion_id !== (int) $run->id) {
                throw new \LogicException('Este objeto pertenece a otro inventario. Requiere conciliación antes de comprar.');
            }
        }
        $run->inventario()->delete();
        foreach ($seleccion as [$clave, $posicion, $cantidad]) {
            DB::table('inventario')->insert([
                'usuario_id' => $run->usuario_id, 'expedicion_id' => $run->id, 'item_id' => $items[$clave],
                'posicion' => $posicion, 'cantidad' => $cantidad, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
