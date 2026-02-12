<?php
namespace App\Classes\TimeKeeping;
class TimeLog {
    public function query($first, $last) {
        $script = "employees.id,
        employees.firstname,
        employees.middlename,
        employees.lastname,
        employees.suffix,
        employees.profile_img,
        MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 1 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',earnings.name,'|',time_logs.status,'|',time_logs.id) END) END) sun,
        MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 2 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',earnings.name,'|',time_logs.status,'|',time_logs.id) END) END) mon,
        MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 3 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',earnings.name,'|',time_logs.status,'|',time_logs.id) END) END) tue,
        MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 4 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',earnings.name,'|',time_logs.status,'|',time_logs.id) END) END) wed,
        MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 5 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',earnings.name,'|',time_logs.status,'|',time_logs.id) END) END) thu,
        MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 6 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',earnings.name,'|',time_logs.status,'|',time_logs.id) END) END) fri,
        MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 7 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',earnings.name,'|',time_logs.status,'|',time_logs.id) END) END) sat,
        'PENDING' as status,
        CASE WHEN SUM(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN time_logs.total_hours END) IS NOT NULL THEN SUM(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN time_logs.total_hours END) ELSE 0 END as 'reg',
        CASE WHEN SUM(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN time_logs.ot_hours END) IS NOT NULL THEN SUM(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN time_logs.ot_hours END) ELSE 0 END as 'ot',
        0 as 'sick',
        0 as 'vac',
        CASE WHEN SUM(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN time_logs.total_hours END) IS NOT NULL THEN SUM(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN time_logs.total_hours END) ELSE 0 END + CASE WHEN SUM(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN time_logs.ot_hours END) IS NOT NULL THEN SUM(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN time_logs.ot_hours END) ELSE 0 END + 0 + 0 as 'total'";

        return $script;
    }

    public function time_logs($first, $last) {
        $script = "MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 1 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',time_logs.type,'|',time_logs.ot_hours,'|',time_logs.late_hours,'|',time_logs.undertime,'|',time_logs.schedule_status) END) END) sun,
        MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 2 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',time_logs.type,'|',time_logs.ot_hours,'|',time_logs.late_hours,'|',time_logs.undertime,'|',time_logs.schedule_status) END) END) mon,
        MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 3 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',time_logs.type,'|',time_logs.ot_hours,'|',time_logs.late_hours,'|',time_logs.undertime,'|',time_logs.schedule_status) END) END) tue,
        MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 4 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',time_logs.type,'|',time_logs.ot_hours,'|',time_logs.late_hours,'|',time_logs.undertime,'|',time_logs.schedule_status) END) END) wed,
        MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 5 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',time_logs.type,'|',time_logs.ot_hours,'|',time_logs.late_hours,'|',time_logs.undertime,'|',time_logs.schedule_status) END) END) thu,
        MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 6 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',time_logs.type,'|',time_logs.ot_hours,'|',time_logs.late_hours,'|',time_logs.undertime,'|',time_logs.schedule_status) END) END) fri,
        MAX(CASE WHEN time_logs.date >= '".$first."' AND time_logs.date <= '".$last."' THEN (CASE WHEN DAYOFWEEK(time_logs.date) = 7 THEN CONCAT(time_logs.time_in,'|',time_logs.time_out,'|',time_logs.total_hours,'|',time_logs.type,'|',time_logs.ot_hours,'|',time_logs.late_hours,'|',time_logs.undertime,'|',time_logs.schedule_status) END) END) sat";

        return $script;
    }

}
?>