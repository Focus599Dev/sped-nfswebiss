<?php

namespace NFePHP\NFSe\WebISS\Common;

/**
 * @category   NFePHP
 * @package    NFePHP\NFSe\WebISS\Common
 * @copyright  Copyright (c) 2008-2019
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Marlon O. Barbosa <marlon.academi at gmail dot com>
 * @link       https://github.com/Focus599Dev/sped-nfsginfe for the canonical source repository
 */

use RuntimeException;
use DOMDocument;
use InvalidArgumentException;
use NFePHP\Common\TimeZoneByUF;
use NFePHP\Common\Certificate;
use NFePHP\NFSe\WebISS\Common\Signer;
use NFePHP\NFSe\WebISS\Soap\SoapCurl;
use NFePHP\Common\Strings;
use NFePHP\NFSe\WebISS\Common\Webservices;
use SoapHeader;
use NFePHP\NFSe\WebISS\Factories\Header;
use NFePHP\Common\Validator;
use NFePHP\NFSe\WebISS\Common\EntitiesCharacters;
use DomXpath;
use Illuminate\Support\Facades\File;

class Tools {

	/**
     * config class
     * @var \stdClass
     */
    public $config;
    /**
     * Path to storage folder
     * @var string
     */
    public $pathwsfiles = '';
    /**
     * Path to schemes folder
     * @var string
     */
    public $pathschemes = '';
    /**
     * ambiente
     * @var string
     */
    public $ambiente = 'homologacao';
    /**
     * Environment
     * @var int
     */
    public $tpAmb = 2;
    /**
     * contingency class
     * @var Contingency
     */
    public $contingency;
    /**
     * soap class
     * @var SoapInterface
     */
    public $soap;
    /**
     * Application version
     * @var string
     */
    public $verAplic = '';
    /**
     * last soap request
     * @var string
     */
    public $lastRequest = '';
    /**
     * last soap response
     * @var string
     */
    public $lastResponse = '';
    /**
     * certificate class
     * @var Certificate
     */
    protected $certificate;
    /**
     * Sign algorithm from OPENSSL
     * @var int
     */
    protected $algorithm = OPENSSL_ALGO_SHA1;
    /**
     * Canonical conversion options
     * @var array
     */
    protected $canonical = [false,false,null,null];

    /**
     * Version of layout
     * @var string
     */
    protected $versao = '2.0.2';
    /**
     * urlPortal
     * Instância do WebService
     *
     * @var string
     */
    protected $urlPortal = '';
    /**
     * urlcUF
     * @var int
     */
    protected $urlcUF;
    /**
     * urlVersion
     * @var string
     */
    protected $urlVersion = '';
    /**
     * urlService
     * @var string
     */
    protected $urlService = '';
    /**
     * @var string
     */
    protected $urlMethod = '';
    /**
     * @var string
     */
    protected $urlOperation = '';
    /**
     * @var string
     */
    protected $urlNamespace = '';
    /**
     * @var string
     */
    protected $urlAction = '';
    /**
     * @var \SoapHeader | null
     */
    protected $objHeader = null;
    /**
     * @var string
     */
    protected $urlHeader = '';
    /**
     * @var array
     */

    protected $soapnamespacesEnv = [
        'xmlns:soap'  => "http://www.w3.org/2003/05/soap-envelope",
        "xmlns:nfse" => "http://nfse.abrasf.org.br"
        // 'xmlns:xsd'   => "http://www.w3.org/2001/XMLSchema",
        // 'xmlns:xsi'   => "http://www.w3.org/2001/XMLSchema-instance",
    ];

    /**
     * @var array
     */
    protected $availableVersions = [
        '1.0.7' => 'WEBISS107',
        '2.0.2' => 'WEBISS202',

    ];

