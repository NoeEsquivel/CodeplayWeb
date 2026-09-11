<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('enemigos')->insert([
            'clave'=>'dragon','nombre'=>'Ignar, Dragón del Último Horizonte',
            'vida'=>1000,'daño'=>65,'defensa'=>18,'magia'=>0,'velocidad'=>1,'recompensa_puntos'=>300,
            'rasgos'=>json_encode(['region'=>5,'experiencia'=>600,'habilidad'=>'jefe',
                'ataque_especial'=>'Aliento del olvido',
                'descripcion'=>'Tres fases: sobre 2/3 de vida reduce 30% el daño físico; bajo 2/3 golpea 15% más fuerte; bajo 1/3, 35% más fuerte y prepara runas cada dos turnos. Siempre reduce 50% el daño de fuego. Las runas aparecen cada tres turnos en las dos primeras fases. Acertar interrumpe el especial, recupera hasta 6 maná y potencia 50% tu próximo golpe. Usa magia sin fuego al principio, guarda curas y observa el aviso del siguiente turno.']),
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    public function down(): void
    {
        $id=DB::table('enemigos')->where('clave','dragon')->value('id');
        if (!$id) return;
        if(DB::table('combates')->where('enemigo_id',$id)->exists()) throw new RuntimeException('El Dragón tiene combates registrados.');
        foreach(DB::table('aventura_expediciones')->get() as $run) foreach(json_decode($run->exploracion ?? '[]',true) as $event) foreach($event['combate']['enemigos'] ?? [] as $enemy) {
            if((int)$enemy['id']===(int)$id) throw new RuntimeException('El Dragón está guardado en una expedición.');
        }
        DB::table('enemigos')->where('id',$id)->delete();
    }
};
