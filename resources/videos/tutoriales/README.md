# Videos de tutoriales con requisitos previos

Los videos de los tutoriales 2 al 10 se almacenan en esta carpeta, fuera de `public`,
para que CodePlay compruebe la sesión del alumno, la visita a la documentación y
los intentos de test antes de reproducirlos. Se sirven mediante `tutorial.video`.

Para reemplazar un video, actualiza el MP4 de esta carpeta con el mismo nombre y
registra el archivo en Git. No coloques copias en `public/videos`, porque podrían
abrirse sin comprobar los requisitos. El video del primer tutorial permanece en
`public/videos/Primera_pagina.mp4` y no exige requisitos previos.

Los temas requeridos se definen en `App\Services\TutorialPrerequisiteService`.
Los registros existentes en `progreso_documentacion` y `resultados_test` cuentan
para el desbloqueo; no se exige una nota mínima ni un intento reciente.
