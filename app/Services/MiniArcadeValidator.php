<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

final class MiniArcadeValidator
{
    private const KEYS = ['menuTitle', 'menuDescription', 'snakeMove', 'snakeWall', 'snakeScore',
        'memoryMatch', 'memoryScore', 'memoryWin', 'structure'];

    public function documents(string $code): ?array
    {
        if (strlen($code) > 60000) {
            return null;
        }
        $documents = json_decode($code, true, 8);
        if (! is_array($documents) || count($documents) !== 3) {
            return null;
        }
        foreach (['menu', 'culebrita', 'memoria'] as $name) {
            if (! isset($documents[$name]) || ! is_string($documents[$name]) || trim($documents[$name]) === '') {
                return null;
            }
        }

        return $documents;
    }

    public function passes(string $code): bool
    {
        return ! in_array(false, $this->checks($code), true);
    }

    public function checks(string $code): array
    {
        $checks = array_fill_keys(self::KEYS, false);
        $documents = $this->documents($code);
        if (! $documents) {
            return $checks;
        }
        $pages = [];
        $scripts = [];
        foreach ($documents as $name => $html) {
            $dom = $this->document($html);
            if (! $dom) {
                return $checks;
            }
            $pages[$name] = new DOMXPath($dom);
            $scripts[$name] = '';
            foreach ($dom->getElementsByTagName('script') as $script) {
                if (! $script->hasAttribute('src') && (! $script->hasAttribute('type')
                    || in_array($script->getAttribute('type'), ['text/javascript', 'application/javascript'], true))) {
                    $scripts[$name] .= "\n".$script->textContent;
                }
            }
        }
        $title = $this->text($pages['menu'], '//h1');
        $description = $this->text($pages['menu'], '//*[@id="descripcion"]');
        $checks['menuTitle'] = mb_strlen($title) >= 4 && mb_strtolower($title) !== 'mi mini arcade';
        $checks['menuDescription'] = mb_strlen($description) >= 35
            && ! str_contains(mb_strtolower($description), 'escribe aquí cómo jugar');
        $checks['structure'] = $this->exists($pages['menu'], '//button[@data-vista="culebrita"] | //a[@data-vista="culebrita"]')
            && $this->exists($pages['menu'], '//button[@data-vista="memoria"] | //a[@data-vista="memoria"]')
            && $this->exists($pages['culebrita'], '//canvas[@id="tableroSerpiente"]')
            && $this->exists($pages['memoria'], '//div[@id="tableroMemoria"]');
        foreach (['culebrita', 'memoria'] as $game) {
            $checks['structure'] = $checks['structure']
                && $this->exists($pages[$game], '//*[@id="puntos"]')
                && $this->exists($pages[$game], '//button[@data-vista="menu"] | //a[@data-vista="menu"]');
        }

        $checks['snakeMove'] = $this->functionPasses($scripts['culebrita'], 'siguienteCabeza', ['cabeza', 'direccion'], [
            [[['x' => 3, 'y' => 4], ['x' => 1, 'y' => 0]], ['x' => 4, 'y' => 4]],
            [[['x' => 8, 'y' => 2], ['x' => -1, 'y' => 0]], ['x' => 7, 'y' => 2]],
            [[['x' => 2, 'y' => 9], ['x' => 0, 'y' => -1]], ['x' => 2, 'y' => 8]],
            [[['x' => 2, 'y' => 9], ['x' => 0, 'y' => 1]], ['x' => 2, 'y' => 10]],
        ]);
        $wallCases = [];
        foreach ([12, 16] as $limit) {
            foreach ([[0, 0, false], [$limit - 1, $limit - 1, false], [5, 4, false],
                [-1, 4, true], [4, -1, true], [$limit, 4, true], [4, $limit, true]] as [$x, $y, $expected]) {
                $wallCases[] = [[['x' => $x, 'y' => $y], $limit], $expected];
            }
        }
        $checks['snakeWall'] = $this->functionPasses($scripts['culebrita'], 'chocaConPared', ['cabeza', 'limite'], $wallCases);
        $scoreCases = [[[0], 10], [[10], 20], [[50], 60], [[120], 130]];
        $checks['snakeScore'] = $this->functionPasses($scripts['culebrita'], 'puntosPorComida', ['puntos'], $scoreCases);
        $checks['memoryMatch'] = $this->functionPasses($scripts['memoria'], 'sonPareja', ['primera', 'segunda'], [
            [['luna', 'luna'], true], [['sol', 'luna'], false], [['estrella', 'estrella'], true], [['luna', 'sol'], false],
        ]);
        $checks['memoryScore'] = $this->functionPasses($scripts['memoria'], 'puntosPorPareja', ['puntos'], [
            [[0], 20], [[20], 40], [[60], 80], [[100], 120],
        ]);
        $checks['memoryWin'] = $this->functionPasses($scripts['memoria'], 'partidaTerminada', ['parejas'], [
            [[0], false], [[1], false], [[5], false], [[6], true],
        ]);

        return $checks;
    }

