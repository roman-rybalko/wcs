<?php

namespace WebConstructionSet\Marketing;

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