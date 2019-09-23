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

use NFePHP\NFSe\GINFE\Make;
use NFePHP\NFSe\GINFE\Exception\DocumentsException;
use stdClass;
use NFePHP\Common\Strings;

class Parser {

	/**
     * @var array
    */
    protected $structure;

    /**
     * @var Make
    */
    protected $make;

    /**
     * @var stdClass
    */
    protected $loteRps;

    /**
     * @var stdClass
    */
    protected $tomador;

    /**
     * @var stdClass
    */
    protected $servico;

    /**
     * Configure environment to correct NFSe layout
     * @param string $version
    */
    public function __construct($version = '3.0.1'){
        
        $ver = str_replace('.', '', $version);

        $path = realpath(__DIR__ . "/../../storage/txtstructure$ver.json");

        $this->structure = json_decode(file_get_contents($path), true);

        $this->version = $version;

        $this->make = new Make();
    }

    /**
     * Convert txt to XML
     * @param array $nota
     * @return string|null
     */
    public function toXml($nota) {
       
        $this->array2xml($nota);

        if ($this->make->monta()) {

            return $this->make->getXML();

        }

        return null;
    }

    /**
     * Converte txt array to xml
     * @param array $nota
     * @return void
    */
    protected function array2xml($nota){

        foreach ($nota as $lin) {
            
            $fields = explode('|', $lin);

            if (empty($fields)) {
                continue;
            }

            $metodo = strtolower(str_replace(' ', '', $fields[0])).'Entity';

            if (!method_exists(__CLASS__, $metodo)) {
                //campo não definido
                throw DocumentsException::wrongDocument(16, $lin);
            }

            $struct = $this->structure[strtoupper($fields[0])];

            $std = $this->fieldsToStd($fields, $struct);

            $this->$metodo($std);
        }
    }

    /**
     * Creates stdClass for all tag fields
     * @param array $dfls
     * @param string $struct
     * @return stdClass
    */
   
    protected static function fieldsToStd($dfls, $struct) {
        
        $sfls = explode('|', $struct);
        
        $len = count($sfls)-1;
        
        $std = new \stdClass();

        for ($i = 1; $i < $len; $i++) {
            
            $name = $sfls[$i];
            
            if (isset($dfls[$i]))
                $data = $dfls[$i];
            else 
                $data = '';

            if (!empty($name)) {

                $std->$name = Strings::replaceSpecialsChars($data);
            }

        }

        return $std;

    }

    /**
     * Create tag LoteRps [A]
     * A|NumeroLote|versao|
     * @param stdClass $std
     * @return void
    */
    private function aEntity($std){

        $this->loteRps = $std;

    }

    /**
     * Complete tag LoteRps [B]
     * B|Cnpj|InscricaoMunicipal|QuantidadeRps|
     * @param stdClass $std
     * @return void
    */
    private function bEntity($std){

        $this->loteRps = (object) array_merge((array) $this->loteRps, (array) $std);

        $this->make->buildLoteRps($this->loteRps);

    }

    /**
     * Create tag Prestador [C]
     * C|Cnpj|InscricaoMunicipal|
     * @param stdClass $std
     * @return void
    */
    private function cEntity($std){
        
        $this->make->buildPrestador($std);

    }

    /**
     * Create tag Tomador [E]
     * E|RazaoSocial|Endereco|Contato|Telefone|Email|Numero|Complemento|Bairro|CodigoMunicipio|Uf|Cep|
     * @param stdClass $std
     * @return void
    */
    private function eEntity($std){

        $this->tomador = (object) array_merge((array) $this->tomador, (array) $std);
        

    }

    /**
     * Complete tag Tomador [E02]
     * E02|Cnpj|InscricaoMunicipal|
     * @param stdClass $std
     * @return void
    */
    private function e02Entity($std){

        $this->tomador = (object) array_merge((array) $this->tomador, (array) $std);

        $this->make->buildTomador($this->tomador);

    }   

    /**
     * Create tag IntermediarioServico [E03]
     * E03|RazaoSocial|CpfCnpj|InscricaoMunicipal|
     * @param stdClass $std
     * @return void
    */
    private function e03Entity($std){

        $this->make->buildIntermediarioServico($std);
    }

    /**
     * Create tag ConstrucaoCivil [F]
     * F|CodigoObra|Art|
     * @param stdClass $std
     * @return void
    */
    private function fEntity($std){

        $this->make->buildConstrucaoCivil($std);

    }

    /**
     * Create tag IdentificacaoRps [H]
     * H|
     * @param stdClass $std
     * @return void
    */
    private function hEntity($std){

    }

    /**
     * Complete tag IdentificacaoRps [H01]
     * H01|Numero|Serie|Tipo
     * @param stdClass $std
     * @return void
    */
    private function h01Entity($std){

        $this->make->buildIdentificacaoRps($std);

    }

    /**
     * Create tag RpsSubstituido [I02]
     * I02|Numero|Serie|Tipo
     * @param stdClass $std
     * @return void
    */
    private function i02Entity($std){

        $this->make->buildRpsSubstituido($std);

    }

    /**
     * Create tag Valores [M]
     * M|ValorServicos|ValorDeducoes|ValorPis|ValorCofins|ValorInss|ValorIr|ValorCsll|IssRetido|ValorIss|ValorIssRetido|OutrasRetencoes|BaseCalculo|Aliquota|ValorLiquidoNfse|DescontoIncondicionado|DescontoCondicionado|
     * @param stdClass $std
     * @return void
    */
    private function mEntity($std){

        $this->servico = $std;

    }

    /**
     * Create tag Servico [N]
     * N|ItemListaServico|CodigoCnae|CodigoTributacaoMunicipio|Discriminacao|CodigoMunicipio|
     * @param stdClass $std
     * @return void
    */
    private function nEntity($std){

        $this->servico = (object) array_merge((array) $this->servico, (array) $std);

        $this->make->buildServico($this->servico);
    }

    /**
     * Complete tag tcInfRps [I]
     * w|DataEmissao|NaturezaOperacao|RegimeEspecialTributacao|OptanteSimplesNacional|IncentivadorCultural|Status|
     * @param stdClass $std
     * @return void
    */
    private function wEntity($std){

        $this->make->buildInfNfse($std);
        
    }
}