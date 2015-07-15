<?php

namespace WebConstructionSet\Marketing;

/**
 * Выдрать строки рекламной кампании откуда-нибудь (Yandex, Google, Excel, txt - в зависимости от реализации)
 */
interface CampaignStrings {
	/**
	 * Получить строки
	 * @return array
	 */
	public function get();
}

?>