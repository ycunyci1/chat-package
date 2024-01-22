# Документация Laravel Чат-Пакета

## Оглавление

1. Введение
2. Зависимости
3. Установка
4. Настройка
5. Важные моменты
6. API Методы
7. Websockets
8. События и слушатели для кастомизации и доработки серверной части

## 1. Введение

Пакет для быстрой установки чата в laravel проект.

## 2. Зависимости

laravel/framework: ^10.0 <br>
laravel/passport: ^11.0.0 <br>
lcobucci/jwt: ^5.0.0

## 3. Установка

composer require chequite/chat

## 4. Настройка
Первым делом необходимо развернуть centrifugo. Очень удобно разворачивать через докер:
1. Подтягиваем себе образ centrifugo: 
```docker pull centrifugo/centrifugo ``` <br><br>
2. Создаем папку для конфигурационного файла ``` mkdir /var/centrifugo ``` <br><br>
3. Создаем файл конфигурации ``` nano /var/centrifugo/config.json ``` <br><br>
4. В файл записываем настройки. Для дефолтного использования конфиг будет выглядеть так:
   { <br>
   "allow_subscribe_for_client": true, <br>
   "api_key": "5af3d597bd452745a937c0ffda270fab060419e27s93f61d4f48e3409015df9ece", <br>
   "token_hmac_secret_key": "8b1482f10d8d47ac838549c8eac07edf3077a00ac6f6886fd5434f896c401cfb8e", <br>
   "admin": true, <br>
   "admin_password": "MoskowCityB@c9", <br>
   "admin_secret": "3fb95b5b93dad7269b7275c525726e86f47e123f1df1158770bfdc27a2fbab1e", <br>
   "allowed_origins": ["http://localhost"] <br>
   } <br>
allow_subscribe_for_client - Разрешаем подписку на каналы <br>
api_key - ключ для апи запросов, сгенерируйте рандомную строку и вставьте сюда <br>
token_hmac_secret_key - секретный ключ для приложения, тоже генерируется рандомная строка <br>
admin - включаем админ панель для дебага и отслеживания работы центрифуги <br>
admin_password - пароль для входа в админ панель <br>
admin_secret - ключ для запросов под админом <br>
allowed_origins - Разрешенные домены. Необходимо указать все домены с которых будут отправляться запросы. Если не укажете, то будете получать ошибку 403. <br><br>

5. Поднимаем контейнер ``` docker run -v /var/centrifugo/config.json:/centrifugo/config.json -p 8000:8000 centrifugo/centrifugo centrifugo -c config.json ``` <br><br>

