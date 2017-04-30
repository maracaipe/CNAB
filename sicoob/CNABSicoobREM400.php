<?php
namespace CNAB\sicoob;

use CNAB\CNAB;
use CNAB\CNABUtil;

class CNABSicoobREM400 extends CNABSicoob {

	public function __construct	($tpBeneficiario, $cpfCnpjBeneficiario, $agencia, $verificadorAgencia, $conta, $verificadorConta, $carteira, $convenio, $beneficiario, $codRemessa, $gravacaoRemessa = ""){
		parent::__construct		($tpBeneficiario, $cpfCnpjBeneficiario, $agencia, $verificadorAgencia, $conta, $verificadorConta, $carteira, $convenio);

		$gravacaoRemessa = empty($gravacaoRemessa) ? date('dmy'): $gravacaoRemessa;

		$codCedente = 	substr(CNABUtil::onlyNumbers($convenio), 0, -1);
		$codCedenteDV = substr(CNABUtil::onlyNumbers($convenio), -1);

		$this->addField("0", 1); //Identifica��o do Registro Header: �0� (zero)
		$this->addField("1", 1); //Tipo de Opera��o: �1� (um)
		$this->addField("REMESSA", 7, ' ', STR_PAD_RIGHT); //Identifica��o por Extenso do Tipo de Opera��o: "REMESSA"
		$this->addField("1", 2, '0'); //Identifica��o do Tipo de Servi�o: �01� (um)
		$this->addField("COBRANCA", 8, ' ', STR_PAD_RIGHT); //Identifica��o por Extenso do Tipo de Servi�o: �COBRAN�A�
		$this->addField("", 7); //Complemento do Registro: Preencher com espa�os em branco
		$this->addField($this->getAgencia(), 4); //Prefixo da Cooperativa: vide planilha "Capa" deste arquivo
		$this->addField($this->getVerificadorAgencia(), 1); //D�gito Verificador do Prefixo: vide planilha "Capa" deste arquivo
		$this->addField($codCedente, 8, '0'); //C�digo do Cliente/Benefici�rio: vide planilha "Capa" deste arquivo
		$this->addField($codCedenteDV, 1); //D�gito Verificador do C�digo: vide planilha "Capa" deste arquivo
		//$this->addField($this->getConvenio(), 6); //N�mero do conv�nio l�der: Preencher com espa�os em branco
		$this->addField("", 6); //N�mero do conv�nio l�der: Preencher com espa�os em branco
		$this->addField(strtoupper($beneficiario), 30, ' ', STR_PAD_RIGHT); //Nome do Benefici�rio: vide planilha "Capa" deste arquivo
		$this->addField("756BANCOOBCED", 18, " ", STR_PAD_RIGHT); //Identifica��o do Banco: "756BANCOOBCED"
		$this->addField($gravacaoRemessa, 6); //Data da Grava��o da Remessa: formato DDMMAA
		$this->addField($codRemessa, 7, '0'); //Seq�encial da Remessa: n�mero seq�encial acrescido de 1 a cada remessa. Inicia com "0000001"
		$this->addField("", 287); //Complemento do Registro: Preencher com espa�os em branco
		$this->addField($this->sequencial++, 6, '0'); //Sequencial do Registro:�000001�
		$this->addField("\r\n", 2);
	}

	public static function retNossoNumero($NossoNumero, $agencia, $convenio){

		$NossoNumero = CNABUtil::fillString($NossoNumero, 7, "0");
		$sequencia = CNABUtil::fillString($agencia, 4, "0"). CNABUtil::fillString(str_replace("-","", $convenio),10, "0") . CNABUtil::fillString($NossoNumero, 7, "0");
		//$sequencia = CNABUtil::fillString($this->getAgencia(), 4, "0"). CNABUtil::fillString(str_replace("-", "", $this->getConta() . $this->getVerificadorConta()), 10, "0") . CNABUtil::fillString($NossoNumero, 7, "0");
		$cont=0;
		$calculoDv = '';

		for($num=0; $num <= strlen($sequencia); $num++) {
			$cont++;
			if($cont == 1){ // constante fixa Sicoob » 3197
				$constante = 3;
			}

			if($cont == 2) {
				$constante = 1;
			}

			if($cont == 3) {
				$constante = 9;
			}

			if($cont == 4) {
				$constante = 7;
				$cont = 0;
			}

			$calculoDv = $calculoDv + (substr($sequencia,$num,1) * $constante);
		}

		$Resto = $calculoDv % 11;
		//$Dv = 11 - $Resto;
		$Dv = $Resto > 1 ? 11 - $Resto : 0;

		/*
		 if ($Dv == 0)
		 	$Dv = 0;

		 if ($Dv == 1)
		 	$Dv = 0;

		 if ($Dv > 9)
		 	$Dv = 0;
		 */

		 return CNABUtil::fillString($NossoNumero, 11, "0") . $Dv;
	}

