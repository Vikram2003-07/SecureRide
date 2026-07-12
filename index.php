<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CabBook - Choose Your Experience</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 2rem;
            color: #fff;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .header h1 span {
            color: #e94560;
        }

        .header p {
            color: #a8b2d8;
            font-size: 1rem;
            margin-top: 10px;
        }

        .badge-container {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(233, 69, 96, 0.15);
            border: 1px solid rgba(233, 69, 96, 0.3);
            border-radius: 50px;
            padding: 6px 16px;
            margin-bottom: 20px;
        }

        .badge-dot {
            width: 8px;
            height: 8px;
            background: #e94560;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        .badge-container span {
            color: #e94560;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .cards-container {
            display: flex;
            gap: 30px;
            justify-content: center;
            flex-wrap: wrap;
            max-width: 900px;
            width: 100%;
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px 35px;
            width: 380px;
            max-width: 100%;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 20px 20px 0 0;
        }

        .card-vulnerable::before {
            background: linear-gradient(90deg, #ff6b6b, #ee5a24);
        }

        .card-secure::before {
            background: linear-gradient(90deg, #6c5ce7, #00b894);
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        }

        .card-vulnerable:hover {
            border-color: rgba(255, 107, 107, 0.4);
            box-shadow: 0 20px 60px rgba(255, 107, 107, 0.2);
        }

        .card-secure:hover {
            border-color: rgba(108, 92, 231, 0.4);
            box-shadow: 0 20px 60px rgba(108, 92, 231, 0.2);
        }

        .card-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
        }

        .card-label {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .label-vulnerable {
            background: rgba(255, 107, 107, 0.15);
            color: #ff6b6b;
            border: 1px solid rgba(255, 107, 107, 0.3);
        }

        .label-secure {
            background: rgba(108, 92, 231, 0.15);
            color: #a29bfe;
            border: 1px solid rgba(108, 92, 231, 0.3);
        }

        .card h2 {
            font-size: 1.6rem;
            color: #fff;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .card p {
            color: #a8b2d8;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .features-list {
            list-style: none;
            margin-bottom: 30px;
            text-align: left;
        }

        .features-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #a8b2d8;
            font-size: 0.9rem;
            padding: 5px 0;
        }

        .features-list li .icon {
            font-size: 1rem;
            flex-shrink: 0;
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 14px 30px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-vulnerable {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: #fff;
            box-shadow: 0 4px 20px rgba(255, 107, 107, 0.3);
        }

        .btn-vulnerable:hover {
            background: linear-gradient(135deg, #ee5a24, #ff6b6b);
            box-shadow: 0 6px 30px rgba(255, 107, 107, 0.5);
            transform: scale(1.02);
        }

        .btn-secure {
            background: linear-gradient(135deg, #6c5ce7, #00b894);
            color: #fff;
            box-shadow: 0 4px 20px rgba(108, 92, 231, 0.3);
        }

        .btn-secure:hover {
            background: linear-gradient(135deg, #00b894, #6c5ce7);
            box-shadow: 0 6px 30px rgba(108, 92, 231, 0.5);
            transform: scale(1.02);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #a8b2d8;
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0 10px;
        }

        .divider-line {
            width: 1px;
            height: 80px;
            background: rgba(255, 255, 255, 0.1);
        }

        .footer-note {
            margin-top: 50px;
            text-align: center;
            color: rgba(168, 178, 216, 0.5);
            font-size: 0.85rem;
        }

        .footer-note span {
            color: rgba(168, 178, 216, 0.8);
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .divider {
                display: none;
            }

            .cards-container {
                flex-direction: column;
                align-items: center;
            }

            .card {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="badge-container">
            <div class="badge-dot"></div>
            <span>Security Demo Platform</span>
        </div>
        <h1>Cab<span>Book</span></h1>
        <p>Choose a version to explore — vulnerable or secure</p>
    </div>

    <div class="cards-container">

        <!-- Vulnerable Version -->
        <div class="card card-vulnerable">
            <h2>⚠️CabBook Vulnerable</h2>
            <p>The original version with intentional security flaws — built for learning and demonstrating common web vulnerabilities.</p>
            <ul class="features-list">
                <li><span class="icon">🔓</span> SQL Injection possible</li>
                <li><span class="icon">🔓</span> XSS vulnerabilities present</li>
                <li><span class="icon">🔓</span> No CSRF protection</li>
                <li><span class="icon">🔓</span> File upload exploits</li>
                <li><span class="icon">🔓</span> Insecure direct object reference</li>
            </ul>
            <a href="cab_vulnerable/index.php" class="btn btn-vulnerable">Enter Vulnerable Version →</a>
        </div>

        <div class="divider">
            <div class="divider-line"></div>
            VS
            <div class="divider-line"></div>
        </div>

        <!-- Secure Version -->
        <div class="card card-secure">
            <h2>🛡️CabBook Secure</h2>
            <p>The hardened version with all vulnerabilities patched — showcasing security best practices and proper implementation.</p>
            <ul class="features-list">
                <li><span class="icon">✅</span> Prepared statements (PDO)</li>
                <li><span class="icon">✅</span> Output escaping (XSS safe)</li>
                <li><span class="icon">✅</span> CSRF token protection</li>
                <li><span class="icon">✅</span> Strict file upload validation</li>
                <li><span class="icon">✅</span> Proper session management</li>
            </ul>
            <a href="cab_secure_v2/index.php" class="btn btn-secure">Enter Secure Version →</a>
        </div>

    </div>

    <div class="footer-note">
        Built for <span>cybersecurity education</span> — compare both versions side by side to understand web vulnerabilities.
    </div>

</body>
</html>
