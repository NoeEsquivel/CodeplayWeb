<?php
namespace App\Services;
use App\Models\TestResultadoSesion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GradeReportService
{
    public function queryForTeacher(Usuario $teacher,array $filters=[]): Builder
    {
        $classIds=$teacher->clasesComoProfesor()->pluck('clases.id');
        $q=TestResultadoSesion::query()->where('colegio_id',$teacher->colegio_id)->whereIn('clase_id',$classIds)
            ->whereHas('sesion',fn($s)=>$s->where('tipo','clase')->where('anfitrion_usuario_id',$teacher->id)->whereNull('eliminada_at'))
            ->with(['usuario:id,nombre','clase:id,nombre','sesion'=>fn($s)=>$s->with(['anfitrion:id,nombre','colegio:id,nombre','bancos:id,nombre'])]);
        if(filled($filters['alumno_id']??null))$q->where('usuario_id',(int)$filters['alumno_id']);
        if(filled($filters['clase_id']??null))$q->where('clase_id',(int)$filters['clase_id']);
        if(filled($filters['sesion_id']??null))$q->where('sesion_id',(int)$filters['sesion_id']);
        if(filled($filters['modalidad']??null))$q->where('modalidad',$filters['modalidad']);
        if(filled($filters['banco_id']??null))$q->whereHas('sesion.bancos',fn($b)=>$b->where('test_bancos.id',(int)$filters['banco_id']));
        if(filled($filters['desde']??null))$q->whereDate('entregada_at','>=',$filters['desde']);
        if(filled($filters['hasta']??null))$q->whereDate('entregada_at','<=',$filters['hasta']);
        return $q->orderByDesc('entregada_at')->orderByDesc('id');
    }

    public function summary(Collection $results,string $dimension): Collection
    {
        $rows=$dimension==='banco'?$results->flatMap(fn($r)=>$r->sesion->bancos->map(fn($b)=>['key'=>$b->id,'name'=>$b->nombre,'r'=>$r]))
            :$results->map(function($r)use($dimension){return match($dimension){
                'alumno'=>['key'=>$r->usuario_id,'name'=>$r->nombre_alumno,'r'=>$r],
                'clase'=>['key'=>$r->clase_id,'name'=>$r->clase?->nombre??'Sin clase','r'=>$r],
                'sesion'=>['key'=>$r->sesion_id,'name'=>$r->sesion->configuracion['titulo']??$r->sesion->codigo_sala,'r'=>$r],
                'profesor'=>['key'=>$r->sesion->anfitrion_usuario_id,'name'=>$r->sesion->anfitrion?->nombre??'Profesor','r'=>$r],
                'colegio'=>['key'=>$r->colegio_id,'name'=>$r->sesion->colegio?->nombre??'Colegio','r'=>$r],
                default=>['key'=>$r->clase_id,'name'=>$r->clase?->nombre??'Sin clase','r'=>$r]};});
        return $rows->groupBy('key')->map(function($items){$rs=$items->pluck('r');return['nombre'=>$items->first()['name'],'intentos'=>$rs->count(),
            'promedio'=>round((float)$rs->avg('calificacion'),2),'correctas'=>$rs->sum('correctas'),'incorrectas'=>$rs->sum('incorrectas'),
            'puntos'=>$rs->sum('puntos_test')];})->values();
    }
}
