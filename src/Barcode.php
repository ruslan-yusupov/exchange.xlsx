<?php

namespace App;


class Barcode
{

    /**
     * @param string $barcode
     * @return bool
     */
    public static function isValid(string $barcode): bool
    {
        $barcode = (string) $barcode;

        if (!preg_match( "/^[0-9]+$/", $barcode )) {
            return false;
        }

        if (13 !== strlen( $barcode )) {
            return false;
        }

        //get check digit
        $check    = substr( $barcode, -1 );
        $barcode  = substr( $barcode, 0, -1 );
        $sumEven = $sumOdd = 0;
        $even     = true;

        while(strlen( $barcode ) > 0) {

            $digit = substr( $barcode, -1 );

            if($even) {
                $sumEven += 3 * $digit;
            } else {
                $sumOdd += $digit;
            }

            $even = !$even;
            $barcode = substr( $barcode, 0, -1 );
        }

        $sum = $sumEven + $sumOdd;
        $sumRoundedUp = ceil($sum/10) * 10;

        return (floatval($check) == ($sumRoundedUp - $sum));
    }

}