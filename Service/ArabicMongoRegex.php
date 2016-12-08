<?php

namespace Ibtikar\GlanceDashboardBundle\Service;


class ArabicMongoRegex {

    /**
     * @param string $string arabic string to convert into regex
     * @return string regex for string partial content
     */
//    public static function getStringRegex($string) {
//        $stringRegex = preg_replace('/[هة]{1}/u', '[هة]{1}', $string);
//        $stringRegex = preg_replace('/[اأ]{1}/u', '[اأ]{1}', $stringRegex);
//        $stringRegex = preg_replace('/[ىي]{1}/u', '[ىي]{1}', $stringRegex);
//        return $stringRegex;
//    }

    /**
     * @param string $string arabic string to convert into regex
     * @return string regex for exact string content
     */
    public static function getExactStringRegex($string) {
        return '/^' . $string . '$/';
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function slugify($string, $separator = '-') {
        return strtolower(trim(preg_replace('/([_\W])+/u', '-', preg_replace("/[\x{064B}-\x{0653}]/u","",$string)), $separator));
    }

}
