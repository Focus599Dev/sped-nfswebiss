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
use Mpdf\Mpdf;

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

        if (isset($this->lastResponse->CancelarNfseResult->CancelarNfseResposta)){

            return $this->lastResponse->CancelarNfseResult->CancelarNfseResposta->asXML();
        }

        return $this->lastResponse->asXML();

    }

   public function generatePDFNfse($xml, $tpAmb, $status, $logoPath){

        $template = file_get_contents(realpath(__DIR__ . '/../template') . '/nfse.html');

        $contentlogoPres = '';

        if (is_file($logoPath)){

            $contentlogoPres = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $codeTrib = array(
            '1401' => 'Lubrifica&ccedil;&atilde;o, limpeza, lustra&ccedil;&atilde;o, revis&atilde;o, carga e recarga, conserto, restaura&ccedil;&atilde;o, blindagem, manuten&ccedil;&atilde;o e conserva&ccedil;&atilde;o de m&aacute;quinas, ve&iacute;culos, aparelhos, equipamentos, motores, elevadores ou de qualquer objeto (exceto pe&ccedil;as e partes empregadas, que ficam sujeitas ao ICMS)'
        );

        $replace = array(
           'logo' =>  'data:image/png;base64,' . base64_encode(file_get_contents(realpath(__DIR__ . '/../template') . '/logo.png')),
           'logo-uberaba' => 'data:image/jpg;base64,' . base64_encode(file_get_contents(realpath(__DIR__ . '/../template') . '/uberaba-200.jpg')),
           'url-selo' => asset('/../vendor/Focus599Dev/sped-nfswebiss/template/selo-wbiss.jpg'),
           'nfenum' => $xml->Nfse->InfNfse->IdentificacaoRps->Numero,
           'serie' => $xml->Nfse->InfNfse->IdentificacaoRps->Serie,
           'dhemi' => (new \DateTime($xml->Nfse->InfNfse->DataEmissao))->format('d/m/Y'),
           'dhEmisec' => (new \DateTime($xml->Nfse->InfNfse->DataEmissao))->format('d/m/Y H:i'),
           'dhcomp' => (new \DateTime($xml->Nfse->InfNfse->DataEmissao))->format('m/Y'),
           'xMun' => $xml->Nfse->InfNfse->PrestadorServico->Endereco->Uf,
           'regimeTrib' => $xml->Nfse->InfNfse->RegimeEspecialTributacao ? 'Nenhum' : 'Esp&eacute;cial',
           'naturesaop' => $xml->Nfse->InfNfse->NaturezaOperacao == 1 ? 'Trib. no munic&#237;pio de Uberaba' : 'Trib. forfor&aacute; munic&#237;pio de Uberaba',
           'nfsserie' => substr($xml->Nfse->InfNfse->Numero, 0, 7),
           'nfsnum' => substr($xml->Nfse->InfNfse->Numero, 7),
           'codveri' => $xml->Nfse->InfNfse->CodigoVerificacao,
           'emirazao' => $xml->Nfse->InfNfse->PrestadorServico->RazaoSocial,
           'emicnpj' => $this->formatCNPJ($xml->Nfse->InfNfse->PrestadorServico->IdentificacaoPrestador->Cnpj),
           'email' => $xml->Nfse->InfNfse->PrestadorServico->Contato->Email,
           'logoPres' => $contentlogoPres,
           'inscMuniEmi' => $xml->Nfse->InfNfse->PrestadorServico->IdentificacaoPrestador->InscricaoMunicipal,
           'FoneEmi' => isset($xml->Nfse->InfNfse->PrestadorServico->Contato->Telefone) ? $xml->Nfse->InfNfse->PrestadorServico->Contato->Telefone : '',
           'OpSimpleNaciEmi' => $xml->Nfse->InfNfse->OptanteSimplesNacional == 1 ? 'Sim' : 'Não',
           'IncetCultEmi' => $xml->Nfse->InfNfse->IncentivadorCultural == 1 ? 'Sim' : 'Não',
           'EnderecoEmi' => $xml->Nfse->InfNfse->PrestadorServico->Endereco->Endereco . ', ' . $xml->Nfse->InfNfse->PrestadorServico->Endereco->Numero . ' Bairro ' . $xml->Nfse->InfNfse->PrestadorServico->Endereco->Bairro . ' CEP ' . $xml->Nfse->InfNfse->PrestadorServico->Endereco->Cep . ' Uberaba - MG',
           'destrazao' => $xml->Nfse->InfNfse->TomadorServico->RazaoSocial,
           'destCNPJ' => isset($xml->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cnpj) ? $this->formatCNPJ($xml->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cnpj) : $this->formatCPF($xml->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cpf),
           'inscMuniDest' => isset($xml->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->InscricaoMunicipal) ? $xml->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->InscricaoMunicipal : '',
           'FoneDest' => $xml->Nfse->InfNfse->TomadorServico->Contato->Telefone,
           'EmailDest' => $xml->Nfse->InfNfse->TomadorServico->Contato->Email,
           'EnderecoDest' => $xml->Nfse->InfNfse->TomadorServico->Endereco->Endereco . ', ' . $xml->Nfse->InfNfse->TomadorServico->Endereco->Numero . ' Bairro ' . $xml->Nfse->InfNfse->TomadorServico->Endereco->Bairro . ' CEP ' . $xml->Nfse->InfNfse->TomadorServico->Endereco->Cep,
           'OutrasInformacoes' => $xml->Nfse->InfNfse->OutrasInformacoes,
           'codTrib' => $xml->Nfse->InfNfse->Servico->CodigoTributacaoMunicipio,
           'textCodeTrib' => isset($codeTrib[(String)$xml->Nfse->InfNfse->Servico->CodigoTributacaoMunicipio]) ? $codeTrib[(String)$xml->Nfse->InfNfse->Servico->CodigoTributacaoMunicipio] : '',
           'vPIS' => isset( $xml->Nfse->InfNfse->Servico->Valores->ValorPis) ? number_format($xml->Nfse->InfNfse->Servico->Valores->ValorPis, 2, ',', '.') : '0,00',
           'vCOFINS' => isset( $xml->Nfse->InfNfse->Servico->Valores->ValorCofins) ? number_format($xml->Nfse->InfNfse->Servico->Valores->ValorCofins, 2, ',', '.') : '0,00',
           'vINSS' => isset( $xml->Nfse->InfNfse->Servico->Valores->ValorInss) ? number_format($xml->Nfse->InfNfse->Servico->Valores->ValorInss, 2, ',', '.') : '0,00',
           'vIR' => isset( $xml->Nfse->InfNfse->Servico->Valores->ValorIr) ? number_format($xml->Nfse->InfNfse->Servico->Valores->ValorIr, 2, ',', '.') : '0,00',
           'vCSLL' => isset( $xml->Nfse->InfNfse->Servico->Valores->ValorCsll) ? number_format($xml->Nfse->InfNfse->Servico->Valores->ValorCsll, 2, ',', '.') : '0,00',
           'vOthers' => '0,00',
           'Discriminacao' => $xml->Nfse->InfNfse->Servico->Discriminacao,
           'valorServ' => number_format((String)$xml->Nfse->InfNfse->Servico->Valores->ValorServicos, 2 ,',', '.'),
           'valorDedu' => '0,00',
           'valorIncod' => '0,00',
           'valorBasecalc' => number_format((String)$xml->Nfse->InfNfse->Servico->Valores->BaseCalculo, 2 ,',', '.'),
           'Aliquota' => number_format(((Float)$xml->Nfse->InfNfse->Servico->Valores->Aliquota * 100), 2 ,',', '.'),
           'valorISS' => number_format((String)$xml->Nfse->InfNfse->Servico->Valores->ValorIss, 2 ,',', '.'),
           'valorISSR' => isset($xml->Nfse->InfNfse->Servico->Valores->ValorIssRetido) ? number_format((String)$xml->Nfse->InfNfse->Servico->Valores->ValorIssRetido, 2 ,',', '.'): '0,00',
           'valorCond' => '0,00',
           'valorLiquido' => number_format((String)$xml->Nfse->InfNfse->Servico->Valores->ValorLiquidoNfse, 2 ,',', '.'),
           'valorTotal' => number_format((String)$xml->Nfse->InfNfse->Servico->Valores->ValorLiquidoNfse, 2 ,',', '.'),
        );


        foreach ($replace as $key => $value) {
            
            $template = str_replace("{{%$key}}", $value, $template);

        }

        $mpdf = new Mpdf();

        $mpdf->SetDisplayMode(100,'default');

        $mpdf->allow_charset_conversion = true;

        $mpdf->charset_in='iso-8859-4';

        $mpdf->SetMargins(0,0,0);    

        $mpdf->WriteHTML(utf8_decode($template));

        $mpdf->Output();

   }

}                                                                                                                            

?>