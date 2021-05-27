<?php
class ScratchConfirmAccountWebhooksHooks {
	private static function invokeEventWebhook($event, $payload) {
		global $wgScratchAccountRequestWebhookEvents, $wgScratchAccountRequestWebhookUrl;
				
		if (in_array($event, $wgScratchAccountRequestWebhookEvents)) {
			$body = json_encode(
				[
					'embeds' => [
						$payload
					]
				]
			);
			
			$opts = ['http' => [
				'method' => 'POST',
				'header' => [
					'Content-Type: application/json',
					'Content-Length: ' . strlen($body)
				],
				'content' => $body
			]];
			
			$context = stream_context_create($opts);
			
			file_get_contents($wgScratchAccountRequestWebhookUrl, false, $context);
		}
	}
	
	public static function onAccountRequestAction($accountRequest, $action, $actioningUsername, $comment) {
		self::invokeEventWebhook('onAccountRequestAction', [
			'title' => 'Account request action: ' . wfMessage('scratch-confirmaccount-' . $action),
			'author' => [
				'name' => $actioningUsername
			],
			'description' => $comment . "\n\n" . '[View the request](https://jacobs-raspberrypi-2.local/wiki/Special:ConfirmAccounts/' . $accountRequest->id . ')'
		]);
	}
	
	public static function onCreateAccount($accountRequest, $actioningUsername) {
		self::invokeEventWebhook('onCreateAccount', [
			'title' => 'Account created',
			'description' => 'The account ' . $accountRequest->username . ' has been created by ' . $actioningUsername
		]);
	}
	
	public static function onAccountRequestSubmitted($requestId, $username, $requestNotes) {
		self::invokeEventWebhook('onAccountRequestSubmitted', [
			'title' => 'New account request',
			'description' => 'The user ' . $username . ' has submitted a new account request with the following request notes:' . "\n" . $requestNotes . "\n\n" . '[View the request](https://jacobs-raspberrypi-2.local/wiki/Special:ConfirmAccounts/' . $requestId . ')'
		]);
	}
}