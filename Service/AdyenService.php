<?php

namespace BSP\AdyenBundle\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

use BSP\AdyenBundle\Event\ChargeEvent;

class AdyenService
{
	public $platform;
	public $merchantAccount;
	public $skin;
	public $sharedSecret;
	public $defaultCurrency;
	public $payment_methods = array();
	public $webservice = array();
	protected $updateChargeAmount = 2; // 2 cent for authorisation
	protected $error;

	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
	 */
	protected $dispatcher;

	public function __construct($platform, $merchantAccount, $skin, $sharedSecret, $currency, array $payment_methods, array $webservice)
	{
		$this->platform = $platform;
		$this->merchantAccount = $merchantAccount;
		$this->skin = $skin;
		$this->sharedSecret = $sharedSecret;
		$this->defaultCurrency = $currency;
		$this->payment_methods = $payment_methods;
		$this->webservice = $webservice;
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
	 * @return void
	 */
	public function setEventDispatcher(EventDispatcherInterface $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @param $account
	 * @param $email
	 * @param  $returnUrl
	 * @return Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function setup( $account, $email, $amount, $currency, $returnUrl )
	{
		$transaction = $this->generateId();
		$today = new \DateTime();
		$parameters = array(
			'merchantReference' => $transaction,
			'paymentAmount'     => $amount,
			'currencyCode'      => $currency,
			'shipBeforeDate'    => $today->format('Y-m-d'),
			'skinCode'          => $this->skin,
			'merchantAccount'   => $this->merchantAccount,
			'sessionValidity'   => $today->modify('+3 hours')->format(DATE_ATOM),
			'shopperEmail'      => $email,
			'shopperReference'  => $account,
			'recurringContract' => 'RECURRING',
			'resURL'            => $returnUrl,
			'allowedMethods'    => implode( ',', $this->payment_methods ),
			'skipSelection'		=> 'true'
		);

		$parameters['merchantSig'] = $this->signature($parameters);

		return new RedirectResponse('https://' . $this->platform . '.adyen.com/hpp/select.shtml?' . http_build_query($parameters));
	}

	/**
	 * @param Account $account
	 * @param  $returnUrl
	 * @return Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function update(Account $account, $returnUrl)
	{
		$paymentAmount = $this->updateChargeAmount;

		$transaction = new $this->entities['transaction'];
		$transaction->setAccount($account);
		$transaction->setType('update');
		$transaction->setAmount($paymentAmount);
		$transaction->setCurrency($account->getPlan()->getCurrency());

		$this->em->persist($transaction);
		$this->em->flush();

		$today = new \DateTime();
		$parameters = array(
			'merchantReference' => 'Update ' . $transaction->getId(),
			'paymentAmount'     => $paymentAmount,
			'currencyCode'      => $account->getPlan()->getCurrency(),
			'shipBeforeDate'    => $today->format('Y-m-d'),
			'skinCode'          => $this->skin,
			'merchantAccount'   => $this->merchantAccount,
			'sessionValidity'   => $today->modify('+3 hours')->format(DATE_ATOM),
			'shopperEmail'      => $account->getEmail(),
			'shopperReference'  => $account->getId(),
			'recurringContract' => 'RECURRING',
			'resURL'            => $returnUrl,
			'allowedMethods'    => implode( ',', $this->payment_methods ),
			'skipSelection'		=> 'true'
		);

		$parameters['merchantSig'] = $this->signature($parameters);

		return new RedirectResponse('https://' . $this->platform . '.adyen.com/hpp/select.shtml?' . http_build_query($parameters));
	}

	protected function getSoapClient( $type )
	{
		ini_set("soap.wsdl_cache_enabled", "0");

		return new \SoapClient(__DIR__.'/../Resources/wsdl/' . $this->platform . '/' . $type . '.wsdl', array(
			'login'         => $this->webservice['username'],
			'password'      => $this->webservice['password'],
			'soap_version'  => SOAP_1_1,
			'style'         => SOAP_DOCUMENT,
			'encoding'      => SOAP_LITERAL,
			'location'      => 'https://pal-' . $this->platform . '.adyen.com/pal/servlet/soap/' . $type,
			'trace' => 1
		));
	}

	protected function modification($type, array $modificationRequest)
	{
		$this->error = null;

		$client = $this->getSoapClient('Payment');
		try
		{
			return $client->__soapCall($type, array(
				$type => array(
					'modificationRequest' => $modificationRequest
				)
			));
		}
		catch(\SoapFault $exception)
		{
			$this->error = $exception->getMessage();

			return false;
		}
	}

	public function cancel(Transaction $transaction)
	{
		$this->error = null;

		if($transaction->isCancelled())
			return true;

		$result = $this->modification('cancel', array(
			'merchantAccount' => $this->merchantAccount,
			'originalReference' => $transaction->getReference()
		));

		if($result->cancelResult && $result->cancelResult->response == '[cancel-received]')
		{
			$transaction->isCancelled(true);
			$this->em->persist($transaction);

			return true;
		}

		return false;
	}

	public function disable(Account $account, $recurringReference = null)
	{
		$this->error = null;

		$client = $this->getSoapClient('Recurring');
		try
		{
			$result = $client->disable(array(
				'request' => array(
					'merchantAccount'           => $this->merchantAccount,
					'shopperReference'          => $account->getId(),
					'recurringDetailReference'  => $recurringReference
				)
			));

			if($result->result && ($result->result->response == '[detail-successfully-disabled]' || $result->result->response == '[all-details-successfully-disabled]'))
			{
				if($recurringReference === null || $account->getRecurringReference() == $recurringReference)
				{
					$account->setRecurringReference(null);
					$account->hasRecurringSetup(false);
					$account->setCardHolder(null);
					$account->setCardNumber(null);
					$account->setCardExpiryMonth(null);
					$account->setCardExpiryYear(null);

					$this->em->persist($account);
					$this->em->flush();
				}

				return true;
			}
			else
			{
				$this->error = print_r($result, true);

				return false;
			}
		}
		catch(\SoapFault $exception)
		{
			$this->error = $exception->getMessage();

			return false;
		}
	}

	public function charge( $account, $email, $amount, $currency = null )
	{
		$this->error = null;
		if ($currency === null) $currency = $this->defaultCurrency;
		
		$client = $this->getSoapClient('Payment');
		try
		{
			$contracts = $this->getContracts($account);
			if ($contracts === false) return false;
			if (count($contracts) == 0)
			{
				$this->error = 'This account has no contracts';
				return false;
			}
			$contract = array_shift( $contracts );
			
			/**
			 * Generate the unique transaction ID
			 */
			$transaction = $this->generateId();
			
			/**
			 * Charge it
			 */
			$result = $client->authorise(array(
				'paymentRequest' => array(
					'selectedRecurringDetailReference' => $contract['recurringDetailReference'],
					'recurring' => array(
						'contract' => 'RECURRING'
					),
					"amount" => array(
						"value" => $amount,
						"currency" => $currency
					),
					'merchantAccount' => $this->merchantAccount,
					'reference' => $transaction,
					'shopperEmail' => $email,
					'shopperReference' => $account,
					'shopperInteraction' => 'ContAuth',
				)
			));

			/**
			 * Notify the charge event
			 */
			$chargeEvent = new ChargeEvent(
				$transaction,
				$account,
				$email,
				$amount,
				$currency,
				$result->paymentResult->resultCode == 'Authorised'
			);
			$this->dispatcher->dispatch('adyen.charge', $chargeEvent);
			
			return true;
		}
		catch(\SoapFault $exception)
		{
			$this->error = $exception->getMessage();
			return false;
		}
	}

