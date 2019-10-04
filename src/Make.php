<?php 

namespace NFePHP\NFSe\GINFE;

/**
 * @category   NFePHP
 * @package    NFePHP\NFSe\GINFE
 * @copyright  Copyright (c) 2008-2019
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Marlon O. Barbosa <marlon.academi at gmail dot com>
 * @link       https://github.com/Focus599Dev/sped-nfsginfe for the canonical source repository
 */

use NFePHP\Common\DOMImproved as Dom;
use NFePHP\Common\Strings;
use stdClass;
use RuntimeException;
use DOMElement;
use DateTime;

class Make{

	/**
     * @var \NFePHP\Common\DOMImproved
    */
    public $dom;

	/**
     * @var DOMElements
    */
	protected $loteRps;

    /**
     * @var DOMElements
    */
    protected $identificacaoRps = [];

    /**
     * @var DOMElements
    */
    protected $prestador = [];

    /**
     * @var DOMElements
    */
    protected $tomador = [];

    /**
     * @var DOMElements
    */
    protected $intermediarioServico = [];

    /**
     * @var DOMElements
    */
    protected $construcaoCivil = [];

    /**
     * @var DOMElements
    */
    protected $rpsSubstituido = [];

    /**
     * @var DOMElements
    */
    protected $servico = [];

    /**
     * @var DOMElements
    */
    protected $infRps = [];

    protected $NumeroLote;

	/**
     * @var array
     */
    public $erros = [];

    /**
     * @var array
    */
    public $rps = [];

    /**
     * @var int
    */
    public $item = 0;

    /**
     * XML RPS
     */
    
    private $xml;

    protected $soapnamespaces = [
        'xmlns'  => "http://www.w3.org/2000/09/xmldsig#",
        'xmlns:p'       => "http://www.ginfes.com.br/servico_enviar_lote_rps_envio_v03.xsd",
        'xmlns:tipos' => "http://www.ginfes.com.br/tipos_v03.xsd",
    ];

	/**
     * Função construtora cria um objeto DOMDocument
     * que será carregado com o documento fiscal
    */
    public function __construct() {
        
        $this->dom = new Dom('1.0', 'UTF-8');

        $this->dom->preserveWhiteSpace = false;

        $this->dom->formatOutput = false;
    }

    public function monta(){

        $EnviarLoteRpsEnvio = $this->dom->createElement('p:EnviarLoteRpsEnvio');

        foreach ($this->soapnamespaces as $key => $namespace) {
            
            $EnviarLoteRpsEnvio->setAttribute($key, $namespace);
        }

        $this->loteRps->setAttribute('Id', $this->NumeroLote);
        
        $ListaRps = $this->dom->createElement("tipos:ListaRps");

        foreach ($this->rps as $key => $rps) {
            
            $this->dom->appChild($this->rps[$key], $this->infRps[$key], 'Falta tag "InfRps"');
            
            $this->dom->appChild($ListaRps, $this->rps[$key] , 'Falta tag "InfRps"');

        }

        $this->dom->appChild($this->loteRps, $ListaRps , 'Falta tag "InfRps"');

        $this->dom->appChild($EnviarLoteRpsEnvio, $this->loteRps , 'Falta tag "InfRps"');

        $this->dom->appendChild($EnviarLoteRpsEnvio);

        $this->xml = $this->dom->saveXML();

        return true;
    }

