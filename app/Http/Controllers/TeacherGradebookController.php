<?php
namespace App\Http\Controllers;
use App\Models\TestBanco;
use App\Models\TestHistorialRespuesta;
use App\Models\TestResultadoSesion;
use App\Models\TestSesion;
use App\Models\Usuario;
use App\Services\GradeReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TeacherGradebookController extends Controller
{
    public function index(Request $request,GradeReportService $reports): View
    {
        $teacher=$this->teacher();$filters=$this->filters($request);$results=$reports->queryForTeacher($teacher,$filters)->limit(1000)->get();
        $summary=$reports->summary($results,$filters['dimension']);$classes=$teacher->clasesComoProfesor()->orderBy('nombre')->get();
        $students=Usuario::whereHas('clasesComoAlumno',fn($q)=>$q->whereIn('clases.id',$classes->pluck('id')))->orderBy('nombre')->get();
        $sessions=TestSesion::where('anfitrion_usuario_id',$teacher->id)->where('tipo','clase')->whereNull('eliminada_at')->latest()->get();
        $banks=TestBanco::whereHas('sesiones',fn($q)=>$q->where('anfitrion_usuario_id',$teacher->id))->orderBy('nombre')->get();
        return view('profesor.calificaciones-index',compact('results','summary','filters','classes','students','sessions','banks'));
    }
    public function detail(TestResultadoSesion $resultado): View
    {$teacher=$this->teacher();$this->ownResult($teacher,$resultado);$resultado->load(['sesion.clase','sesion.colegio','sesion.anfitrion','sesion.bancos','usuario']);$answers=TestHistorialRespuesta::where('sesion_id',$resultado->sesion_id)->where('participante_id',$resultado->participante_id)->where('intento',$resultado->intento)->orderBy('ronda')->orderBy('orden')->get();return view('profesor.calificaciones-individual',compact('resultado','answers'));}
    public function printClass(Request $request,GradeReportService $reports): View
    {$teacher=$this->teacher();$filters=$this->filters($request);abort_unless(!empty($filters['clase_id']),422);$this->ownClassId($teacher,(int)$filters['clase_id']);$class=$teacher->clasesComoProfesor()->findOrFail((int)$filters['clase_id']);$results=$reports->queryForTeacher($teacher,$filters)->get();return view('profesor.calificaciones-clase-print',compact('results','filters','class','teacher'));}
    public function export(Request $request,GradeReportService $reports): Response
    {$teacher=$this->teacher();$rows=$reports->queryForTeacher($teacher,$this->filters($request))->get();$out=fopen('php://temp','r+');fputcsv($out,['Alumno','Clase','Actividad','Banco','Profesor','Colegio','Modalidad','Intento','Calificación','Correctas','Incorrectas','Puntos','Duración segundos','Fecha']);foreach($rows as $r)fputcsv($out,[$r->nombre_alumno,$r->clase?->nombre,$r->sesion->configuracion['titulo']??$r->sesion->codigo_sala,$r->sesion->bancos->pluck('nombre')->join(' + '),$r->sesion->anfitrion?->nombre,$r->sesion->colegio?->nombre,$r->modalidad,$r->intento,$r->calificacion,$r->correctas,$r->incorrectas,$r->puntos_test,$r->duracion_segundos,$r->entregada_at]);rewind($out);$csv=stream_get_contents($out);fclose($out);return response("\xEF\xBB\xBF".$csv,200,['Content-Type'=>'text/csv; charset=UTF-8','Content-Disposition'=>'attachment; filename="calificaciones-codeplay.csv"']);}
    public function archive(TestSesion $sesion): RedirectResponse{$t=$this->teacher();$this->ownSession($t,$sesion);$sesion->update(['archivada_at'=>now(),'archivada_por_usuario_id'=>$t->id]);return back()->with('success','Sesión archivada.');}
    public function restore(TestSesion $sesion): RedirectResponse{$t=$this->teacher();$this->ownSession($t,$sesion);$sesion->update(['archivada_at'=>null,'archivada_por_usuario_id'=>null]);return back()->with('success','Sesión restaurada.');}
    public function destroy(TestSesion $sesion): RedirectResponse{$t=$this->teacher();$this->ownSession($t,$sesion);abort_unless(in_array($sesion->estado,['finalizada','cerrada'],true),422);$sesion->update(['eliminada_at'=>now(),'archivada_at'=>now(),'archivada_por_usuario_id'=>$t->id]);return redirect()->route('profesor.calificaciones.index')->with('success','Sesión retirada del panel. Su historial permanece protegido.');}
    private function filters(Request $r): array{return $r->validate(['dimension'=>['nullable','in:alumno,clase,sesion,banco,profesor,colegio'],'alumno_id'=>['nullable','integer'],'clase_id'=>['nullable','integer'],'sesion_id'=>['nullable','integer'],'banco_id'=>['nullable','integer'],'modalidad'=>['nullable','in:trivia,examen'],'desde'=>['nullable','date'],'hasta'=>['nullable','date','after_or_equal:desde']])+['dimension'=>$r->input('dimension','clase')];}
    private function teacher(): Usuario{$u=Usuario::findOrFail((int)session('usuario_id'));abort_unless($u->esProfesor(),403);return $u;}
    private function ownClassId(Usuario $t,int $id): void{abort_unless($t->clasesComoProfesor()->whereKey($id)->exists(),404);}
    private function ownSession(Usuario $t,TestSesion $s): void{abort_unless((int)$s->anfitrion_usuario_id===(int)$t->id&&(int)$s->colegio_id===(int)$t->colegio_id&&$t->clasesComoProfesor()->whereKey($s->clase_id)->exists(),404);}
    private function ownResult(Usuario $t,TestResultadoSesion $r): void{$this->ownSession($t,$r->sesion);}
}
