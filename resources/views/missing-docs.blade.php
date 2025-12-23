<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Not Generated</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.8em;
        }

        .emoji {
            font-size: 3em;
            margin-bottom: 20px;
        }

        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .steps {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: left;
        }

        .steps ol {
            margin-left: 20px;
            color: #666;
        }

        .steps li {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }

        .command {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: left;
            overflow-x: auto;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        .links {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .links a {
            display: inline-block;
            color: #667eea;
            text-decoration: none;
            margin: 10px 10px;
            font-size: 0.9em;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="emoji">📄</div>

        <h1>API Documentation</h1>
        <p>Documentation has not been generated yet.</p>

        <div class="steps">
            <p><strong>To generate API documentation, run:</strong></p>
            <div class="command">
                php artisan api-inspector:generate
            </div>

            <ol>
                <li>This command scans your API routes</li>
                <li>Extracts FormRequest validation rules</li>
                <li>Generates documentation in multiple formats</li>
                <li>Saves files to <code>storage/api-docs/</code></li>
            </ol>
        </div>

        <p style="margin-top: 30px; font-size: 0.9em; color: #999;">
            💡 After generating, refresh this page to see your documentation.
        </p>

        <div class="links">
            <a href="https://github.com/irabbi360/laravel-api-inspector">GitHub</a>
            <a href="https://packagist.org/packages/irabbi360/laravel-api-inspector">Packagist</a>
        </div>
    </div>
</body>
</html>
