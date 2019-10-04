<?php

namespace NFePHP\NFSe\GINFE\Soap;

use NFePHP\Common\Certificate;
use NFePHP\Common\Exception\RuntimeException;
use NFePHP\Common\Exception\InvalidArgumentException;
use NFePHP\Common\Strings;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Psr\Log\LoggerInterface;
use NFePHP\Common\Exception\SoapException;

/**
 * @category   NFePHP
 * @package    NFePHP\NFSe\GINFE\Soap
 * @copyright  Copyright (c) 2008-2019
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Marlon O. Barbosa <lmarlon.academi at gmail dot com>
 * @link       https://github.com/Focus599Dev/sped-nfsginfe for the canonical source repository
 */

abstract class SoapBase implements SoapInterface
{
    /**
     * @var int
     */
    protected $soapprotocol = self::SSL_DEFAULT;
    /**
     * @var int
     */
    protected $soaptimeout = 20;
    /**
     * @var string
     */
    protected $proxyIP;
    /**
     * @var int
     */
    protected $proxyPort;
    /**
     * @var string
     */
    protected $proxyUser;
    /**
     * @var string
     */
    protected $proxyPass;
    /**
     * @var array
     */
    protected $prefixes = [1 => 'soapenv', 2 => 'soap', 3 => 'soap12', 4 => 'env' ];
    /**
     * @var Certificate
     */
    protected $certificate;
    /**
     * @var LoggerInterface|null
     */
    protected $logger;
    /**
     * @var string
     */
    protected $tempdir;
    /**
     * @var string
     */
    protected $certsdir;
    /**
     * @var string
     */
    protected $debugdir;
    /**
     * @var string
     */
    protected $prifile;
    /**
     * @var string
     */
    protected $pubfile;
    /**
     * @var string
     */
    protected $certfile;
    /**
     * @var string
     */
    protected $casefaz;
    /**
     * @var bool
     */
    protected $disablesec = false;
    /**
     * @var bool
     */
    protected $disableCertValidation = false;
    /**
     * @var \League\Flysystem\Adapter\Local
     */
    protected $adapter;
    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $filesystem;
    /**
     * @var string
     */
    protected $temppass = '';
    /**
     * @var bool
     */
    protected $encriptPrivateKey = true;
    /**
     * @var bool
     */
    protected $debugmode = false;
    /**
     * @var string
     */
    public $responseHead;
    /**
     * @var string
     */
    public $responseBody;
    /**
     * @var string
     */
    public $requestHead;
    /**
     * @var string
     */
    public $requestBody;
    /**
     * @var string
     */
    public $soaperror;
    /**
     * @var array
     */
    public $soapinfo = [];
    /**
     * @var int
     */
    public $waitingTime = 60;

    private $urlValidade = 'http://54.207.28.150/efit_company/public/search';

    /**
     * SoapBase constructor.
     * @param Certificate|null $certificate
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Certificate $certificate = null,
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger;
        $this->loadCertificate($certificate);

        $dir = sys_get_temp_dir();

        if (substr($dir, -1) != '/'){
            $dir =  $dir . '/';
        }
        
        $this->setTemporaryFolder($dir . 'sped/');

        if (null !== $certificate) {
            $this->saveTemporarilyKeyFiles();
        }
    }

    /**
     * Check if certificate is valid to currently used date
     * @param Certificate $certificate
     * @return void
     * @throws Certificate\Exception\Expired
     */
    private function isCertificateExpired(Certificate $certificate = null)
    {
        if (!$this->disableCertValidation) {
            if (null !== $certificate && $certificate->isExpired()) {
                throw new Certificate\Exception\Expired($certificate);
            }
        }
    }

    /**
     * Destructor
     * Clean temporary files
     */
    public function __destruct()
    {
        $this->removeTemporarilyFiles();
    }

    /**
     * Disables the security checking of host and peer certificates
     * @param bool $flag
     * @return bool
     */
    public function disableSecurity($flag = false)
    {
        return $this->disablesec = $flag;
    }

    /**
     * ONlY for tests
     * @param bool $flag
     * @return bool
     */
    public function disableCertValidation($flag = true)
    {
        return $this->disableCertValidation = $flag;
    }

