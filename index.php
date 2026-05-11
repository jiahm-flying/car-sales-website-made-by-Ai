<?php
session_start();
$userName = $_SESSION['user']['name'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>qmsl - Quick Market Sold Line</title>
    <style>
        /* Base Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f7f6;
            overflow-x: hidden;
        }

        /* Header / Top Navbar */
        header {
            background-color: rgb(0,0,0);
            backdrop-filter: blur(10px);
            color: white;
            padding: 1rem 2rem;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .a_name {
            text-decoration: none;
            font-size: 16px;
            color: #ffffff;
            white-space: nowrap;
            padding: 6px 18px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(6px);
            line-height: 1.4;
            margin-left: auto;
            transition: opacity 0.2s;
            cursor: pointer;
        }
        .a_name:hover {opacity: 0.8;}

        .menu-icon {
            font-size: 30px;
            cursor: pointer;
            transition: 0.3s;
        }

        .menu-icon:hover { color: #bdc3c7; }

        .logo {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        /* Sidebar Navigation */
        .sidebar {
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(15px);
            overflow-x: hidden;
            transition: 0.5s;
            box-shadow: 2px 0 15px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
        }

        /* Logo inside sidebar */
        .sidebar-logo {
            width: 180px;
            margin: 30px 0 20px 25px;
            display: block;
            opacity: 0;
            transition: opacity 0.3s 0.2s;
            background-color: transparent; 
            border-radius: 5px;
        }

        .sidebar a {
            padding: 15px 32px;
            text-decoration: none;
            font-size: 18px;
            color: #d1d1d1;
            display: block;
            transition: 0.3s;
            white-space: nowrap;
            letter-spacing: 1px;
        }

        .sidebar a:hover { 
            color: #ffffff; 
            background-color: rgba(255,255,255,0.1); 
        }

        .sidebar .closebtn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 36px;
            cursor: pointer;
            color: #ffffff;
        }

        /* Hero Section */
        .hero {
            min-height: 85vh;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 0 20px;
        }

        .brand-container {
            cursor: pointer;
            transition: transform 0.3s;
            user-select: none;
        }
        
        .brand-container:hover { transform: scale(1.01); }

        .hero h1 {
            font-size: 4.5rem;
            text-transform: uppercase;
            letter-spacing: 8px;
            margin-bottom: 5px;
        }

        .hero h3 {
            font-size: 1.2rem;
            font-weight: 300;
            letter-spacing: 2px;
            margin-bottom: 15px;
            color: #bdc3c7;
        }

        .new-description {
            font-size: 1rem;
            font-weight: 300;
            margin-bottom: 25px;
            color: #bdc3c7;
        }

        /* Collapsible Intro */
        .intro-container {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.6s ease-in-out, opacity 0.4s;
            opacity: 0;
            max-width: 800px;
        }

        .intro-container.expanded {
            max-height: 500px;
            opacity: 1;
        }

        .intro-text {
            font-size: 1.05rem;
            line-height: 1.8;
            padding: 20px 0;
            color: #ecf0f1;
            border-top: 1px solid rgba(255,255,255,0.2);
        }

        .toggle-hint {
            font-size: 0.8rem;
            color: #95a5a6;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cta-button {
            margin-top: 30px;
            background-color: #ffffff;
            color: #000000;
            padding: 12px 40px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s;
            display: inline-block;
        }

        .cta-button:hover { background-color: #bdc3c7; }

        .black-zone { background:#000; color:#fff; }

        .footer {
            padding: 3rem 5%;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .footer p {
            color: rgba(255,255,255,0.35);
            font-weight: 300;
            letter-spacing: 0.05em;
        }

        /* Overlay */
        .overlay {
            display: none;
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        /* Content Section */
        .content-section { padding: 80px 10%; text-align: center; }
        .car-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 40px; }
        .car-card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .car-card img { width: 100%; height: 220px; object-fit: cover; }
        .car-info { padding: 25px; }
    </style>
</head>
<body>
    <div id="mySidebar" class="sidebar">
        <span class="closebtn" onclick="closeNav()">&times;</span>
        <img src="logo.png" alt="qmsl Logo" class="sidebar-logo" id="sidebarLogo">
        <a href="search.php">Search</a>
        <a href="add-car.html">Add Car</a>
        <a href="inventory.php">Inventory</a>
        <a href="login.php">Log in</a>
        <a href="register.php">Register</a>
    </div>

    <div id="overlay" class="overlay" onclick="closeNav()"></div>

    <header>
        <div class="menu-icon" onclick="openNav()">&#9776;</div>
        <div class="logo">qmsl</div>
        <?php if (!empty($userName)): ?>
            <a href="logout.php" class="a_name" title="Click here to log out"><?php echo htmlspecialchars($userName); ?></a>
        <?php endif; ?>
    </header>

    <div class="hero">
        <div class="brand-container" onclick="toggleIntro()">
            <h1>qmsl</h1>
            <h3>.quick market sold line</h3>
            <p class="new-description">Thousands of high-quality used and new cars for you to choose from.</p>
            <p class="toggle-hint" id="hintText">Click to read our story ▾</p>
        </div>

        <div id="introContent" class="intro-container">
            <p class="intro-text">
                QMSL is a used car trading platform built on the brand philosophy of Quick Market Sold Line.<br>
                We are committed to creating a fast, transparent and efficient express lane for second-hand vehicle transactions.<br>
                We carefully select a large number of high-quality car sources, build a safe and reliable trading bridge for both buyers and sellers, 
                making used car transactions time-saving and hassle-free, helping you start your wonderful driving life easily.
            </p>
        </div>

        <a href="search.php" class="cta-button">Browse Now</a>
    </div>

    <section class="content-section">
        <h2>Popular Models</h2>
        <div class="car-grid">
            <div class="car-card">
                <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=400&q=80" alt="Sports Car">
                <div class="car-info"><h3>Luxury Sports Car</h3><p>Down payment starts at 10%</p></div>
            </div>
            <div class="car-card">
                <img src="https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=400&q=80" alt="Sedan">
                <div class="car-info"><h3>Business Sedan</h3><p>Premium driving experience</p></div>
            </div>
            <div class="car-card">
                <img src="https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=400&q=80" alt="SUV">
                <div class="car-info"><h3>All-Terrain SUV</h3><p>Conquer any road</p></div>
            </div>
        </div>
    </section>

    <footer class="footer black-zone">
        <p>© 2025 QMSL. All rights reserved.</p>
    </footer>

    <script>
        function openNav() {
            document.getElementById("mySidebar").style.width = "300px";
            document.getElementById("overlay").style.display = "block";
            setTimeout(() => { document.getElementById("sidebarLogo").style.opacity = "1"; }, 200);
        }

        function closeNav() {
            document.getElementById("mySidebar").style.width = "0";
            document.getElementById("overlay").style.display = "none";
            document.getElementById("sidebarLogo").style.opacity = "0";
        }

        function toggleIntro() {
            const intro = document.getElementById("introContent");
            const hint = document.getElementById("hintText");
            if (intro.classList.contains("expanded")) {
                intro.classList.remove("expanded");
                hint.innerText = "Click to read our story ▾";
            } else {
                intro.classList.add("expanded");
                hint.innerText = "Click to collapse ▴";
            }
        }
    </script>
</body>
</html>
