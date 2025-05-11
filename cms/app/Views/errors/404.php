<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found - GitHub Repository Crawler</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: #24292e;
            background-color: #f6f8fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .error-container {
            max-width: 600px;
            padding: 2rem;
            text-align: center;
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .error-code {
            font-size: 8rem;
            font-weight: 600;
            margin: 0;
            color: #0366d6;
            line-height: 1;
        }
        .error-title {
            font-size: 2rem;
            margin: 1rem 0 2rem;
        }
        .error-message {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            color: #586069;
        }
        .btn {
            display: inline-block;
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            color: white;
            background-color: #0366d6;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }
        .btn:hover {
            background-color: #044289;
        }
        .octocat {
            width: 120px;
            height: 120px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <svg class="octocat" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#0366d6" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
        </svg>
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Page Not Found</h2>
        <p class="error-message">The page you're looking for doesn't exist or has been moved.</p>
        <a href="/" class="btn">Go to Homepage</a>
    </div>
</body>
</html>