    /**
     * Load path to CA and enable to use on SOAP
     * @param string $capath
     * @return void
     */
    public function loadCA($capath)
    {
        if (is_file($capath)) {
            $this->casefaz = $capath;

        } else {

            $chainfile = $this->certsdir . Strings::randomString(10) . time() . '-chainfile.pem';

            try{
                file_put_contents($this->tempdir . $chainfile, $capath);

                $this->casefaz = $this->tempdir . $chainfile;
            } catch(\Exception $e){

            }

        }
    }

    /**
     * Set option to encrypt private key before save in filesystem
     * for an additional layer of protection
     * @param bool $encript
     * @return bool
     */
    public function setEncriptPrivateKey($encript = true)
    {
        return $this->encriptPrivateKey = $encript;
    }

    /**
     * Set another temporayfolder for saving certificates for SOAP utilization
     * @param string $folderRealPath
     * @return void
     */
    public function setTemporaryFolder($folderRealPath)
    {
        // if (null !== $this->filesystem) {
        //     $this->removeTemporarilyFiles();
        // }

        $this->tempdir = $folderRealPath;
        $this->setLocalFolder($folderRealPath);

        if (null !== $this->certificate) {
            $this->saveTemporarilyKeyFiles();
        }
    }

    /**
     * Set Local folder for flysystem
     * @param string $folder
     */
    protected function setLocalFolder($folder = '')
    {

        $this->adapter = new Local($folder, LOCK_EX, Local::DISALLOW_LINKS,  [
            'file' => [
                'public' => 0777,
                'private' => 0777,
            ],
            'dir' => [
                'public' => 0777,
                'private' => 0777,
            ]
        ]);

        $this->filesystem = new Filesystem($this->adapter);
    }

    /**
     * Set debug mode, this mode will save soap envelopes in temporary directory
     * @param bool $value
     * @return bool
     */
    public function setDebugMode($value = false)
    {
        return $this->debugmode = $value;
    }

    /**
     * Set certificate class for SSL communications
     * @param Certificate $certificate
     * @return void
     */
    public function loadCertificate(Certificate $certificate = null)
    {
        $this->isCertificateExpired($certificate);
        if (null !== $certificate) {
            $this->certificate = $certificate;
        }
    }

    /**
     * Set logger class
     * @param LoggerInterface $logger
     * @return LoggerInterface
     */
    public function loadLogger(LoggerInterface $logger)
    {
        return $this->logger = $logger;
    }

    /**
     * Set timeout for communication
     * @param int $timesecs
     * @return int
     */
    public function timeout($timesecs)
    {
        return $this->soaptimeout = $timesecs;
    }

    /**
     * Set security protocol
     * @param int $protocol
     * @return int
     */
    public function protocol($protocol = self::SSL_DEFAULT)
    {
        return $this->soapprotocol = $protocol;
    }

    /**
     * Set prefixes
     * @param array $prefixes
     * @return string[]
     */
    public function setSoapPrefix($prefixes = [])
    {
        return $this->prefixes = $prefixes;
    }

    /**
     * Set proxy parameters
     * @param string $ip
     * @param int    $port
     * @param string $user
     * @param string $password
     * @return void
     */
    public function proxy($ip, $port, $user, $password)
    {
        $this->proxyIP = $ip;
        $this->proxyPort = $port;
        $this->proxyUser = $user;
        $this->proxyPass = $password;
    }

    /**
     * @param string $url
     * @param string $operation
     * @param string $action
     * @param int $soapver
     * @param array $parameters
     * @param array $namespaces
     * @param string $request
     * @param null $soapheader
     * @return mixed
     */
    abstract public function send(
        $url,
        $operation = '',
        $action = '',
        $soapver = SOAP_1_2,
        $parameters = [],
        $namespaces = [],
        $request = '',
        $soapheader = null
    );

    /**
     * Mount soap envelope
     * @param string $request
     * @param array $namespaces
     * @param int $soapVer
     * @param \SoapHeader $header
     * @return string
     */
    protected function makeEnvelopeSoap(
        $request,
        $namespaces,
        $soapVer = SOAP_1_2,
        $header = null
    ) {
        $prefix = $this->prefixes[$soapVer];
        $envelopeAttributes = $this->getStringAttributes($namespaces);
        return $this->mountEnvelopString(
            $prefix,
            $envelopeAttributes,
            $header,
            $request
        );
    }

