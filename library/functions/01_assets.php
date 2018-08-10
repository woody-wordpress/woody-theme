<?php

function humanDays($number)
{
    if ($number % 7 === 0) {
        $week_number = $number / 7;
        if ($week_number > 1) {
            $human_string = $week_number . ' semaines';
        } else {
            $human_string = $week_number . ' semaine';
        }
    } else {
        if ($number > 1) {
            $human_string = $number . ' jours';
        } else {
            $human_string = $number . ' jour';
        }
    }

    return $human_string;
}
