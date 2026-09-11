@if($enemigo['clave']==='dragon')
<path d="M58 40 29 13 5 20l9 39 20-12 15 26-18 17 33-6 12 13 18-8-10-20 21-22 22 12 8-39-25-7-27 27-1-18-12-13-4 17-14-5Z"/><path class="detail" d="m17 26 31 35m75-35L92 61M60 42l8 4m9-4 8-4M66 57l13 1M57 71l20 8m-9-14 8 26M26 29l2 20m86-20-2 20"/>
@elseif($enemigo['clave']==='archivista')
<path d="M35 30 70 12l35 18v59H35ZM46 52q12-5 24 3 12-8 24-3v27q-12-4-24 3-12-7-24-3Z"/><path class="detail" d="M58 35h7m11 0h7M70 55v27m-21-20h12m18 0h12"/>
@elseif($enemigo['clave']==='matriarca')
<path d="M50 40Q38 13 70 12q33 2 21 28L78 54q40 13 28 36H30q-13-22 31-34ZM49 22 39 8l22 9 10-13 10 13 22-9-11 17"/><path class="detail" d="M57 34h7m12 0h7M62 44l8 9 8-9M43 78q30-15 49 0"/>
@elseif($enemigo['clave']==='titan')
<path d="m40 30 17-22h25l19 22-9 19 20 36-24 10-17-16-18 16-25-10 20-36Z"/><path class="detail" d="m43 29 27 10 30-10M70 9v70M52 45l10 4m17 0 9-4M45 72l18-8m17 0 15 8"/>
@elseif($enemigo['habilidad']==='vampiro')
<path d="m50 40-33-17 8 30-15 23 43 10h36l41-10-17-23 10-30-34 17Q83 9 68 13Q53 11 50 40Z"/><path class="detail" d="m55 44 9 3m13 0 9-3m-27 17 5 13 6-9 6 9 5-13M32 46l17 24m59-24L90 70"/>
@elseif(in_array($enemigo['habilidad'],['invocar','drenar','resiste_magia']))
<path d="M39 89 50 32 70 10l20 22 11 57ZM105 21h8v69h-8ZM103 18l6-13 10 13-10 12Z"/><path class="detail" d="m52 38 18-11 18 11M59 49h7m11 0h7M55 74h32m-18-8v20"/>
@elseif(in_array($enemigo['habilidad'],['coraza','resiste_fisico']))
<path d="m43 19 27-9 28 9v28L86 60l18 27H35l19-27-11-13ZM25 46l26-8 19 12-8 32-24 10-15-28Z"/><path class="detail" d="M53 30h35M59 40h8m10 0h8M35 55l15 21 10-21M76 67v18"/>
@elseif($enemigo['habilidad']==='division')
<path d="M24 83Q10 54 46 40Q65 14 92 36q40 18 23 48Z"/><path class="detail" d="M51 56v10m34-10v10m-24 9h16"/>
@elseif($enemigo['habilidad']==='veneno')
<path d="M39 82q-24-24 3-38l28-16q-10-17 10-17 30 4 17 28L65 65q-9 13 20 15l25-15 13 20q-32 25-84-3Z"/><path class="detail" d="M79 23h9M88 38l18 2 9-5m-9 5 9 6M44 57l10 13"/>
@elseif($enemigo['habilidad']==='embestida')
<path d="M37 83 29 53l15-25 14-9h26l15 9 13 27-10 28-23 8-9-12-12 12Z"/><path class="detail" d="M53 42h10m15 0h10M60 59l10 7 10-7m-31 14 10 9m22 0 10-9"/>
@else
<path d="m45 30 24-16 25 16 5 40-19 20H58L40 70Z"/><path class="detail" d="M44 43h52M54 42v9m30-9v9M60 70h22M32 30l-12 18 12 13m77-31 11 18-11 13"/>
@endif