	public function buildLoteRps($std){

		$possible = [
			'NumeroLote',
			'versao',
			'Cnpj',
			'InscricaoMunicipal',
			'QuantidadeRps',
		];

        $std = $this->equilizeParameters($std, $possible);
        
        $loteRps = $this->dom->createElement("p:LoteRps");

        $this->dom->addChild(
            $loteRps,
            "tipos:NumeroLote",
            $std->NumeroLote,
            true,
            "Numero do Lote RPS"
        );

        $this->dom->addChild(
            $loteRps,
            "tipos:Cnpj",
            $std->Cnpj,
            true,
            "Numero do CNPJ"
        );

        $this->dom->addChild(
            $loteRps,
            "tipos:InscricaoMunicipal",
            $std->InscricaoMunicipal,
            true,
            "Numero de InscricaoMunicipal"
        );

        $this->dom->addChild(
            $loteRps,
            "tipos:QuantidadeRps",
            $std->QuantidadeRps ? $std->QuantidadeRps : 1,
            true,
            "Numero de QuantidadeRps"
        );

        $this->loteRps =  $loteRps;

        $this->NumeroLote = $std->NumeroLote;

        return $this->loteRps;

	} 

    public function buildIdentificacaoRps($std){

        $possible = [
            'Numero',
            'Serie',
            'Tipo'
        ];

        $std = $this->equilizeParameters($std, $possible);

        $identificacaoRps = $this->dom->createElement("tipos:IdentificacaoRps");

        $this->dom->addChild(
            $identificacaoRps,
            "tipos:Numero",
            $std->Numero,
            true,
            "Numero do Lote RPS"
        );

        $this->dom->addChild(
            $identificacaoRps,
            "tipos:Serie",
            $std->Serie,
            true,
            "Serie do Lote RPS"
        );

        $this->dom->addChild(
            $identificacaoRps,
            "tipos:Tipo",
            $std->Tipo,
            true,
            "Tipo do Lote RPS"
        );

        $this->identificacaoRps[$this->item] = $identificacaoRps;

    }

    public function buildPrestador($std){

        $this->item = $this->item + 1;

        $this->rps[$this->item] = $this->dom->createElement("tipos:Rps");

        $possible = [
            'Cnpj',
            'InscricaoMunicipal'
        ];

        $std = $this->equilizeParameters($std, $possible);

        $prestador = $this->dom->createElement("tipos:Prestador");

        $this->dom->addChild(
            $prestador,
            "tipos:Cnpj",
            $std->Cnpj,
            true,
            "Cnpj Prestador"
        );

        $this->dom->addChild(
            $prestador,
            "tipos:InscricaoMunicipal",
            $std->InscricaoMunicipal,
            true,
            "InscricaoMunicipal Prestador"
        );

        $this->prestador[$this->item] = $prestador;
        
    }

