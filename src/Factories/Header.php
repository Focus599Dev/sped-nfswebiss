<?php

namespace NFePHP\NFSe\WebISS\Factories;

/**
 * @category   NFePHP
 * @package    NFePHP\NFSe\WebISS\Factories\
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
        return "<?xml version=\"1.0\" encoding=\"utf-8\"?>"
            . "<cabecalho "
            . "xmlns=\"http://www.abrasf.org.br/nfse.xsd\" versao=\"$version\">"
            . "<versaoDados>$version</versaoDados>"
            . "</cabecalho>";
    }
}
