<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Eclipse Core Package</title>
        <style>
            body {
                font-family: system-ui, -apple-system, sans-serif;
                margin: 0;
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            .container {
                text-align: center;
                padding: 2rem;
            }
            h1 {
                font-size: 3rem;
                margin-bottom: 1rem;
                font-weight: 300;
            }
            p {
                font-size: 1.2rem;
                opacity: 0.9;
            }
            .admin-link {
                display: inline-block;
                margin-top: 2rem;
                padding: 1rem 2rem;
                background: rgba(255, 255, 255, 0.2);
                color: white;
                text-decoration: none;
                border-radius: 50px;
                border: 1px solid rgba(255, 255, 255, 0.3);
                transition: all 0.3s ease;
            }
            .admin-link:hover {
                background: rgba(255, 255, 255, 0.3);
                transform: translateY(-2px);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Eclipse Core Package</h1>
            <p>Welcome to your Laravel workbench environment</p>
            <a href="/admin" class="admin-link">Access Admin Panel</a>
        </div>
    </body>
</html>
