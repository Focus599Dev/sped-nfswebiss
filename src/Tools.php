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

use NFePHP\NFSe\GINFE\Common\Tools as ToolsBase;
use NFePHP\Common\Strings;
use NFePHP\NFSe\GINFE\Common\Signer;
use DOMDocument;
use NFePHP\Common\DOMImproved as Dom;


class Tools extends ToolsBase {

	public function enviaRPS($xml){

		if (empty($xml)) {
            throw new InvalidArgumentException('$xml');
        }
        //remove all invalid strings
        $xml = Strings::clearXmlString($xml);

        $servico = 'RecepcionarLoteRpsV3';

        $this->servico(
            $servico,
            $this->config->municipio,
            $this->tpAmb
        );

        $request = Signer::sign(
            $this->certificate,
            $xml,
            'EnviarLoteRpsEnvio',
            'Id',
            $this->algorithm,
            $this->canonical
        );

        $this->lastRequest = $request;
        
        $this->isValid($this->versao, $request, 'servico_enviar_lote_rps_envio');

        $parameters = ['RecepcionarLoteRps' => $request];

        $request = $this->MakeEnvelope($servico, $request);
        
        $this->lastResponse = $this->sendRequest($request, $parameters);
        
        $this->lastResponse = $this->removeStuffs($this->lastResponse);

        $auxResp = simplexml_load_string($this->lastResponse);

        return (String) $auxResp->return[0];

	}

    public function consultaLoteRPS($prot, \stdClass $prestador){

        $servico = 'ConsultarLoteRpsV3';

        $this->servico(
            $servico,
            $this->config->municipio,
            $this->tpAmb
        );

        $namespaces = array(
            'xmlns:p="http://www.ginfes.com.br/servico_consultar_lote_rps_envio_v03.xsd"',
            'xmlns:tipos="http://www.ginfes.com.br/tipos_v03.xsd"',
            'xmlns="http://www.w3.org/2000/09/xmldsig#"'
        );

        $xml = '<p:ConsultarLoteRpsEnvio Id="' . str_pad(rand(0, pow(10, 5)-1), 5, '0', STR_PAD_LEFT) . '" ';

            $xml .= implode(' ', $namespaces) . '>';

            $xml .= '<p:Prestador>';

                $xml .= '<tipos:Cnpj>' . $prestador->cnpj . '</tipos:Cnpj>';
                
                $xml .= '<tipos:InscricaoMunicipal>' . $prestador->inscricaoMunicipal . '</tipos:InscricaoMunicipal>';
                
            $xml .= '</p:Prestador>';

            $xml .= '<p:Protocolo>' . $prot . '</p:Protocolo>';

        $xml .= '</p:ConsultarLoteRpsEnvio>';

        $request = Signer::sign(
            $this->certificate,
            $xml,
            'ConsultarLoteRpsEnvio',
            'Id',
            $this->algorithm,
            $this->canonical
        );

        $this->lastRequest = $request;

        $this->isValid($this->versao, $request, 'servico_consultar_lote_rps_envio');

        $parameters = ['ConsultarLoteRpsEnvio' => $request];

        $request = $this->MakeEnvelope($servico, $request);

        $this->lastResponse = $this->sendRequest($request, $parameters);

        $this->lastResponse = $this->removeStuffs($this->lastResponse);

        $auxResp = simplexml_load_string($this->lastResponse);

        return (String) $auxResp->return[0];

    }

