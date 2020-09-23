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

> Для запуска контейнера потребуются свободные порты 80 и 3306 на
> вашем компьютере

Скачиваем проект из GitHub

    ...

Устанавливаем зависимости

    docker-compose run --rm lamp composer install

Запускаем контейнер

    docker-compose up -d

Теперь в браузере доступен базовый сайт

    http://localhost/

Запуск тестов
    
    docker-compose exec lamp /bin/bash
    > cd app
    > vendor/bin/codecept run