    /**
     * Constructor
     * load configurations,
     * load Digital Certificate,
     * map all paths,
     * set timezone and
     * and instanciate Contingency::class
     * @param string $configJson content of config in json format
     * @param Certificate $certificate
    */
    public function __construct($configJson, Certificate $certificate) {
        $this->pathwsfiles = realpath(
            __DIR__ . '/../../storage'
        ).'/';
        //valid config json string
        $this->config = json_decode($configJson);

        $this->version($this->config->versao);

        $this->setEnvironmentTimeZone($this->config->siglaUF);

        $this->certificate = $certificate;

        $this->setEnvironment($this->config->tpAmb);

        $this->soap = new SoapCurl($certificate);

        if ($this->config->proxy){

            $this->soap->proxy($this->config->proxy, $this->config->proxyPort, $this->config->proxyUser, $this->config->proxyPass);

        }

        $this->urlPortal = 'http://nfse.abrasf.org.br';

    }

    /**
     * Set or get parameter layout version
     * @param string $version
     * @return string
     * @throws InvalidArgumentException
     */
    public function version($version = '2.0.2'){

        if (null === $version) {
            return $this->versao;
        }
        //Verify version template is defined
        if (false === isset($this->availableVersions[$version])) {
            throw new \InvalidArgumentException('Essa versão de layout não está disponível');
        }

        $this->versao = $version;

        $this->config->schemes = $this->availableVersions[$version];

        $this->pathschemes = realpath(
            __DIR__ . '/../../schemes/'. $this->config->schemes
        ).'/';

        return $this->versao;
    }

    /**
     * Sets environment time zone
     * @param string $acronym (ou seja a sigla do estado)
     * @return void
    */

    public function setEnvironmentTimeZone($acronym){

        date_default_timezone_set(TimeZoneByUF::get($acronym));

    }

    /**
     * Alter environment from "homologacao" to "producao" and vice-versa
     * @param int $tpAmb
     * @return void
    */

    public function setEnvironment($tpAmb = 2){
        if (!empty($tpAmb) && ($tpAmb == 1 || $tpAmb == 2)) {
            $this->tpAmb = $tpAmb;
            $this->ambiente = ($tpAmb == 1) ? 'producao' : 'homologacao';
        }
    }

    /**
     * Performs xml validation with its respective
     * XSD structure definition document
     * NOTE: if dont exists the XSD file will return true
     * @param string $version layout version
     * @param string $body
     * @param string $method
     * @return boolean
    */
    protected function isValid($version, $body, $method){

        $schema = $this->pathschemes.$method."_v$version.xsd";

        if (!is_file($schema)) {
            return true;
        }

        return Validator::isValid(
            $body,
            $schema
        );

    }

    /**
     * Assembles all the necessary parameters for soap communication
     * @param string $service
     * @param string $uf
     * @param int $tpAmb
     * @param bool $ignoreContingency
     * @return void
     */
    protected function servico(
        $service,
        $mun,
        $tpAmb,
        $ignoreContingency = false
    ) {

        $ambiente = $tpAmb == 1 ? "producao" : "homologacao";

        $webs = new Webservices($this->getXmlUrlPath());

        $sigla = $mun;
        $stdServ = $webs->get($sigla, $ambiente);

        if ($stdServ === false) {

            throw new \RuntimeException(
                "Nenhum serviço foi localizado para esta unidade "
                . "da federação [$sigla]"
            );

        }

        if (empty($stdServ->$service->url)) {

            throw new \RuntimeException(
                "Este serviço [$service] não está disponivel."
            );

        }

        //recuperação da versão
        $this->urlVersion = $stdServ->$service->version;
        //recuperação da url do serviço
        $this->urlService = $stdServ->$service->url;
        //recuperação do método
        $this->urlMethod = $stdServ->$service->method;
        //recuperação da operação
        $this->urlOperation = $stdServ->$service->operation;
        //montagem do namespace do serviço
        $this->urlNamespace = sprintf(
            "%s",
            $this->urlPortal
        );

        //montagem do cabeçalho da comunicação SOAP
        $this->urlHeader = Header::get(
            substr($this->versao, 0, 1)
        );

        $this->urlAction =
            $this->urlNamespace
            .'/'
            . $this->urlMethod;

        $this->objHeader = new SoapHeader(
            $this->urlNamespace,
            'cabecalho',
            ['versao' => substr($this->versao, 0, 1)]
        );

    }

