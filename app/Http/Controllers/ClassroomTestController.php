<?php

namespace App\Http\Controllers;

use App\Models\TestSesion;
use App\Models\TestSesionPregunta;
use App\Models\Usuario;
use App\Services\ClassroomExamService;
use App\Services\LiveTestEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomTestController extends Controller
{
    public function index(): View
    {
        $student=$this->student(); $classIds=$student->clasesComoAlumno()->pluck('clases.id');
        $sessions=TestSesion::where('tipo','clase')->where('colegio_id',$student->colegio_id)->whereIn('clase_id',$classIds)
            ->whereIn('estado',['espera','activa'])->where(fn($q)=>$q->whereNull('disponible_desde')->orWhere('disponible_desde','<=',now()))
            ->where(fn($q)=>$q->whereNull('disponible_hasta')->orWhere('disponible_hasta','>',now()))
            ->with(['clase:id,nombre','anfitrion:id,nombre','bancos:id,nombre'])
            ->withExists(['participantes as ya_ingreso'=>fn($q)=>$q->where('usuario_id',$student->id)])->latest()->get();
        return view('alumno.tests-clase',compact('sessions'));
    }

    public function enter(Request $request, TestSesion $sesion, LiveTestEngineService $engine): RedirectResponse
    {
        $student=$this->student(); $this->visible($student,$sesion);
        abort_unless(in_array($sesion->estado,['espera','activa'],true)
            &&(!$sesion->disponible_desde||now()->greaterThanOrEqualTo($sesion->disponible_desde))
            &&(!$sesion->disponible_hasta||now()->lessThan($sesion->disponible_hasta)),404);
        try {
            $participant=$sesion->participantes()->where('usuario_id',$student->id)->first();
            if(!$participant){$joined=$engine->unir($student,$sesion->codigo_sala,null,'clase');$request->session()->put("live_test_tokens.{$sesion->id}",$joined['reconexion_token']);}
            return redirect()->route($sesion->modalidad==='examen'?'alumno.tests.clase.examen':'alumno.tests.clase.trivia',$sesion);
        } catch(\DomainException $e){return back()->with('error',$e->getMessage());}
    }

    public function trivia(TestSesion $sesion, LiveTestEngineService $engine): View
    {
        abort_unless($sesion->modalidad==='trivia',404); $this->visible($this->student(),$sesion);
        abort_unless($sesion->participantes()->where('usuario_id',$this->student()->id)->exists(),404);
        try{$state=$engine->estado($this->student(),$sesion);}catch(\DomainException){abort(404);}
        return view('alumno.tests-clase-trivia',['sesion'=>$sesion,'estado'=>$state,'token'=>session("live_test_tokens.{$sesion->id}")]);
    }

    public function exam(TestSesion $sesion, ClassroomExamService $exams): View
    {
        abort_unless($sesion->modalidad==='examen',404); $this->visible($this->student(),$sesion);
        abort_unless($sesion->participantes()->where('usuario_id',$this->student()->id)->exists(),404);
        try{$state=$exams->estado($this->student(),$sesion);}catch(\DomainException){abort(404);}
        return view('alumno.tests-clase-examen',compact('sesion','state'));
    }

    public function saveExam(Request $request, TestSesion $sesion, TestSesionPregunta $emision, ClassroomExamService $exams): RedirectResponse
    {
        abort_unless($sesion->modalidad==='examen',404); $this->visible($this->student(),$sesion); $request->validate(['respuesta'=>['present']]);
        try{$exams->save($this->student(),$sesion,$emision,$request->input('respuesta'));return back()->with('success','Respuesta guardada automáticamente.');}
        catch(\DomainException $e){return back()->with('error',$e->getMessage());}
    }

    public function submitExam(TestSesion $sesion, ClassroomExamService $exams): RedirectResponse
    {
        abort_unless($sesion->modalidad==='examen',404); $this->visible($this->student(),$sesion);
        try{$exams->submit($this->student(),$sesion);return back()->with('success','Examen entregado. Ya puedes consultar tu calificación.');}
        catch(\DomainException $e){return back()->with('error',$e->getMessage());}
    }

    private function visible(Usuario $student,TestSesion $session): void
    {
        abort_unless($session->tipo==='clase'&&(int)$session->colegio_id===(int)$student->colegio_id
            &&$student->clasesComoAlumno()->whereKey($session->clase_id)->exists(),404);
    }

    private function student(): Usuario { $u=Usuario::findOrFail((int)session('usuario_id'));abort_unless($u->esAlumno(),403);return $u; }
}
