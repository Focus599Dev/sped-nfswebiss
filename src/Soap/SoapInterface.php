<?php

namespace NFePHP\NFSe\GINFE\Soap;

/**
 * @category   NFePHP
 * @package    NFePHP\NFSe\GINFE\Soap
 * @copyright  Copyright (c) 2008-2019
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Marlon O. Barbosa <lmarlon.academi at gmail dot com>
 * @link       https://github.com/Focus599Dev/sped-nfsginfe for the canonical source repository
 */

use NFePHP\Common\Certificate;
use Psr\Log\LoggerInterface;

interface SoapInterface
{
    
    //constants
    const SSL_DEFAULT = 0; //default
    const SSL_TLSV1 = 1; //TLSv1
    const SSL_SSLV2 = 2; //SSLv2
    const SSL_SSLV3 = 3; //SSLv3
    const SSL_TLSV1_0 = 4; //TLSv1.0
    const SSL_TLSV1_1 = 5; //TLSv1.1
    const SSL_TLSV1_2 = 6; //TLSv1.2
    
    /**
     *
     * @param Certificate $certificate
     */
    public function loadCertificate(Certificate $certificate);
    
    /**
     * Set logger class
     * @param LoggerInterface $logger
     */
    public function loadLogger(LoggerInterface $logger);
    
    /**
     * Set timeout for connection
     * @param int $timesecs
     */
    public function timeout($timesecs);
    
    /**
     * Set security protocol for soap communications
     * @param int $protocol
     */
    public function protocol($protocol = self::SSL_DEFAULT);
    
    /**
     * Set proxy parameters
     * @param string $ip
     * @param int $port
     * @param string $user
     * @param string $password
     */
    public function proxy($ip, $port, $user, $password);
    
    /**
     * Send soap message
     * @param string $url
     * @param string $operation
     * @param string $action
     * @param int $soapver
     * @param array $parameters
     * @param array $namespaces
     * @param \SoapHeader $soapheader
     * @param string $request
     */
    public function send(
        $url,
        $operation = '',
        $action = '',
        $soapver = SOAP_1_2,
        $parameters = [],
        $namespaces = [],
        $request = '',
        $soapheader = null
    );
}
