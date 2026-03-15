# Simple Loan Requests

Тестовое задание для WIAM. 
Имитация сервиса для подачи и обработки заявок на займ.  

[Тестовое задание](https://docs.google.com/document/d/15BRZincw_j7dIdaKxQZNsy-L8h4OYE12)

---

## Стек технологий

- PHP 8.4 + PHP-FPM
- Yii2 (yii2-app-basic)
- PostgreSQL 16
- Nginx 1.25
- Docker / Docker Compose

---

## Запуск 

### 1. Клонировать репозиторий

```bash
git clone https://github.com/dmitryat/yii2-simple-loan-requests-wiam.git
```

### 2. Подготовить файл окружения

Отредактировать `.env` при необходимости:

```env
APP_PORT=80

DB_HOST=db
DB_PORT=5432
DB_NAME=loans
DB_USER=user
DB_PASSWORD=password
```

### 3. Собрать и запустить контейнеры

```bash
docker compose up -d --build
```

При старте PHP-контейнер автоматически:
- Проверяет и устанавливает фреймворк через composer
- Дожидается готовности PostgreSQL
- Применяет миграции (`yii migrate`)
- Запускает PHP-FPM


---

## API

### POST /requests — Подача заявки на займ

**Тело запроса (JSON):**

```json
{
  "user_id": 1,
  "amount": 50000,
  "term": 30
}
```

| Поле      | Тип     | Описание                  |
|-----------|---------|---------------------------|
| `user_id` | integer | ID пользователя |
| `amount`  | integer | Сумма займа               |
| `term`    | integer | Срок в днях               |

**Успешный ответ (201):**

```json
{
  "id": 42,
  "result": true
}
```

**Ответ при ошибке (400):**

```json
{
  "result": false
}
```

---

### GET /processor — Запуск обработки заявок

Запускает последовательную обработку всех заявок в статусе `pending`.

```bash
curl "http://localhost:80/processor?delay=2"
```

| Параметр | Тип     | Описание                           |
|----------|---------|------------------------------------|
| `delay`  | integer | Задержка для имитации длительности обработки (сек.)  |

**Ответ (200):**

```json
{
  "result": true
}
```

---

## Статусы заявки

| Статус         | Описание                              |
|----------------|---------------------------------------|
| `pending`      | Ожидает обработки                     |
| `under_review` | Взята в обработку воркером            |
| `approved`     | Одобрена                              |
| `declined`     | Отклонена                             |

**Бизнес-правило:** у одного пользователя может быть не более одной одобренной заявки — гарантируется уникальным частичным индексом на уровне БД.

---

## Примеры запросов

```bash
# Подать заявку
curl -X POST http://localhost:80/requests \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1, "amount": 100000, "term": 90}'

# Запустить обработчик с задержкой 3 секунды
curl "http://localhost:80/processor?delay=3"

# Запустить два одновременных обработчика с задержками в 1 и 3 секунды
curl "http://localhost:80/processor?delay=1" &
curl "http://localhost:80/processor?delay=3" &
wait
```


---

## Структура проекта

```
├── components/         # Application (кастомный класс приложения)
├── config/             # Конфигурация (web.php, db.php)
├── controllers/        # ApiController (контроллер запросов)
├── docker/
│   ├── nginx/          # Конфиг Nginx
│   └── php/            # Dockerfile, entrypoint.sh, wait-for-db.sh
├── migrations/         # Миграции БД
├── models/             # LoanRequest, LoanRequestPayload, LoanProcessingParams
├── services/           # LoanService — бизнес-логика
└── web/                # Точка входа (index.php)
```

Файлы и структура basic шаблона сохранены.

---

## Затраченное время

| Этап                                          | Время     |
|-----------------------------------------------|-----------|
| Настройка Docker, разворачивание basic шаблона | ~2 ч      |
| Проектирование схемы БД и архитектуры         | ~0.5 ч      |
| Реализация базовых моделей, сервиса и контроллера     | ~1 ч      |
| Обработка конкурентности и улучшение (FOR UPDATE, индекс) | ~1 ч      |
| Отладка и тестирование                        | ~1.5 ч      |
| Ревью, корректировка readme                        | ~0.5 ч      |
| **Итого**                                     | **~6.5 ч**  |

---

## Контакты
TG автора : https://t.me/dmitry_tal