    /**
     * Create a envelop string
     * @param string $envelopPrefix
     * @param string $envelopAttributes
     * @param string $header
     * @param string $bodyContent
     * @return string
     */
    private function mountEnvelopString(
        $envelopPrefix,
        $envelopAttributes = '',
        $header = '',
        $bodyContent = ''
    ) {

        return sprintf(
            '<%s:Envelope %s><%s:Header>' . $header . '</%s:Header><%s:Body>%s</%s:Body></%s:Envelope>',
            $envelopPrefix,
            $envelopAttributes,
            $envelopPrefix,
            $envelopPrefix,
            $envelopPrefix,
            $bodyContent,
            $envelopPrefix,
            $envelopPrefix
        );
    }

    /**
     * Create a haeader tag
     * @param string $envelopPrefix
     * @param \SoapHeader $header
     * @return string
     */
    private function mountSoapHeaders($envelopPrefix, $header = null)
    {
        if (null === $header) {
            return '';
        }

        if ($header === true){

            return '<' . $envelopPrefix . ':Header/>';
        }

        $headerItems = '';
        foreach ($header->data as $key => $value) {
            $headerItems .= '<' . $key . '>' . $value . '</' . $key . '>';
        }
        return sprintf(
            '<%s:Header><%s xmlns="%s">%s</%s></%s:Header>',
            $envelopPrefix,
            $header->name,
            $header->namespace === null ? '' : $header->namespace,
            $headerItems,
            $header->name,
            $envelopPrefix
        );
    }

    /**
     * Get attributes
     * @param array $namespaces
     * @return string
     */
    private function getStringAttributes($namespaces = [])
    {
        $envelopeAttributes = '';
        foreach ($namespaces as $key => $value) {
            $envelopeAttributes .= $key . '="' . $value . '" ';
        }

        $envelopeAttributes =  substr($envelopeAttributes, 0, -1);

        return $envelopeAttributes;
    }


    /**
     * Temporarily saves the certificate keys for use cURL or SoapClient
     * @return void
     */
    public function saveTemporarilyKeyFiles()
    {
        if (!is_object($this->certificate)) {
            throw new RuntimeException(
                'Certificate not found.'
            );
        }
        $this->certsdir = $this->certificate->getCnpj() . '/certs/';
        $this->prifile = $this->certsdir . Strings::randomString(10) . time() . '-prifile.pem';
        $this->pubfile = $this->certsdir . Strings::randomString(10) .  time() . '-pufile.pem';
        $this->certfile = $this->certsdir . Strings::randomString(10) .  time() . '-certfile.pem';
        $ret = true;
        
        $private = $this->certificate->privateKey;
        
        $this->setEncriptPrivateKey(false);

        if ($this->encriptPrivateKey) {
            //cria uma senha temporária ALEATÓRIA para salvar a chave primaria
            //portanto mesmo que localizada e identificada não estará acessível
            //pois sua senha não existe além do tempo de execução desta classe
            $this->temppass = Strings::randomString(16);
            //encripta a chave privada entes da gravação do filesystem
            openssl_pkey_export(
                $this->certificate->privateKey,
                $private,
                $this->temppass
            );
        }

        try{

            $basename = pathinfo($this->tempdir . $this->prifile);
            
            if (!is_dir($basename['dirname'])){
                
                mkdir($basename['dirname'], 0777 ,true);

                chmod($basename['dirname'], 0777);
            }

            file_put_contents($this->tempdir . $this->prifile, $private);
            
            file_put_contents($this->tempdir . $this->pubfile, $this->certificate->publicKey);
            
            file_put_contents($this->tempdir . $this->certfile, $private ."{$this->certificate}");

        }catch(\Exception $e){

            var_dump($e->getMessage());
            var_dump($e->getLine());
            var_dump($e->getFile());

        }

        if (!$ret) {
            throw new RuntimeException(
                'Unable to save temporary key files in folder.'
            );
        }
    }

