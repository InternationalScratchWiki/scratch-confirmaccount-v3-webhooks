<?php

use ScratchConfirmAccount\Hook\AccountRequestActionHook;
use ScratchConfirmAccount\Hook\AccountRequestSubmittedHook;
use ScratchConfirmAccount\Hook\RequestedAccountCreatedHook;

class ScratchConfirmAccountWebhooksHooks implements AccountRequestActionHook, AccountRequestSubmittedHook, RequestedAccountCreatedHook {
	private static function invokeEventWebhook($event, $payload) {
		global $wgScratchAccountRequestWebhookUrls;

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
		return SpecialPage::getTitleFor('ConfirmAccounts')->getSubpage((string)$requestId)->getFullURL('', '', PROTO_CURRENT);
	}

	public function	onAccountRequestAction($accountRequest, string $action, ?string $actorUsername, string $comment) {
		self::invokeEventWebhook('onAccountRequestAction', [
			'title' => wfMessage('scratch-confirmaccount-webhooks-action-title', wfMessage('scratch-confirmaccount-' . $action), $accountRequest->username, $actorUsername ?? $accountRequest->username)->text(),
			'description' => wfMessage('scratch-confirmaccount-webhooks-action-description', $comment, self::urlForRequest($accountRequest->id))->text()
		]);
	}

	public function onRequestedAccountCreated($accountRequest, string $actorUsername) {
		self::invokeEventWebhook('onCreateAccount', [
			'title' => wfMessage('scratch-confirmaccount-webhooks-account-created-title', $accountRequest->username)->text(),
			'description' => wfMessage('scratch-confirmaccount-webhooks-account-created-description', $accountRequest->username, $actorUsername)->text()
		]);
	}

	public function onAccountRequestSubmitted($requestId, string $username, string $requestNotes) {
		self::invokeEventWebhook('onAccountRequestSubmitted', [
			'title' => wfMessage('scratch-confirmaccount-webhooks-request-created-title', $username)->text(),
			'description' => wfMessage('scratch-confirmaccount-webhooks-request-created-description', $username, $requestNotes, self::urlForRequest($requestId))->text()
		]);
	}
}