	private function retNossoNumeroOBJ($NossoNumero){
		return self::retNossoNumero($NossoNumero, $this->getAgencia(), $this->getConvenio());
	}

	public function addTitulo(CNABSicoobTituloREM400 $oTitulo, $calcNossoNumero = true){

														//SEQ	INICIO	FINAL	TAM	M�SCARA	CAMPO / DESCRI��O / CONTE�DO
		$this->addField("1", 1);							//1		001		001		001	9(01)	Identifica��o do Registro Detalhe: 1 (um)
		$this->addField($this->getTpPessoa(), 2);			//2		002		003		002	9(02)	"Tipo de Inscri��o do Benefici�rio:
																							//""01"" = CPF
																							//""02"" = CNPJ  "
		$this->addField($this->getCpfCnpj(), 14);		//3		004		017		014	9(14)	N�mero do CPF/CNPJ do Benefici�rio
		$this->addField($this->getAgencia(), 4);					//4		018		021		004	9(04)	Prefixo da Cooperativa: vide planilha "Capa" deste arquivo
		$this->addField($this->getVerificadorAgencia(), 1);		//5		022		022		001	9(01)	D�gito Verificador do Prefixo: vide planilha "Capa" deste arquivo
		$this->addField($this->getConta(), 8);						//6		023		030		008	9(08)	Conta Corrente: vide planilha "Capa" deste arquivo
		$this->addField($this->getVerificadorConta(), 1);			//7		031		031		001	X(01)	D�gito Verificador da Conta: vide planilha "Capa" deste arquivo
		//$this->addField($this->getConvenio(), 6);					//8		032		037		006	9(06)	N�mero do Conv�nio de Cobran�a do Benefici�rio: "000000"
		$this->addField("0", 6, "0");					//8		032		037		006	9(06)	N�mero do Conv�nio de Cobran�a do Benefici�rio: "000000"
		$this->addField("", 25);								//9		038		062		025	X(25)	N�mero de Controle do Participante: Preencher com espa�os em branco

		$this->addField((int)$this->getCarteira() == 1 && $calcNossoNumero ? $this->retNossoNumeroOBJ($oTitulo->getNossoNumero()): $oTitulo->getNossoNumero(), 12);
																							//10	063		074		012	9(12)	"Nosso N�mero:
																							//- Para comando 01 com emiss�o a cargo do Sicoob (vide planilha ""Capa"" deste arquivo e lista de comandos seq. 23): Preencher com zeros
																							//- Para comando 01 com emiss�o a cargo do Benefici�rio ou para os demais comandos (vide planilha ""Capa"" deste arquivo e lista de comandos seq. 23):
																							//Preencher da seguinte forma:
																							//- Posi��o 063 a 073 � N�mero seq�encial a partir de ""0000000001"", n�o sendo admitida reutiliza��o ou duplicidade.
																							//- Posi��o 074 a 074 � DV do Nosso-N�mero, calculado pelo m�dulo 11."

		$this->addField($oTitulo->getParcela(), 2, "0");					//11	075		076		002	9(02)	N�mero da Parcela: "01" se parcela �nica
		$this->addField($oTitulo->getGrupoValor(), 2, "0");							//12	077		078		002	9(02)	Grupo de Valor: "00"
		$this->addField("", 3);								//13	079		081		003	X(03)	Complemento do Registro: Preencher com espa�os em branco

		//Verificar com jo�o paulo
		$this->addField($oTitulo->getIndicativoMensagem(), 1);								//14	082		082		001	X(01)	"Indicativo de Mensagem ou Sacador/Avalista:
																					//Em branco: Poder� ser informada nas posi��es 352 a 391 (SEQ 50) qualquer mensagem para ser impressa no boleto;
																					//�A�: Dever� ser informado nas posi��es 352 a 391 (SEQ 50) o nome e CPF/CNPJ do sacador"
		$this->addField("", 3);								//15	083		085		003	X(03)	Prefixo do T�tulo: Preencher com espa�os em branco
		$this->addField($oTitulo->getVariacaoCarteira(), 3, "0");							//16	086		088		003	9(03)	Varia��o da Carteira: "000"
		$this->addField($oTitulo->getContaCaucao(), 1);								//17	089		089		001	9(01)	Conta Cau��o: "0"
		$this->addField($oTitulo->getNumeroContratoGarantia(), 5, "0");								//18	090		094		005	9(05)	"N�mero do Contrato Garantia:                                                                                                                                                                                                                                                                     Para Carteira 1 preencher ""00000"";
																							//Para Carteira 3 preencher com o  n�mero do contrato sem DV."
		$this->addField($oTitulo->getDVContrato(), 1, "0");								//19	095		095		001	X(01)	"DV do contrato:                                                                                                                                                                                                                                                                     Para Carteira 1 preencher ""0"";
																							//Para Carteira 3 preencher com o D�gito Verificador."
		$this->addField($oTitulo->getBordero(), 6);								//20	096		101		006	9(06)	Numero do border�: preencher em caso de carteira 3
		$this->addField("", 5);								//21	102		105		004	X(04)	Complemento do Registro: Preencher com espa�os em branco
		//$this->addField($oTitulo->getTipoEmissao(), 1);								//22	106		106		001	9(01)	"Tipo de Emiss�o:
																							//1 - Cooperativa
																							//2 - Cliente"
		$this->addField($this->getCarteira(), 2, "0");					//23	107		108		002	9(02)	"Carteira/Modalidade:
																							//01 = Simples Com Registro
																							//03 = Garantida Caucionada
		$this->addField($oTitulo->getComandoMovimento(), 2, "0");							//24	109		110		002	9(02)	"Comando/Movimento:
																							//01 = Registro de T�tulos
																							//02 = Solicita��o de Baixa
																							//04 = Concess�o de Abatimento
																							//05 = Cancelamento de Abatimento
																							//06 = Altera��o de Vencimento
																							//08 = Altera��o de Seu N�mero
																							//09 = Instru��o para Protestar
																							//10 = Instru��o para Sustar Protesto
																							//11 = Instru��o para Dispensar Juros
																							//12 = Altera��o de Pagador
																							//31 = Altera��o de Outros Dados
																							//34 = Baixa - Pagamento Direto ao Benefici�rio
		$this->addField($oTitulo->getSeuNumero(), 10, "0");					//25	111		120		010	X(10)	Seu N�mero/N�mero atribu�do pela Empresa
		$this->addField($oTitulo->getVencimento(), 6);						//26	121		126		006	A(06)	"Data Vencimento: formato DDMMAA
																							//Normal ""DDMMAA""
																							//A vista = ""888888""
																							//Contra Apresenta��o = ""999999"""
		$this->addField($oTitulo->getValor(), 13);								//27	127		139		013	9(11)V99	Valor do Titulo
		$this->addField("756", 3);							//28	140		142		003	9(03)	N�mero Banco: "756"
		$this->addField($this->getAgencia(), 4);					//29	143		146		004	9(04)	Prefixo da Cooperativa: vide planilha "Capa" deste arquivo
		$this->addField($this->getVerificadorAgencia(), 1);		//30	147		147		001	X(01)	D�gito Verificador do Prefixo: vide planilha "Capa" deste arquivo
		$this->addField($oTitulo->getEspecieTitulo(), 2, "0");							//31	148		149		002	9(02)	"Esp�cie do T�tulo :
																							//01 = Duplicata Mercantil
																							//02 = Nota Promiss�ria
																							//03 = Nota de Seguro
																							//05 = Recibo
																							//06 = Duplicata Rural
																							//08 = Letra de C�mbio
																							//09 = Warrant
																							//10 = Cheque
																							//12 = Duplicata de Servi�o
																							//13 = Nota de D�bito
																							//14 = Triplicata Mercantil
																							//15 = Triplicata de Servi�o
																							//18 = Fatura
																							//20 = Ap�lice de Seguro
																							//21 = Mensalidade Escolar
																							//22 = Parcela de Cons�rcio
																							//99 = Outros"
		$this->addField($oTitulo->getAceite(), 1);							//32	150		150		001	X(01)	"Aceite do T�tulo:
																							//""0"" = Sem aceite
																							//""1"" = Com aceite"
		$this->addField($oTitulo->getEmissao(), 6);							//33	151		156		006	9(06)	Data de Emiss�o do T�tulo: formato DDMMAA
		$this->addField($oTitulo->getPriInstrucaoCodificada(), 2, "0");								//34	157		158		002	9(02)	"Primeira instru��o codificada:
																							//Regras de impress�o de mensagens nos boletos:
																							//* Primeira instru��o (SEQ 34) = 00 e segunda (SEQ 35) = 00, n�o imprime nada.
																							//* Primeira instru��o (SEQ 34) = 01 e segunda (SEQ 35) = 01, desconsidera-se as instru��es CNAB e imprime as mensagens relatadas no trailler do arquivo.
																							//* Primeira e segunda instru��o diferente das situa��es acima, imprimimos o conte�do CNAB:
																							//  00 = AUSENCIA DE INSTRUCOES
																							//  01 = COBRAR JUROS
																							//  03 = PROTESTAR 3 DIAS UTEIS APOS VENCIMENTO
																							//  04 = PROTESTAR 4 DIAS UTEIS APOS VENCIMENTO
																							//  05 = PROTESTAR 5 DIAS UTEIS APOS VENCIMENTO
																							//  07 = NAO PROTESTAR
																							//  10 = PROTESTAR 10 DIAS UTEIS APOS VENCIMENTO
																							//  15 = PROTESTAR 15 DIAS UTEIS APOS VENCIMENTO
																							//  20 = PROTESTAR 20 DIAS UTEIS APOS VENCIMENTO
																							//  22 = CONCEDER DESCONTO SO ATE DATA ESTIPULADA
																							//  42 = DEVOLVER APOS 15 DIAS VENCIDO
																							//  43 = DEVOLVER APOS 30 DIAS VENCIDO"
		$this->addField($oTitulo->getSegInstrucaoCodificada(), 2, "0");								//35	159		160		002	9(02)	Segunda instru��o: vide SEQ 33
		$this->addField($oTitulo->getMora(), 6);								//36	161		166		006	9(02)V9999	"Taxa de mora m�s
																								//Ex: 022000 = 2,20%"
		$this->addField($oTitulo->getMulta(), 6);								//37	167		172		006	9(02)V9999	"Taxa de multa
																								//Ex: 022000 = 2,20%"
		$this->addField("", 1);
		//$this->addField($oTitulo->getDistribuicao(), 1);								//38	173		173		001	9(01)	"Tipo Distribui��o
																							//1 � Cooperativa
																							//2 - Cliente"
		$this->addField($oTitulo->getDtPrimeiroDesconto(), 6);								//39	174		179		006	9(06)	"Data primeiro desconto:
																							//Informar a data limite a ser observada pelo cliente para o pagamento do t�tulo com Desconto no formato DDMMAA.
																							//Preencher com zeros quando n�o for concedido nenhum desconto.
																							//Obs: A data limite n�o poder� ser superior a data de vencimento do t�tulo."
		$this->addField($oTitulo->getPrimeiroDesconto(), 13);								//40	180		192		013	9(11)V99	"Valor primeiro desconto:
																							//Informar o valor do desconto, com duas casa decimais.
																							//Preencher com zeros quando n�o for concedido nenhum desconto."
		$this->addField($oTitulo->getMoeda(), 13, "0");								//41	193		205		013	9(13)	"193-193 � C�digo da moeda
																							//194-205 � Valor IOF / Quantidade Monet�ria: ""000000000000""
																							//Se o c�digo da moeda for REAL, o valor restante representa o IOF. Se o c�digo da moeda for diferente de REAL, o valor restante ser� a quantidade monet�ria.    "
		$this->addField($oTitulo->getAbatimento(), 13);								//42	206		218		013	9(11)V99	Valor Abatimento
		$this->addField($oTitulo->getTpPagador(), 2);								//43	219		220		002	9(01)	"Tipo de Inscri��o do Pagador:
																							//""01"" = CPF
																							//""02"" = CNPJ "
		$this->addField($oTitulo->getPagadorCpfCnpj(), 14);								//44	221		234		014	9(14)	N�mero do CNPJ ou CPF do Pagador
		$this->addField($oTitulo->getPagador(), 40, ' ', STR_PAD_RIGHT);								//45	235		274		040	A(40)	Nome do Pagador
		$this->addField($oTitulo->getPagadorEndereco(), 37, ' ', STR_PAD_RIGHT);								//46	275		311		037	A(37)	Endere�o do Pagador
		$this->addField($oTitulo->getPagadorBairro(), 15, ' ', STR_PAD_RIGHT);								//47	312		326		015	X(15)	Bairro do Pagador
		$this->addField($oTitulo->getPagadorCep(), 8, ' ', STR_PAD_RIGHT);								//48	327		334		008	9(08)	CEP do Pagador
		$this->addField($oTitulo->getPagadorCidade(), 15, ' ', STR_PAD_RIGHT);								//49	335		349		015	A(15)	Cidade do Pagador
		$this->addField($oTitulo->getPagadorUF(), 2);								//50	350		351		002	A(02)	UF do Pagador
		$this->addField($oTitulo->getMensagem(), 40, ' ', STR_PAD_RIGHT);								//51	352		391		040	X(40)	"Observa��es/Mensagem ou Sacador/Avalista:
																							//Quando o SEQ 14 � Indicativo de Mensagem ou Sacador/Avalista - for preenchido com espa�os em branco, as informa��es constantes desse campo ser�o impressas no campo �texto de responsabilidade da Empresa�, no Recibo do Sacado e na Ficha de Compensa��o do boleto de cobran�a.
																							//Quando o SEQ 14 � Indicativo de Mensagem ou Sacador/Avalista - for preenchido com �A� , este campo dever� ser preenchido com o nome/raz�o social do Sacador/Avalista"
		$this->addField($oTitulo->getDiasProtesto(), 2);								//52	392		393		002	X(02)	"N�mero de Dias Para Protesto:
																							//Quantidade dias para envio protesto. Se = ""0"", utilizar dias protesto padr�o do cliente cadastrado na cooperativa. "
		$this->addField($oTitulo->getComplemento(), 1);								//53	394		395		001	X(01)	Complemento do Registro: Preencher com espa�os em branco
		$this->addField($this->sequencial++, 6, "0");								//54	395		400		006	9(06)	Seq�encial do Registro: Incrementado em 1 a cada registro
		$this->addField("\r\n", 2);
	}