	public function getContracts( $account )
	{
		$this->error = null;
		
		$client = $this->getSoapClient('Recurring');
		try
		{
			$result = $client->listRecurringDetails(array(
				'request' => array(
					'merchantAccount'   => $this->merchantAccount,
					'shopperReference'  => $account,
					'recurring' => array(
						'contract' => 'RECURRING'
					)
				)
			));
			
			$array = $this->toArray($result);

			/**
			 * Fix the array when only one contract is found
			 */
			if(isset($array['result']['details']['RecurringDetail']['recurringDetailReference']))
				$array['result']['details']['RecurringDetail'] = array($array['result']['details']['RecurringDetail']);

			$contracts = array();
			if(isset($array['result']['details']['RecurringDetail']))
			{
				foreach($array['result']['details']['RecurringDetail'] AS $key => $details)
				{
					$date = new \DateTime($details['creationDate']);
					$contracts[$date->format('U')] = $details;
				}

				krsort($contracts);
			}

			return $contracts;
		}
		catch(\SoapFault $exception)
		{
			$this->error = $exception->getMessage();

			return false;
		}
	}

	public function processNotification(array $notification)
	{
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @return bool
	 */
	public function verifyAndProcessNotification(Request $request)
	{
		if($request->query->has('merchantReference') && $request->query->has('skinCode')
		&& $request->query->has('shopperLocale') && $request->query->has('paymentMethod')
		&& $request->query->has('authResult') && $request->query->has('pspReference') && $request->query->has('merchantSig'))
		{
			$parameters = array(
				'merchantReference' => $request->query->get('merchantReference'),
				'skinCode'          => $request->query->get('skinCode'),
				'shopperLocale'     => $request->query->get('shopperLocale'),
				'paymentMethod'     => $request->query->get('paymentMethod'),
				'authResult'        => $request->query->get('authResult'),
				'pspReference'      => $request->query->get('pspReference')
			);
			$expectedSignature = $this->signature($parameters);

			if($request->query->get('merchantSig') == $expectedSignature)
				return $this->processNotification($parameters);
		}

		return false;
	}

	public function getError()
	{
		return $this->error;
	}

	protected function generateId()
	{
		/**
		 * @todo: Make the ID generation configurable
		 */
		return uniqid();
	}
	
	protected function signature(array $parameters)
	{
		$hmac = array();

		foreach(array('authResult', 'pspReference',
					  'paymentAmount', 'currencyCode', 'shipBeforeDate', 'merchantReference', 'skinCode', 'merchantAccount',
		              'sessionValidity', 'shopperEmail', 'shopperReference', 'recurringContract', 'allowedMethods', 'blockedMethods',
		              'shopperStatement', 'merchantReturnData', 'billingAddressType', 'deliveryAddressType', 'offset') AS $parameter)
		{
			if(isset($parameters[$parameter]))
				$hmac[] = $parameters[$parameter];
		}

		return base64_encode(hash_hmac('sha1', implode($hmac), $this->sharedSecret, true));
	}

	protected function toArray($d)
	{
		if (is_object($d)) {
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars($d);
		}

		if (is_array($d)) {
			/*
			* Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return array_map(array($this, 'toArray'), $d);
		}
		else {
			// Return array
			return $d;
		}
	}
}