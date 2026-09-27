<?php
namespace App\Services;

use App\Models\TestHistorialRespuesta;
use App\Models\TestResultadoSesion;
use App\Models\TestSesion;

class TestHistoryService
{
    public function snapshot(TestSesion $session): void
    {
        $attempt=max(1,(int)($session->configuracion['intento_actual']??1));
        $session->loadMissing(['preguntas.pregunta','preguntas.respuestas','participantes']);
        foreach($session->participantes->where('es_anfitrion',false) as $participant){
            foreach($session->preguntas as $emission){
                $answer=$emission->respuestas->firstWhere('participante_id',$participant->id);
                $question=$emission->pregunta;
                TestHistorialRespuesta::updateOrCreate([
                    'sesion_id'=>$session->id,'participante_id'=>$participant->id,
                    'sesion_pregunta_original_id'=>$emission->id,'intento'=>$attempt,
                ],[
                    'usuario_id'=>$participant->usuario_id,'colegio_id'=>$session->colegio_id,'clase_id'=>$session->clase_id,
                    'profesor_id'=>$session->anfitrion_usuario_id,'banco_id'=>$question->banco_id,'ronda'=>$emission->ronda,
                    'orden'=>$emission->orden,'pregunta'=>$question->enunciado,'tipo_pregunta'=>$question->tipo,
                    'opciones'=>$question->opciones,'respuestas_correctas'=>$question->respuestas_correctas,
                    'respuesta_alumno'=>$answer?->respuesta,'correcta'=>(bool)($answer?->correcta??false),'puntos_otorgados'=>$answer?->puntos_otorgados??0,
                    'puntos_maximos'=>$emission->puntos_maximos,'tiempo_respuesta_ms'=>$answer?->tiempo_respuesta_ms,'respondida_at'=>$answer?->respondida_at,
                ]);
            }
        }
        foreach($session->participantes->where('es_anfitrion',false) as $participant){
            $answers=TestHistorialRespuesta::where('sesion_id',$session->id)->where('participante_id',$participant->id)->where('intento',$attempt);
            $correct=(clone $answers)->where('correcta',true)->count();
            $total=$session->preguntas->count();$possible=(int)$session->preguntas->sum('puntos_maximos');$earned=(int)(clone $answers)->sum('puntos_otorgados');
            $start=$participant->examen_iniciado_at??$session->iniciada_at??$participant->unido_at;
            $end=$participant->finalizado_at??$session->finalizada_at??$session->cerrada_at??now();
            $grade=$session->modalidad==='examen'
                ?($possible?round($earned*100/$possible,2):0)
                :($total?round($correct*100/$total,2):0);
            TestResultadoSesion::updateOrCreate(['sesion_id'=>$session->id,'participante_id'=>$participant->id,'intento'=>$attempt],[
                'usuario_id'=>$participant->usuario_id,'colegio_id'=>$session->colegio_id,'clase_id'=>$session->clase_id,
                'nombre_alumno'=>$participant->nombre_visible,'modalidad'=>$session->modalidad,'total_preguntas'=>$total,
                'correctas'=>$correct,'incorrectas'=>max(0,$total-$correct),'calificacion'=>$grade,
                'puntos_test'=>$session->modalidad==='trivia'?$participant->puntos_test:$earned,
                'duracion_segundos'=>$start&&$end?max(0,$start->diffInSeconds($end)):null,'estado'=>'entregado',
                'iniciada_at'=>$start,'entregada_at'=>$end,
            ]);
        }
    }
}