	public function getFile($msgRespBeneficiario1 = "", $msgRespBeneficiario2 = "", $msgRespBeneficiario3 = "", $msgRespBeneficiario4 = "", $msgRespBeneficiario5 = ""){
		$this->addField("9", 1);
		$this->addField("", 193);
		$this->addField($msgRespBeneficiario1, 40);
		$this->addField($msgRespBeneficiario2, 40);
		$this->addField($msgRespBeneficiario3, 40);
		$this->addField($msgRespBeneficiario4, 40);
		$this->addField($msgRespBeneficiario5, 40);
		$this->addField($this->sequencial++, 6, "0");
		$this->addField("\r\n", 2);
/*
		1	001	001	001	9(01)	Identifica��o Registro Trailler: "9"
		2	002	194	193	X(193)	Complemento do Registro: Preencher com espa�os em branco
		3	195	234	040	X(40)	"Mensagem responsabilidade Benefici�rio:
								Quando o SEQ 34 = ""01"" e o SEQ 35 = ""01"", preencher com mensagens/intru��es de responsabilidade do Benefici�rio
								Quando o SEQ 34 e SEQ 35 forem preenchidos com valores diferentes destes, preencher com espa�os em branco"
		4	235	274	040	X(40)	"Mensagem responsabilidade Benefici�rio:
								Quando o SEQ 34 = ""01"" e o SEQ 35 = ""01"", preencher com mensagens/intru��es de responsabilidade do Benefici�rio
								Quando o SEQ 34 e SEQ 35 forem preenchidos com valores diferentes destes, preencher com espa�os em branco"
		5	275	314	040	X(40)	"Mensagem responsabilidade Benefici�rio:
								Quando o SEQ 34 = ""01"" e o SEQ 35 = ""01"", preencher com mensagens/intru��es de responsabilidade do Benefici�rio
								Quando o SEQ 34 e SEQ 35 forem preenchidos com valores diferentes destes, preencher com espa�os em branco"
		6	315	354	040	X(40)	"Mensagem responsabilidade Benefici�rio:
								Quando o SEQ 34 = ""01"" e o SEQ 35 = ""01"", preencher com mensagens/intru��es de responsabilidade do Benefici�rio
								Quando o SEQ 34 e SEQ 35 forem preenchidos com valores diferentes destes, preencher com espa�os em branco"
		7	355	394	040	X(40)	"Mensagem responsabilidade Benefici�rio:
								Quando o SEQ 34 = ""01"" e o SEQ 35 = ""01"", preencher com mensagens/intru��es de responsabilidade do Benefici�rio
								Quando o SEQ 34 e SEQ 35 forem preenchidos com valores diferentes destes, preencher com espa�os em branco"
		8	395	400	006	9(06)	Seq�encial do Registro: Incrementado em 1 a cada registro
*/
		return parent::getFile();
	}
}