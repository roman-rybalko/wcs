<?php

namespace WebConstructionSet\Advertising\CampaignStrings;

# The Google Ads APIs PHP Client Library
# https://github.com/googleads/googleads-php-lib
# git clone https://github.com/googleads/googleads-php-lib.git

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . dirname(__FILE__) . '/googleads-php-lib/src');  // надо для гуглового кода - он подгружает свои модули по относительным путям
class GoogleConfig {
	const ADWORDS_VERSION = 'v201506';
}
require_once 'Google/Api/Ads/AdWords/Lib/AdWordsUser.php';
require_once 'Google/Api/Ads/AdWords/Lib/AdWordsConstants.php';
require_once 'Google/Api/Ads/AdWords/' . GoogleConfig::ADWORDS_VERSION . '/AdGroupCriterionService.php';

class Google implements \WebConstructionSet\Advertising\CampaignStrings {
	private $authData, $devToken, $compName, $cliCustId;

	/**
	 * Инициализация
	 * @param array $authData [client_id => ..., client_secret => ..., access_token|refresh_token => ...]
	 * @param string $developerToken Пароль на доступ к API (см. в настройках MCC-аккаунта)
	 * @param string $companyName Название организации
	 */
	public function __construct($authData, $developerToken, $companyName, $clientCustomerId) {
		$this->authData = $authData;
		$this->devToken = $developerToken;
		$this->compName = $companyName;
		$this->cliCustId = $clientCustomerId;
	}

	public function get() {
		$user = new \AdWordsUser(null, $this->devToken, $this->compName, $this->cliCustId, null, $this->authData);
		$adGroupCriterionService = $user->GetService('AdGroupCriterionService', GoogleConfig::ADWORDS_VERSION);
		$selector = new \Selector();
		$selector->fields = ['KeywordText'];
		$selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);
		$strings = [];
		do {
			$page = $adGroupCriterionService->get($selector);
			if (isset($page->entries))
				foreach ($page->entries as $adGroupCriterion)
					$strings[] = $adGroupCriterion->criterion->text;
			$selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
		} while ($page->totalNumEntries > $selector->paging->startIndex);
		return $strings;
	}
}
