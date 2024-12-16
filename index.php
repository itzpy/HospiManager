<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="./assets/css/styles.css" />    
    <link rel="shortcut icon" href="./assets/images/favicon.ico" type="image/x-icon">
    <title>Hospital Management System | Home</title>
  </head>
  <body>
    <div class="wrapper">
      <nav>
        <div class="nav-container animate-zoom-in duration-1000">
          <div class="nav-logo">
            <p>Hospi Manager</p>
          </div>
          <div class="nav-menu" id="navMenu">
            <ul>
              <li><a href="#home" class="link active">Home</a></li>
              <li><a href="#aboutus" class="link">About</a></li>
              <li><a href="#contactus" class="link">Contact</a></li>
            </ul>
          </div>
          <div class="nav-button">
            <a href="./view/login.php">
              <button class="btn white-btn animate-zoom-in duration-1000" id="loginBtn">
                Log In
              </button>
            </a>
          </div>
          <div class="nav-button">
            <a href="./view/register.php">
              <button class="btn white-btn animate-zoom-in duration-1000" id="loginBtn">
                Register
              </button>
            </a>
          </div>
          <div class="search">
            <input type="search" placeholder="search services" />
            <i class="fa-solid fa-magnifying-glass"></i>
          </div>
        </div>
      </nav>

      <section class="landingpage">
        <div class="content">
          <div class="heading" data-aos="fade-right" data-aos-duration="2000">
            <h1>Welcome to Hospital Management System!</h1>
            <p>
              Revolutionizing healthcare management through comprehensive digital solutions. 
              Streamline patient care, inventory tracking, and administrative tasks with our cutting-edge platform.
            </p>
            <p>Empower your healthcare facility with smart, efficient technology.</p>
            <div class="button-container">
              <a href="view/register.php"><button class="btn" data-aos="fade-left" data-aos-duration="2000" data-aos-delay="00" data-aos-margin="">
                Get Started
              </button></a>
            </div>
          </div>
        </div>
      </section>

      <section class="aboutus" id="aboutus">
        <div class="container">
          <img src="./assets/images/hospital.jpg" data-aos="zoom-in-" data-aos-duration="2000" />
          <div class="aboutus-content">
            <h1 data-aos="fade-left" data-aos-duration="1000">About Our Hospital Management System</h1>
            <p data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
              Our Hospital Management System is a comprehensive digital solution designed to transform 
              healthcare administration. By integrating patient records, inventory management, and 
              administrative workflows, we provide healthcare providers with a powerful tool to 
              enhance patient care, operational efficiency, and resource management.
            </p>
          </div>
        </div>
      </section>

      <section class="details-services" id="details-services">
        <div class="container">
          <div class="section-header">
            <h2 data-aos="fade-up" data-aos-duration="1000">Our Comprehensive Hospital Management Solutions</h2>
            <p data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
              Empowering healthcare providers with intelligent, integrated technology
            </p>
          </div>

          <div class="services-grid">
            <div class="service-card" data-aos="fade-right" data-aos-duration="1000">
              <div class="service-icon">
                <i class="fas fa-notes-medical"></i>
              </div>
              <h3>Patient Record Management</h3>
              <p>
                Centralize and secure patient information with our advanced electronic health record system. 
                Seamlessly track medical history, treatments, and personal details.
              </p>
              <ul>
                <li>Comprehensive patient profiles</li>
                <li>Secure data storage</li>
                <li>Easy information retrieval</li>
              </ul>
            </div>

            <div class="service-card" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
              <div class="service-icon">
                <i class="fas fa-calendar-check"></i>
              </div>
              <h3>Appointment Scheduling</h3>
              <p>
                Streamline patient appointments with our intelligent scheduling system. 
                Reduce wait times and optimize healthcare provider resources.
              </p>
              <ul>
                <li>Real-time slot availability</li>
                <li>Automated reminders</li>
                <li>Multi-department coordination</li>
              </ul>
            </div>

            <div class="service-card" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="400">
              <div class="service-icon">
                <i class="fas fa-warehouse"></i>
              </div>
              <h3>Inventory Management</h3>
              <p>
                Maintain optimal stock levels with our advanced inventory tracking system. 
                Ensure critical medical supplies are always available.
              </p>
              <ul>
                <li>Real-time stock monitoring</li>
                <li>Automated reordering</li>
                <li>Expiration date tracking</li>
              </ul>
            </div>

            <div class="service-card" data-aos="fade-right" data-aos-duration="1000" data-aos-delay="600">
              <div class="service-icon">
                <i class="fas fa-chart-line"></i>
              </div>
              <h3>Reporting & Analytics</h3>
              <p>
                Gain insights into hospital operations with comprehensive reporting tools. 
                Make data-driven decisions to improve patient care and efficiency.
              </p>
              <ul>
                <li>Customizable dashboards</li>
                <li>Performance metrics</li>
                <li>Trend analysis</li>
              </ul>
            </div>

            <div class="service-card" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="800">
              <div class="service-icon">
                <i class="fas fa-user-shield"></i>
              </div>
              <h3>User Access Management</h3>
              <p>
                Control and monitor user access with robust authentication and permission systems. 
                Ensure data privacy and compliance.
              </p>
              <ul>
                <li>Role-based access</li>
                <li>Secure login</li>
                <li>Audit trails</li>
              </ul>
            </div>

            <div class="service-card" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="1000">
              <div class="service-icon">
                <i class="fas fa-mobile-alt"></i>
              </div>
              <h3>Mobile Accessibility</h3>
              <p>
                Access critical hospital information from anywhere with our mobile-friendly interface. 
                Stay connected and responsive.
              </p>
              <ul>
                <li>Responsive design</li>
                <li>Cross-platform compatibility</li>
                <li>Secure mobile access</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <section class="contactus" id="contactus">
        <div class="container">
          <div class="contactus-content">
            <h1 data-aos="fade-left" data-aos-duration="1000">Discover Our Solution</h1>
            <p data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
              Transform your healthcare facility's operations with our Hospital Management System. 
              We're committed to providing innovative technology that improves patient care and 
              operational efficiency.
            </p>
          </div>
          <div class="contactus-content">
            <h1 data-aos="fade-left" data-aos-duration="1000">Get in Touch</h1>
            <p data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
              Have questions or want to learn more about how our system can benefit your healthcare facility?
            </p>
            <ul class="contact-info">
              <li style="margin-bottom: 1em;">
                <ion-icon name="location-outline" class="contact-icon" style="margin-right: 1em;"></ion-icon>
                <span>Hospital Management Solutions, Tech Avenue, Innovation City</span>
              </li>
              <li style="margin-bottom: 1em;">
                <ion-icon name="call-outline" class="contact-icon" style="margin-right: 1em;"></ion-icon>
                <span>+1 (555) HOSPITAL</span>
              </li>
              <li>
                <ion-icon name="mail-outline" class="contact-icon" style="margin-right: 1em;"></ion-icon>
                <span>support@hospitalmanagement.tech</span>
              </li>
            </ul>
          </div>
        </div>
      </section>
  </div>
  <footer class="footer">
    <p>&copy; 2024 Hospi Manager. All Rights Reserved.</p>
  </footer>
  <style>
    .footer {
      position: relative;
      left: 0;
      bottom: 0;
      width: 100%;
      background-color: #722f37;
      color: white;
      text-align: center;
      padding: 10px 0;
    }
  </style>
  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  <script>
    AOS.init();
  </script>
  </div>
</body>

</html>