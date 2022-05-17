<?php
/**
* 
* PayU Chile para Magento 2
* 
* @category     elOOm
* @package      Modulo PayUCl
* @copyright    Copyright (c) 2022 elOOm (https://eloom.tech)
* @version      2.0.0
* @license      https://opensource.org/licenses/OSL-3.0
* @license      https://opensource.org/licenses/AFL-3.0
*
*/
declare(strict_types=1);

namespace Eloom\PayUCl\Gateway\Request\Payment;

use Eloom\PayU\Gateway\PayU\Enumeration\PaymentMethod;
use Eloom\PayU\Gateway\Request\Payment\AuthorizeDataBuilder;
use Eloom\PayUCl\Gateway\Config\Multicaja\Config;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class MulticajaDataBuilder implements BuilderInterface {
	
	const COOKIE = 'cookie';
	
	const USER_AGENT = 'userAgent';
	
	const PAYMENT_METHOD = 'paymentMethod';
	
	const EXPIRATION_DATE = 'expirationDate';
	
	private $config;
	
	protected $urlBuilder;
	
	private $cookieManager;
	
	private $httpHeader;
	
	public function __construct(Config $config,
	                            CookieManagerInterface $cookieManager,
	                            Header $httpHeader,
	                            UrlInterface $urlBuilder) {
		$this->config = $config;
		$this->cookieManager = $cookieManager;
		$this->httpHeader = $httpHeader;
		$this->urlBuilder = $urlBuilder;
	}
	
	public function build(array $buildSubject) {
		$paymentDataObject = SubjectReader::readPayment($buildSubject);
		$payment = $paymentDataObject->getPayment();
		$storeId = $payment->getOrder()->getStoreId();
		
		$expiration = new \DateTime('now +' . $this->config->getExpiration($storeId) . ' day');
		
		return [AuthorizeDataBuilder::TRANSACTION => [
			self::PAYMENT_METHOD => PaymentMethod::memberByKey('multicaja')->getCode(),
			self::COOKIE => $this->cookieManager->getCookie('PHPSESSID'),
			self::USER_AGENT => $this->httpHeader->getHttpUserAgent(),
			self::EXPIRATION_DATE => $expiration->format('Y-m-d\TH:i:s'),
			'extraParameters' => [
				'NETWORK_CALLBACK_URL' => $this->urlBuilder->getUrl('/', ['_secure' => true]),
				'INSTALLMENTS_NUMBER' => 1
			]
		]];
	}
}