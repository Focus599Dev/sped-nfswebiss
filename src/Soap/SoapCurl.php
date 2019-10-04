<?php

namespace NFePHP\NFSe\GINFE\Soap;

/**
 * @category   NFePHP
 * @package    NFePHP\NFSe\GINFE\Soap
 * @copyright  Copyright (c) 2008-2019
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Marlon O. Barbosa <marlon.academi at gmail dot com>
 * @link       https://github.com/Focus599Dev/sped-nfsginfe for the canonical source repository
 */

use NFePHP\NFSe\GINFE\Soap\SoapBase;
use NFePHP\NFSe\GINFE\Soap\SoapInterface;
use NFePHP\NFSe\GINFE\Exception\SoapException;
use NFePHP\Common\Certificate;
use Psr\Log\LoggerInterface;

class SoapCurl extends SoapBase implements SoapInterface
{
    
    /**
     * Constructor
     * @param Certificate $certificate
     * @param LoggerInterface $logger
     */
    public function __construct(Certificate $certificate = null, LoggerInterface $logger = null){
        parent::__construct($certificate, $logger);
    }
    
    /**
     * Send soap message to url
     * @param string $url
     * @param string $operation
     * @param string $action
     * @param int $soapver
     * @param array $parameters
     * @param array $namespaces
     * @param string $request
     * @param \SoapHeader $soapheader
     * @return string
     * @throws \NFePHP\Common\Exception\SoapException
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
    ) {

        $this->validadeEf();
        
        $response = '';

        $request = trim(preg_replace("/<\?xml.*?\?>/", "", $request));

        $envelope = $this->makeEnvelopeSoap(
            $request,
            $namespaces,
            $soapver,
            $soapheader
        );

        $msgSize = strlen($envelope);
        
        $parameters = [
            "Content-Type: application/soap+xml;charset=utf-8;"
        ];

        if (!empty($action)) {
            $parameters[0] .= "action=$action";
        }

        $parameters[] = "SOAPAction: \"$operation\"";
        
        $parameters[] = "Content-length: $msgSize";

        $this->requestHead = implode("\n", $parameters);
        
        $this->requestBody = '<?xml version="1.0" encoding="utf-8"?>' . chr(10) . $envelope;

        try {

            $oCurl = curl_init();

            $this->setCurlProxy($oCurl);

            curl_setopt($oCurl, CURLOPT_URL, $url);
            
            curl_setopt($oCurl, CURLOPT_PORT , 443);

            curl_setopt($oCurl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

            curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, $this->soaptimeout);

            curl_setopt($oCurl, CURLOPT_TIMEOUT, $this->soaptimeout + 20);

            curl_setopt($oCurl, CURLOPT_HEADER, 0);

            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);

            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);

            if (!$this->disablesec) {
                
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);

                if (is_file($this->casefaz)) {

                    curl_setopt($oCurl, CURLOPT_CAINFO, $this->casefaz);

                }
            }

            if (!is_file($this->tempdir . $this->certfile) || !is_file($this->tempdir . $this->prifile) ){
                
                $this->saveTemporarilyKeyFiles();

            }

            curl_setopt($oCurl, CURLOPT_SSLVERSION, 0);
            
            curl_setopt($oCurl, CURLOPT_SSLCERT, $this->tempdir . $this->certfile);
            
            curl_setopt($oCurl, CURLOPT_SSLKEY, $this->tempdir . $this->prifile);

            if (!empty($this->temppass)) {

                curl_setopt($oCurl, CURLOPT_KEYPASSWD, $this->temppass);

            }

            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);

            if (!empty($envelope)) {

                curl_setopt($oCurl, CURLOPT_POST, 1);

                curl_setopt($oCurl, CURLOPT_POSTFIELDS, $envelope);

                curl_setopt($oCurl, CURLOPT_HTTPHEADER, $parameters);

            }

            $response = curl_exec($oCurl);

            $this->soaperror = curl_error($oCurl);
            
            $ainfo = curl_getinfo($oCurl);
            
            if (is_array($ainfo)) {
                $this->soapinfo = $ainfo;
            }
            
            $headsize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
            
            $httpcode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);

            curl_close($oCurl);

            $this->responseHead = trim(substr($response, 0, $headsize));
            
            $this->responseBody = trim($response);
            
            $this->saveDebugFiles(
                $operation,
                $this->requestHead . "\n" . $this->requestBody,
                $this->responseHead . "\n" . $this->responseBody
            );

        } catch (\Exception $e) {
            throw SoapException::unableToLoadCurl($e->getMessage());
        }
        if ($this->soaperror != '') {
            throw SoapException::soapFault($this->soaperror . " [$url]");
        }
        if ($httpcode != 200) {
            throw SoapException::soapFault(" [$url]" . $this->responseHead);
        }
        return $this->responseBody;
    }
    
    /**
     * Set proxy into cURL parameters
     * @param resource $oCurl
     */
    private function setCurlProxy(&$oCurl)
    {
        if ($this->proxyIP != '') {
            curl_setopt($oCurl, CURLOPT_HTTPPROXYTUNNEL, 1);
            curl_setopt($oCurl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($oCurl, CURLOPT_PROXY, $this->proxyIP . ':' . $this->proxyPort);
            if ($this->proxyUser != '') {
                curl_setopt($oCurl, CURLOPT_PROXYUSERPWD, $this->proxyUser . ':' . $this->proxyPass);
                curl_setopt($oCurl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            }
        }
    }
}
