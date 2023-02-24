<?php

namespace App\Services\Litecoin;

class PublicKeyToAddress
{


    public function g($orig)
    {
        $bten    = $this->bc_hexdec($orig);
        $base58  = $this->bc_base58_encode($bten);
        $backten = $this->bc_base58_decode($base58);
        $back    = $this->bc_dechex($backten);
        dump("Orig: " . $orig);
        dump("bten: " . $bten);
        dump("58:   " . $base58);
        dump("ag10: " . $backten);
        dump("Back:   " . $back);
    }

    // original arbitrary encode function
    private function arb_encode($num, $basestr)
    {
        $base = strlen($basestr);
        $rep = '';

        while ($num > 0) {
            $rem = $num % $base;
            $rep = $basestr[$rem] . $rep;
            $num = ($num - $rem) / $base;
        }
        return $rep;
    }

    private function arb_decode($num, $basestr)
    {
        $base = strlen($basestr);
        $dec = 0;

        $num_arr = str_split((string)$num);
        $cnt = strlen($num);
        for ($i = 0; $i < $cnt; $i++) {
            $pos = strpos($basestr, $num_arr[$i]);
            if ($pos === false) {
                throw new \Exception(sprintf('Unknown character %s at offset %d', $num_arr[$i], $i));
            }
            $dec = ($dec * $base) + $pos;
        }
        return $dec;
    }

    // BCmath version for huge numbers
    private function bc_arb_encode($num, $basestr)
    {
        if (!function_exists('bcadd')) {
            throw new \Exception('You need the BCmath extension.');
        }

        $base = strlen($basestr);
        $rep = '';

        while (true) {
            if (strlen($num) < 2) {
                if (intval($num) <= 0) {
                    break;
                }
            }
            $rem = bcmod($num, $base);
            $rep = $basestr[intval($rem)] . $rep;
            $num = bcdiv(bcsub($num, $rem), $base);
        }
        return $rep;
    }

    private function bc_arb_decode($num, $basestr)
    {
        if (!function_exists('bcadd')) {
            throw new Exception('You need the BCmath extension.');
        }

        $base = strlen($basestr);
        $dec = '0';

        $num_arr = str_split((string)$num);
        $cnt = strlen($num);
        for ($i = 0; $i < $cnt; $i++) {
            $pos = strpos($basestr, $num_arr[$i]);
            if ($pos === false) {
                throw new Exception(sprintf('Unknown character %s at offset %d', $num_arr[$i], $i));
            }
            $dec = bcadd(bcmul($dec, $base), $pos);
        }
        return $dec;
    }


    // base 58 alias
    private function bc_base58_encode($num)
    {
        return $this->bc_arb_encode($num, '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ');
    }

    private function bc_base58_decode($num)
    {
        return $this->bc_arb_decode($num, '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ');
    }

    //hexdec with BCmath
    private function bc_hexdec($num)
    {
        return $this->bc_arb_decode(strtolower($num), '0123456789abcdef');
    }

    private function bc_dechex($num)
    {
        return $this->bc_arb_encode($num, '0123456789abcdef');
    }
}
