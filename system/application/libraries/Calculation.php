<?php
/**
* Mathematics functions, for conversion rates etc.
*/

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Calculation {

    /*
     * derive conversion rate, which is calculated differently depending on the goal type
     */
    public static function getConversionRate($impressions, $conversions, $aggregatedValue, $goalType) {
        if($goalType == GOAL_TYPE_TIMEONPAGE) {
            $cr = ($conversions == 0) ? 0 : ($aggregatedValue / $conversions);
        }
        elseif($goalType == GOAL_TYPE_PI_LIFT) {
            $cr = ($conversions == 0) ? 0 : ($aggregatedValue / $conversions);
        }
        else {
            $cr = ($impressions == 0) ? 0 : ($conversions / $impressions);        
        }
        return (double)$cr;
    }

    /**
     * Given the z_score for a variant, ir returns the confidence for it
     * @param float $zscore
     * @return float
     */
    public static function getConfidence($zscore) {
        $b1 = 0.319381530;
        $b2 = -0.356563782;
        $b3 = 1.781477937;
        $b4 = -1.821255978;
        $b5 = 1.330274429;
        $p = 0.2316419;
        $c = 0.39894228;

        if ($zscore >= 0.0) {
            $t = 1.0 / ( 1.0 + $p * $zscore );
            return (1.0 - $c * exp(-$zscore * $zscore / 2.0) * $t *
                    ( $t * ( $t * ( $t * ( $t * $b5 + $b4 ) + $b3 ) + $b2 ) + $b1 ));
        } else {
            $t = 1.0 / ( 1.0 - $p * $zscore );
            return ( $c * exp(-$zscore * $zscore / 2.0) * $t *
                    ( $t * ( $t * ( $t * ( $t * $b5 + $b4 ) + $b3 ) + $b2 ) + $b1 ));
        }
    }

    /*
     * Calculate standard deviation depending on goal type
     */
    public static function calcStdDev($cr, $aggregated_value, $aggregated_square_value , $conversions, $goalType) {
        if(($goalType == GOAL_TYPE_TIMEONPAGE) || ($goalType == GOAL_TYPE_PI_LIFT) 
            || ($goalType == GOAL_TYPE_COMBINED)) {
            $q1 = $aggregated_value;
            $q2 = $aggregated_square_value;
            if($conversions == 0) {
                $stddev = 0;
            }
            else {
                $Q = $q2 - (pow($q1,2) / $conversions);
                $stddev = sqrt($Q / ($conversions - 1));  
            }
            return $stddev;
        }
        else {
            $stddev = sqrt($cr*(1-$cr));
        }
        return $stddev;
    }

    /*
     *  Formula to calculate a z-score
     */
    public static function calcZscore($controlData, $variantData, $goaltype) {
        $CI = & get_instance();
        $min_impressions = $CI->config->item('MIN_IMPRESSIONS');
        $min_conversions = $CI->config->item('MIN_CONVERSIONS');
        if($goaltype == GOAL_TYPE_TIMEONPAGE) {
            $n = $variantData['conversions'];
            $nCtrl = $controlData['conversions'];
            if(($n < $min_conversions) || ($nCtrl < $min_conversions)) {
                return 0;                
            }
        }
        else if($goaltype == GOAL_TYPE_PI_LIFT) {
            $n = $variantData['conversions'];
            $nCtrl = $controlData['conversions'];
            if(($n < $min_conversions) || ($nCtrl < $min_conversions)) {
                return 0;                
            }
        }
        else if($goaltype == GOAL_TYPE_COMBINED) {
            $i = $variantData['impressions'];
            $iCtrl = $controlData['impressions'];
            if(($i < $min_impressions) || ($iCtrl < $min_impressions)) {
                return 0;                
            }
            $n = $variantData['conversions'];
            $nCtrl = $controlData['conversions'];
            if(($n < $min_conversions) || ($nCtrl < $min_conversions)) {
                return 0;                
            }
        }
        else {
            $n = $variantData['impressions'];
            $nCtrl = $controlData['impressions'];
            if(($n < $min_impressions) || ($nCtrl < $min_impressions)) {
                return 0;                
            }
            $conv = $variantData['conversions'];
            $convCtrl = $controlData['conversions'];
            if(($conv < $min_conversions) || ($convCtrl < $min_conversions)) {
                return 0;                
            }
        }

        $cr = $variantData['cr'];
        $crCtrl = $controlData['cr'];
        $stddev = $variantData['standard_deviation'];
        $stddevCtrl = $controlData['standard_deviation'];

        $denominator = sqrt(pow($stddev,2)/$n + pow($stddevCtrl,2)/$nCtrl);
        if($denominator!=0) {
            $z_score = ABS(($cr - $crCtrl) / $denominator);                                     
        }
        else {
            $z_score = 0;
        }
        //dblog_debug("z-score: $z_score calcZscore controldata: " . print_r($controlData,true) . " variantdata:" . print_r($variantData,true) . " goaltype:$goaltype");
        return $z_score;
    }

}