    private function document(string $html): ?DOMDocument
    {
        if (! preg_match('/<html\b/i', $html) || ! preg_match('/<body\b/i', $html)
            || preg_match('/<!ENTITY\b|<!DOCTYPE[^>]*\b(?:SYSTEM|PUBLIC)\b/i', $html)) {
            return null;
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $dom = new DOMDocument;
            $dom->resolveExternals = false;
            $dom->substituteEntities = false;

            return $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET) ? $dom : null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function exists(DOMXPath $page, string $path): bool
    {
        return $page->query($path)->length > 0;
    }

    private function text(DOMXPath $page, string $path): string
    {
        return trim(preg_replace('/\s+/u', ' ', $page->query($path)->item(0)->textContent ?? '') ?? '');
    }

    /** Tokenization keeps quoted strings intact and removes comments, preventing fake functions in hints. */
    private function tokens(string $script): array
    {
        $tokens = [];
        $offset = 0;
        $pattern = '~\G(\s+|//[^\r\n]*|/\*[\s\S]*?\*/|"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'|`(?:\\\\.|[^`\\\\])*`|[A-Za-z_$][A-Za-z0-9_$]*|\d+(?:\.\d+)?|===|!==|==|!=|<=|>=|&&|\|\||.)~s';
        while (preg_match($pattern, $script, $match, 0, $offset)) {
            $token = $match[1];
            $offset += strlen($token);
            if (ctype_space($token) || str_starts_with($token, '//') || str_starts_with($token, '/*')) {
                // JavaScript inserts a semicolon if return is followed by a line break.
                if (end($tokens) === 'return' && (str_contains($token, "\n") || str_contains($token, "\r"))) {
                    $tokens[] = ';';
                }

                continue;
            }
            $tokens[] = $token;
        }

        return $tokens;
    }

    private function functionPasses(string $script, string $name, array $parameters, array $cases): bool
    {
        $tokens = $this->tokens($script);
        $bodies = [];
        for ($index = 0, $count = count($tokens); $index < $count - 2; $index++) {
            if ($tokens[$index] !== 'function' || $tokens[$index + 1] !== $name || $tokens[$index + 2] !== '(') {
                continue;
            }
            $cursor = $index + 3;
            $actual = [];
            while ($cursor < $count && $tokens[$cursor] !== ')') {
                $actual[] = $tokens[$cursor++];
            }
            if (implode('', $actual) !== implode(',', $parameters) || ($tokens[++$cursor] ?? '') !== '{') {
                return false;
            }
            $start = ++$cursor;
            $depth = 1;
            while ($cursor < $count && $depth > 0) {
                if ($tokens[$cursor] === '{') {
                    $depth++;
                } elseif ($tokens[$cursor] === '}') {
                    $depth--;
                }
                $cursor++;
            }
            if ($depth !== 0) {
                return false;
            }
            $bodies[] = array_slice($tokens, $start, $cursor - $start - 1);
        }
        if (count($bodies) !== 1) {
            return false;
        }
        $body = $bodies[0];
        if (array_shift($body) !== 'return') {
            return false;
        }
        if (end($body) === ';') {
            array_pop($body);
        }
        try {
            foreach ($cases as [$arguments, $expected]) {
                $result = (new MiniArcadeExpression)->calculate($body, array_combine($parameters, $arguments));
                if (is_array($expected) && is_array($result)) {
                    ksort($expected);
                    ksort($result);
                }
                if ($result !== $expected) {
                    return false;
                }
            }
        } catch (\Throwable $error) {
            return false;
        }

        return true;
    }
}
