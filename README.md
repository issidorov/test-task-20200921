# Тестовое задание (Yii2)

Задача: хранить массив(ы) строковых данных переменной длинны с изменяющимся 
набором ключей в атрибутах модели ActiveRecord (с сохранением в БД).

1. Реализовать через треит
2. Реализовать через поведение
3. Реализовать возможность выборки моделей из БД на основе фильтра по 
некоторым наборам ключ-значение из массива данных

### Результат

Все файлы относящиеся к этому тестовому заданию находятся в двух директориях:
- `tz` &mdash; тут находятся созданный треит и поведение
- `tests/unit/tz` &mdash; юнит тесты

Созданный треит и поведение используются только в фейковых моделях,
которые находятся в юнит тестах:
- `tests/unit/tz/_model_by_behavior/MyFake.php`
- `tests/unit/tz/_model_by_trait/MyFake.php`

### Запуск в Docker и тестирование

Скачиваем проект из GitHub

    ...

Запускаем контейнер

    docker-compose up -d

Устанавливаем зависимости

    docker-compose exec php /bin/bash
    # composer install

Теперь в браузере доступен базовый сайт

    http://localhost:8000/

Запуск тестов
    
    docker-compose exec php /bin/bash
    # vendor/bin/codecept run
