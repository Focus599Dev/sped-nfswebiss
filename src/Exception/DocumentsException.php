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

class DocumentsException extends \InvalidArgumentException implements ExceptionInterface
{
    public static $list = [
        0 => "",
        12 => "O TXT não representa uma NFSe",
        13 => "O numero de notas indicado na primeira linha do TXT é diferente do numero total de notas do txt.",
        16 => "O txt tem um campo não definido {{msg}}",
        17 => "O txt não está no formato adequado.",
    ];
    
    public static function wrongDocument($code, $msg = '')
    {
        $msg = self::replaceMsg(self::$list[$code], $msg);
        return new static($msg);
    }
    
    private static function replaceMsg($input, $msg)
    {
        return str_replace('{{msg}}', $msg, $input);
    }
}
