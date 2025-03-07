<?php
session_start();
include("database.php");

// Logout logic
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    // Unset user-specific session variables
    unset($_SESSION['id']);
    unset($_SESSION['email']);

    // If there are no other session variables, destroy the session
    if (empty($_SESSION)) {
        session_destroy();
    }

    // Redirect to the login page
    header("Location: login.php");
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to the login page if no user is logged in
    header("Location: login.php");
    exit();
}

$loggedInEmail = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sexy Body Fitness Gym</title>
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
</head>
<body>
    <header class="header">
        <a href="#" class="logo">FIT <span>NEZZ</span></a>
        <nav class="navbar">
            <a href="index.php">Home</a>
            <a href="membership.php">Membership</a>
            <a href="package.php">Packages</a>
        </nav>
        <div class="icons">
        <a href="../php/profile.php" class="btn" style="background-color: #808080;"><i class="far fa-user"></i></a>
            <div id="menu-btn" class="fas fa-bars"></div>
            <?php if(isset($_SESSION['email'])) { ?>
        <a href="?logout=1" class="btn" style="background-color: #808080;"><i class="fas fa-sign-out-alt"></i></a>
    <?php } ?>
        </div>
    </header>
    <section class="home">
        <div class="swiper home-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide box" style="background:linear-gradient(rgba(0,0,0,.3),rgba(0,0,0,.3)), url(../images/gym.jpg);">
                    <div class="content">
                        <h3>join fitnezz today</h3>
                        <p>We believe fitness should be accessible to everyone, everywhere, regardless of income level or access to a gym. That's why we offer hundreds of free, full-length workout videos, the most affordable and effective workout programs on the web, meal plans, and helpful health, nutrition and fitness information.</p>
                    </div>
                </div>
                <div class="swiper-slide box" style="background: linear-gradient(rgba(0,0,0,.3),rgba(0,0,0,.3)), url(../images/gym2.jpg);">
                    <div class="content">
                        <h3>Fit life, happy life.</h3>
                        <p>Our goal is to make health and fitness attainable, affordable and approachable.</p>
                    </div>
                </div>
                <div class="swiper-slide box" style="background: linear-gradient(rgba(0,0,0,.3),rgba(0,0,0,.3)), url(../images/gym3.jpg);">
                    <div class="content">
                        <h3>Wake up, work out, kick ass, repeat.</h3>
                        <p>Fitness is not about being better than someone else, it's about being better than you used to be.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="feature">
        <h1 class="heading"> Featured <span>Classes</span></h1>
        <div class="swiper feature-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="../images/Feature-classes.jpg" alt="">
                    </div>
                    <div class="content">
                        <div class="price" onclick="window.location.href='package.php'">₱250.00</div>
                        <h3>Gym</h3>
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="../images/Feature-classes-2.jpg" alt="">
                    </div>
                    <div class="content">
                        <div class="price" onclick="window.location.href='package.php'">₱125.00</div>
                        <h3>Zumba</h3>
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="../images/Feature-classes-3.jpg" alt="">
                    </div>
                    <div class="content">
                        <div class="price" onclick="window.location.href='package.php'">₱350.00</div>
                        <h3>Full Course</h3>
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="../images/Feature-classes-4.jpg" alt="">
                    </div>
                    <div class="content">
                        <div class="price" onclick="window.location.href='package.php'">₱125.00</div>
                        <h3>Fitness</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="trainers">
        <h1 class="heading">Expert <span>trainers</span></h1>
        <div class="swiper trainer-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="../images/trainer-1.jpg" alt="">
                    </div>
                    <div class="name">
                        <h1>Emma Satchell</h1>
                        <p>Zumba Instructor</p>
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="../images/trainer-3.jpg" alt="">
                    </div>
                    <div class="name">
                        <h1>Bart Codilla</h1>
                        <p>Zumba Instructor</p>
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="../images/trainer.jpg" alt="">
                    </div>
                    <div class="name">
                        <h1>Arnel Salo</h1>
                        <p>gym trainer</p>
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="../images/trainer-2.jpg" alt="">
                    </div>
                    <div class="name">
                        <h1>Bench Press</h1>
                        <p>gym trainer</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="blogs">
        <h1 class="heading">our <span>blogs</span></h1>
        <div class="swiper blogs-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="../images/blog.jpg" alt="">
                    </div>
                    <div class="content">
                        <h3>Arnel's Fitness</h3>
                        <p>NOTHING WILL WORK UNLESS YOU DO</p>
                    </div>
                    <div class="button">
                        <a href="https://www.facebook.com/profile.php?id=100070900072559&sk=about" class="btn">Visit Us!</a>
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="../images/blog-2.jpg" alt="">
                    </div>
                    <div class="content">
                        <h3>Sexy Body Fitness Gym</h3>
                        <p>TRICEP. PUSH. DOWN</p>
                    </div>
                    <div class="button">
                        <a href="https://www.facebook.com/profile.php?id=100063761685601" class="btn">Visit Us!</a>
                    </div>
                </div>
                <div class="swiper-slide box">
                    <div class="image">
                        <img src="../images/blog-3.jpg" alt="">
                    </div>
                    <div class="content">
                        <h3>ZAETRO DANCE FITNESS YT</h3>
                        <p>Sweat goal squad!!!</p>
                    </div>
                    <div class="button">
                        <a href="https://www.youtube.com/@zaetrodancefitness4571/videos" class="btn">Visit Us!</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="footer">
        <div class="box-container">
            <div class="box">
                <h1>about</h1>
                <div class="text">
                    <p>Our concept is simple. Provide our local communities with the most motivating fitness environment possible.  As a valued member, you will receive the cleanest facilities with the friendliest staff guaranteed.  No matter your gym experience or level of fitness, you will find our atmosphere welcoming and inspiring. Our mission is to ultimately help you achieve your end goal. </p>
                </div>
            </div>
            <div class="box">
                <h1>contact info</h1>
                <div class="icons">
                    <a href="https://www.google.com/maps/place/Sexy+Body+Fitness+Center/@10.3353811,123.863008,13z/data=!4m10!1m2!2m1!1s2nd+floor,+Minimart+Building,+MC+Briones,+cor+M.+L.+Quezon+Ave,+Mandaue+City,+6014+Cebu!3m6!1s0x33a9999e34f1842d:0x6989428486fff3e9!8m2!3d10.3353811!4d123.9392257!15sClcybmQgZmxvb3IsIE1pbmltYXJ0IEJ1aWxkaW5nLCBNQyBCcmlvbmVzLCBjb3IgTS4gTC4gUXVlem9uIEF2ZSwgTWFuZGF1ZSBDaXR5LCA2MDE0IENlYnWSAQNneW3gAQA!16s%2Fg%2F11n5g6mkf3?entry=ttu"><i class="fas fa-map-marker-alt"></i>2nd floor, Minimart Building, MC Briones, cor M. L. Quezon Ave, Mandaue City, 6014 Cebu</a>
                    <a href="#"><i class="fas fa-phone-alt"></i>09431298262</a>
                    <a href="#"><i class="fas fa-envelope"></i>gym_addict36@yahoo.com</a>
                    <a href="https://www.facebook.com/profile.php?id=100063761685601"><i class="fab fa-facebook-f"></i>Sexy Body Fitness Gym</a>
                </div>
            </div>
        </div>
    </section>
    <script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script> 
    <script src="../js/script.js"></script>
</body>
</html>