    public function consultaSituacaoLoteRPS($prot, \stdClass $prestador){

        $servico = 'ConsultarSituacaoLoteRpsV3';

        $this->servico(
            $servico,
            $this->config->municipio,
            $this->tpAmb
        );

        $namespaces = array(
            'xmlns:p="http://www.ginfes.com.br/servico_consultar_situacao_lote_rps_envio_v03.xsd"',
            'xmlns:tipos="http://www.ginfes.com.br/tipos_v03.xsd"',
            'xmlns="http://www.w3.org/2000/09/xmldsig#"'
        );

        $xml = '<p:ConsultarSituacaoLoteRpsEnvio Id="' . str_pad(rand(0, pow(10, 5)-1), 5, '0', STR_PAD_LEFT) . '" ';

            $xml .= implode(' ', $namespaces) . '>';

            $xml .= '<p:Prestador>';

                $xml .= '<tipos:Cnpj>' . $prestador->cnpj . '</tipos:Cnpj>';
                
                $xml .= '<tipos:InscricaoMunicipal>' . $prestador->inscricaoMunicipal . '</tipos:InscricaoMunicipal>';
                
            $xml .= '</p:Prestador>';

            $xml .= '<p:Protocolo>' . $prot . '</p:Protocolo>';

        $xml .= '</p:ConsultarSituacaoLoteRpsEnvio>';

        $request = Signer::sign(
            $this->certificate,
            $xml,
            'ConsultarSituacaoLoteRpsEnvio',
            'Id',
            $this->algorithm,
            $this->canonical
        );

        $this->lastRequest = $request;

        $this->isValid($this->versao, $request, 'servico_consultar_situacao_lote_rps_envio');

        $parameters = ['ConsultarSituacaoLoteRpsEnvio' => $request];

        $request = $this->MakeEnvelope($servico, $request);

        $this->lastResponse = $this->sendRequest($request, $parameters);

        $this->lastResponse = $this->removeStuffs($this->lastResponse);

        $auxResp = simplexml_load_string($this->lastResponse);

        return (String) $auxResp->return[0];

    }
    
    public function ConsultarNfsePorRps(\stdClass $indenRPS , \stdClass $prestador){

        $servico = 'ConsultarNfsePorRpsV3';

        $this->servico(
            $servico,
            $this->config->municipio,
            $this->tpAmb
        );

        $namespaces = array(
            'xmlns:p="http://www.ginfes.com.br/servico_consultar_nfse_rps_envio_v03.xsd"',
            'xmlns:tipos="http://www.ginfes.com.br/tipos_v03.xsd"',
            'xmlns="http://www.w3.org/2000/09/xmldsig#"'
        );

        $xml = '<p:ConsultarNfseRpsEnvio ';

            $xml .= implode(' ', $namespaces) . '>';

            $xml .= '<p:IdentificacaoRps>';

                $xml .= '<tipos:Numero>' . $indenRPS->Numero . '</tipos:Numero>';
                
                $xml .= '<tipos:Serie>' . $indenRPS->Serie . '</tipos:Serie>';
                
                $xml .= '<tipos:Tipo>' . $indenRPS->Tipo . '</tipos:Tipo>';

            $xml .= '</p:IdentificacaoRps>';

            $xml .= '<p:Prestador>';

                $xml .= '<tipos:Cnpj>' . $prestador->cnpj . '</tipos:Cnpj>';
                
                $xml .= '<tipos:InscricaoMunicipal>' . $prestador->inscricaoMunicipal . '</tipos:InscricaoMunicipal>';
                
            $xml .= '</p:Prestador>';

        $xml .= '</p:ConsultarNfseRpsEnvio>';

        $request = Signer::sign(
            $this->certificate,
            $xml,
            'ConsultarNfseRpsEnvio',
            'Id',
            $this->algorithm,
            $this->canonical
        );

        $this->lastRequest = $request;

        $this->isValid($this->versao, $request, 'servico_consultar_nfse_rps_envio');

        $parameters = ['ConsultarNfseRpsEnvio' => $request];

        $request = $this->MakeEnvelope($servico, $request);

        $this->lastResponse = $this->sendRequest($request, $parameters);

        $this->lastResponse = $this->removeStuffs($this->lastResponse);

        $auxResp = simplexml_load_string($this->lastResponse);

        return (String) $auxResp->return[0];

    }
}                                                                                                                            

?>