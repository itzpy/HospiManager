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
    <!-- <link rel="stylesheet" href="./assets/css/login.css" /> -->
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
              <li><a href="#services" class="link active">Services</a></li>
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
          <div class="search">
            <input type="search" placeholder="search services" />
            <i class="fa-solid fa-magnifying-glass"></i>
          </div>
        </div>
      </nav>

      <!-- Landing Page -->
      <section class="landingpage">
        <div class="content">
          <div class="heading" data-aos="fade-right" data-aos-duration="2000">
            <h1>Welcome to Our Hospital Management System!</h1>
            <p>
              Our system provides comprehensive management of hospital operations, including patient records, appointments, and inventory management.
            </p>
            <p>Explore our services and see how we can help streamline your hospital's operations.</p>
            <div class="button-container">
              <a href="./view/login.php"><button class="btn" data-aos="fade-left" data-aos-duration="2000" data-aos-delay="00" data-aos-margin="">
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
            <h1 data-aos="fade-left" data-aos-duration="1000">About Us</h1>
            <p data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
              Our Hospital Management System is designed to improve the efficiency and effectiveness of hospital operations. We provide tools for managing patient records, scheduling appointments, and tracking inventory.
            </p>
          </div>
        </div>
      </section>

      <section class="details-services" id="details-services">
        <div class="title">
          <h1 data-aos="zoom-in-up" data-aos-duration="1000">
            Why Choose Our System? </h1>
        </div>
        <div class="services">
          <div class="card" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
            <img src="./assets/images/patient_records.jpg" alt="Patient Records" />
            <div class="description">
              <h1>Comprehensive Patient Records:</h1>
              <p>
                <li>
                  Maintain detailed patient records, including medical history, treatments, and appointments.
                </li>
              </p>
            </div>
          </div>
          <div class="card" data-aos="zoom-in" data-aos-duration="1000" data-aos-delay="200">
            <img src="./assets/images/appointments.jpg" alt="Appointments" />
            <div class="description">
              <h1>Efficient Appointment Scheduling:</h1>
              <p>
                <li>
                  Schedule and manage patient appointments with ease, ensuring optimal use of resources.
                </li>
              </p>
            </div>
          </div>
          <div class="card" data-aos="zoom-in" data-aos-duration="1000" data-aos-delay="400">
            <img src="./assets/images/inventory_management.jpg" alt="Inventory Management" />
            <div class="description">
              <h1>Effective Inventory Management:</h1>
              <p>
                <li>
                  Track and manage hospital inventory, ensuring that essential supplies are always available.
                </li>
              </p>
            </div>
          </div>
        </div>
      </section>

      <section class="contactus" id="contactus">
        <div class="container">
          <div class="contactus-content">
            <h1 data-aos="fade-left" data-aos-duration="1000">Visit Us Today</h1>
            <p data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
              Visit our hospital to experience the benefits of our management system firsthand. We're open 24 hours and ready to assist you.
            </p>
          </div>
          <div class="contactus-content">
            <h1 data-aos="fade-left" data-aos-duration="1000">Get in Touch</h1>
            <p data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
              We'd love to hear from you! Reach out to us for any inquiries or feedback.
            </p>
            <ul class="contact-info">
              <li style="margin-bottom: 1em;">
                <ion-icon name="location-outline" class="contact-icon" style="margin-right: 1em;"></ion-icon>
                <span>Hospital Management, Main Street, City, Country</span>
              </li>
              <li style="margin-bottom: 1em;">
                <ion-icon name="call-outline" class="contact-icon" style="margin-right: 1em;"></ion-icon>
                <span>+123 456 7890</span>
              </li>
              <li>
                <ion-icon name="mail-outline" class="contact-icon" style="margin-right: 1em;"></ion-icon>
                <span>contact@hospitalmanagement.com</span>
              </li>
            </ul>
          </div>
        </div>
      </section>
    </div>
    <footer class="footer">
      <p>&copy; 2024 Hospital Management. All Rights Reserved.</p>
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
  </body>
</html>