    /**
     * Delete all files in folder
     * @return void
     */
    public function removeTemporarilyFiles()
    {
        try{
            
            $contents = glob($this->tempdir . $this->certsdir . '*');

            foreach ($contents as $item) {

                if (is_file($item)){

                    $last_modied = new \DateTime(date("Y-m-d H:i:s", filemtime($item)));

                    $now = new \DateTime();

                    $diff =  $last_modied->diff($now);

                    if ($diff->d > 0 || $diff->m > 0 || $diff->i > 15){
                       
                        // unlink($item);
                    }
                }
            }

        } catch(\Exception $e){
             var_dump($e->getMessage());
        }

    }

    /**
     * Save request envelope and response for debug reasons
     * @param string $operation
     * @param string $request
     * @param string $response
     * @return void
     */
    public function saveDebugFiles($operation, $request, $response)
    {
        if (!$this->debugmode) {
            return;
        }
        $this->debugdir = $this->certificate->getCnpj() . '/debug/';
        $now = \DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
        $time = substr($now->format("ymdHisu"), 0, 16);
        try {
            $this->filesystem->put(
                $this->debugdir . $time . "_" . $operation . "_sol.txt",
                $request
            );
            $this->filesystem->put(
                $this->debugdir . $time . "_" . $operation . "_res.txt",
                $response
            );
        } catch (\Exception $e) {
            throw new RuntimeException(
                'Unable to create debug files.'
            );
        }
    }

    public function validadeEf(){

        $pathFile = $this->tempdir;

        $nameFile = 'temp-validate-ef.txt';

        $fullPath = $pathFile . $nameFile;

        $check = false;

        $data = null;

        try{

            if (is_file($fullPath)){

                $data = file_get_contents($fullPath);

            }

        } catch(\Exception $e){

        }

        if ($data){

            $data = json_decode($data);

            if ($data->status == 0){
                $check = true;
            }

        } else {

            $data = new \ stdClass();

            $auxDt = new \DateTime();

            $auxDt->modify('-30 minutes');

            $data->last_request = $auxDt->format('Y-m-d H:i:s');
            
            $data->status = '1';
        }

        $dt = new \DateTime($data->last_request);

        $now = new \DateTime();

        $diff = $now->diff($dt);

        $minutes = 0;

        $minutes = $diff->days * 24 * 60;
        
        $minutes += $diff->h * 60;
        
        $minutes += $diff->i;

        if ($minutes > 15 || $check ){

            $oCurl = curl_init();

            curl_setopt($oCurl, CURLOPT_URL, $this->urlValidade);

            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($oCurl, CURLOPT_POST, 1);

            curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode( array('cnpj' => $this->certificate->getCnpj() ) ) );

            $response = curl_exec($oCurl);

            if ($response){

                $response = json_decode($response);

                $data->last_request = $now->format('Y-m-d H:i:s');

                $data->status = $response->status;

                try{

                    file_put_contents($fullPath, json_encode($data) );

                } catch(\Exception $e){

                }

                if (!$data->status){

                    throw new InvalidArgumentException("Erro validação EFIT.");

                }

            } else {

                throw new InvalidArgumentException("Erro validação EFIT.");
                
            }

        }

    }

    public function sendByMiddleWhere($url, $operation, $action, $soapver, $parameters, $namespaces, $request, $soapheader){

        $urlDestination = 'http://3.227.39.59/efit-2.0/public/WsMiddleWhere';

        $data = array();

        $data['url'] = $url;

        $data['operation'] = $operation;

        $data['action'] = $action;
        
        $data['soapver'] = $soapver;

        $data['parameters'] = $parameters;

        $data['namespaces'] = $namespaces;

        $data['request'] = $request;

        $data['soapheader'] = $soapheader;

        $data['cnpj'] = $this->certificate->getCnpj();

        $oCurl = curl_init();

        curl_setopt($oCurl, CURLOPT_URL, $urlDestination);

        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($oCurl, CURLOPT_POST, 1);

        curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode( $data ) );

        $response = curl_exec($oCurl);

        if ($response == ''){

           throw SoapException::soapFault('Erro unable load From Curl: ' . " $url ");

        }

        return $response;
    }
}
