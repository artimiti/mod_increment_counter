<?php
defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;

function evaluateExpression($expr) {
    // Разрешённые символы: цифры, пробелы, +, *, -, /, (, )
    if (!preg_match('/^[0-9\s\+\*\-\(\)\/\.]+$/', $expr)) {
        return false;
    }
    // Ограничиваем длину
    if (strlen($expr) > 100) return false;

    // Безопасное вычисление через eval() с жёсткими ограничениями
    try {
        // Дополнительная проверка: только арифметика
        $result = eval('return ' . $expr . ';');
        if (is_numeric($result) && is_finite($result)) {
            return (int) $result;
        }
    } catch (ParseError $e) {
        return false;
    }
    return false;
}

$startNumber = (int) $params->get('start_number', 1);
$increment   = (int) $params->get('increment', 1);
$periodExpr  = trim($params->get('period_expr', '1'));
$startDate   = $params->get('start_date');

if (!$startDate) {
    echo '<div class="increment">—</div>';
    return;
}

$periodSeconds = evaluateExpression($periodExpr);
if ($periodSeconds === false || $periodSeconds <= 0) {
    echo '<div class="increment">Ошибка в периоде</div>';
    return;
}

$now     = new Date();
$startDt = new Date($startDate);

$diffSeconds = $now->toUnix() - $startDt->toUnix();
$periodsPassed = $diffSeconds >= 0 ? (int) floor($diffSeconds / $periodSeconds) : 0;

$result = $startNumber + ($periodsPassed * $increment);

echo '<div class="increment">' . $result . '</div>';