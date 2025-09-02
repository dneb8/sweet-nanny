<?php

namespace App\Classes;

use Illuminate\Support\Str;
use Carbon\Carbon;

class Utility
{
    /**
     * Función que se encarga de sanitizar una cadena recibida.
     */
    public static function sanitizarString(?String $string): String 
    {
        if ( !$string ) return "";
        
        return Str::squish($string);
    }

    /**
     * Función que retorna una fecha formateada al español.
     */
    public static function fechaEsp(String $fecha, Bool $dia = false, Bool $hora = false): String
    {
        $mes = array("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");
        Carbon::setLocale('es');
        $f = Carbon::parse($fecha);
        return  ($dia?$f->dayName.' ':''). $f->day .' de '. $mes[($f->month)-1] . " de ". $f->year . ($hora?', a las '.$f->hour.':'.($f->minute < 10 ? '0'.$f->minute : $f->minute): '');
    }

    /**
     * Función que retorna una fecha formateada al español (formato corto).
     */
    public static function fechaCorta(String $fecha): String
    {
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        Carbon::setLocale('es');
        $f = Carbon::parse($fecha);
        return $f->day . '/' . $meses[$f->month-1] . '/' . $f->format('y');
    }

    /**
     * Function to strip accents from string
     */
    public static function stripAccents(String $string): String
    {
        return strtr(utf8_decode($string), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }

    /**
     * Función que retorna un string con un monto formateado $#,###,###.##
     */
    public static function montoFormateado (Int|Float $monto): String
    {
        return '$' . number_format($monto, 2, '.', ',');
    }

    /**
     * Función que calcula el IVA de un monto.
     */
    public static function ivaMonto (Int|Float $monto): Int|Float
    {
        return ($monto) - ($monto / 1.16);
    }

    /**
     * Función que resta el IVA a un monto.
     */
    public static function montoSinIva (Int|Float $monto): Int|Float
    {
        return $monto / 1.16;
    }

    /**
     * Genera un listado enumerado según la cantidad de elementos de un arreglo.
     * E.g
     * 
     * Input: ['Hola', 'cómo', 'estás?']
     * 
     * Output:
     * ```
     * 1.- Hola
     * 2.- cómo
     * 3.- estás?
     * ```
     * 
     * @param array<string> $items, elementos a imprimir en la lista.
     * @param bool $ordered, determina si la lista va numerada.
     * @param string $prefix, prefijo entre número (si aplica) y el inicio de cada item
     * @param string $suffix, $sufijo entre el final de cada item y el inicio del siguiente.
     */
    public static function listFromArray(array $items, bool $orderedList = true, string $prefix = '.- ', string $suffix = '.'): string
    {
        $list = <<<EOT
        
        EOT;

        collect($items)->each(function ($item, $index) use (&$list, $items, $orderedList, $prefix, $suffix) {
            $newLine = $orderedList 
            ? ($index + 1) . $prefix . $item . $suffix
            : $prefix . $item . $suffix;

            if (count($items) - 1 === $index) {
                $list .= <<<EOT
                $newLine
                EOT;
            } else {
                $list .= <<<EOT
                $newLine

                EOT;
            }
        });

        return $list;
    }
}