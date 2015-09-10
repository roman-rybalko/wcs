<?php

namespace WebConstructionSet\Advertising;

/**
 * Выдрать строки рекламной кампании откуда-нибудь (Yandex, Google, Excel, txt - в зависимости от реализации)
 */
interface CampaignStrings {
	/**
	 * Получить строки
	 * @return [string]
	 */
	public function get();
}
