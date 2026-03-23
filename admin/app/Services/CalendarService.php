<?php

declare(strict_types=1);

class CalendarService
{
    public function monthBounds(int $month, int $year): array
    {
        return [mktime(0,0,0,$month,1,$year), mktime(23,59,59,$month+1,0,$year)];
    }

    public function dayBounds(int $timestamp): array
    {
        $m = (int) date('n', $timestamp);
        $d = (int) date('j', $timestamp);
        $y = (int) date('Y', $timestamp);
        return [mktime(0,0,0,$m,$d,$y), mktime(23,59,59,$m,$d,$y)];
    }

    public function groupByDay(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $ts = (int) preg_replace('/\D/', '', (string) $row['entrytime']);
            $day = (int) date('j', $ts);
            $out[$day][] = $row;
        }
        return $out;
    }
}
