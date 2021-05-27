<?php
/**
* 
* PayU Chile para Magento 2
* 
* @category     Eloom
* @package      Modulo PayUCl
* @copyright    Copyright (c) 2021 eloom (https://www.eloom.dev)
* @version      1.0.0
* @license      https://opensource.org/licenses/OSL-3.0
* @license      https://opensource.org/licenses/AFL-3.0
*
*/
declare(strict_types=1);

namespace Eloom\PayUCl\Model\Ui\Multicaja;

use Eloom\PayUCl\Gateway\Config\Multicaja\Config as MulticajaConfig;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Session\SessionManagerInterface;

class ConfigProvider implements ConfigProviderInterface {

	const CODE = 'eloom_payments_payu_multicaja';

	private $multicajaConfig;

	private $session;

	protected $escaper;

	public function __construct(SessionManagerInterface $session,
	                            Escaper $escaper,
	                            MulticajaConfig $multicajaConfig) {
		$this->session = $session;
		$this->escaper = $escaper;
		$this->multicajaConfig = $multicajaConfig;
	}

	public function getConfig() {
		$storeId = $this->session->getStoreId();

		$payment = [];
		$isActive = $this->multicajaConfig->isActive($storeId);
		if ($isActive) {
			$payment = [
				self::CODE => [
					'isActive' => $isActive,
					'instructions' => $this->getInstructions($storeId)
				]
			];
		}

		return [
			'payment' => $payment
		];
	}

	protected function getInstructions($storeId): string {
		return $this->escaper->escapeHtml($this->multicajaConfig->getInstructions($storeId));
	}
}