    /**
     * Send request message to webservice
     * @param array $parameters
     * @param string $request
     * @return string
     */
    protected function sendRequest($request, array $parameters = []){
        $this->checkSoap();

        return (string) $this->soap->send(
            $this->urlService,
            $this->urlMethod,
            $this->urlAction,
            2,
            $parameters,
            $this->soapnamespacesEnv,
            $request,
            $this->objHeader
        );
    }

    /**
     * Verify if SOAP class is loaded, if not, force load SoapCurl
     */
    protected function checkSoap(){
        if (empty($this->soap)) {
            $this->soap = new SoapCurl($this->certificate);
        }
    }

    /**
     * Create envelope padrão
     */
    protected function MakeEnvelope($servico, $request){

        list($major,$middle,$minor) = explode('.', $this->versao);

        $version = "{$major}.{$middle}{$minor}";

        $servico .= 'Request';

        $xml = '<nfse:'.$servico.'>';

        $cabecalho =  "<cabecalho "
            . "xmlns=\"http://www.abrasf.org.br/nfse.xsd\" versao=\"$version\">"
            . "<versaoDados>$version</versaoDados>"
            . "</cabecalho>";

        $xml .= '<nfseCabecMsg>'.'<![CDATA['.$cabecalho.']]>'.'</nfseCabecMsg>';

        $xml .= '<nfseDadosMsg>'.'<![CDATA['.$request.']]>'.'</nfseDadosMsg>';

        $xml .= '</nfse:'.$servico.'>';

        return $xml;
    }


    public function removeStuffs($xml){

        if (preg_match('/<soap:Body>/', $xml)){

            $tag = '<soap:Body>';

            $xml = substr( $xml, ( strpos($xml, $tag) + strlen($tag) ), strlen($xml)  );

            $tag = '</soap:Body>';

            $xml = substr( $xml, 0 , strpos($xml, $tag) );

        } else if (preg_match('/<soapenv:Body>/', $xml)){

            $tag = '<soapenv:Body>';

            $xml = substr( $xml, ( strpos($xml, $tag) + strlen($tag) ), strlen($xml)  );

            $tag = '</soapenv:Body>';

            $xml = substr( $xml, 0 , strpos($xml, $tag) );

        }  else if (preg_match('/<soap12:Body>/', $xml)){

            $tag = '<soap12:Body>';

            $xml = substr( $xml, ( strpos($xml, $tag) + strlen($tag) ), strlen($xml)  );

            $tag = '</soap12:Body>';

            $xml = substr( $xml, 0 , strpos($xml, $tag) );

        } else if (preg_match('/<env:Body>/', $xml)){

            $tag = '<env:Body>';

            $xml = substr( $xml, ( strpos($xml, $tag) + strlen($tag) ), strlen($xml)  );

            $tag = '</env:Body>';

            $xml = substr( $xml, 0 , strpos($xml, $tag) );

        } else if (preg_match('/<env:Body/', $xml)){

            $tag = '<env:Body xmlns:env=\'http://www.w3.org/2003/05/soap-envelope\'>';

            $xml = substr( $xml, ( strpos($xml, $tag) + strlen($tag) ), strlen($xml)  );

            $tag = '</env:Body>';

            $xml = substr( $xml, 0 , strpos($xml, $tag) );

        } else if (preg_match('/<S:Body>/', $xml)){

            $tag = '<S:Body>';

            $xml = substr( $xml, ( strpos($xml, $tag) + strlen($tag) ), strlen($xml)  );

            $tag = '</S:Body>';

            $xml = substr( $xml, 0 , strpos($xml, $tag) );

        } else if (preg_match('/<s:Body>/', $xml)){

            $tag = '<s:Body>';

            $xml = substr( $xml, ( strpos($xml, $tag) + strlen($tag) ), strlen($xml)  );

            $tag = '</s:Body>';

            $xml = substr( $xml, 0 , strpos($xml, $tag) );

        }

        if (preg_match('/ns3:/', $xml)){

           $xml = preg_replace('/ns3:/', '', $xml);

        }

        if (preg_match('/ns2:/', $xml)){

           $xml = preg_replace('/ns2:/', '', $xml);
        }

        if (preg_match('/ns4:/', $xml)){

           $xml = preg_replace('/ns4:/', '', $xml);
        }

        $xml = htmlspecialchars_decode($xml);

        $xml = preg_replace('/&#xD;/', '', $xml);

        $xml = trim(preg_replace("/<\?xml.*?\?>/", "", $xml));

        return $xml;
    }

