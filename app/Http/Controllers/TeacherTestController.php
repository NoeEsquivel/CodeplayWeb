<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\TestBanco;
use App\Models\TestBancoPregunta;
use App\Models\TestSesion;
use App\Models\Usuario;
use App\Services\LiveTestEngineService;
use App\Services\LiveTestSessionService;
use App\Services\ClassroomExamService;
use App\Services\TestBankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TeacherTestController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $this->teacher($request);
        $classes = $teacher->clasesComoProfesor()->where('clases.activa', true)->withCount('alumnos')->orderBy('nombre')->get();
        $banks = TestBanco::where('creador_usuario_id', $teacher->id)->where('colegio_id', $teacher->colegio_id)
            ->where('origen', 'profesor')->withCount('preguntas')->with(['clasesAsignadas:id,nombre','preguntas'=>fn($q)=>$q->where('activa',true)])->latest('updated_at')->get();
        $systemBanks = TestBanco::where('origen', 'sistema')->where('activo', true)->where('estado', 'publicado')
            ->withCount(['preguntas' => fn ($q) => $q->where('activa', true)])->with(['clasesAsignadas:id,nombre','preguntas'=>fn($q)=>$q->where('activa',true)])->orderBy('nombre')->get();
        $sessions = TestSesion::where('anfitrion_usuario_id', $teacher->id)->where('tipo', 'clase')
            ->whereIn('clase_id', $classes->pluck('id'))->with(['clase:id,nombre', 'bancos:id,nombre'])
            ->withCount(['participantes', 'preguntas'])->latest()->limit(30)->get();

        return view('profesor.tests-index', compact('classes', 'banks', 'systemBanks', 'sessions'));
    }

    public function create(Request $request): View
    {
        $this->teacher($request);
        return view('profesor.tests-create');
    }

    public function store(Request $request, TestBankService $service): RedirectResponse
    {
        $data = $request->validate(['nombre' => ['required','string','max:160'], 'materia' => ['required','string','max:100'], 'descripcion' => ['nullable','string','max:2000']]);
        $bank = $service->crear($this->teacher($request), $data);
        return redirect()->route('profesor.tests.banks.edit', $bank)->with('success', 'Banco docente creado como borrador.');
    }

    public function edit(Request $request, TestBanco $banco): View
    {
        $this->ownBank($this->teacher($request), $banco);
        $banco->load(['preguntas' => fn ($q) => $q->orderBy('orden'), 'clasesAsignadas:id,nombre']);
        $classes = $this->teacher($request)->clasesComoProfesor()->where('clases.activa', true)->orderBy('nombre')->get();
        $systemBanks = TestBanco::where('origen', 'sistema')->where('activo', true)->where('estado', 'publicado')
            ->with(['preguntas' => fn ($q) => $q->where('activa', true)->orderBy('orden')])->orderBy('nombre')->get();
        return view('profesor.tests-edit', compact('banco', 'classes', 'systemBanks'));
    }

    public function update(Request $request, TestBanco $banco, TestBankService $service): RedirectResponse
    {
        $teacher = $this->teacher($request); $this->ownBank($teacher, $banco);
        $data = $request->validate(['nombre'=>['required','string','max:160'],'materia'=>['required','string','max:100'],'descripcion'=>['nullable','string','max:2000']]);
        $service->actualizar($teacher, $banco, $data);
        return back()->with('success', 'Cambios guardados. Publica nuevamente cuando termines.');
    }

    public function destroy(Request $request, TestBanco $banco, TestBankService $service): RedirectResponse
    {
        $teacher = $this->teacher($request); $this->ownBank($teacher, $banco); $service->eliminar($teacher, $banco);
        return redirect()->route('profesor.tests.index')->with('success', 'Banco eliminado.');
    }

    public function publish(Request $request, TestBanco $banco, TestBankService $service): RedirectResponse
    {
        $teacher = $this->teacher($request); $this->ownBank($teacher, $banco);
        try { $service->publicar($teacher, $banco); return back()->with('success', 'Banco publicado y listo para asignar.'); }
        catch (\DomainException $e) { return back()->with('error', $e->getMessage()); }
    }

    public function assign(Request $request, TestBanco $banco): RedirectResponse
    {
        $teacher = $this->teacher($request); $this->accessibleBank($teacher, $banco, false);
        $data = $request->validate([
            'clase_ids' => ['nullable','array'], 'clase_ids.*' => ['integer','distinct'],
            'disponible_desde' => ['nullable','date'], 'disponible_hasta' => ['nullable','date','after:disponible_desde'],
        ]);
        $classIds = array_map('intval', $data['clase_ids'] ?? []);
        $allowed = $teacher->clasesComoProfesor()->whereIn('clases.id', $classIds)->pluck('clases.id')->map(fn ($id)=>(int)$id)->all();
        abort_unless(count($allowed) === count($classIds), 404);
        $pivot = [];
        foreach ($allowed as $classId) $pivot[$classId] = [
            'asignado_por_usuario_id' => $teacher->id,
            'disponible_desde' => $data['disponible_desde'] ?? null,
            'disponible_hasta' => $data['disponible_hasta'] ?? null,
            'activo' => true,
        ];
        $ownClassIds = $teacher->clasesComoProfesor()->pluck('clases.id')->map(fn ($id) => (int) $id)->all();
        $currentOwnAssignments = $banco->clasesAsignadas()->whereIn('clases.id', $ownClassIds)
            ->wherePivot('asignado_por_usuario_id', $teacher->id)->pluck('clases.id')->map(fn ($id) => (int) $id)->all();
        $toDetach = array_values(array_diff($currentOwnAssignments, $allowed));
        if ($toDetach) $banco->clasesAsignadas()->detach($toDetach);
        if ($pivot) $banco->clasesAsignadas()->syncWithoutDetaching($pivot);
        return back()->with('success', 'Asignación por clases actualizada.');
    }

    public function storeQuestion(Request $request, TestBanco $banco, TestBankService $service): RedirectResponse
    {
        $teacher=$this->teacher($request); $this->ownBank($teacher,$banco);
        try{$service->agregarPregunta($teacher,$banco,$this->questionData($request));return back()->with('success','Pregunta añadida.');}
        catch(\InvalidArgumentException|\DomainException $e){return back()->withInput()->with('error',$e->getMessage());}
    }

    public function updateQuestion(Request $request, TestBanco $banco, TestBancoPregunta $pregunta, TestBankService $service): RedirectResponse
    {
        $teacher=$this->teacher($request); $this->ownBank($teacher,$banco);
        try{$service->actualizarPregunta($teacher,$banco,$pregunta,$this->questionData($request));return back()->with('success','Pregunta actualizada.');}
        catch(\InvalidArgumentException|\DomainException $e){return back()->with('error',$e->getMessage());}
    }

    public function destroyQuestion(Request $request, TestBanco $banco, TestBancoPregunta $pregunta, TestBankService $service): RedirectResponse
    {
        $teacher=$this->teacher($request); $this->ownBank($teacher,$banco); $service->eliminarPregunta($teacher,$banco,$pregunta);
        return back()->with('success','Pregunta eliminada.');
    }

    public function importSystemQuestions(Request $request, TestBanco $banco, TestBankService $service): RedirectResponse
    {
        $teacher=$this->teacher($request); $this->ownBank($teacher,$banco);
        $data=$request->validate(['pregunta_ids'=>['required','array','min:1','max:100'],'pregunta_ids.*'=>['integer','distinct']]);
        $ids=array_map('intval',$data['pregunta_ids']);
        $questions=TestBancoPregunta::whereIn('id',$ids)->where('activa',true)
            ->whereHas('banco',fn($q)=>$q->where('origen','sistema')->where('activo',true)->where('estado','publicado'))->get();
        abort_unless($questions->count()===count($ids),404);
        foreach($questions as $question){$service->agregarPregunta($teacher,$banco,[
            'tipo'=>$question->tipo,'enunciado'=>$question->enunciado,'opciones'=>$question->opciones,
            'respuestas_correctas'=>$question->respuestas_correctas,'explicacion'=>$question->explicacion,
            'tiempo_limite'=>$question->tiempo_limite,
            'puntos'=>$question->puntos,
        ]);}
        return back()->with('success',$questions->count().' pregunta(s) del sistema incorporadas al banco.');
    }

    public function createSession(Request $request, LiveTestSessionService $sessions, LiveTestEngineService $engine): RedirectResponse
    {
        $teacher=$this->teacher($request);
        $data=$request->validate([
            'clase_id'=>['required','integer'],'banco_ids'=>['required','array','min:1','max:20'],'banco_ids.*'=>['integer','distinct'],
            'modalidad'=>['required','in:trivia,examen'],'tiempo_pregunta'=>['nullable','integer','min:5','max:300'],
            'puntaje_objetivo'=>['nullable','integer','min:50','max:100000'],'disponible_desde'=>['nullable','date'],
            'disponible_hasta'=>['nullable','date','after:disponible_desde'],'orden_aleatorio'=>['nullable','boolean'],
            'titulo'=>['required','string','max:160'],'duracion_examen_minutos'=>['nullable','integer','min:1','max:1440'],
            'puntos_pregunta'=>['nullable','array'],'puntos_pregunta.*'=>['integer','min:1','max:1000'],
        ]);
        $class=Clase::findOrFail($data['clase_id']); $this->ownClass($teacher,$class);
        $banks=$this->resolveSessionBanks($teacher,$class,$data['banco_ids']);
        try{
            $session=$sessions->crear($teacher,[
                'tipo'=>'clase','modalidad'=>$data['modalidad'],'clase_id'=>$class->id,
                'tiempo_pregunta'=>$data['modalidad']==='trivia'?($data['tiempo_pregunta']??20):null,
                'puntaje_objetivo'=>$data['modalidad']==='trivia'?($data['puntaje_objetivo']??500):null,
                'max_participantes'=>30,'disponible_desde'=>$data['disponible_desde']??null,'disponible_hasta'=>$data['disponible_hasta']??null,
                'configuracion'=>['orden_aleatorio'=>$request->boolean('orden_aleatorio'),'segundos_resultado'=>5,
                    'titulo'=>$data['titulo'],'duracion_examen_minutos'=>$data['modalidad']==='examen'?($data['duracion_examen_minutos']??null):null,
                    'puntos_pregunta'=>collect($data['puntos_pregunta']??[])->mapWithKeys(fn($v,$k)=>[(string)(int)$k=>(int)$v])->all()],
            ],$banks->all());
            $engine->abrirSala($teacher,$session);
            return redirect()->route('profesor.tests.sessions.show',$session)->with('success','Sesión creada. Comparte el código con la clase.');
        }catch(\DomainException|\InvalidArgumentException $e){return back()->withInput()->with('error',$e->getMessage());}
    }

    public function showSession(Request $request, TestSesion $sesion, LiveTestEngineService $engine): View
    {
        $teacher=$this->teacher($request); $this->ownSession($teacher,$sesion);
        $state=$engine->estado($teacher,$sesion);
        $sesion->load(['clase:id,nombre','bancos:id,nombre','participantes.usuario:id,nombre','participantes.respuestas','resultados']);
        return view('profesor.tests-session',compact('sesion','state'));
    }

    public function sessionAction(Request $request, TestSesion $sesion, LiveTestEngineService $engine, ClassroomExamService $exams, string $action): RedirectResponse
    {
        $teacher=$this->teacher($request); $this->ownSession($teacher,$sesion);
        try{
            if($action==='finish'&&$sesion->modalidad==='examen')$exams->submitPending($sesion);
            match($action){'start'=>$engine->iniciar($teacher,$sesion),'pause'=>$engine->pausar($teacher,$sesion),'resume'=>$engine->reanudar($teacher,$sesion),'finish'=>$engine->cerrar($teacher,$sesion),default=>abort(404)};
            return back()->with('success','Estado de la sesión actualizado.');
        }catch(\DomainException $e){return back()->with('error',$e->getMessage());}
    }

    private function resolveSessionBanks(Usuario $teacher, Clase $class, array $ids): Collection
    {
        $ids=array_values(array_unique(array_map('intval',$ids)));
        $banks=TestBanco::whereIn('id',$ids)->where('activo',true)->where('estado','publicado')->get();
        if($banks->count()!==count($ids))throw new \DomainException('Uno de los bancos no está disponible.');
        foreach($banks as $bank){$this->accessibleBank($teacher,$bank,true);$assigned=$bank->clasesAsignadas()->whereKey($class->id)->wherePivot('asignado_por_usuario_id',$teacher->id)->wherePivot('activo',true)->exists();if(!$assigned)throw new \DomainException('Asigna cada banco a la clase antes de crear la sesión.');}
        return $banks;
    }

    private function questionData(Request $request): array
    {
        $request->validate(['tipo'=>['required','in:opcion_unica,seleccion_multiple,verdadero_falso,respuesta_directa'],'enunciado'=>['required','string','max:2000'],'explicacion'=>['nullable','string','max:3000'],'tiempo_limite'=>['required','integer','min:5','max:300'],'puntos'=>['nullable','integer','min:1','max:1000']]);
        $type=(string)$request->input('tipo');$options=null;$answers=[];
        if(in_array($type,['opcion_unica','seleccion_multiple'],true)){$options=collect((array)$request->input('opciones',[]))->map(fn($v)=>trim((string)$v))->filter()->all();$answers=$type==='opcion_unica'?[(string)$request->input('respuesta_unica')]:array_values((array)$request->input('respuestas_multiples',[]));}
        elseif($type==='verdadero_falso')$answers=[(string)$request->input('respuesta_booleana')];
        else $answers=preg_split('/\R/u',(string)$request->input('respuestas_escritas',''),-1,PREG_SPLIT_NO_EMPTY)?:[];
        return ['tipo'=>$type,'enunciado'=>$request->input('enunciado'),'opciones'=>$options,'respuestas_correctas'=>$answers,'explicacion'=>$request->input('explicacion'),'tiempo_limite'=>(int)$request->input('tiempo_limite',20),'puntos'=>(int)$request->input('puntos',1)];
    }

    private function teacher(Request $request): Usuario {$user=$request->attributes->get('usuario_actual')??Usuario::find(session('usuario_id'));abort_unless($user instanceof Usuario&&$user->esProfesor()&&$user->colegio_id,403);return $user;}
    private function ownClass(Usuario $teacher,Clase $class): void {abort_unless((int)$class->colegio_id===(int)$teacher->colegio_id&&$class->profesores()->whereKey($teacher->id)->exists(),404);}
    private function ownBank(Usuario $teacher,TestBanco $bank): void {abort_unless($bank->origen==='profesor'&&(int)$bank->creador_usuario_id===(int)$teacher->id&&(int)$bank->colegio_id===(int)$teacher->colegio_id,404);}
    private function accessibleBank(Usuario $teacher,TestBanco $bank,bool $published): void {if($bank->esSistema()){abort_unless($bank->activo&&(!$published||$bank->estado==='publicado'),404);return;}$this->ownBank($teacher,$bank);if($published)abort_unless($bank->activo&&$bank->estado==='publicado',404);}
    private function ownSession(Usuario $teacher,TestSesion $session): void {abort_unless($session->tipo==='clase'&&(int)$session->colegio_id===(int)$teacher->colegio_id&&(int)$session->anfitrion_usuario_id===(int)$teacher->id,404);$this->ownClass($teacher,$session->clase);}
}
