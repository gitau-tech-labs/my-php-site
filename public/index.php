<?php
$year = date("Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My PHP Site</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color: #1a1a1a;
            line-height: 1.6;
            background: #fafafa;
        }
        a { color: inherit; text-decoration: none; }

        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.2rem 2rem;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: sticky; top: 0; z-index: 100;
        }
        nav .logo { font-weight: 700; font-size: 1.2rem; }
        nav ul { list-style: none; display: flex; gap: 1.5rem; }
        nav a:hover { color: #6366f1; }

        .hero {
            text-align: center;
            padding: 6rem 1.5rem 4rem;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
        }
        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        .hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 2rem;
            opacity: 0.9;
        }
        .btn {
            display: inline-block;
            padding: 0.9rem 2rem;
            background: #fff;
            color: #6366f1;
            border-radius: 50px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1100px;
            margin: -3rem auto 0;
            padding: 0 2rem 4rem;
            position: relative;
        }
        .card {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .card .icon { font-size: 2rem; margin-bottom: 0.8rem; }
        .card h3 { margin-bottom: 0.5rem; color: #111; }
        .card p { color: #555; font-size: 0.95rem; }

        .about {
            max-width: 800px;
            margin: 0 auto;
            padding: 3rem 2rem 5rem;
            text-align: center;
        }
        .about h2 { font-size: 2rem; margin-bottom: 1rem; }
        .about p { color: #555; }

        footer {
            text-align: center;
            padding: 2rem;
            background: #111;
            color: #888;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<nav>
    <div class="logo">🚀 MySite</div>
    <ul>
        <li><a href="#features">Features</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#contact">Contact</a></li>
    </ul>
</nav>

<section class="hero">
    <h1>Build. Ship. Scale.</h1>
    <p>A modern PHP app running on Render — fast, simple, and always available.</p>
    <a href="#features" class="btn">Get Started</a>
</section>

<section class="features" id="features">
    <div class="card">
        <div class="icon">⚡</div>
        <h3>Lightning Fast</h3>
        <p>Powered by PHP 8.2 and Apache, served from Render's global edge.</p>
    </div>
    <div class="card">
        <div class="icon">🐳</div>
        <h3>Dockerized</h3>
        <p>Fully containerized for reproducible builds and easy scaling.</p>
    </div>
    <div class="card">
        <div class="icon">🔒</div>
        <h3>Secure by Default</h3>
        <p>HTTPS, environment variables, and Postgres-ready out of the box.</p>
    </div>
</section>

<section class="about" id="about">
    <h2>About</h2>
    <p>
        This is a starter landing page built with PHP, deployed via Docker on Render.
        Swap out the content, add your own pages, and connect a database whenever you're ready.
    </p>
</section>

<footer id="contact">
    © <?= $year ?> MySite — Built with PHP &amp; Render
</footer>

</body>
</html>
