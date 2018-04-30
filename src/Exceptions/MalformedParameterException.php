<?php
/**
 * Created by PhpStorm.
 * User: janwaldecker
 * Date: 29.04.18
 * Time: 19:08
 */

namespace NicAPI;


class MalformedParameterException extends \Exception
{

    /**
     * MalformedParameterException constructor.
     * @param string $string
     */
    public function __construct($string)
    {
        parent::__construct($string);
    }
}