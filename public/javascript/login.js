document.addEventListener("DOMContentLoaded", function() {
    const loginForm = document.getElementById("loginForm");
    const loginButton = document.querySelector(".btn.solid");
    const errorText = document.querySelector(".error-text");

    if (loginForm) {
        loginForm.addEventListener("submit", async function(event) {
            event.preventDefault();
            
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value.trim();

            if (!email || !password) {
                errorText.textContent = "Please fill in all fields.";
                return;
            }

            loginButton.disabled = true;
            loginButton.value = "Logging in...";

            try {
                const response = await fetch("../api/login.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ email, password })
                });

                const result = await response.json();
                console.log(result);

                if (response.ok) {
                    if (result.message === "Login successful!") {
                        window.location.href = result.usertype === "worker" ? "worker_home.php" : "home.php";
                    } else {
                        errorText.textContent = result.message;
                    }
                } else {
                    errorText.textContent = "Passowrd or Email dont match !";
                }
            } catch (error) {
                console.error(error);
                errorText.textContent = "Network error. Please check your connection.";
            } finally {
                loginButton.disabled = false;
                loginButton.value = "Login";
            }
        });
    }
});
