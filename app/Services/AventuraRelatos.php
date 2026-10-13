<?php

namespace App\Services;

class AventuraRelatos
{
    public static function todos(): array
    {
        // Each entry provides a narrative setting for a question from the existing bank.
        return [
            'vagabundo' => ['El lector de runas', 'Un anciano mira una tablilla al revés. «Sé mucho de caminos, pero estas instrucciones me ganaron».', 'Ayudar a descifrarla', '¿De dónde salió la tablilla?', 'La encontró en una biblioteca. Leer con calma suele ser mejor que adivinar.', '«No sé programar, pero eso suena raro». Golpea el suelo con su bastón… y te pisa un pie por accidente.'],
            'puente' => ['Un puente sin instrucciones', 'La guardiana no puede activar el puente. Una inscripción contiene la pieza que falta.', 'Revisar la inscripción', '¿Qué debemos reparar?', 'La guardiana necesita comprender una instrucción, no construir un programa entero.', 'El puente suelta un chorro de agua. Tu dignidad se moja más que tus botas.'],
            'mago' => ['El hechizo desordenado', 'Un aprendiz convirtió su sombrero en una tetera. Pide ayuda para entender su manual.', 'Leer el manual', '¿Qué estabas intentando?', 'Quería ordenar instrucciones. Ahora el sombrero sirve té; no todo salió mal.', 'La tetera estornuda brillantina. El mago decide estudiar un poco más.'],
            'puerta' => ['La puerta de la biblioteca', 'Una puerta con ojos protege libros de otros mundos. Solo pide una respuesta razonada.', 'Resolver la pregunta', 'Hablar con la puerta', '«Compara cada opción antes de responder», aconseja mientras gira sus bisagras.', 'La puerta suspira y se cierra. «Vuelve con una idea nueva, no con una patada».'],
            'trampa' => ['Un mecanismo sospechoso', 'Ves un hilo junto a unas runas. Una exploradora intenta comprender el mecanismo.', 'Ayudar a desactivarlo', 'Examinar desde lejos', 'Parece una prueba de comprensión. La exploradora te deja leer antes de tocar nada.', 'El mecanismo lanza una nube de harina. Ahora pareces un fantasma aprendiz.'],
            'robot' => ['El pequeño autómata', 'Un robot de madera repite «¿y ahora qué?» frente a una bifurcación. Su guía contiene una duda.', 'Revisar su guía', 'Preguntar por su misión', 'Reparte cartas. Necesita interpretar bien las instrucciones para no entregárselas a un árbol.', 'El robot agradece tu intento y entrega una carta a una piedra. La piedra no responde.'],
            'mercader' => ['La oferta del mercader', 'Un mercader ofrece una recompensa a quien resuelva la pregunta de su libro de viajes.', 'Aceptar el reto', 'Consultar las condiciones', 'No cobra entrada. Su recompensa será una sorpresa; equivocarte también puede traer consecuencias.', '«Casi», dice. Cuenta sus monedas, se distrae y vuelve a empezar desde uno.'],
            'alquimista' => ['La receta imposible', 'Una alquimista tiene frascos etiquetados y una pregunta en su receta. «Mejor entender antes de mezclar».', 'Interpretar la receta', 'Leer la advertencia', 'Los símbolos del libro representan conceptos de programación. No necesitas mezclar nada real.', 'La mezcla burbujea y huele a calcetín. La alquimista decide abrir todas las ventanas.'],
            'bardo' => ['La canción interrumpida', 'Un bardo olvidó la explicación que acompaña su canción sobre programación.', 'Ayudar con la explicación', 'Escuchar una estrofa', 'La canción recuerda que equivocarse y corregir también es aprender.', 'El bardo canta tu respuesta… desafinando tanto que hasta los grillos protestan.'],
            'cartografa' => ['El mapa incompleto', 'Una cartógrafa intenta interpretar notas de una antigua escuela de programación.', 'Revisar sus notas', 'Preguntar por el mapa', 'Las notas mezclan caminos y conceptos. Primero comprende la pregunta; después decide.', 'La cartógrafa dibuja un signo de interrogación enorme. «Este también es un descubrimiento».'],
            'hada' => ['La luz atrapada', 'Un hada está encerrada en una linterna rúnica. Una pregunta mantiene cerrado el sello.', 'Intentar abrir el sello', 'Hablar con el hada', 'Tiene paciencia. Dice que leer todas las opciones ayuda más que pulsar la primera.', 'El sello hace «plop». El hada cruza los brazos: «Bueno, al menos aprendimos qué no hacer».'],
            'gato' => ['El guardián de los apuntes', 'Un gato duerme sobre un libro. Acepta compartir un secreto si comprendes una de sus preguntas.', 'Leer la pregunta', 'Negociar con el gato', 'El gato no acepta órdenes. Pero acepta que lo intentes, que ya es bastante.', 'El gato te mira en silencio, empuja una piedra de la mesa y vuelve a dormirse.'],
        ];
    }
}
