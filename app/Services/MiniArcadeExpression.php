<?php

namespace App\Services;

/**
 * Parser of the small return-expression language taught in challenge 20.
 * It never invokes student functions, PHP eval, JavaScript, SQL or a process.
 * Calls, assignments, loops, getters and access outside supplied values are rejected.
 */
final class MiniArcadeExpression
{
    private array $tokens;
    private array $values;
    private int $position = 0;

    public function calculate(array $tokens, array $values)
    {
        if (count($tokens) > 128) {
            throw new \InvalidArgumentException('Expression too long');
        }
        $this->tokens = $tokens;
        $this->values = $values;
        $this->position = 0;
        $result = $this->expression(0, 0);
        if ($this->position !== count($this->tokens)) {
            throw new \InvalidArgumentException('Unsupported expression');
        }
        return $result;
    }

    private function expression(int $precedence, int $depth)
    {
        if ($depth > 24) {
            throw new \InvalidArgumentException('Expression too deep');
        }
        $left = $this->value($depth + 1);
        $priorities = ['||' => 1, '&&' => 2, '===' => 3, '!==' => 3, '==' => 3, '!=' => 3,
            '<' => 4, '>' => 4, '<=' => 4, '>=' => 4, '+' => 5, '-' => 5];
        while (isset($priorities[$this->peek()]) && $priorities[$this->peek()] >= $precedence) {
            $operator = $this->take();
            $right = $this->expression($priorities[$operator] + 1, $depth + 1);
            $left = $this->operate($operator, $left, $right);
        }
        return $left;
    }

    private function value(int $depth)
    {
        $token = $this->take();
        if ($token === '(') {
            $value = $this->expression(0, $depth + 1);
            $this->expect(')');
            return $value;
        }
        if ($token === '!' || $token === '-' || $token === '+') {
            $value = $this->value($depth + 1);
            if ($depth > 24 || ($token !== '!' && !is_numeric($value))) {
                throw new \InvalidArgumentException('Invalid unary value');
            }
            return $token === '!' ? !$value : ($token === '-' ? -$value : +$value);
        }
        if ($token === '{') {
            $object = [];
            do {
                $key = trim($this->take(), "\"'");
                if (!in_array($key, ['x', 'y'], true) || array_key_exists($key, $object)) {
                    throw new \InvalidArgumentException('Invalid object key');
                }
                $this->expect(':');
                $object[$key] = $this->expression(0, $depth + 1);
                if ($this->peek() !== ',') {
                    break;
                }
                $this->take();
                if ($this->peek() === '}') {
                    break;
                }
            } while (true);
            $this->expect('}');
            return $object;
        }
        if (in_array($token, ['true', 'false', 'null'], true)) {
            return ['true' => true, 'false' => false, 'null' => null][$token];
        }
        if (preg_match('/^\d+(?:\.\d+)?$/D', $token)) {
            return str_contains($token, '.') ? (float) $token : (int) $token;
        }
        if (!array_key_exists($token, $this->values)) {
            throw new \InvalidArgumentException('Unknown value');
        }
        $value = $this->values[$token];
        if ($this->peek() === '.') {
            $this->take();
            $key = $this->take();
            if (!is_array($value) || !in_array($key, ['x', 'y'], true) || !array_key_exists($key, $value)) {
                throw new \InvalidArgumentException('Invalid property');
            }
            $value = $value[$key];
        }
        return $value;
    }

    private function operate(string $operator, $left, $right)
    {
        if (in_array($operator, ['+', '-', '<', '>', '<=', '>='], true)
            && ((!is_int($left) && !is_float($left)) || (!is_int($right) && !is_float($right)))) {
            throw new \InvalidArgumentException('Expected numeric values');
        }
        return match ($operator) {
            '+' => $left + $right,
            '-' => $left - $right,
            '<' => $left < $right,
            '>' => $left > $right,
            '<=' => $left <= $right,
            '>=' => $left >= $right,
            '===', '==' => $left === $right,
            '!==', '!=' => $left !== $right,
            '&&' => (bool) $left && (bool) $right,
            '||' => (bool) $left || (bool) $right,
        };
    }

    private function peek(): string
    {
        return $this->tokens[$this->position] ?? '';
    }

    private function take(): string
    {
        if (!array_key_exists($this->position, $this->tokens)) {
            throw new \InvalidArgumentException('Unexpected end');
        }
        return $this->tokens[$this->position++];
    }

    private function expect(string $expected): void
    {
        if ($this->take() !== $expected) {
            throw new \InvalidArgumentException('Unexpected token');
        }
    }
}
