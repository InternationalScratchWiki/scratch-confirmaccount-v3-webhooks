<?php
class ScratchConfirmAccountWebhooksHooks {
	private static function invokeEventWebhook($event, $payload) {
		global $wgScratchAccountRequestWebhookEvents, $wgScratchAccountRequestWebhookUrls;
				
		if (!empty($wgScratchAccountRequestWebhookUrls[$event])) {			
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
			
			file_get_contents($wgScratchAccountRequestWebhookUrls[$event], false, $context);
		}
	}
	
	private static function urlForRequest($requestId) {
		return SpecialPage::getTitleFor( 'ConfirmAccounts' )->getSubpage($requestId)->getFullURL('', '', PROTO_CURRENT);
	}
	
	public static function onAccountRequestAction($accountRequest, $action, $actioningUsername, $comment) {		
		self::invokeEventWebhook('onAccountRequestAction', [
			'title' => wfMessage('scratch-confirmaccount-webhooks-action-title', wfMessage('scratch-confirmaccount-' . $action), $accountRequest->username, $actioningUsername ?? $accountRequest->username)->text(),
			'description' => wfMessage('scratch-confirmaccount-webhooks-action-description', $comment, self::urlForRequest($accountRequest->id))->text()
		]);
	}
	
	public static function onCreateAccount($accountRequest, $actioningUsername) {
		self::invokeEventWebhook('onCreateAccount', [
			'title' => wfMessage('scratch-confirmaccount-webhooks-account-created-title', $accountRequest->username)->text(),
			'description' => wfMessage('scratch-confirmaccount-webhooks-account-created-description', $accountRequest->username, $actioningUsername)->text()
		]);
	}
	
	public static function onAccountRequestSubmitted($requestId, $username, $requestNotes) {
		self::invokeEventWebhook('onAccountRequestSubmitted', [
			'title' => wfMessage('scratch-confirmaccount-webhooks-request-created-title', $username)->text(),
			'description' => wfMessage('scratch-confirmaccount-webhooks-request-created-description', $username, $requestNotes, self::urlForRequest($requestId))->text()
		]);
	}
}