    public function buildTomador($std){
        
        $possible = [
            'RazaoSocial',
            'Endereco',
            'Contato',
            'Telefone',
            'Email',
            'Numero',
            'Complemento',
            'Bairro',
            'CodigoMunicipio',
            'Uf',
            'Cep',
            'CpfCnpj',
            'InscricaoMunicipal',
        ];

        $std = $this->equilizeParameters($std, $possible);

        $tomador = $this->dom->createElement("tipos:Tomador");

        if ($std->CpfCnpj || $std->InscricaoMunicipal){
            
            $identificacaoTomador = $this->dom->createElement("tipos:IdentificacaoTomador");

            $CpfCnpj = $this->dom->createElement("tipos:CpfCnpj");

            if (strlen($std->CpfCnpj) > 11){
               
                $this->dom->addChild(
                    $CpfCnpj,
                    "tipos:Cnpj",
                    $std->CpfCnpj,
                    false,
                    "Cnpj Tomador"
                );

            } else {

                $this->dom->addChild(
                    $CpfCnpj,
                    "tipos:Cpf",
                    $std->CpfCnpj,
                    false,
                    "Cpf Tomador"
                );

            }

            $this->dom->appChild($identificacaoTomador, $CpfCnpj , 'Falta tag "identificacaoTomador"');

            $this->dom->addChild(
                $identificacaoTomador,
                "tipos:InscricaoMunicipal",
                $std->InscricaoMunicipal,
                false,
                "InscricaoMunicipal Tomador"
            );

            $this->dom->appChild($tomador, $identificacaoTomador , 'Falta tag "identificacaoTomador"');

        }

        $this->dom->addChild(
            $tomador,
            "tipos:RazaoSocial",
            $std->RazaoSocial,
            false,
            "RazaoSocial Tomador"
        );

        if ($std->Endereco || $std->Numero || $std->Complemento || $std->Bairro || $std->CodigoMunicipio || $std->Uf || $std->Cep){

            $endereco = $this->dom->createElement("tipos:Endereco");

            $this->dom->addChild(
                $endereco,
                "tipos:Endereco",
                $std->Endereco,
                false,
                "Endereco Tomador"
            );

            $this->dom->addChild(
                $endereco,
                "tipos:Numero",
                $std->Numero,
                false,
                "Numero Tomador"
            );

            $this->dom->addChild(
                $endereco,
                "tipos:Complemento",
                $std->Complemento,
                false,
                "Complemento Tomador"
            );

            $this->dom->addChild(
                $endereco,
                "tipos:Bairro",
                $std->Bairro,
                false,
                "Bairro Tomador"
            );

            $this->dom->addChild(
                $endereco,
                "tipos:CodigoMunicipio",
                $std->CodigoMunicipio,
                false,
                "CodigoMunicipio Tomador"
            );

            $this->dom->addChild(
                $endereco,
                "tipos:Uf",
                $std->Uf,
                false,
                "Uf Tomador"
            );

            $this->dom->addChild(
                $endereco,
                "tipos:Cep",
                $std->Cep,
                false,
                "Cep Tomador"
            );
            
            $this->dom->appChild($tomador, $endereco , 'Falta tag "tomador"');

        }

        if ( $std->Telefone || $std->Email ){

            $contato = $this->dom->createElement("tipos:Contato");

            $this->dom->addChild(
                $contato,
                "tipos:Telefone",
                $std->Telefone,
                false,
                "Telefone Tomador"
            );

            $this->dom->addChild(
                $contato,
                "tipos:Email",
                $std->Email,
                false,
                "Email Tomador"
            );

            $this->dom->appChild($tomador, $contato , 'Falta tag "tomador"');

        }

        $this->tomador[$this->item] = $tomador;
    }

    public function buildIntermediarioServico($std){

        $possible = [
            'RazaoSocial',
            'CpfCnpj',
            'InscricaoMunicipal'
        ];

        $std = $this->equilizeParameters($std, $possible);

        $intermediarioServico = $this->dom->createElement("tipos:IntermediarioServico");

        $this->dom->addChild(
            $intermediarioServico,
            "tipos:RazaoSocial",
            $std->RazaoSocial,
            true,
            "RazaoSocial IntermediarioServico"
        );

        $CpfCnpj = $this->dom->createElement("tipos:CpfCnpj");

        if (strlen($std->CpfCnpj) > 11){
           
            $this->dom->addChild(
                $CpfCnpj,
                "tipos:Cnpj",
                $std->CpfCnpj,
                false,
                "Cnpj Tomador"
            );

        } else {

            $this->dom->addChild(
                $CpfCnpj,
                "tipos:Cpf",
                $std->CpfCnpj,
                false,
                "Cpf Tomador"
            );

        }

        $this->dom->appChild($intermediarioServico, $CpfCnpj , 'Falta tag "tomador"');

        $this->dom->addChild(
            $intermediarioServico,
            "tipos:InscricaoMunicipal",
            $std->InscricaoMunicipal,
            false,
            "InscricaoMunicipal IntermediarioServico"
        );

        $this->intermediarioServico[$this->item] = $intermediarioServico;
    }

    public function buildConstrucaoCivil($std){

         $possible = [
            'CodigoObra',
            'Art'
        ];

        $std = $this->equilizeParameters($std, $possible);

        $construcaoCivil = $this->dom->createElement("tipos:ConstrucaoCivil");

        $this->dom->addChild(
            $construcaoCivil,
            "tipos:CodigoObra",
            $std->CodigoObra,
            true,
            "CodigoObra de ConstrucaoCivil"
        );

        $this->dom->addChild(
            $construcaoCivil,
            "tipos:Art",
            $std->Art,
            true,
            "Art de ConstrucaoCivil"
        );

        $this->construcaoCivil[$this->item] = $construcaoCivil;
    }

