<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "student_services_db"; 

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM announcements ORDER BY date_posted DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .navbar { background: -blue; padding: 15px; color: white; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .navbar .logo { font-size: 24px; font-weight: bold;color: gold }
        .navbar .nav-links { list-style: none; padding: 0; margin: 0; display: flex; }
        .navbar .nav-links li { margin: 0 15px; }
        .navbar .nav-links a { color: white; text-decoration: none; font-size: 18px; }
        
        .slideshow-container { width: 80%; margin: 50px auto; position: relative; }
        .slide { position: relative; }
        .slide img { width: 100%; height: 400px; object-fit: cover; }
        .caption { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); background: rgba(0, 0, 0, 0.6); color: white; padding: 10px 20px; border-radius: 5px; }
        
        .slick-prev, .slick-next { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255, 255, 255, 0.7); color: black; border: none; font-size: 25px; padding: 10px 15px; cursor: pointer; z-index: 10; border-radius: 50%; }
        .slick-prev { left: -80px; }
        .slick-next { right: -40px; }
        .slick-prev:hover, .slick-next:hover { background: white; color: black; }
        .slick-dots { bottom: 10px; }
        .slick-dots li button:before { color: rgb(255, 255, 255); font-size: 20px; }
        .slick-dots li.slick-active button:before { color: rgb(247, 215, 8); }
        
        .welcome { text-align: center; padding: 50px 20px; background: #f4f4f4; }
        .about { text-align: center; padding: 50px 20px; background: #fff; }
        .about h2 { margin-bottom: 20px; }
        .about p { max-width: 800px; margin: 0 auto; font-size: 18px; line-height: 1.6; text-align: justify; }
        .services { text-align: center; padding: 50px 20px; background: #f4f4f4; }
        .services h2 { margin-bottom: 20px; }
        .services .service { display: inline-block; width: 45%; margin: 20px 2.5%; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .services .service h3 { margin-bottom: 15px; }
        .footer { background: #333; color: white; text-align: center; padding: 20px; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">NEUST Gabaldon Student Services</div>
        <ul class="nav-links">
            <li><a href="#">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="login.php">Login</a></li>
        </ul>
    </div>
    <div class="welcome">
        <h2>Welcome to NEUST Gabaldon Student Services Management System</h2>
       
    </div>

    <div class="slideshow-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="slide">
                <img src="uploads/announcements/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
                <div class="caption"> <?= htmlspecialchars($row['title']) ?> </div>
            </div>
        <?php endwhile; ?>
    </div>

   

    <div class="about" id="about">
        <h2>About Us</h2>
        <p>
            NEUST Gabaldon Student Services Management System is designed to optimize and enhance the management of various student services. 
            Our goal is to provide an efficient, user-friendly platform for students and faculty to access essential services such as announcements, 
            scholarships, grievances, and dormitory management.
        </p>
    </div>

    <div class="services" id="services">
        <h2>Our Services</h2>
        <div class="service">
            <h3>Announcements</h3>
            <p>Stay updated with the latest news and announcements from NEUST Gabaldon. Our platform ensures you never miss important updates.</p>
        </div>
        <div class="service">
            <h3>Scholarships</h3>
            <p>Apply for various scholarships offered by NEUST Gabaldon. Our platform provides a opt    imize application process to help you secure financial support.</p>
        </div>
        <div class="service">
            <h3>Grievances</h3>
            <p>Have any concerns or issues? Use our grievance service to report and resolve your problems efficiently and effectively.</p>
        </div>
        <div class="service">
            <h3>Dormitory Services</h3>
            <p>Manage your dormitory applications and stay updated with dormitory services offered by NEUST Gabaldon.</p>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 NEUST Gabaldon. All Rights Reserved.</p>
    </div>
    
    <script>
        $(document).ready(function(){
            $('.slideshow-container').slick({
                dots: true,
                infinite: true,
                speed: 500,
                autoplay: true,
                autoplaySpeed: 3000,
                prevArrow: '<button class="slick-prev">&#10094;</button>',
                nextArrow: '<button class="slick-next">&#10095;</button>'
            });
        });
    </script>
</body>
</html>