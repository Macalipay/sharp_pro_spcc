<?php
namespace App\Classes\Computation\Payroll;

class Salary {

    public function annual($entry, $type) {
        switch($type) {
            case "monthly":
                $output = $entry * 12;
                return $output;

                break;

            case "semi_monthly":
                $output = $entry * 24;
                return $output;
                
                break;
                
            case "weekly":
                $output = $entry * 52;
                return $output;
                
                break;
                
            case "daily":
                $output = $entry * 312;
                return $output;
                
                break;

            case "hourly":
                $output = $entry * 2496;
                return $output;
                
                break;

            default:
                return false;
        }
    }

    public function monthly($entry, $type) {
        switch($type) {
            case "annual":
                $output = $entry/12;
                return $output;

                break;
            case "semi_monthly":
                $output = $entry * 2;
                return $output;
                
                break;
                
            case "weekly":
                $output = $entry * 4;
                return $output;
                
                break;
                
            case "daily":
                $output = $entry * 26;
                return $output;
                
                break;

            case "hourly":
                $output = $entry * 208;
                return $output;
                
                break;
            default:
                return false;
        }
    }

    public function semi_monthly($entry, $type) {
        switch($type) {
            case "annual":
                $output = $entry/24;
                return $output;
                
                break;

            case "monthly":
                $output = $entry/2;
                return $output;
                
                break;
                
            case "weekly":
                $output = $entry * 2;
                return $output;
                
                break;
                
            case "daily":
                $output = $entry * 13;
                return $output;
                
                break;

            case "hourly":
                $output = $entry * 104;
                return $output;
                
                break;
            default:
                return $entry;
        }
    }
    
    public function weekly($entry, $type) {
        switch($type) {
            case "annual":
                $output = $entry/52;
                return $output;
                
                break;

            case "monthly":
                $output = ($entry*12)/52;
                return $output;
                
                break;

            case "semi_monthly":
                $output = $entry/2;
                return $output;
                
                break;

            case "daily":
                $output = (($entry * 26)*12)/52;
                return $output;
                
                break;

            case "hourly":
                $output = $entry * 52;
                return $output;
                
                break;
                
            default:
                return false;
        }
    }

    public function daily($entry, $type) {
        switch($type) {
            case "annual":
                $output = $entry/288;
                return $output;
                
                break;
            case "monthly":
                $output = $entry/26;
                return $output;
                
                break;
            case "semi_monthly":
                $output = $entry/12;
                return $output;
                
                break;

            case "weekly":
                $output = $entry/4;
                return $output;
                
                break;

            case "hourly":
                $output = $entry * 8;
                return $output;
                
                break;
            default:
                return false;
        }
    }

    public function hourly($entry, $type) {
        switch($type) {
            case "annual":
                $output = $entry/2496;
                return $output;
                
                break;
            case "monthly":
                $output = $entry/208;
                return $output;
                
                break;

            case "semi_monthly":
                $output = $entry/104;
                return $output;
                
                break;

            case "weekly":
                $output = $entry/52;
                return $output;
                
                break;

            case "daily":
                $output = $entry/8;
                return $output;
                
                break;

            default:
                return false;
        }
    }

}