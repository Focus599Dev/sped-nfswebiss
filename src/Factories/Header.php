<?php

namespace NFePHP\NFSe\GINFE\Factories;

/**
 * @category   NFePHP
 * @package    NFePHP\NFSe\GINFE\Factories\
 * @copyright  Copyright (c) 2008-2019
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Marlon O. Barbosa <marlon.academi at gmail dot com>
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
        return "<ns2:cabecalho "
            . "versao=\"$version\" xmlns:ns2=\"http://www.ginfes.com.br/cabecalho_v03.xsd\">"
            . "<versaoDados>$version</versaoDados>"
            . "</ns2:cabecalho>";
    }
}
