<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="author" content="Papa Yaw Badu" />
    <title>Recipe Feed</title>
    <link rel="stylesheet" href="../assets/css/styles1.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap"
      rel="stylesheet"
    />
  </head>
  <body>
    <header>
      <h1>Explore Recipes</h1>
      <nav>
        <ul>
          <li><a href="..\index.php">Home</a></li>
          <!-- Link to Landing Page -->
          <li><a href="login.php">Login</a></li>
          <!-- Link to Login Page -->
          <li><a href="register.php">Sign Up</a></li>
          <!-- Link to Signup Page -->
          <!-- <li><a href="./admin/dashboard.php">Dashboard</a></li>
          Link to the Dashboard -->
        </ul>
      </nav>
    </header>
    <main>
      <section class="search">
        <input type="text" id="search" placeholder="Search recipes..." />
      </section>

      <section class="recipe-grid">
        <div class="recipe-card">
          <img
            src="../assets/images/Ghana Jollof.jpg"
            alt="Delicious Ghana Jollof Rice"
          />
          <h2>Ghana Jollof</h2>
          <p>This is the best Jollof you can possibly try in Africa</p>
          <p>Rating: ★★★★☆</p>
        </div>

        <div class="recipe-card">
          <img
            src="../assets/images/Tuo-zafi and beef stew.jfif"
            alt="Tuo Zafi with Beef Stew"
          />
          <h2>Tuo-zafi</h2>
          <p>This this is food originated from the North of Ghana</p>
          <p>Rating: ★★★★★</p>
        </div>

        <div class="recipe-card">
          <img
            src="../assets/images/Garden Eggs Abom and Apem-Boiled Green Plantains.jfif"
            alt="Ampesie"
          />
          <h2>Ampesie</h2>
          <p>This is one of the best Delicacies in amongst the Akan people</p>
          <p>Rating: ★★★★☆</p>
        </div>
        <div class="recipe-card">
          <img
            src="../assets/images/Fufu with Chicken Light SOUP.jfif"
            alt="Fufu"
          />
          <h2>Fufu</h2>
          <p>
            Most Ghanaians will agree that this is the best local food in the
            country
          </p>
          <p>Rating: ★★★★★</p>
        </div>
      </section>
    </main>
    <footer>
      <p>&copy; 2024 Badu's Recipes</p>
    </footer>
  </body>
</html>
