<?php

namespace NFePHP\NFSe\GINFE\Exception;

/**
 * @category   NFePHP
 * @package    NFePHP\NFSe\GINFE\Exception
 * @copyright  Copyright (c) 2008-2019
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Marlon O. Barbosa <lmarlon.academi at gmail dot com>
 * @link       https://github.com/Focus599Dev/sped-nfsginfe for the canonical source repository
 */

class SoapException extends \RuntimeException implements ExceptionInterface
{
    public static function unableToLoadCurl($message)
    {
        return new static('Unable to load cURL, '
            . 'verify if libcurl is installed. ' . $message);
    }

    public static function soapFault($message)
    {
        return new static(
            'An error occurred while trying to communication via soap, '
            . $message);
    }
}
