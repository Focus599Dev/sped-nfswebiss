<?php 

namespace NFePHP\NFSe\WebISS;

/**
 * @category   NFePHP
 * @package    NFePHP\NFSe\WebISS
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
     * @var stdClass
    */
    public $infoAdic = null;

    /**
     * XML RPS
     */
    
    private $xml;

    protected $soapnamespaces = [
        'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance",
        'xmlns:xsd' => "http://www.w3.org/2001/XMLSchema",
        'xmlns'     => "http://www.abrasf.org.br/nfse.xsd",
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

        $EnviarLoteRpsEnvio = $this->dom->createElement('EnviarLoteRpsSincronoEnvio');

        foreach ($this->soapnamespaces as $key => $namespace) {
            
            $EnviarLoteRpsEnvio->setAttribute($key, $namespace);
        }
        
        $this->loteRps->setAttribute('Id', $this->NumeroLote);
        
        $ListaRps = $this->dom->createElement("ListaRps");

        foreach ($this->rps as $key => $rps) {

            $this->dom->addChild(
                $this->infRps[$key],
                "RegimeEspecialTributacao",
                $this->infoAdic->RegimeEspecialTributacao,
                false,
                "RegimeEspecialTributacao"
            );

            $this->dom->addChild(
                $this->infRps[$key],
                "OptanteSimplesNacional",
                $this->infoAdic->OptanteSimplesNacional,
                true,
                "OptanteSimplesNacional"
            );

            $this->dom->addChild(
                $this->infRps[$key],
                "IncentivoFiscal",
                $this->infoAdic->IncentivoFiscal,
                true,
                "IncentivoFiscal ISS"
            );
            
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
			'CpfCnpj',
			'InscricaoMunicipal',
			'QuantidadeRps',
		];

        $dt = new \DateTime();

        $idLote = $lote = $dt->format('YmdHis').rand(0, 9);

        $std->NumeroLote = $idLote;

        $std = $this->equilizeParameters($std, $possible);
        
        $loteRps = $this->dom->createElement("LoteRps");

        if($std->versao){
            $versao = $std->versao;
            list($major,$middle,$minor) = explode('.',$versao);
            $loteRps->setAttribute('versao', "{$major}.{$middle}{$minor}");
        }

        $this->dom->addChild(
            $loteRps,
            "NumeroLote",
            $std->NumeroLote,
            true,
            "Numero do Lote RPS"
        );

        $CpfCnpj = $this->dom->createElement("CpfCnpj");

        if(strlen($std->CpfCnpj) <= 11){
            $this->dom->addChild(
                $CpfCnpj,
                "Cpf",
                $std->CpfCnpj,
                true,
                "Numero do Cpf"
            );
        }else{
            $this->dom->addChild(
                $CpfCnpj,
                "Cnpj",
                $std->CpfCnpj,
                true,
                "Numero do Cnpj"
            );
        }

        $this->dom->appChild($loteRps, $CpfCnpj , 'Falta tag "CpfCnpj"');

        $this->dom->addChild(
            $loteRps,
            "InscricaoMunicipal",
            $std->InscricaoMunicipal,
            true,
            "Numero de InscricaoMunicipal"
        );

        $this->dom->addChild(
            $loteRps,
            "QuantidadeRps",
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
            'Tipo',
            'DataEmissao',
            'Status'
        ];

        $std = $this->equilizeParameters($std, $possible);
        
        $Rps = $this->dom->createElement("Rps");

        $identificacaoRps = $this->dom->createElement("IdentificacaoRps");

        $this->dom->addChild(
            $identificacaoRps,
            "Numero",
            $std->Numero,
            true,
            "Numero do Lote RPS"
        );

        $this->dom->addChild(
            $identificacaoRps,
            "Serie",
            $std->Serie,
            true,
            "Serie do Lote RPS"
        );

        $this->dom->addChild(
            $identificacaoRps,
            "Tipo",
            $std->Tipo,
            true,
            "Tipo do Lote RPS"
        );

        $this->dom->appChild($Rps, $identificacaoRps , 'Falta tag "identificacaoRps"');

        $this->dom->addChild(
            $Rps,
            "DataEmissao",
            $std->DataEmissao,
            true,
            "Data da Emissao"
        );

        $this->dom->addChild(
            $Rps,
            "Status",
            $std->Status,
            true,
            "Status Rps"
        );

        $this->identificacaoRps[$this->item] = $Rps;

    }

    public function buildPrestador($std){

        $this->item = $this->item + 1;

        $this->rps[$this->item] = $this->dom->createElement("Rps");

        $possible = [
            'CpfCnpj',
            'InscricaoMunicipal'
        ];

        $std = $this->equilizeParameters($std, $possible);

        $prestador = $this->dom->createElement("Prestador");

        $CpfCnpj = $this->dom->createElement("CpfCnpj");

        if($std->CpfCnpj <= 11){

            $this->dom->addChild(
                $CpfCnpj,
                "Cpf",
                $std->CpfCnpj,
                true,
                "CpfCnpj Prestador"
            );
        }else{
            $this->dom->addChild(
                $CpfCnpj,
                "Cnpj",
                $std->CpfCnpj,
                true,
                "CpfCnpj Prestador"
            );
        }

        $this->dom->appChild($prestador, $CpfCnpj , 'Falta tag "CpfCnpj"');




        $this->dom->addChild(
            $prestador,
            "InscricaoMunicipal",
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

        $tomador = $this->dom->createElement("Tomador");

        if ($std->CpfCnpj || $std->InscricaoMunicipal){
            
            $identificacaoTomador = $this->dom->createElement("IdentificacaoTomador");

            $CpfCnpj = $this->dom->createElement("CpfCnpj");

            if (strlen($std->CpfCnpj) > 11){
               
                $this->dom->addChild(
                    $CpfCnpj,
                    "Cnpj",
                    $std->CpfCnpj,
                    false,
                    "Cnpj Tomador"
                );

            } else {

                $this->dom->addChild(
                    $CpfCnpj,
                    "Cpf",
                    $std->CpfCnpj,
                    false,
                    "Cpf Tomador"
                );

            }

            $this->dom->appChild($identificacaoTomador, $CpfCnpj , 'Falta tag "identificacaoTomador"');

            $this->dom->addChild(
                $identificacaoTomador,
                "InscricaoMunicipal",
                $std->InscricaoMunicipal,
                false,
                "InscricaoMunicipal Tomador"
            );

            $this->dom->appChild($tomador, $identificacaoTomador , 'Falta tag "identificacaoTomador"');

        }

        $this->dom->addChild(
            $tomador,
            "RazaoSocial",
            $std->RazaoSocial,
            false,
            "RazaoSocial Tomador"
        );

        if ($std->Endereco || $std->Numero || $std->Complemento || $std->Bairro || $std->CodigoMunicipio || $std->Uf || $std->Cep){

            $endereco = $this->dom->createElement("Endereco");

            $this->dom->addChild(
                $endereco,
                "Endereco",
                $std->Endereco,
                false,
                "Endereco Tomador"
            );

            $this->dom->addChild(
                $endereco,
                "Numero",
                $std->Numero,
                false,
                "Numero Tomador"
            );

            $this->dom->addChild(
                $endereco,
                "Complemento",
                $std->Complemento,
                false,
                "Complemento Tomador"
            );

            $this->dom->addChild(
                $endereco,
                "Bairro",
                $std->Bairro,
                false,
                "Bairro Tomador"
            );

            $this->dom->addChild(
                $endereco,
                "CodigoMunicipio",
                $std->CodigoMunicipio,
                false,
                "CodigoMunicipio Tomador"
            );

            $this->dom->addChild(
                $endereco,
                "Uf",
                $std->Uf,
                false,
                "Uf Tomador"
            );

            $this->dom->addChild(
                $endereco,
                "Cep",
                $std->Cep,
                false,
                "Cep Tomador"
            );
            
            $this->dom->appChild($tomador, $endereco , 'Falta tag "tomador"');

        }

        if ( $std->Telefone || $std->Email ){

            $contato = $this->dom->createElement("Contato");

            $this->dom->addChild(
                $contato,
                "Telefone",
                $std->Telefone,
                false,
                "Telefone Tomador"
            );

            $this->dom->addChild(
                $contato,
                "Email",
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

        $intermediarioServico = $this->dom->createElement("Intermediario");

        $identificacaoIntermediario = $this->dom->createElement("IdentificacaoIntermediario");


        $CpfCnpj = $this->dom->createElement("CpfCnpj");

        if (strlen($std->CpfCnpj) > 11){
           
            $this->dom->addChild(
                $CpfCnpj,
                "Cnpj",
                $std->CpfCnpj,
                false,
                "Cnpj Tomador"
            );

        } else {

            $this->dom->addChild(
                $CpfCnpj,
                "Cpf",
                $std->CpfCnpj,
                false,
                "Cpf Tomador"
            );

        }

        $this->dom->appChild($identificacaoIntermediario, $CpfCnpj , 'Falta tag "tomador"');
       
        
        $this->dom->addChild(
            $identificacaoIntermediario,
            "InscricaoMunicipal",
            $std->InscricaoMunicipal,
            false,
            "InscricaoMunicipal IntermediarioServico"
        );

        
        $this->dom->appChild($intermediarioServico,$identificacaoIntermediario, 'Falta tag "tomador"');
        
        $this->dom->addChild(
            $intermediarioServico,
            "RazaoSocial",
            $std->RazaoSocial,
            false,
            "RazaoSocial IntermediarioServico"
        );

        $this->intermediarioServico[$this->item] = $intermediarioServico;
    }

    public function buildConstrucaoCivil($std){

         $possible = [
            'CodigoObra',
            'Art'
        ];

        $std = $this->equilizeParameters($std, $possible);

        $construcaoCivil = $this->dom->createElement("ConstrucaoCivil");

        $this->dom->addChild(
            $construcaoCivil,
            "CodigoObra",
            $std->CodigoObra,
            true,
            "CodigoObra de ConstrucaoCivil"
        );

        $this->dom->addChild(
            $construcaoCivil,
            "Art",
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

        $rpsSubstituido = $this->dom->createElement("RpsSubstituido");

        $this->dom->addChild(
            $rpsSubstituido,
            "Numero",
            $std->Numero,
            true,
            "Numero do Lote RPS"
        );

        $this->dom->addChild(
            $rpsSubstituido,
            "Serie",
            $std->Serie,
            true,
            "Serie do Lote RPS"
        );

        $this->dom->addChild(
            $rpsSubstituido,
            "Tipo",
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
           'ResponsavelRetencao',
           'ItemListaServico',
           'CodigoCnae',
           'CodigoTributacaoMunicipio',
           'Discriminacao',
           'CodigoMunicipio',
           'CodigoPais',
           'ExigibilidadeISS',
           'MunicipioIncidencia',
           'NumeroProcesso'
        ];

        $std = $this->equilizeParameters($std, $possible);

        $servico = $this->dom->createElement("Servico");
        
        $valores = $this->dom->createElement("Valores");

        $this->dom->addChild(
            $valores,
            "ValorServicos",
            $std->ValorServicos,
            true,
            "ValorServicos RPS"
        );

        $this->dom->addChild(
            $valores,
            "ValorDeducoes",
            $std->ValorDeducoes,
            false,
            "ValorDeducoes RPS"
        );

        $this->dom->addChild(
            $valores,
            "ValorPis",
            $std->ValorPis,
            false,
            "ValorPis RPS"
        );

        $this->dom->addChild(
            $valores,
            "ValorCofins",
            $std->ValorCofins,
            false,
            "ValorCofins RPS"
        );

        $this->dom->addChild(
            $valores,
            "ValorInss",
            $std->ValorInss,
            false,
            "ValorInss RPS"
        );

        $this->dom->addChild(
            $valores,
            "ValorIr",
            $std->ValorIr,
            false,
            "ValorIr RPS"
        );
        
        $this->dom->addChild(
            $valores,
            "ValorCsll",
            $std->ValorCsll,
            false,
            "ValorCsll RPS"
        );

        $this->dom->addChild(
            $valores,
            "OutrasRetencoes",
            $std->OutrasRetencoes,
            false,
            "OutrasRetencoes RPS"
        );
        
        $this->dom->addChild(
            $valores,
            "ValorIss",
            $std->ValorIss,
            false,
            "ValorIss RPS"
        );
        
        /*
        $this->dom->addChild(
            $valores,
            "BaseCalculo",
            $std->BaseCalculo,
            false,
            "BaseCalculo RPS"
        );
        */
        $this->dom->addChild(
            $valores,
            "Aliquota",
            $std->Aliquota,
            false,
            "Aliquota RPS"
        );
      /*  
        $this->dom->addChild(
            $valores,
            "ValorLiquidoNfse",
            $std->ValorLiquidoNfse,
            false,
            "ValorLiquidoNfse RPS"
        );
        */
        $this->dom->addChild(
            $valores,
            "DescontoIncondicionado",
            $std->DescontoIncondicionado,
            false,
            "DescontoIncondicionado RPS"
        );
        
        $this->dom->addChild(
            $valores,
            "DescontoCondicionado",
            $std->DescontoCondicionado,
            false,
            "DescontoCondicionado RPS"
        );
        
        $this->dom->appChild($servico, $valores , 'Falta tag "servico"');
        
        $this->dom->addChild(
            $servico,
            "IssRetido",
            $std->IssRetido,
            true,
            "IssRetido RPS"
        );

        $this->dom->addChild(
            $servico,
            "ResponsavelRetencao",
            $std->ResponsavelRetencao,
            false,
            "Responsavel Retencao RPS"
        );

        
        $this->dom->addChild(
            $servico,
            "ItemListaServico",
            $std->ItemListaServico,
            false,
            "ItemListaServico RPS"
        );

        $this->dom->addChild(
            $servico,
            "CodigoCnae",
            $std->CodigoCnae,
            false,
            "CodigoCnae RPS"
        );
        
        $this->dom->addChild(
            $servico,
            "CodigoTributacaoMunicipio",
            $std->CodigoTributacaoMunicipio,
            false,
            "CodigoTributacaoMunicipio RPS"
        );

        $this->dom->addChild(
            $servico,
            "Discriminacao",
            $std->Discriminacao,
            true,
            "Discriminacao RPS"
        );

        $this->dom->addChild(
            $servico,
            "CodigoMunicipio",
            $std->CodigoMunicipio,
            true,
            "CodigoMunicipio RPS"
        );

        $this->dom->addChild(
            $servico,
            "CodigoPais",
            $std->CodigoPaisBacen,
            false,
            "Codigo Pais"
        );

        
        $this->dom->addChild(
            $servico,
            "ExigibilidadeISS",
            $std->ExigibilidadeISS,
            true,
            "Exigibilidade ISS"
        );

        $this->dom->addChild(
            $servico,
            "MunicipioIncidencia",
            $std->MunicipioIncidencia,
            false,
            "MunicipioIncidencia ISS"
        );

        $this->dom->addChild(
            $servico,
            "NumeroProcesso",
            $std->NumeroProcesso,
            false,
            "NumeroProcesso ISS"
        );


        $this->servico[$this->item] = $servico;


    }

    public function buildInfNfse($std){

        $possible = [
            'Competencia',
            'NaturezaOperacao',
            'RegimeEspecialTributacao',
            'OptanteSimplesNacional',
            'IncentivadorCultural',
            'IncentivoFiscal'
        ];

        $std = $this->equilizeParameters($std, $possible);

        $infRps = $this->dom->createElement("InfDeclaracaoPrestacaoServico");

        $infRps->setAttribute('Id', 'RPS'.$this->NumeroLote);
        
        $this->dom->appChild($infRps, $this->identificacaoRps[$this->item] , 'Falta tag "InfDeclaracaoPrestacaoServico"');

        $this->dom->addChild(
            $infRps,
            "Competencia",
            $std->Competencia,
            true,
            "Competencia RPS"
        );

        $this->infoAdic = (object) array_merge(array(), (array)$std );
/*
        $this->dom->addChild(
            $infRps,
            "NaturezaOperacao",
            $std->NaturezaOperacao,
            true,
            "NaturezaOperacao RPS"
        );


        $this->dom->addChild(
            $infRps,
            "RegimeEspecialTributacao",
            $std->RegimeEspecialTributacao,
            false,
            "RegimeEspecialTributacao RPS"
        );

        $this->dom->addChild(
            $infRps,
            "OptanteSimplesNacional",
            $std->OptanteSimplesNacional,
            true,
            "OptanteSimplesNacional RPS"
        );  

        $this->dom->addChild(
            $infRps,
            "IncentivadorCultural",
            $std->IncentivadorCultural,
            true,
            "IncentivadorCultural RPS"
        );

        $this->dom->addChild(
            $infRps,
            "Status",
            $std->Status,
            true,
            "Status RPS"
        ); 
 */       

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