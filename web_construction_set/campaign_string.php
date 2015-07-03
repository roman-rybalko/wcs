<?php

namespace WebConstructionSet;

/**
 * Получить строку рекламной кампании, по которой к нам пришли (из GET-параметров utm_*, из Referrer)
 */
interface CampaignString {
	/**
	 * Получить строку
	 * @return string
	 */
	public function get();
}

?>