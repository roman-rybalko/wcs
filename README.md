# Web Construction Set (WCS)
WCS - это набор компонентов для разработки web-приложений.
Содержит компоненты для работы с базами данных,
преобразования информации, изменения страниц и т.д.

# CodeStyle
Используется стандартный PHP CodeStyle.

Имена - CamelCase. Имена классов/интерфейсов/пространств имен начинаются с заглавной. Имена членов класса - со строчной.
Имена локальных и свободных переменных, свободных функций - func_name/$var_name.
Каждый class/interface находится в отдельном файле. Каждый namesace находится в отдельном каталоге.
Имена на файловой системе только строчными буквами с подчеркиванием '_'.
Имя класса/интейфейса/пространства имен преобразуется в имя на файловой системе следующим образом:
Name -> name, CamelCase -> camel_case, Name1Name2Name3 -> name1_name2_name3.

# Компоненты
Далее описание имеющихся компонентов.

## ContentModifier\Js
Генерирует код JavaScript, который изменяет элементы на странице.

## ContentModifier\JQuery
Генерирует noConflict-объект jQuery заданной версии.

## ContentModifier\Xslt, OutputBuffer\XsltHtml
Обработчик XSLT.

## CampaignString
Компоненты, которые извлекают строку рекламной кампании при переходе из поисковой системы.

## CampaignStrings
Компоненты, которые извлекают строки рекламных кампаний из настроек Yandex.Direct, Google AdWords и др. источников.

## Database\Relational
Интерфейс к реляционной БД.

## Database\Relational\PrefixWrapper
Адаптер для замены имен таблиц и полей.

## Database\Relational\SimpleAdapter
Адаптер для произвольного класса с аналогичным интерфейсом, обрабатывает исключения.

## Accounting\User, Database\User, Database\Relational\User
Управление пользователями: проверка пароля, ведение сессии.

## Database\KeyValue, Database\Relational\KeyValue
БД ключ-значение (map).

## Url\Tools
Утилиты для работы с URL.

## Xml\LibxmlErrorHandler
Собирает ошибки libxml (libxml_use_internal_errors).

## OutputBuffer\XmlFormatter
Форматирует вывод XML (tidy).