6. После того как установили пакет и подняли центрифугу, надо добавить в .env файле ключи: <br> 
CENTRIFUGO_URL - url вашего centrifugo сервера (Пример http://3.22.83.190:8000, может быть и именованный домен) <br>
CENTRIFUGO_API_KEY - api key, который вы указали в конфиге на сервере centrifugo (Пример 5af3d5s97bd452745a937c0fda270fab060419e2793af61d4f48e3409015df) <br>
CENTRIFUGO_SECRET= секретный ключ, который вы также указали в конфиге на сервере centrifugo (Пример 8b1a482f10d8d47ac83s8549c8e07edf3077a00ac6f6886fd5434f896c401cf) <br> <br>

7. Запускаем миграции для пакета после установки: ``` php artisan migrate ```
8. Создаем символьную ссылку на папку storage: ``` php artisan storage:link ```

Официальная документация: https://centrifugal.dev/

## 5. Важные моменты

### 1. При регистрации и логине нужно генерировать токен на сервере и возвращать клиенту.

```JwtService::generateJwt($user->id)``` - сгенерирует нужный токен

### 2. Сообщение автоматически ставится прочитанным:
- Когда пользователь открыл чат
- Когда пользователь отправил сообщение

Как читать сообщения при открытом диалоге нужно думать на клиенте, эндпоинт есть ниже

### 3. Токен центрифуги живет час. Необходимо обновлять его в этом интервале. Я обновлял его при каждой перезагрузке страницы, т.к не силен в клиентской части
## 6. Api методы

### 1. Получить информацию о текущем пользователе 
- **Endpoint**: `/api/get-user-info`
- **Method**: `GET`
#### Пример ответа

```json
{
  "id": 1,
  "avatar": "https://your-domain.ru/path/to/file",
  "name": "Петр Петров"
}
```

#### Описание полей ответа:
| Parameter | Type    | Description                  |
|-----------|---------|------------------------------|
| `id`      | integer | ID текущего пользователя     |
| `name`    | string  | Имя текущего пользователя    |
| `avatar`  | string  | Аватар текущего пользователя |

### 2. Получить список чатов
- **Endpoint**: `/api/chats`
- **Method**: `GET`
#### Описание
Получить список чатов для текущего пользователя
#### Пример ответа

```json
{
  "id": 137,
  "companion_name": "Михаил Михайлов",
  "avatar": "https://your-domain.ru/path/to/file",
  "last_message": {
    "text": "Текст сообщения",
    "timestamp": "15:33",
    "sender_id": 9,
    "sender_name": "Сергей Сергеев",
    "was_read": true
  }
}
```
#### Описание полей ответа:
| Parameter                | Type        | Description                             |
|--------------------------|-------------|-----------------------------------------|
| `id`                     | integer     | ID собеседника                          |
| `companion_name`         | string      | Имя собеседника                         |
| `avatar`                 | string      | Аватар собеседника                      |
| `last_message`           | object/null | Информация о последнем сообщении в чате |
| `last_message.text`      | string      | Текст последнего сообщения              |
| `last_message.timestamp` | string      | Время отправки последнего сообщения     |
| `last_message.sender_id` | integer     | ID отправителя                          |
| `last_message.was_read`  | boolean     | Сообщение было прочитано                |
### 3. Создать чат
- **Endpoint**: `/api/chats`
- **Method**: `POST`
#### Описание
Создать чат для текущего пользователя с выбранным пользователем
#### Обязательные параметры:
| Parameter     | Type    | Description                           |
|---------------|---------|---------------------------------------|
| `companionId` | integer | ID пользователя с которым создаем чат |

#### Пример ответа

```json
{
  "chatId": 138
}
```
#### Описание полей ответа:
| Parameter | Type        | Description        |
|-----------|-------------|--------------------|
| `chatId`  | integer     | ID созданного чата |

### 3. Получить сообщения чата
- **Endpoint**: `/api/chats/{chatId}/messages`
- **Method**: `GET`
#### Параметры url:
| Parameter | Type    | Description |
|-----------|---------|-------------|
| `chatId`  | integer | ID чата     |

#### Пример ответа

```json
{
  "chatId": 138,
  "messages": {
     {
        "text": "Привет",
        "was_read": true,
        "timestamp": "16:53",
        "user": {
          "id": 3,
          "email": "user@gmail.com",
          "avatar": "https://your-domain.ru/path/to/file",
          "is_online": false,
          "name": "Алексей Алексеев",
          "last_seen_at": "24.01.2024 15:30"
        },
     }
  },
  "companion": {
      "id": 3,
      "email": "user@gmail.com",
      "avatar": "https://your-domain.ru/path/to/file",
      "is_online": false,
      "name": "Алексей Алексеев",
      "last_seen_at": "24.01.2024 15:30",
  }
}
```
#### Описание полей ответа:
| Parameter                      | Type    | Description                           |
|--------------------------------|---------|---------------------------------------|
| `chatId`                       | integer | ID чата                               |
| `messages`                     | object  | Список сообщений                      |
| `messages.*`                   | object  | Информация о сообщении                |
| `messages.*.text`              | string  | Текст сообщения                       |
| `messages.*.was_read`          | string  | Сообщение было прочитано              |
| `messages.*.timestamp`         | string  | Дата отправки сообщения               |
| `messages.*.user`              | object  | Информация об отправителе сообщения   |
| `messages.*.user.id`           | integer | ID отправителя                        |
| `messages.*.user.email`        | string  | Email отправителя                     |
| `messages.*.user.avatar`       | string  | Аватар отправителя                    |
| `messages.*.user.is_online`    | boolean | Статус отправителя                    |
| `messages.*.user.name`         | string  | Имя отправителя                       |
| `messages.*.user.last_seen_at` | string  | Дата последнего входа(если не онлайн) |
| `companion`                    | object  | Информация о собеседнике              |
| `companion.id`                 | integer | ID собеседника                        |
| `companion.email`              | string  | Email собеседника                     |
| `companion.avatar`             | string  | Аватар собеседника                    |
| `companion.is_online`          | boolean | Статус собеседника                    |
| `companion.name`               | string  | Имя собеседника                       |
| `companion.last_seen_at`       | string  | Дата последнего входа(если не онлайн) |

### 4. Отправить сообщение
- **Endpoint**: `/api/chats/{chatId}/messages`
- **Method**: `POST`

#### Параметры url:
| Parameter | Type    | Description |
|-----------|---------|-------------|
| `chatId`  | integer | ID чата     |

#### Пример ответа

```json
{
   "text": "Hello world!",
   "user": {
      "id": 5,
      "email": "email@gmail.com",
      "avatar": "https://your-domain.ru/path/to/file",
      "is_online": false,
      "name": "Александр Александров",
      "last_seen_at": "25.01.2024 10:15"
   },
   "was_read": true,
   "timestamp": "11:30"
}
```

#### Описание полей ответа:
| Parameter        | Type    | Description               |
|------------------|---------|---------------------------|
| `text`           | string  | Текст сообщения           |
| `user`           | object  | Информация об отправителе |
| `user.id`        | integer | ID отправителя            |
| `user.email`     | string  | Email отправителя         |
| `user.avatar`    | string  | Avatar отправителя        |
| `user.is_online` | boolean | Статус отправителя        |
| `user.name`      | string  | Имя отправителя           |
| `was_read`       | boolean | Было прочитано            |
| `timestamp`      | string  | Дата отправки             |

### 5. Сообщить о том, что пользователь печатает
- **Endpoint**: `/api/typing`
- **Method**: `POST`

#### Обязательные параметры:
| Parameter | Type    | Description                                                        |
|-----------|---------|--------------------------------------------------------------------|
| `chatId`  | integer | ID чата в котором текущий пользователь начал или закончил печатать |
| `typing`  | boolean | true - начал печатать, false - закончил печатать                   |

#### Пример ответа

```json
{
}
```

#### Параметры ответа
Пустой объект

### 6. Поиск пользователя
- **Endpoint**: `/api/search-users`
- **Method**: `GET`

#### Обязательные GET параметры
| Parameter | Type    | Description                    |
|-----------|---------|--------------------------------|
| `search`  | string  | Текст поиска (имя или фамилия) |

#### Пример ответа

```json
{
   {
      "id": 3,
      "email": "email@gmail.com",
      "avatar": "https://your-domain.ru/path/to/file",
      "is_online": false,
      "name": "Филип Филипов",
      "last_seen_at": "24.01.2024 19:17"
   },
   {
      "id": 157,
      "email": "email2@gmail.com",
      "avatar": "https://your-domain.ru/path/to/file",
      "is_online": true,
      "name": "Филип Сергеев",
      "last_seen_at": null
   }
}
```

#### Описание полей ответа:
| Parameter        | Type    | Description                            |
|------------------|---------|----------------------------------------|
| `*`              | object  | Информация о найденном пользователе    |
| `*.id`           | integer | ID пользователя                        |
| `*.email`        | string  | E-mail пользователя                    |
| `*.avatar`       | string  | Аватар пользователя                    |
| `*.is_online`    | boolean | Статус пользователя                    |
| `*.name`         | string  | Имя пользователя                       |
| `*.last_seen_at` | string  | Дата последнего входа (если не онлайн) |

#### 7. Обновить centrifugo token
- **Endpoint**: `/api/update-centrifugo-token`
- **Method**: `GET`

#### Пример ответа

```json
{
   "centrifugo_token": "ass2jajdwk2kasdkaskgkbkbggb5ib5o412kf2occe3"
}
```

#### 8. Сообщить, что пользователь прочитал сообщение
- **Endpoint**: `/api/chats/{chatId}/messages/{messageId}`
- **Method**: `POST`

#### Описание
Запрос нужен для того, чтобы когда пользователь находится чате и ему приходит сообщение, то надо сообщить серверу, что прочитано

#### Url параметры
| Parameter   | Type    | Description                                    |
|-------------|---------|------------------------------------------------|
| `chatId`    | integer | ID чата в котором делаем сообщение прочитанным |
| `messageId` | integer | ID сообщения которое делаем прочитанным        |

#### Пример ответа

```json
{
}
```

#### Параметры ответа
Пустой объект

## 7. Websockets

Для работы на клиенте с вебсокетами я использовал пакет центрифуги: https://github.com/centrifugal/centrifuge-js. По документации легко разобраться. Единственный момент. Нужно передавать centrifuge token при подписках на каналы.

### 1. Прослушивание обновления списка чатов
- **Channel name**: `user-{userId}-chats`

#### Данные, которые приходят в канал
| Parameter                | Type        | Description                                        |
|--------------------------|-------------|----------------------------------------------------|
| `id`                     | integer     | ID собеседника                                     |
| `companion_name`         | string      | Имя собеседника                                    |
| `avatar`                 | string      | Аватар собеседника                                 |
| `last_message`           | object/null | Информация о последнем сообщении в чате            |
| `last_message.text`      | string      | Текст последнего сообщения                         |
| `last_message.timestamp` | string      | Время отправки последнего сообщения (Формат 15:45) |
| `last_message.sender_id` | integer     | ID отправителя                                     |
| `last_message.was_read`  | boolean     | Сообщение было прочитано                           |

### 2. Прослушивание обновления сообщений в чате
- **Channel name**: `user-{chatId}-messages`

#### Данные, которые приходят в канал

| Parameter        | Type    | Description               |
|------------------|---------|---------------------------|
| `text`           | string  | Текст сообщения           |
| `user`           | object  | Информация об отправителе |
| `user.id`        | integer | ID отправителя            |
| `user.email`     | string  | Email отправителя         |
| `user.avatar`    | string  | Avatar отправителя        |
| `user.is_online` | boolean | Статус отправителя        |
| `user.name`      | string  | Имя отправителя           |
| `was_read`       | boolean | Было прочитано            |
| `timestamp`      | string  | Дата отправки             |


### 3. Прослушивание обновления статуса собеседника (online, offline)
- **Channel name**: `user-{userId}-status`

#### Данные, которые приходят в канал

| Parameter  | Type    | Description                                                                                  |
|------------|---------|----------------------------------------------------------------------------------------------|
| `isOnline` | boolean | true - онлайн, false - offline                                                               |
| `userId`   | integer | ID пользователя, у которого изменился статус                                                 |
| `lastSeen` | string  | Нужно для того, чтобы показывать когда пользователь был последний раз, если isOnline = false |

### 4. Прослушивания события набора сообщения
- **Channel name**: `chat.{chatId}`

#### Данные, которые приходят в канал
| Parameter | Type    | Description                                |
|-----------|---------|--------------------------------------------|
| `chatId`  | integer | ID чата                                    |
| `userId`  | integer | ID пользователя, который печатает          |
| `typing`  | boolean | true - печатает, false - перестал печатать |

### 5. Пример того, как я на клиенте подписывался на канал обновления сообщений в чате
```js
const sub = this.centrifuge.newSubscription(`user-${chatId}-messages`);

sub.on('publication', (response) => {
    this.currentChat.messages.push(JSON.parse(response.data))
    this.scrollToBottom();
});

sub.subscribe()
```

## 8. События и слушатели для кастомизации и доработки серверной части

- Ивент обновления чата Dd1\Chat\Events\ChatsUpdated

- Ивент отправки сообщения/обновления текущего чата Dd1\Chat\Events\MessageSent

- Ивент набора сообщения Dd1\Chat\Events\TypingEvent

- Ивент обновления статуса online/offline Dd1\Chat\Events\UserStatusUpdatedEvent

#### Если необходимо создать новый канал для прослушивания нового события, то просто нужно создать Event и забиндить слушателя Dd1\Chat\Listeners\CentrifugoPushToChannel в ServiceProvider. Слушатель ожидает всегда 2 поля в construct:

| Parameter | Type   | Description                                                      |
|-----------|--------|------------------------------------------------------------------|
| `channel` | string | Название канала                                                  |
| `data`    | array  | Массив с данными, которые будут отправляться по websocket каналу |
