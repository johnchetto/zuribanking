<?php
include 'db_connect.php';
$images = [];
$result = $conn->query("SELECT image_path, caption FROM images");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="CSS_styling/landing.css">
    <style>
  /* ✅ Container spans full width but remains elegant */
/* Full-width slider */
.slider {
  width: 100vw; /* full viewport width */
  max-width: 100%; /* never exceed screen width */
  height: 60vh; /* adjust height */
  max-height: 500px;
  min-height: 300px;
  overflow: hidden;
  position: relative;
  margin: 0; /* remove extra margins */
}

.slider img {
  width: 100%;
  height: 100%;
  object-fit: cover; /* cover entire container */
  display: block;
  transition: opacity 1s ease-in-out;
}

/* Caption styling */
#slider-caption {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  text-align: center;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
  color: #fff;
  padding: 15px 10px;
  font-size: 1.2rem;
  font-weight: 500;
  letter-spacing: 0.5px;
}

/* Fade animation */
#slider-image.fade-out,
#slider-caption.fade-out {
  opacity: 0;
  transition: opacity 1s ease-in-out;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .slider {
    height: 40vh;
  }

  #slider-caption {
    font-size: 1rem;
    padding: 10px;
  }
}

@media (max-width: 480px) {
  #slider-caption {
    font-size: 0.9rem;
  }
}

</style>

  </head>

  <body>
    <header role="banner" class="site-header">
      <nav aria-label="Utility Navigation">
        <p class="badge-text">Secure &middot; Fast &middot; Reliable</p>
      </nav>
    </header>

    <main role="main" class="hero-section">
      <section class="hero-content" aria-labelledby="main-heading">
        <h1 id="main-heading">
          Welcome to Zuri Online Banking
          <span class="highlight">Management</span> System
        </h1>

        <p class="description">
          Experience seamless Zuri online banking management with real-time
          tracking, smart automation and secure access.
        </p>

        <!-- ✅ Image Slider -->
<div class="slider">
  <?php if (!empty($images)): ?>
      <img id="slider-image" 
           src="<?php echo htmlspecialchars($images[0]['image_path']); ?>" 
           alt="<?php echo htmlspecialchars($images[0]['caption']); ?>">
      <p id="slider-caption"><?php echo htmlspecialchars($images[0]['caption']); ?></p>
  <?php else: ?>
      <img id="slider-image" src="images/placeholder.jpg" alt="No images available">
      <p id="slider-caption">No images to display</p>
  <?php endif; ?>
</div>

<!-- ⭐ ADD THE HAPPY USERS SECTION RIGHT HERE -->
<section class="happy-users">
    <h2 class="section-title">What Our Users Are Saying</h2>

    <div class="users-row">

        <div class="user-card">
            <img src="img/user1.jpg" alt="Happy rural user 1">
            <p>Zuri Online Banking will make saving and sending money easier even from my rural home.</p>
        </div>

        <div class="user-card">
            <img src="img/user2.jpg" alt="Happy rural user 2">
            <p>My business will grow because payments will now be fast and secure with Zuri</p>
        </div>

        <div class="user-card">
            <img src="img/user3.jpg" alt="Happy rural user 3">
            <p>Finally digital banking has reached us. Zuri gives me freedom and convenience.</p>
        </div>

    </div>
</section>

        <div class="call-to-action-group">
          <a href="signup.php">Get Started</a>
          <a href="login.php">Sign In</a>
        </div>
      </section>
      <br><br>

      <!-- ✅ Features Section -->
      <section role="region" class="feature-layout">
        <div class="feature-grid" aria-label="Zuri Banking Features">
          <h2 style="color: red;">Core Features Overview</h2>

          <article class="feature-card">
            <h3>Two-Factor Authentication</h3>
            <p>
              Advanced security with OTP verification to protect your account
              from unauthorized access.
            </p>
          </article>

          <article class="feature-card">
            <!-- Fixed invalid tag -->
            <h3 style="color: red;">Instant Transfers</h3>
            <p>
              Move your money instantly between accounts with zero delays, 24/7.
            </p>
          </article>

          <article class="feature-card">
            <h3>Bank-Level Security</h3>
            <p>
              Your data is protected by military-grade encryption and constant
              fraud monitoring.
            </p>
          </article>

          <article class="feature-card">
            <h3>User-Friendly Interface</h3>
            <p>
              A clean, intuitive design makes managing your finances simple on
              any device.
            </p>
          </article>

          <article class="feature-card">
            <h3>Transaction Tracking</h3>
            <p>
              Monitor and categorize every transaction in real-time for better
              budgeting.
            </p>
          </article>

          <article class="feature-card">
            <h3>24/7 Accessibility</h3>
            <p>
              Manage your bank accounts anytime, anywhere, across all devices.
            </p>
          </article>
        </div>

        <div class="cta-section">
          <h1>Ready to Get Started?</h1>
          <p>
            Join thousands of users who trust Zuri for their online banking
            needs. Create your account today and experience the future of
            banking.
          </p>
          <a href="signup.php">Create Your Account</a>
        </div>
      </section>
    </main>
    <section id="about-hero">
    <div class="hero-container">
        <h2>About Us</h2>
        <p>
            Zuri Online Banking Management System provides fast, secure, and reliable access to your financial services. 
            We simplify online banking with modern digital tools, ensuring a smooth experience across all devices.
        </p>
    </div>

    <div class="contact-container">
        <h2>Contact Us</h2>
        <p><strong>Phone:</strong> 0791575532</p>
        <a href="https://wa.me/254791575532" target="_blank" class="whatsapp-link">
            <img src="img/whatsapp.jpg" alt="WhatsApp"> Chat on WhatsApp
        </a>
    </div>
</section>


    <footer>
      <p>
        &copy; 2025 Zuri Online Banking Management System | All Rights Reserved.
      </p>
    </footer>

    <script>
      let images = <?php echo json_encode($images); ?>;
      if (images.length > 0) {
          let index = 0;
          const imageTag = document.getElementById('slider-image');
          const captionTag = document.getElementById('slider-caption');

          setInterval(() => {
              // fade out
              imageTag.classList.add('fade-out');
              captionTag.classList.add('fade-out');

              setTimeout(() => {
                  index = (index + 1) % images.length;
                  imageTag.src = images[index].image_path;
                  captionTag.textContent = images[index].caption;

                  // fade in
                  imageTag.classList.remove('fade-out');
                  captionTag.classList.remove('fade-out');
              }, 1000); // must match CSS transition time
          }, 4000); // 4 seconds per slide
      }
    </script>

  </body>
</html>
