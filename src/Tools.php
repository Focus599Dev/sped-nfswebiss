<?php 

namespace NFePHP\NFSe\GINFE;

/**
 * @category   NFePHP
 * @package    NFePHP\NFSe\GINFE
 * @copyright  Copyright (c) 2008-2019
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Marlon O. Barbosa <lmarlon.academi at gmail dot com>
 * @link       https://github.com/Focus599Dev/sped-nfsginfe for the canonical source repository
 */

use NFePHP\NFSe\GINFE\Common\Tools as ToolsBase;
use NFePHP\Common\Strings;
use NFePHP\NFSe\GINFE\Common\Signer;
use DOMDocument;

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

        $xml = trim(preg_replace("/<\?xml.*?\?>/", "", $xml));

        $request = "<EnviarLoteRpsEnvio ";

        foreach ($this->soapnamespaces as $key => $namespace) {
        	$request .= ' ' . $key . '="' . $namespace . '" '; 
        }

        $request .= '>';

        $request .= "$xml"
            . "</EnviarLoteRpsEnvio>";

        $signed = Signer::sign(
            $this->certificate,
            $request,
            'Rps',
            'Id',
            $this->algorithm,
            $this->canonical
        );

        $signed = Signer::sign(
            $this->certificate,
            $signed,
            'EnviarLoteRpsEnvio',
            'Id',
            $this->algorithm,
            $this->canonical
        );

        $request = $signed;

        $this->lastRequest = $request;
        
        $this->isValid($this->versao, $request, 'servico_enviar_lote_rps_envio');

	}
}                                                                                                                            

?>