    /**
     * Recover path to xml data base with list of soap services
     * @return string
    */
    protected function getXmlUrlPath() {
        $file = $this->pathwsfiles
        . "wsnfe_".$this->versao."_mod.xml";

        if (! file_exists($file)) {
            return '';
        }

        return file_get_contents($file);
    }

    public function getLastRequest(){
        return $this->lastRequest;
    }

    public function getLastResponse(){
        return $this->lastResponse;
    }

    /**
     * Convert string xml message to cdata string
     * @param string $message
     * @return string
     */
    protected function stringTransform($message){

        return EntitiesCharacters::unconvert(htmlentities($message, ENT_NOQUOTES));

    }

    /**
     * Remove os marcadores de XML
     * @param string $body
     * @return string
     */
    public function clear($body){
        $body = str_replace('<?xml version="1.0"?>', '', $body);
        $body = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $body);
        $body = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $body);
        return $body;
    }

    public function execCurl($url, $method, $data, $fallowLocation = true){

        $httpcode = null;

        $response = null;

        try{

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);

            if ($method == 'POST')
                curl_setopt($ch, CURLOPT_POST, true);

            if ($data && is_array($data)){
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            } else if (is_string($data)){
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }

            if ($fallowLocation)
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // curl_setopt($ch, CURLOPT_HTTPHEADER, 0);

            // curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieName);

            // curl_setopt($ch, CURLOPT_COOKIEFILE, realpath(__DIR__ . '/../') . $this->cookieName); //saved cookies

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            $response = curl_exec($ch);

            curl_close($ch);

        } catch (\Exception $e){

            throw $e;

        }

        return $response;
    }

    public function fillPost ($post, $html){

        $xpath = new DomXpath($html);

        foreach ($post as $key => $post_value) {

            foreach ($xpath->query('//input[@name="' . $key . '"]') as $rowNode) {

                if($rowNode->getAttribute('value'))
                    $post[$key] = $rowNode->getAttribute('value');
            }
        }

        return $post;
    }

    public function formatCNPJ($cnpj){
        if (!$cnpj)
            return '';

        return substr($cnpj, 0, 2) . '.' .  substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
    }

    public function formatCPF($cpf){
        if (!$cpf)
            return '';

        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }

    public function getPdfPath($xml){

        $oldEfit = config('envs.OLD_EFIT_ABSOLUTE');

        $dtFolder = substr(str_replace("-", "", (new \DateTime($xml->Nfse->InfNfse->DataEmissao))->format('d/m/Y')), 0, 6);

        $dtFolder2 = substr(str_replace("-", "", (new \DateTime($xml->Nfse->InfNfse->DataEmissao))->format('d/m/Y')), 6, 2);

        if (trim($dtFolder) === "") {
            $dtFolder = "UNKNOW";
        }

        $path = $oldEfit . "files/nfs/pdf/{$dtFolder}{$dtFolder2}/";

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }


    public function getPdfFileName($xml){

        $num = $xml->Nfse->InfNfse->IdentificacaoRps->Numero;

        $rps = $xml->Nfse->InfNfse->Numero;

        $type = $xml->Nfse->InfNfse->IdentificacaoRps->Tipo;

        $fileName = "{$num}" . "-{$rps}" . "-{$type}" . ".pdf";

        return $fileName;
    }
}

?>
