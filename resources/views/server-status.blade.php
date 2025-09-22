<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITA Wiki Backend - Server Status</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            margin: 2rem;
        }
        .status-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .status-text {
            color: #2d3748;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .subtitle {
            color: #718096;
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        .info-card {
            background: #f7fafc;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .info-card h3 {
            margin: 0 0 0.5rem 0;
            color: #2d3748;
            font-size: 1rem;
        }
        .info-card p {
            margin: 0;
            color: #718096;
            font-size: 0.9rem;
        }
        .api-link {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            margin-top: 1rem;
            transition: background 0.3s;
        }
        .api-link:hover {
            background: #5a67d8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="status-icon">🚀</div>
        <h1 class="status-text">Server is Running!</h1>
        <p class="subtitle">ITA Wiki Backend API is up and running successfully</p>
        
        <div class="info-grid">
            <div class="info-card">
                <h3>🌐 Server Status</h3>
                <p>Online & Healthy</p>
            </div>
            <div class="info-card">
                <h3>🗄️ Database</h3>
                <p>Connected & Ready</p>
            </div>
            <div class="info-card">
                <h3>📚 API Documentation</h3>
                <p>Available at /docs</p>
            </div>
            <div class="info-card">
                <h3>🔧 Environment</h3>
                <p>{{ app()->environment() }}</p>
            </div>
        </div>
        
        <a href="/docs" class="api-link">View API Documentation</a>
    </div>
</body>
</html>
