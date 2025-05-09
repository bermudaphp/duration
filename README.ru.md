# Duration (Продолжительность)

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](https://opensource.org/licenses/MIT)

*Read this in other languages: [English](README.md)*

## Обзор

`Duration` - это неизменяемый (immutable) класс-обертка для работы с временными интервалами с поддержкой формата ISO 8601. Он предоставляет комплексный API для создания, манипулирования, форматирования и сравнения временных интервалов типобезопасным и последовательным образом.

## Содержание

- [Возможности](#возможности)
- [Установка](#установка)
- [Основное использование](#основное-использование)
- [Фабричные методы](#фабричные-методы)
- [Поддержка ISO 8601](#поддержка-iso-8601)
- [Методы конвертации](#методы-конвертации)
- [Арифметические операции](#арифметические-операции)
- [Операции сравнения](#операции-сравнения)
- [Операции инкремента и декремента](#операции-инкремента-и-декремента)
- [Методы форматирования](#методы-форматирования)
- [Сериализация](#сериализация)
- [Конвертация DateInterval](#конвертация-dateinterval)
- [Примеры](#примеры)
- [Тестирование](#тестирование)
- [Участие в разработке](#участие-в-разработке)
- [Лицензия](#лицензия)

## Возможности

- **Неизменяемый дизайн**: Все операции возвращают новые экземпляры, сохраняя оригинал
- **Текучий интерфейс**: Цепочка методов для более чистого кода
- **Поддержка ISO 8601**: Анализ и генерация строк продолжительности ISO 8601 (например, `P1Y2M3DT4H5M6S`)
- **Комплексные единицы времени**: Конвертация между секундами, минутами, часами, днями, неделями, месяцами и годами
- **Расширенное сравнение**: Сравнение продолжительностей с поддержкой режимов ALL/ANY при работе с массивами
- **Инкремент/Декремент**: Удобные методы для увеличения или уменьшения продолжительности
- **Множественные опции форматирования**: Человекочитаемые, пользовательские шаблоны форматирования и массивы компонентов
- **Поддержка сериализации**: Встроенная сериализация JSON
- **Интеграция с DateInterval**: Конвертация в/из класса DateInterval PHP

## Установка

```bash
composer require bermudaphp/duration
```

## Основное использование

```php
use Bermuda\Stdlib\Duration;

// Создание продолжительности 1 час и 30 минут
$duration = new Duration(5400);

// Получить общее количество секунд
echo $duration->toSeconds(); // 5400

// Конвертировать в минуты
echo $duration->toMinutes(); // 90

// Конвертировать в часы
echo $duration->toHours(); // 1

// Получить представление ISO 8601
echo $duration->toISO8601(); // "PT1H30M"

// Получить человекочитаемое представление
echo $duration->toHumanReadable(); // "01:30:00"
```

## Фабричные методы

`Duration` предоставляет несколько фабричных методов для создания экземпляров:

```php
// Из конкретных единиц времени
$fromSeconds = Duration::fromSeconds(60);       // 60 секунд
$fromMinutes = Duration::fromMinutes(5);        // 5 минут
$fromHours = Duration::fromHours(2);            // 2 часа
$fromDays = Duration::fromDays(1);              // 1 день
$fromWeeks = Duration::fromWeeks(2);            // 2 недели
$fromMonths = Duration::fromMonths(3);          // 3 месяца
$fromYears = Duration::fromYears(1);            // 1 год

// Из строки ISO 8601
$fromISO = Duration::fromISO8601('P1Y2M3DT4H5M6S');

// Из DateInterval
$interval = new \DateInterval('P1DT6H');
$fromInterval = Duration::fromDateInterval($interval);
```

## Поддержка ISO 8601

`Duration` полностью поддерживает формат продолжительности ISO 8601:

```php
// Проверка строк ISO 8601
$isValid = Duration::validate('P1Y2M3DT4H5M6S'); // true
$isInvalid = Duration::validate('P1X'); // false

// Создание из строки ISO 8601
$duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');

// Конвертация в строку ISO 8601
echo $duration->toISO8601(); // "P1Y2M3DT4H5M6S"
```

### Правила формата ISO 8601

- Формат: `P[n]Y[n]M[n]DT[n]H[n]M[n]S`
- `P` - обозначение продолжительности (период), размещается в начале
- `Y` - обозначение лет
- `M` - обозначение месяцев
- `D` - обозначение дней
- `T` - обозначение времени (требуется, если используются компоненты времени)
- `H` - обозначение часов
- `M` - обозначение минут
- `S` - обозначение секунд

Пример: `P3Y6M4DT12H30M5S` представляет продолжительность 3 года, 6 месяцев, 4 дня, 12 часов, 30 минут и 5 секунд.

## Методы конвертации

Конвертация продолжительности в различные единицы времени:

```php
$duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');

echo $duration->toSeconds(); // Общее количество секунд
echo $duration->toMinutes(); // Общее количество минут (округлено вниз)
echo $duration->toHours();   // Общее количество часов (округлено вниз)
echo $duration->toDays();    // Общее количество дней (округлено вниз)
echo $duration->toWeeks();   // Общее количество недель (округлено вниз)
echo $duration->toMonths();  // Общее количество месяцев (округлено вниз)
echo $duration->toYears();   // Общее количество лет (округлено вниз)
```

## Арифметические операции

Выполнение арифметических операций с продолжительностями:

```php
$duration1 = new Duration(3600); // 1 час
$duration2 = new Duration(1800); // 30 минут

// Сложение
$sum = $duration1->add($duration2); // 1 час 30 минут

// Добавление конкретных единиц времени
$plusOneHour = $duration1->addHours(1);     // Добавить 1 час
$plusTenMinutes = $duration1->addMinutes(10); // Добавить 10 минут
$plusYear = $duration1->addYears(1);        // Добавить 1 год

// Вычитание (выбрасывает исключение, если результат будет отрицательным)
$difference = $duration1->strictSubtract($duration2); // 30 минут

// Безопасное вычитание (возвращает ноль, если результат будет отрицательным)
$safeDifference = $duration1->subtract($duration2); // 30 минут

// Вычитание конкретных единиц времени (выбрасывает исключение, если результат будет отрицательным)
try {
    $minusOneHour = $duration2->subtractHours(1); // Исключение!
} catch (\InvalidArgumentException $e) {
    echo "Нельзя вычесть 1 час из 30 минут!";
}
$minusTenMinutes = $duration2->subtractMinutes(10); // 20 минут

// Умножение
$doubled = $duration1->multiply(2); // 2 часа

// Деление
$halved = $duration1->divide(2); // 30 минут
```

## Операции сравнения

Сравнение продолжительностей различными методами:

```php
$duration1 = new Duration(3600); // 1 час
$duration2 = new Duration(7200); // 2 часа
$duration3 = new Duration(3600); // 1 час

// Сравнение двух продолжительностей
$comparisonResult = $duration1->compareTo($duration2); // -1 (меньше)

// Равенство
$isEqual = $duration1->equals($duration3); // true
$isNotEqual = $duration1->equals($duration2); // false

// Больше/меньше
$isLess = $duration1->lessThan($duration2); // true
$isLessOrEqual = $duration1->lessThanOrEqual($duration3); // true
$isGreater = $duration2->greaterThan($duration1); // true
$isGreaterOrEqual = $duration1->greaterThanOrEqual($duration3); // true

// Проверка, находится ли между двумя продолжительностями
$isBetween = $duration1->between(
    new Duration(1800),  // 30 минут
    new Duration(10800), // 3 часа
    true                // включительно (по умолчанию)
); // true
```

### Сравнение с массивами продолжительностей

Сравнение продолжительности с несколькими продолжительностями с использованием режимов ALL или ANY:

```php
$duration = new Duration(3600); // 1 час
$durations = [
    new Duration(1800),  // 30 минут
    new Duration(7200),  // 2 часа
    new Duration(10800)  // 3 часа
];

// Режим ALL (по умолчанию) - условие должно быть верно для ВСЕХ продолжительностей в массиве
$isLessThanAll = $duration->lessThan($durations, Duration::COMPARE_ALL); // false
$isGreaterThanAll = $duration->greaterThan($durations, Duration::COMPARE_ALL); // false

// Режим ANY - условие должно быть верно для ХОТЯ БЫ ОДНОЙ продолжительности в массиве
$isLessThanAny = $duration->lessThan($durations, Duration::COMPARE_ANY); // false
$isGreaterThanAny = $duration->greaterThan($durations, Duration::COMPARE_ANY); // true
```

## Операции инкремента и декремента

Инкремент или декремент продолжительностей:

```php
$duration = Duration::fromMinutes(5); // 5 минут

// Инкремент на 1 секунду
$incremented = $duration->increment(); // 5 минут 1 секунда

// Инкремент на определенное количество
$incrementedByMinute = $duration->incrementBy(60); // 6 минут

// Инкремент на другую продолжительность
$anotherDuration = Duration::fromMinutes(10);
$combined = $duration->incrementByDuration($anotherDuration); // 15 минут

// Декремент (выбрасывает исключение, если результат будет отрицательным)
$decremented = $duration->decrement(); // 4 минуты 59 секунд

// Декремент на определенное количество (выбрасывает исключение, если результат будет отрицательным)
$decrementedByMinute = $duration->decrementBy(60); // 4 минуты

// Безопасный декремент (возвращает ноль, если результат будет отрицательным)
$safeDecremented = $duration->safeDecrement(); // 4 минуты 59 секунд

// Безопасный декремент на определенное количество (возвращает ноль, если результат будет отрицательным)
$safeDecrementedByTooMuch = $duration->safeDecrementBy(600); // 0 секунд
```

## Методы форматирования

Форматирование продолжительностей различными способами:

```php
$duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');

// Преобразование в массив компонентов
$array = $duration->toArray();
/*
[
    'years' => 1,
    'months' => 2,
    'days' => 3,
    'hours' => 4,
    'minutes' => 5,
    'seconds' => 6
]
*/

// Форматирование с использованием пользовательского шаблона
$formatted = $duration->format('Годы: %Y, Месяцы: %M, Дни: %D, Время: %H:%I:%S');
// "Годы: 01, Месяцы: 02, Дни: 03, Время: 04:05:06"

// Человекочитаемый формат
$readable = $duration->toHumanReadable(); // "01:02:03:04:05:06"

// Преобразование в DateInterval
$interval = $duration->toDateInterval();
```

### Заполнители формата

Метод `format()` поддерживает следующие заполнители:

| Заполнитель | Описание |
|-------------|----------|
| `%Y` | Годы (с ведущим нулем) |
| `%M` | Месяцы (с ведущим нулем) |
| `%D` | Дни (с ведущим нулем) |
| `%H` | Часы (с ведущим нулем) |
| `%I` | Минуты (с ведущим нулем) |
| `%S` | Секунды (с ведущим нулем) |
| `%T` | Общее количество секунд |

## Сериализация

Класс `Duration` реализует интерфейсы `JsonSerializable` и `Stringable`:

```php
$duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');

// Преобразование в строку (формат ISO 8601)
echo (string)$duration; // "P1Y2M3DT4H5M6S"

// Сериализация JSON
$json = json_encode($duration);
/*
{
    "seconds": 37090998,
    "iso8601": "P1Y2M3DT4H5M6S",
    "components": {
        "years": 1,
        "months": 2,
        "days": 3,
        "hours": 4,
        "minutes": 5,
        "seconds": 6
    }
}
*/
```

## Конвертация DateInterval

Конвертация между `Duration` и PHP-классом `DateInterval`:

```php
// Создание из DateInterval
$interval = new \DateInterval('P1YT6H');
$duration = Duration::fromDateInterval($interval);

// Конвертация в DateInterval
$newInterval = $duration->toDateInterval();
echo $newInterval->format('%y лет, %h часов'); // "1 лет, 6 часов"
```

## Примеры

### Создание таймера обратного отсчета

```php
// Создание 5-минутного таймера обратного отсчета
$countdownDuration = Duration::fromMinutes(5);

// Имитация обратного отсчета (в реальном приложении это было бы в цикле с sleep)
for ($i = 0; $i < 5; $i++) {
    echo "Осталось: " . $countdownDuration->toHumanReadable() . PHP_EOL;
    $countdownDuration = $countdownDuration->decrementBy(60); // Уменьшение на 1 минуту
}

echo "Время вышло!";
```

### Работа с мероприятиями

```php
// Продолжительность мероприятия
$eventDuration = Duration::fromHours(2);

// Проверка, длиннее ли мероприятие 1 часа, но короче 3 часов
$isReasonableLength = $eventDuration->between(
    Duration::fromHours(1),
    Duration::fromHours(3)
);

// Создание 15-минутного перерыва
$breakDuration = Duration::fromMinutes(15);

// Добавление 15-минутного перерыва в конце
$totalDuration = $eventDuration->add($breakDuration);

echo "Общая продолжительность мероприятия: " . $totalDuration->toHumanReadable();
```

### Отслеживание времени задачи

```php
// Начальное время хранится в базе данных в секундах
$taskTimeSeconds = 3600; // 1 час уже отслежен
$taskDuration = new Duration($taskTimeSeconds);

// Пользователь работает еще 45 минут
$additionalTime = Duration::fromMinutes(45);
$updatedTaskDuration = $taskDuration->add($additionalTime);

// Получение компонентов времени задачи для отображения
$components = $updatedTaskDuration->toArray();
$hours = $components['hours'] ?? 0;
$minutes = $components['minutes'] ?? 0;

echo "Время задачи: {$hours}ч {$minutes}м";
```

## Тестирование

Запуск тестов PHPUnit:

```bash
composer test
```

## Участие в разработке

Вклады в проект приветствуются! Пожалуйста, не стесняйтесь отправлять Pull Request.

## Лицензия

Этот проект лицензирован под лицензией MIT - см. файл LICENSE для получения подробной информации.
