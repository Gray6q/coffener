// JavaScript pentru Hamburger Menu
      document.addEventListener("DOMContentLoaded", function () {
        const hamburger = document.getElementById("hamburger");
        const mobileMenu = document.getElementById("mobileMenu");
        const overlay = document.getElementById("overlay");
        const mobileLinks = document.querySelectorAll(".header__mobile-link");

        // Toggle mobile menu
        function toggleMobileMenu() {
          hamburger.classList.toggle("active");
          mobileMenu.classList.toggle("active");
          overlay.classList.toggle("active");
          document.body.style.overflow = mobileMenu.classList.contains("active") ? "hidden" : "auto";
        }

        // Close mobile menu
        function closeMobileMenu() {
          hamburger.classList.remove("active");
          mobileMenu.classList.remove("active");
          overlay.classList.remove("active");
          document.body.style.overflow = "auto";
        }

        // Event listeners
        hamburger.addEventListener("click", toggleMobileMenu);
        overlay.addEventListener("click", closeMobileMenu);

        // Close menu when clicking on mobile links
        mobileLinks.forEach((link) => {
          link.addEventListener("click", closeMobileMenu);
        });

        // Close menu on escape key
        document.addEventListener("keydown", function (e) {
          if (e.key === "Escape") {
            closeMobileMenu();
          }
        });
      });