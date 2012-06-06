<?php

namespace BSP\AdyenBundle\Service;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use BSP\AdyenBundle\Event\NotificationEvent;

class NotificationService
{
	/**
	 * @var @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
	 */
	protected $dispatcher;

	/**
	 * @var string
	 */
	protected $logDirectory;

	public function __construct( EventDispatcherInterface $dispatcher, $logDirectory )
	{
		$this->dispatcher = $dispatcher;
		$this->logDirectory = $logDirectory;
	}

	public function sendNotification($request)
	{
		if(is_array($request->notification->notificationItems->NotificationRequestItem))
		{
			foreach($request->notification->notificationItems->NotificationRequestItem as $item)
			{
				$this->process($item);
			}
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

		$notEvent = new NotificationEvent( $item );
		$this->dispatcher->dispatch('adyen.notification'.strtolower($item->eventCode), $notEvent);

		file_put_contents($this->logDirectory . '/adyen.log', $output, FILE_APPEND);
	}
}
