<?php 
session_start(); // Start session

// Check if user is authenticated
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header("Location: login.html"); // Redirect to login page if not authenticated
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <link rel="stylesheet" href="css/search.css">
  </head>
  <body>
    <?php include_once "header.html"; ?>

    <button class="chatbot-toggler">
      <a href="users.php"><i class="bi bi-chat-left" style="color: white; font-size: 24px;"></i></a>  
    </button>
<br><button class="emergency-toggler">
    <a href="emergency.php"><i class="bi bi-exclamation-triangle-fill" style="color: white;font-size: 24px;"></i></a>
</button>

    <section class="portfolio" id="portfolio">
      <h2>Our Services</h2>
      <p>From cozy homes to bustling offices, FixIt's expertise in plumbing, electrical, HVAC, gardening, and more transforms every space into a haven of comfort and functionality.</p>
      <div class="wrapper">
        <div class="search-box">
          <i class="bx bx-search"></i>
          <input type="text" placeholder="Search for a service" />
          <div class="icon"><i class="fas fa-search"></i></div>
        </div>
      </div>
      <ul class="cards"  id="services-list">
      </ul>
    </section>


    <script src="javascript/script.js"></script>
    <!-- JavaScript to fetch data from API and populate services -->
    <script>
       fetch('../api/services.php')
.then(response => response.json())
.then(data => {
    console.log('Fetched services:', data); // Add this line to log the fetched data

    if (!Array.isArray(data)) {
        console.error('Expected an array but got:', data);
        return;
    }

    const servicesContainer = document.createElement('ul');
    servicesContainer.className = 'cards';

    data.forEach(service => {
        const li = document.createElement('li');
        li.className = 'card';
        li.setAttribute('data-name', service.title);
        // Inside the data.forEach loop where you generate service cards
        li.innerHTML = `
            <img src="images/${service.images}" alt="${service.title}">
            <h3>${service.title}</h3>
            <p>${service.description}</p>
            <a href="list_workers.php?service_id=${encodeURIComponent(service.service_id)}"><button class="btn btn2">Book Now</button></a>
        `;

        servicesContainer.appendChild(li);
    });

    document.getElementById('services-list').appendChild(servicesContainer);

    const search = document.querySelector(".search-box input");
    const cards = document.querySelectorAll(".card");

    search.addEventListener("keyup", () => {
        const searchValue = search.value.trim().toLowerCase();

        cards.forEach(card => {
            const cardName = card.getAttribute("data-name").toLowerCase();
            const cardElement = card.closest('.card');

            if (cardName.includes(searchValue)) {
                cardElement.style.display = "block";
            } else {
                cardElement.style.display = "none";
            }
        });
    });

    search.addEventListener("keyup", () => {
        if (search.value === "") {
            cards.forEach(card => {
                card.closest('.card').style.display = "block";
            });
        }
    });
})
.catch(error => console.error('Error fetching services:', error));

    </script>
  </body>
</html>
