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

use NFePHP\NFSe\WebISS\Common\Tools as ToolsBase;
use NFePHP\Common\Strings;
use NFePHP\NFSe\WebISS\Common\Signer;
use DOMDocument;
use NFePHP\Common\DOMImproved as Dom;

class Tools extends ToolsBase {

	public function enviaRPS($xml){

		if (empty($xml)) {
            throw new InvalidArgumentException('$xml');
        }
        //remove all invalid strings
        $xml = Strings::clearXmlString($xml);

        $servico = 'RecepcionarLoteRps';

        $this->servico(
            $servico,
            $this->config->municipio,
            $this->tpAmb
        );

        $request = Signer::sign(
            $this->certificate,
            $xml,
            'LoteRps',
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

        $this->lastResponse = simplexml_load_string($this->lastResponse);

        if (isset($this->lastResponse->RecepcionarLoteRpsResult->EnviarLoteRpsResposta)){

            return $this->lastResponse->RecepcionarLoteRpsResult->EnviarLoteRpsResposta->asXML();
        }

        return $this->lastResponse->asXML();

	}

    public function consultaLoteRPS($prot, \stdClass $prestador){

        $servico = 'ConsultarLoteRps';

        $this->servico(
            $servico,
            $this->config->municipio,
            $this->tpAmb
        );

        $namespaces = array(
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',
            'xmlns:xsd="http://www.w3.org/2001/XMLSchema"',
            'xmlns="http://www.abrasf.org.br/nfse"'
        );

        $xml = '<ConsultarLoteRpsEnvio ';

            $xml .= implode(' ', $namespaces) . '>';

            $xml .= '<Prestador>';

                $xml .= '<Cnpj>' . $prestador->cnpj . '</Cnpj>';
                
                $xml .= '<InscricaoMunicipal>' . $prestador->inscricaoMunicipal . '</InscricaoMunicipal>';
                
            $xml .= '</Prestador>';

            $xml .= '<Protocolo>' . $prot . '</Protocolo>';

        $xml .= '</ConsultarLoteRpsEnvio>';

        $request = $xml;

        $this->lastRequest = $request;

        $this->isValid($this->versao, $request, 'servico_consultar_lote_rps_envio');

        $parameters = ['ConsultarLoteRpsEnvio' => $request];

        $request = $this->MakeEnvelope($servico, $request);
        
        $this->lastResponse = $this->sendRequest($request, $parameters);

        $this->lastResponse = $this->removeStuffs($this->lastResponse);

        $this->lastResponse = simplexml_load_string($this->lastResponse);

        if (isset($this->lastResponse->ConsultarLoteRpsResult->ConsultarLoteRpsResposta)){

            return $this->lastResponse->ConsultarLoteRpsResult->ConsultarLoteRpsResposta->asXML();
        }

        return $this->lastResponse->asXML();

    }

    public function consultaSituacaoLoteRPS($prot, \stdClass $prestador){

        $servico = 'ConsultarSituacaoLoteRps';

        $this->servico(
            $servico,
            $this->config->municipio,
            $this->tpAmb
        );

        $namespaces = array(
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',
            'xmlns:xsd="http://www.w3.org/2001/XMLSchema"',
            'xmlns="http://www.abrasf.org.br/nfse"'
        );

        $xml = '<ConsultarSituacaoLoteRpsEnvio ';

            $xml .= implode(' ', $namespaces) . '>';

            $xml .= '<Prestador>';

                $xml .= '<Cnpj>' . $prestador->cnpj . '</Cnpj>';
                
                $xml .= '<InscricaoMunicipal>' . $prestador->inscricaoMunicipal . '</InscricaoMunicipal>';
                
            $xml .= '</Prestador>';

            $xml .= '<Protocolo>' . $prot . '</Protocolo>';

        $xml .= '</ConsultarSituacaoLoteRpsEnvio>';

        $request = $xml;

        $this->lastRequest = $request;

        $this->isValid($this->versao, $request, 'servico_consultar_situacao_lote_rps_envio');

        $parameters = ['ConsultarSituacaoLoteRpsEnvio' => $request];

        $request = $this->MakeEnvelope($servico, $request);
        
        $this->lastResponse = $this->sendRequest($request, $parameters);

        $this->lastResponse = $this->removeStuffs($this->lastResponse);

        $this->lastResponse = simplexml_load_string($this->lastResponse);

        if (isset($this->lastResponse->ConsultarSituacaoLoteRpsResult->ConsultarSituacaoLoteRpsResposta)){

            return $this->lastResponse->ConsultarSituacaoLoteRpsResult->ConsultarSituacaoLoteRpsResposta->asXML();
        }

        return $this->lastResponse->asXML();

    }
    
    public function ConsultarNfsePorRps(\stdClass $indenRPS , \stdClass $prestador){

        $servico = 'ConsultarNfsePorRps';

        $this->servico(
            $servico,
            $this->config->municipio,
            $this->tpAmb
        );

        $namespaces = array(
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',
            'xmlns:xsd="http://www.w3.org/2001/XMLSchema"',
            'xmlns="http://www.abrasf.org.br/nfse"'
        );

        $xml = '<ConsultarNfseRpsEnvio ';

            $xml .= implode(' ', $namespaces) . '>';

            $xml .= '<IdentificacaoRps>';

                $xml .= '<Numero>' . $indenRPS->Numero . '</Numero>';
                
                $xml .= '<Serie>' . $indenRPS->Serie . '</Serie>';
                
                $xml .= '<Tipo>' . $indenRPS->Tipo . '</Tipo>';

            $xml .= '</IdentificacaoRps>';

            $xml .= '<Prestador>';

                $xml .= '<Cnpj>' . $prestador->cnpj . '</Cnpj>';
                
                $xml .= '<InscricaoMunicipal>' . $prestador->inscricaoMunicipal . '</InscricaoMunicipal>';
                
            $xml .= '</Prestador>';

        $xml .= '</ConsultarNfseRpsEnvio>';

        $request = $xml;

        $this->lastRequest = $request;

        $this->isValid($this->versao, $request, 'servico_consultar_nfse_rps_envio');

        $parameters = ['ConsultarNfseRpsEnvio' => $request];

        $request = $this->MakeEnvelope($servico, $request);
        
        $this->lastResponse = $this->sendRequest($request, $parameters);

        $this->lastResponse = $this->removeStuffs($this->lastResponse);

        $this->lastResponse = simplexml_load_string($this->lastResponse);

        if (isset($this->lastResponse->ConsultarNfsePorRpsResult->ConsultarNfseResposta)){

            return $this->lastResponse->ConsultarNfsePorRpsResult->ConsultarNfseResposta->asXML();
        }

        return $this->lastResponse->asXML();

    }

    public function CancelaNfse($pedCan){

        $servico = 'CancelarNfse';

        $this->servico(
            $servico,
            $this->config->municipio,
            $this->tpAmb
        );

        $namespaces = array(
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',
            'xmlns:xsd="http://www.w3.org/2001/XMLSchema"',
            'xmlns="http://www.abrasf.org.br/nfse"'
        );

        $xml = '<CancelarNfseEnvio ';

            $xml .= implode(' ', $namespaces) . '>';

            $xml .= '<Pedido>';

                $xml .= '<InfPedidoCancelamento Id="' . $pedCan->Numero . '">';

                    $xml .= '<IdentificacaoNfse>';

                        $xml .= '<Numero>' . $pedCan->Numero . '</Numero>';
                        
                        $xml .= '<Cnpj>' . $pedCan->cnpj . '</Cnpj>';

                        $xml .= '<InscricaoMunicipal>' . $pedCan->InscricaoMunicipal . '</InscricaoMunicipal>';

                        $xml .= '<CodigoMunicipio>' . $pedCan->CodigoMunicipio . '</CodigoMunicipio>';

                    $xml .= '</IdentificacaoNfse>';

                    $xml .= '<CodigoCancelamento>' . $pedCan->CodigoCancelamento . '</CodigoCancelamento>';

                $xml .= '</InfPedidoCancelamento>';

            $xml .= '</Pedido>';

        $xml .= '</CancelarNfseEnvio>';

        $request = $xml;

        $this->lastRequest = $request;

        $this->isValid($this->versao, $request, 'servico_cancelar_nfse_envio');

        $parameters = ['CancelarNfseEnvio' => $request];

        $request = $this->MakeEnvelope($servico, $request);
        
        $this->lastResponse = $this->sendRequest($request, $parameters);

        $this->lastResponse = $this->removeStuffs($this->lastResponse);

        $this->lastResponse = simplexml_load_string($this->lastResponse);

        var_dump($this->lastResponse);
        
        if (isset($this->lastResponse->CancelarNfseResult->CancelarNfseResposta)){

            return $this->lastResponse->CancelarNfseResult->CancelarNfseResposta->asXML();
        }

        return $this->lastResponse->asXML();

    }

    public function generateUrlPDFNfse($code_municipio, $CodigoVerificacao, $nnf, $cnpj_emit ){

        throw new \Exception("Não foi possivel gerar o PDF");
        

    }

}                                                                                                                            

?>