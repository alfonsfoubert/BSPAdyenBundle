<?php

namespace BSP\AdyenBundle\Service;

use BSP\AdyenBundle\Service\AdyenService;
use BSP\AdyenBundle\Event\NotificationEvent;

class NotificationService
{
	/**
     * @var \BSP\AdyenBundle\Service\AdyenService
     */
	protected $adyen;

	/**
	 * @var string
	 */
	protected $logDirectory;

	public function __construct( AdyenService $adyen, $logDirectory )
	{
		$this->adyen = $adyen;
		$this->logDirectory = $logDirectory;
	}

	public function sendNotification($request)
	{
		if(is_array($request->notification->notificationItems->NotificationRequestItem))
		{
			foreach($request->notification->notificationItems->NotificationRequestItem as $item)
				$this->process($item);
		}
		else
		{
			$this->process($request->notification->notificationItems->NotificationRequestItem);
		}

		return array("notificationResponse" => "[accepted]");
	}

	protected function process( $item )
	{
		$output = print_r($item, true) . PHP_EOL;
		/*
		$notEvent = new NotificationEvent( 
				$item->'live',
				$item->'eventCode',
				$item->'pspReference',
				$item->'originalReference',
				$item->'merchantReference',
				$item->'merchantAccountCode',
				$item->'eventDate',
				$item->'success',
				$item->'paymentMethod',
				$item->'operations',
				$item->'reason',
				$item->'amount',
				$item );
		
		$this->dispatcher->dispatch('adyen.notification', $notEvent);
		*/
		file_put_contents($this->logDirectory . '/adyen.log', $output, FILE_APPEND);
	}
}