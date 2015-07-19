<?php

namespace WebConstructionSet\Accounting;

/**
 * Интерфейс OAuth
 */
interface OAuth {

	/**
	 * Обработка транзакции OAuth
	 * Может выдать header()
	 * @return boolean true - данные готовы (получен token или ошибка), false - еще в процессе
	 */
	public function process();

	/**
	 * Получить Token (тип Access/Refresh зависит от реализации)
	 * @return string Token или null
	 */
	public function getToken();

	/**
	 * Получить описание ошибки
	 * @return string тип и описание
	 */
	public function getError();
}