    public function buildRpsSubstituido($std){

        $possible = [
            'Numero',
            'Serie',
            'Tipo'
        ];

        $std = $this->equilizeParameters($std, $possible);

        $rpsSubstituido = $this->dom->createElement("tipos:RpsSubstituido");

        $this->dom->addChild(
            $rpsSubstituido,
            "tipos:Numero",
            $std->Numero,
            true,
            "Numero do Lote RPS"
        );

        $this->dom->addChild(
            $rpsSubstituido,
            "tipos:Serie",
            $std->Serie,
            true,
            "Serie do Lote RPS"
        );

        $this->dom->addChild(
            $rpsSubstituido,
            "tipos:Tipo",
            $std->Tipo,
            true,
            "Tipo do Lote RPS"
        );

        $this->rpsSubstituido[$this->item] = $rpsSubstituido;

    }

    public function buildServico($std){

        $possible = [
           'ValorServicos',
           'ValorDeducoes',
           'ValorPis',
           'ValorCofins',
           'ValorInss',
           'ValorIr',
           'ValorCsll',
           'IssRetido',
           'ValorIss',
           'ValorIssRetido',
           'OutrasRetencoes',
           'BaseCalculo',
           'Aliquota',
           'ValorLiquidoNfse',
           'DescontoIncondicionado',
           'DescontoCondicionado',
           'ItemListaServico',
           'CodigoCnae',
           'CodigoTributacaoMunicipio',
           'Discriminacao',
           'CodigoMunicipio'
        ];

        $std = $this->equilizeParameters($std, $possible);

        $servico = $this->dom->createElement("tipos:Servico");
        
        $valores = $this->dom->createElement("tipos:Valores");

        $this->dom->addChild(
            $valores,
            "tipos:ValorServicos",
            $std->ValorServicos,
            true,
            "ValorServicos RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:ValorDeducoes",
            $std->ValorDeducoes,
            true,
            "ValorDeducoes RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:ValorPis",
            $std->ValorPis,
            false,
            "ValorPis RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:ValorCofins",
            $std->ValorCofins,
            false,
            "ValorCofins RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:ValorInss",
            $std->ValorInss,
            false,
            "ValorInss RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:ValorIr",
            $std->ValorIr,
            false,
            "ValorIr RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:ValorCsll",
            $std->ValorCsll,
            false,
            "ValorCsll RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:IssRetido",
            $std->IssRetido,
            true,
            "IssRetido RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:ValorIssRetido",
            $std->ValorIssRetido,
            false,
            "ValorIssRetido RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:OutrasRetencoes",
            $std->OutrasRetencoes,
            false,
            "OutrasRetencoes RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:BaseCalculo",
            $std->BaseCalculo,
            false,
            "BaseCalculo RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:Aliquota",
            $std->Aliquota,
            false,
            "Aliquota RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:ValorLiquidoNfse",
            $std->ValorLiquidoNfse,
            false,
            "ValorLiquidoNfse RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:DescontoIncondicionado",
            $std->DescontoIncondicionado,
            false,
            "DescontoIncondicionado RPS"
        );

        $this->dom->addChild(
            $valores,
            "tipos:DescontoCondicionado",
            $std->DescontoCondicionado,
            false,
            "DescontoCondicionado RPS"
        );

        $this->dom->appChild($servico, $valores , 'Falta tag "servico"');

        $this->dom->addChild(
            $servico,
            "tipos:ItemListaServico",
            $std->ItemListaServico,
            false,
            "ItemListaServico RPS"
        );

        $this->dom->addChild(
            $servico,
            "tipos:CodigoCnae",
            $std->CodigoCnae,
            false,
            "CodigoCnae RPS"
        );

        $this->dom->addChild(
            $servico,
            "tipos:CodigoTributacaoMunicipio",
            $std->CodigoTributacaoMunicipio,
            false,
            "CodigoTributacaoMunicipio RPS"
        );

        $this->dom->addChild(
            $servico,
            "tipos:Discriminacao",
            $std->Discriminacao,
            false,
            "Discriminacao RPS"
        );

        $this->dom->addChild(
            $servico,
            "tipos:CodigoMunicipio",
            $std->CodigoMunicipio,
            false,
            "CodigoMunicipio RPS"
        );

        $this->servico[$this->item] = $servico;

    }

