<?php
namespace App\Classes\Computation;

class TimeLog {

    public function late_hours($in, $start) {
        $late_hours = 0;

        if($in <= $start) {
            $late_hours = 0;
        }
        else {
            $late_hours = ($in - $start)/3600;
        }

        return $late_hours;
    }

    public function overtime($out, $end) {
        $overtime = 0;

        if($end <= $out) {
            $overtime = ($out - $end)/3600;
        }
        else {
            $overtime = 0;
        }

        return $overtime;
    }

    public function undertime($out, $end) {
        $undertime = 0;

        if($end <= $out) {
            $undertime = 0;
        }
        else {
            $undertime = ($end - $out)/3600;
        }

        return $undertime;
    }

    public function subtotal($start, $end, $out) {
        $subtotal = 0;

        if($end > $out) {
            $subtotal = ($end - $start)/3600;
        }
        else {
            $subtotal = ($out - $start)/3600;
        }

        return $subtotal;
    }

    public function total_hours($subtotal, $late_hours, $overtime, $undertime) {
        $total_hours = 0;

        $total_hours = ($subtotal - ($late_hours + $overtime + $undertime));

        return $total_hours;
    }
}
?>