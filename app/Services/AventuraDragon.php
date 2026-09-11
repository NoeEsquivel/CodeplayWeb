<?php

namespace App\Services;

/** Rules shared by the authoritative battle engine and its read-only presentation. */
class AventuraDragon
{
    public static function fase(array $enemy): int
    {
        return $enemy['vida'] * 3 <= $enemy['max'] ? 3 : ($enemy['vida'] * 3 <= $enemy['max'] * 2 ? 2 : 1);
    }

    public static function nombre(int $fase): string
    {
        return [1=>'Escamas de obsidiana',2=>'Alas de la tormenta',3=>'Corazón del volcán'][$fase];
    }

    public static function especial(array $enemy): string
    {
        return [1=>'Aliento del olvido',2=>'Tormenta de runas',3=>'Último eclipse'][self::fase($enemy)];
    }

    public static function prepara(array $enemy, int $turno): bool
    {
        return $turno % (self::fase($enemy) === 3 ? 2 : 3) === 0;
    }
}