    public function buildInfNfse($std){

        $possible = [
            'DataEmissao',
            'NaturezaOperacao',
            'RegimeEspecialTributacao',
            'OptanteSimplesNacional',
            'IncentivadorCultural',
            'Status'
        ];

        $std = $this->equilizeParameters($std, $possible);

        $infRps = $this->dom->createElement("tipos:InfRps");

        $infRps->setAttribute('Id', $this->NumeroLote);
        
        $this->dom->appChild($infRps, $this->identificacaoRps[$this->item] , 'Falta tag "infRps"');

        $this->dom->addChild(
            $infRps,
            "tipos:DataEmissao",
            $std->DataEmissao,
            true,
            "DataEmissao RPS"
        );

        $this->dom->addChild(
            $infRps,
            "tipos:NaturezaOperacao",
            $std->NaturezaOperacao,
            true,
            "NaturezaOperacao RPS"
        );

        $this->dom->addChild(
            $infRps,
            "tipos:RegimeEspecialTributacao",
            $std->RegimeEspecialTributacao,
            true,
            "RegimeEspecialTributacao RPS"
        );

        $this->dom->addChild(
            $infRps,
            "tipos:OptanteSimplesNacional",
            $std->OptanteSimplesNacional,
            true,
            "OptanteSimplesNacional RPS"
        );  

        $this->dom->addChild(
            $infRps,
            "tipos:IncentivadorCultural",
            $std->IncentivadorCultural,
            true,
            "IncentivadorCultural RPS"
        );

        $this->dom->addChild(
            $infRps,
            "tipos:Status",
            $std->Status,
            true,
            "Status RPS"
        ); 

        if (isset ($this->rpsSubstituido[$this->item])){
            
            $this->dom->appChild($infRps, $this->rpsSubstituido[$this->item] , 'Falta tag "infRps"');

        }

        if (isset ($this->servico[$this->item])){

            $this->dom->appChild($infRps, $this->servico[$this->item] , 'Falta tag "infRps"');


        }

        if (isset ($this->prestador[$this->item])){

            $this->dom->appChild($infRps, $this->prestador[$this->item] , 'Falta tag "infRps"');

        }

        if (isset ($this->tomador[$this->item])){

            $this->dom->appChild($infRps, $this->tomador[$this->item] , 'Falta tag "infRps"');

        }

        if (isset ($this->intermediarioServico[$this->item])){

            $this->dom->appChild($infRps, $this->intermediarioServico[$this->item] , 'Falta tag "infRps"');

        }

        if (isset ($this->construcaoCivil[$this->item])){

            $this->dom->appChild($infRps, $this->construcaoCivil[$this->item] , 'Falta tag "infRps"');

        }
        
        $this->infRps[$this->item] = $infRps;
    }

	/**
     * Includes missing or unsupported properties in stdClass
     * @param stdClass $std
     * @param array $possible
     * @return stdClass
    */
    protected function equilizeParameters(stdClass $std, $possible){
        
        $arr = get_object_vars($std);

        foreach ($possible as $key) {

            if (!array_key_exists($key, $arr)) {

                $std->$key = null;

            }

        }

        return $std;
    }

    /**
     * Returns xml string and assembly it is necessary
     * @return string
    */
    public function getXML(){
        if (empty($this->xml)) {
            $this->monta();
        }

        return $this->xml;
    }

}                                                                                                                            

?>