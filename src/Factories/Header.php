<?php

namespace NFePHP\NFSe\GINFE\Factories;

/**
 * @category   NFePHP
 * @package    NFePHP\NFSe\GINFE\Factories\
 * @copyright  Copyright (c) 2008-2019
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Marlon O. Barbosa <lmarlon.academi at gmail dot com>
 * @link       https://github.com/Focus599Dev/sped-nfsginfe for the canonical source repository
 */


class Header
{
    /**
     * Return header
     * @param string $namespace
     * @param int $cUF
     * @param string $version
     * @return string
     */
    public static function get($version)
    {
        return "<cabecalho "
            . "xmlns=\"http://www.ginfes.com.br/tipos_v03.xsd\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" versao=\"$version\">"
            . "<versaoDados>$version</versaoDados>"
            . "</cabecalho>";
    }
}
