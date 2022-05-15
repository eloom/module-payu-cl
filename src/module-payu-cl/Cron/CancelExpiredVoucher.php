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

namespace Eloom\PayUCl\Cron;

use Eloom\Payment\Api\Data\OrderPaymentInterface;
use Eloom\PayU\Api\Data\OrderPaymentPayUInterface;
use Eloom\PayUCl\Gateway\Config\Multicaja\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Payment\Repository;
use Psr\Log\LoggerInterface;

class CancelExpiredVoucher {

	private $paymentRepository;

	private $searchCriteriaBuilder;

	private $logger;

	private $config;

	public function __construct(LoggerInterface $logger,
	                            Repository $paymentRepository,
	                            SearchCriteriaBuilder $searchCriteriaBuilder,
	                            Config $config) {
		$this->logger = $logger;
		$this->paymentRepository = $paymentRepository;
		$this->searchCriteriaBuilder = $searchCriteriaBuilder;
		$this->config = $config;
	}

	public function execute() {
		if ($this->config->isCancelable()) {
			$searchCriteria = $this->searchCriteriaBuilder
				->addFilter('method', \Eloom\PayUCl\Model\Ui\Multicaja\ConfigProvider::CODE, 'eq')
				->addFilter(OrderPaymentPayUInterface::TRANSACTION_STATE, \Eloom\PayU\Gateway\PayU\Enumeration\PayUTransactionState::PENDING()->key(), 'eq')
				->addFilter(OrderPaymentInterface::CANCEL_AT, date('Y-m-d H:i:s', strtotime('now')), 'lt')
				->create();

			$paymentList = $this->paymentRepository->getList($searchCriteria)->getItems();
			if (count($paymentList)) {
				$processor = ObjectManager::getInstance()->get(\Eloom\PayU\Model\PaymentManagement\Processor::class);

				foreach ($paymentList as $payment) {
					try {
						$this->logger->info(sprintf("%s - Canceling voucher - Order %s", __METHOD__, $payment->getOrder()->getIncrementId()));
						$processor->cancelPayment($payment);
					} catch (\Exception $e) {
						$this->logger->critical(sprintf("%s - Exception: %s", __METHOD__, $e->getMessage()));
						//$this->logger->critical(sprintf("%s - Exception: %s", __METHOD__, $e->getTraceAsString()));
					}
				}
			}
		}
	}
}