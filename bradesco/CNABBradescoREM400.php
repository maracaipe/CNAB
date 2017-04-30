<?php
namespace CNAB\bradesco;

use CNAB\CNAB;
use CNAB\CNABUtil;

class CNABBradescoREM400 extends CNABBradesco {

	public function __construct	($tpBeneficiario, $cpfCnpjBeneficiario, $agencia, $verificadorAgencia, $conta, $verificadorConta, $carteira, $convenio, $beneficiario, $codRemessa, $gravacaoRemessa = ""){
		parent::__construct($tpBeneficiario, $cpfCnpjBeneficiario, $agencia, $verificadorAgencia, $conta, $verificadorConta, $carteira, $convenio);

		$gravacaoRemessa = empty($gravacaoRemessa) ? date('dmy'): $gravacaoRemessa;

		$this->addField("0", 1); //Identifica��o do Registro Header: �0� (zero)
		$this->addField("1", 1); //Tipo de Opera��o: �1� (um)
		$this->addField("REMESSA", 7, ' ', STR_PAD_RIGHT); //Identifica��o por Extenso do Tipo de Opera��o: "REMESSA"
		$this->addField("1", 2, '0'); //Identifica��o do Tipo de Servi�o: �01� (um)
		$this->addField("COBRANCA", 15, ' ', STR_PAD_RIGHT); //Identifica��o por Extenso do Tipo de Servi�o: �COBRAN�A�
		$this->addField($convenio, 20, '0');//ACESSORIO ESCRITURAL
		$this->addField(strtoupper($beneficiario), 30, ' ', STR_PAD_RIGHT); //Nome do Benefici�rio
		$this->addField("237BRADESCO", 18, " ", STR_PAD_RIGHT); //Identifica��o do Banco: "237BRADESCO"
		$this->addField($gravacaoRemessa, 6); //Data da Grava��o da Remessa: formato DDMMAA
		$this->addField("", 8);
		$this->addField("MX", 2);
		$this->addField($codRemessa, 7, '0'); //Seq�encial da Remessa: n�mero seq�encial acrescido de 1 a cada remessa. Inicia com "0000001"
		$this->addField("", 277); //Complemento do Registro: Preencher com espa�os em branco
		$this->addField($this->sequencial++, 6, '0'); //Sequencial do Registro:�000001�
		$this->addField("\r\n", 2);
	}

