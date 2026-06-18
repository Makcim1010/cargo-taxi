<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ястреб API</title>
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
            padding: 2rem;
            background-color: #1e293b;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
            border: 1px solid #334155;
        }
        h1 {
            color: #38bdf8;
            margin-bottom: 0.5rem;
        }
        p {
            color: #94a3b8;
            font-size: 1.1rem;
        }
        .status {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.25rem 0.75rem;
            background-color: #065f46;
            color: #34d399;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Бэкенд работает</h1>
        <p>Проект работает в режиме REST API.</p>
        <p>Для тестирования используйте Bruno и обращайтесь к эндпоинтам через <code>/api/...</code></p>
        <span class="status">Laravel v13.11.2</span>
    </div>
</body>
</html>