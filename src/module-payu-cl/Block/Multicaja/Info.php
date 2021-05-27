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

namespace Eloom\PayUCl\Block\Multicaja;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

class Info extends \Eloom\PayU\Block\Info {

	public function getPaymentLink() {
		return $this->getInfo()->getAdditionalInformation('paymentLink');
	}

	public function getPdfLink() {
		return $this->getInfo()->getAdditionalInformation('pdfLink');
	}

	public function getBarCode() {
		return null;
	}
}