	public function addTitulo(CNABBradescoTituloREM400 $oTitulo){

		$this->addField("1", 1); //1		001		001		001	9(01)	Identifica��o do Registro Detalhe: 1 (um)
		$this->addField("", 5);//Ag�ncia de D�bito (opcional)
		$this->addField("", 1);//D�gito da Ag�ncia de D�bito (opcional)
		$this->addField("", 5);//Raz�o da Conta Corrente (opcional)
		$this->addField("", 7);//Conta Corrente (opcional)
		$this->addField("", 1);//D�gito da Conta Corrente (opcional)

		$this->addField('0');//ZERO
		$this->addField($this->getCarteira(), 3, 0);//Carteira
		$this->addField($this->getAgencia(), 5, 0);//Agencia
		$this->addField($this->getConta(), 7, 0);//Conta
		$this->addField($this->getVerificadorConta());//Verificador

		$this->addField($oTitulo->getSeuNumero(), 25, 0);//N� Controle do Participante - Uso da Empresa
		$this->addField("237", 3);//C�digo do Banco a ser debitado na C�mara de Compensa��o - N� do Banco �237�
		$this->addField($oTitulo->getMulta(), 1);//Campo de Multa - Se = 2 considerar percentual de multa. Se = 0, sem multa. Vide Obs
		$this->addField($oTitulo->getMultaVlr(), 4, 0);//Percentual de multa - Percentual de multa a ser considerado
		if(($oTitulo->getCondEmissaoCobrancao() == 1 && !is_null($oTitulo->getNossoNumero())) || $oTitulo->getCondEmissaoCobrancao() == 2)
			$this->addField($this->retNossoNumeroOBJ($oTitulo->getNossoNumero()), 12);//Identifica��o do T�tulo no Banco - N�mero Banc�rio para Cobran�a Com e Sem Registro Vide
		else
			$this->addField($oTitulo->getNossoNumero(), 12, 0);//Identifica��o do T�tulo no Banco - N�mero Banc�rio para Cobran�a Com e Sem Registro Vide
		//$this->addField($oTitulo->getNossoNumero(), 11);//Identifica��o do T�tulo no Banco - N�mero Banc�rio para Cobran�a Com e Sem Registro Vide
		//$this->addField("", 1);//Digito de Auto Conferencia do N�mero Banc�rio. - Digito N/N
		$this->addField($oTitulo->getDesconto(), 10, 0);//Desconto Bonifica��o por dia - Valor do desconto bonif./dia
		$this->addField($oTitulo->getCondEmissaoCobrancao(), 1);//Condi��o para Emiss�o da Papeleta de Cobran�a - 1 = Banco emite e Processa o registro. 2 = Cliente emite e o Banco somente processa o registro
		$this->addField($oTitulo->getIdenEmiteBoletoDebAut(), 1);//Ident. se emite Boleto para D�bito Autom�tico - N= N�o registra na cobran�a. Diferente de N registra e emite Boleto.
		$this->addField("", 10);//Identifica��o da Opera��o do Banco
		$this->addField("R", 1);//Indicador Rateio Cr�dito (opcional)
		$this->addField("", 1);//Endere�amento para Aviso do D�bito Autom�tico em Conta Corrente (opcional)
		$this->addField("", 2);//BRANCO
		$this->addField($oTitulo->getIdfOcorrencia(), 2);//C�digos de ocorr�ncia
		$this->addField($oTitulo->getDocumento(), 10);//N� do Documento - DOCUMENTO
		$this->addField($oTitulo->getVencimento(), 6);//Data do Vencimento do T�tulo
		$this->addField($oTitulo->getValor(), 13, 0);//Valor do T�tulo (preencher sem ponto e sem v�rgula)
		$this->addField("000", 3, '0');//Banco Encarregado da Cobran�a - Preencher com zeros
		$this->addField("00000", 5, '0');//Ag�ncia Deposit�ria - Preencher com zeros
		$this->addField($oTitulo->getEspecie(), 2);//Esp�cie de T�tulo ->
													//01-Duplicata
													//02-Nota Promiss�ria
													//03-Nota de Seguro
													//04-Cobran�a Seriada
													//05-Recibo
													//10-Letras de C�mbio
													//11-Nota de D�bito
													//12-Duplicata de Serv.
													//30-Boleto de Proposta
													//99-Outros
		$this->addField("N", 1);//Identifica��o - Sempre = N
		$this->addField($oTitulo->getEmissao(), 6);//Data da emiss�o do T�tulo
		$this->addField($oTitulo->getPrimInstrucao(), 2, '0');//1� instru��o
		$this->addField($oTitulo->getSegInstrucao(), 2, '0');//2� instru��o
		$this->addField($oTitulo->getMora(), 13, '0');//Valor a ser cobrado por Dia de Atraso - //Mora por Dia de Atraso
		$this->addField($oTitulo->getDtaLimitDesc(), 6);//Data Limite P/Concess�o de Desconto
		$this->addField($oTitulo->getDesconto(), 13, '0');//Valor do Desconto
		$this->addField($oTitulo->getVlrIOF(), 13, '0');//Valor do IOF
		$this->addField($oTitulo->getVlrAbatimento(), 13, '0');//Valor do Abatimento a ser concedido ou cancelado
		$this->addField($oTitulo->getTpPagador(), 2);//Identifica��o do Tipo de Inscri��o do Pagador ->
																				//01-CPF
																				//02-CNPJ
																				//03-PIS/PASEP
																				//98-N�o tem
																				//99-Outros
		$this->addField($oTitulo->getPagadorCpfCnpj(), 14, '0');//N� Inscri��o do Pagador
		$this->addField($oTitulo->getPagador(), 40, ' ', STR_PAD_RIGHT);//Nome do Pagador
		$this->addField($oTitulo->getPagadorEndereco(), 40, ' ', STR_PAD_RIGHT);//Endere�o Completo
		$this->addField($oTitulo->getPriMensagem(), 12);//1� Mensagem
		$this->addField($oTitulo->getPagadorCep(), 8);//CEP
		$this->addField($oTitulo->getSegMensagem(), 60);//Sacador/Avalista ou 2� Mensagem
		$this->addField($this->sequencial++, 6, "0");
		$this->addField("\r\n", 2);
	}

	public function getFile(){
		$this->addField("9", 1);
		$this->addField("", 393);
		$this->addField($this->sequencial++, 6, "0");
		$this->addField("\r\n", 2);

		return parent::getFile();
	}

	public static function retNossoNumero($NossoNumero, $carteira){

		$NossoNumero = CNABUtil::fillString($NossoNumero, 11, "0");
		$sequencia = CNABUtil::fillString($carteira, 2, "0") . $NossoNumero;

		$cont = 2;
		$sum = 0.0;

		for($num=0; $num <= strlen($sequencia); $num++) {

			$sum += substr($sequencia, $num, 1) * $cont;

			if($cont == 2)
				$cont = 8;

			$cont--;
		}

		$Resto = $sum % 11;

		if ($Resto == 1)
			$Dv = "P";
		else if ($Resto == 0)
			$Dv = 0;
		else
			$Dv = 11 - $Resto;

		return CNABUtil::fillString($NossoNumero, 11, "0") . $Dv;
	}

	private function retNossoNumeroOBJ($NossoNumero){
		return self::retNossoNumero($NossoNumero, $this->getCarteira());
	}
}