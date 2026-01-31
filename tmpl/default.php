<?php
defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;

// === Получение параметров ===
$startNumber = (int) $params->get('start_number', 1);
$increment   = (int) $params->get('increment', 1);
$periodExpr  = trim($params->get('period_expr', '1'));
$startDate   = $params->get('start_date');

if (!$startDate) {
    echo '<div class="increment">—</div>';
    return;
}

// === Встроенная безопасная проверка выражения (без функции) ===
$periodSeconds = false;
if (preg_match('/^[0-9\s\+\*\-\(\)\/\.]+$/', $periodExpr) && strlen($periodExpr) <= 100) {
    try {
        $evalResult = eval('return ' . $periodExpr . ';');
        if (is_numeric($evalResult) && is_finite($evalResult)) {
            $periodSeconds = (int) $evalResult;
        }
    } catch (ParseError $e) {
        $periodSeconds = false;
    }
}

if ($periodSeconds === false || $periodSeconds <= 0) {
    echo '<div class="increment">Ошибка в периоде</div>';
    return;
}

// === Расчёт в UTC (Joomla сохраняет дату как UTC) ===
$startDt = new Date(substr($startDate, 0, 10) . ' 00:00:00', 'UTC');
$now     = new Date('now', 'UTC');

$diffSeconds = $now->toUnix() - $startDt->toUnix();
$periodsPassed = $diffSeconds >= 0 ? (int) floor($diffSeconds / $periodSeconds) : 0;

$result = $startNumber + ($periodsPassed * $increment);

$formatted = number_format($result, 0, '', ' ');
echo '<div class="increment">' . $formatted . '</div>';