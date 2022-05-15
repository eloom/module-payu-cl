<?php
/**
* 
* PayU Chile para Magento 2
* 
* @category     elOOm
* @package      Modulo PayUCl
* @copyright    Copyright (c) 2022 Ã©lOOm (https://eloom.tech)
* @version      2.0.0
* @license      https://opensource.org/licenses/OSL-3.0
* @license      https://opensource.org/licenses/AFL-3.0
*
*/
declare(strict_types=1);

namespace Eloom\PayUCl\Model\Ui\Multicaja;

use Eloom\PayUCl\Gateway\Config\Multicaja\Config as MulticajaConfig;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider implements ConfigProviderInterface {

	const CODE = 'eloom_payments_payu_multicaja';

	protected $assetRepo;

	private $multicajaConfig;

	protected $escaper;

	protected $storeManager;

	private static $allowedCurrencies = ['CLP', 'USD'];

	public function __construct(Repository              $assetRepo,
	                            Escaper                 $escaper,
	                            MulticajaConfig         $multicajaConfig,
	                            StoreManagerInterface $storeManager) {
		$this->assetRepo = $assetRepo;
		$this->escaper = $escaper;
		$this->multicajaConfig = $multicajaConfig;
		$this->storeManager = $storeManager;
	}

	public function getConfig() {
		$store = $this->storeManager->getStore();
		$payment = [];
		$storeId = $store->getStoreId();
		$isActive = $this->multicajaConfig->isActive($storeId);
		if ($isActive) {
			$currency = $store->getCurrentCurrencyCode();
			if (!in_array($currency, self::$allowedCurrencies)) {
				return ['payment' => [
					self::CODE => [
						'message' =>  sprintf("Currency %s not supported.", $currency)
					]
				]];
			}

			$payment = [
				self::CODE => [
					'isActive' => $isActive,
					'instructions' => $this->getInstructions($storeId),
					'url' => [
						'logo' => $this->assetRepo->getUrl('Eloom_PayUCl::images/klap.svg')
					]
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