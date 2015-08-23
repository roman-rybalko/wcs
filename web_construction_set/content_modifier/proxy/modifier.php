<?php

namespace WebConstructionSet\ContentModifier\Proxy;

/**
 * Интерфейс модиикатора проксируемого контента.
 */
interface Modifier {
	/**
	 * Вызывается при получении заголовков.
	 * @param string $contentType
	 * @param string $data
	 * @return boolean Модификатор должен вернуть true, если он собирается обрабатывать данный сонтент
	 */
	public function detect($contentType, $data);

	/**
	 * Вызывается для каждого блока данных.
	 * @param string $data Последний вызов $data = null
	 * @return string Блок данных для замены
	 */
	public function process($data);
}
