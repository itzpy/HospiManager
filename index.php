<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
  <link rel="stylesheet" href="./assets/css/index.css" />
  <link rel="shortcut icon" href="../EVPH-Project/assets/images/favicon.ico" type="image/x-icon">


  <title>Hospi Manager | Home</title>
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
            <li><a href="#aboutus" class="link">About Us</a></li>
            <li><a href="#contactus" class="link">Contact Us</a></li>
          </ul>
        </div>
        <div class="nav-button">
          <a href="./view/login.php">
            <button class="btn white-btn animate-zoom-in duration-1000" id="loginBtn">
              Log In
            </button>
          </a>
          <a href="./view/register.php">
            <button class="btn animate-zoom-in duration-1000" id="signupBtn">
              Sign Up
            </button>
          </a>
        </div>
    </nav>

    <!-- Landing Page -->
    <section class="landingpage">
      <div class="content">
        <div class="heading" data-aos="fade-right" data-aos-duration="2000">
          <h1>Welcome to Hospi Manager</h1>
          <p>
            Hospi Manager is a hospital management system that helps hospital staff to keep track of their
            inventory, and monitor their activities. It is an efficient and
            user-friendly system that helps to streamline hospital operations and makes sure that they properly monitor their inventory.
          </p>
          <p>
            We are here to make your life easier. Our platform is designed to make it easy for you to manage your hospital's inventory and monitor your activities. We understand that managing a hospital can be a daunting task, and that is why we have created this platform to help you.
          </p>

          <div class="button-container">
            <a href="./view/register.php"><button class="btn" data-aos="fade-left"
                data-aos-duration="2000" data-aos-delay="00" data-aos-margin="">
                Get Started
              </button></a>
          </div>
        </div>

    </section>

    <section class="aboutus" id="aboutus">
      <div class="container">
        <img src="./assets/images/hospital.jpg" data-aos="zoom-in-" data-aos-duration="2000" />
        <div class="aboutus-content">
          <h1 data-aos="fade-left" data-aos-duration="1000">About us</h1>
          <p data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
            At Hospi Manager Headquarters, we believe that managing a hospital's inventory and
            activities should be easy and efficient. Our online platform is
            designed to make it easy for you to keep track of your inventory and
            monitor your activities. With our user-friendly interface and
            efficient system, you can rest assured that your hospital is running
            smoothly.
          </p>
        </div>
      </div>
    </section>

    <section class="details" id="details">
      <div class="title">
        <h1 data-aos="zoom-in-up" data-aos-duration="1000">
          Why Choose Hospi Manager? </h1>
      </div>
      <div class="details-content">
        <div class="card" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
          <img src="./assets/images/inventory_management.jpg" alt="Hospi Manager Inventory" />
          <div class="description">
            <h1>Efficient Inventory Management:</h1>
            <p>
              <li>
                Keep track of your hospital's inventory with ease. Our system is designed to help you monitor your inventory and receive notifications when items are running low.
              </li>
            </p>
          </div>
        </div>
        <div class="card" data-aos="zoom-in" data-aos-duration="1000" data-aos-delay="200">
          <img src="./assets/images/hospital_activities.jpg" alt="Hospi Manager Activities" />
          <div class="description">
            <h1>Monitor Your Activities:</h1>
            <p>
              <li>
                With our system, you can monitor your activities and receive notifications when something is about to happen.
              </li>
            </p>
          </div>
        </div>
        <div class="card" data-aos="zoom-in" data-aos-duration="1000" data-aos-delay="400">
          <img src="./assets/images/user-friendly.jpg" alt="Hospi Manager User Friendly" />
          <div class="description">
            <h1>User-Friendly Interface:</h1>
            <p>
              <li>
                Our system is designed to be user-friendly and easy to use. You don't have to be a tech expert to use it.
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
            Take the first step towards efficient hospital management with
            Hospi Manager. We're open 24 hours and ready to help you manage your
            hospital's inventory and activities with ease.
          </p>
        </div>
        <div class="contactus-content">
          <h1 data-aos="fade-left" data-aos-duration="1000">Get in Touch</h1>
          <p data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
            We'd love to hear from you! Reach out to us for any inquiries or
            feedback, or simply to say hello.
          </p>
          <ul class="contact-info">
            <li style="margin-bottom: 1em;">
              <ion-icon name="location-outline" class="contact-icon" style="margin-right: 1em;"></ion-icon>
              <span>Hospi Manager, Ashesi University, Berekuso, Ghana</span>
            </li>
            <li style="margin-bottom: 1em;">
              <ion-icon name="call-outline" class="contact-icon" style="margin-right: 1em;"></ion-icon>
              <span>+233 244 122 000</span>
            </li>
            <li>
              <ion-icon name="mail-outline" class="contact-icon" style="margin-right: 1em;"></ion-icon>
              <span>hospimangergh@gmail.com</span>
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