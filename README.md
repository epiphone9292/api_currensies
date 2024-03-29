# api_currensies

Для работы необходимо авторизоваться и получить токен

    POST /auth
    в теле 'login' и 'pass'

При успешной авторизации в ответ приходит в теле "auth:true",
и заголовок "Access-token - 'токен'".
Заголовок необходимо передавать со следующими запросами.
Время жизни токена - 15 минут, при запросах время обновляется.

    GET /currencies — список курсов валют,
    можно указать номер страницы (/currencies/3), тогда будет выводить по 10 штук

    GET /currency/id — курс валюты только переданного id

Параметры ответа:

    'error' - string - сообщение об ошибке, если нет ошибок - пустая строка
    'items' - array - массив найденных значений в формате
        ['id', 'name', 'rate'] либо пустой массив, если не найдено

    'auth' - bool - только для авторизации, успешно ли прошла

Пример запроса на авторизацию:

    http://91.77.169.35

    в теле
    [
        'login' => 'admin',
        'pass' => 'zlo'
    ]
    Ответ:
    {
        "auth":false,
        "error":"invalid login or password"
    }

Пример currencies:

    http://91.77.169.35/currencies/3
    Передаём заголовок 'Access-token' => '98f43a9274fc1ca8ac508f49e105b388', полученный в /auth

Пример ответа:

    {
        "items":[
            {"id":"R01775", "name":"Швейцарский франк", "rate":"64.8632"},
            {"id":"R01810","name":"Южноафриканских рэндов","rate":"4.2771"},
            {"id":"R01815","name":"Вон Республики Корея","rate":"0.0538"},
            {"id":"R01820","name":"Японских иен","rate":"0.5971"}
        ],
        "error":""
    }

=========================================================================


В папке 'scripts' - скрипты для запуска из консоли.

    create.php создаёт таблицы для работы и добавляет пльзователя admin/admin
    update.php заполняет или обновляет таблицу currency
