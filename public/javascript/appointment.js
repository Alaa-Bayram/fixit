document.addEventListener("DOMContentLoaded", function () {
    const showPopupButtons = document.querySelectorAll(".take-appointment-btn");
    const formPopup = document.querySelector(".form-popup");
    const blurOverlay = document.querySelector(".blur-bg-overlay");
    const closePopupBtn = formPopup.querySelector(".close-btn");
    const workerNameField = document.getElementById("worker-name");
    const workerIdField = document.getElementById("worker-id");
    const appointmentDateInput = document.getElementById("appointment-date");
    const appointmentTimeInput = document.getElementById("appointment-time");

    // Set the minimum date to today's date
    const today = new Date().toISOString().split('T')[0];
    if (appointmentDateInput) {
        appointmentDateInput.setAttribute('min', today);
    }

    if (appointmentTimeInput) {
        const times = [
            '07:00', '07:15', '07:30', '07:45',
            '08:00', '08:15', '08:30', '08:45',
            '09:00', '09:15', '09:30', '09:45',
            '10:00', '10:15', '10:30', '10:45',
            '11:00', '11:15', '11:30', '11:45',
            '12:00', '12:15', '12:30', '12:45',
            '13:00', '13:15', '13:30', '13:45',
            '14:00', '14:15', '14:30', '14:45',
            '15:00', '15:15', '15:30', '15:45',
            '16:00', '16:15', '16:30', '16:45',
            '17:00', '17:15', '17:30', '17:45',
            '18:00', '18:15', '18:30', '18:45',
            '19:00', '19:15', '19:30', '19:45',
            '20:00'
        ]; // Times from 07:00 to 20:00 in 15-minute intervals

        appointmentTimeInput.addEventListener('focus', function() {
            appointmentTimeInput.innerHTML = ''; // Clear previous options

            times.forEach(time => {
                const option = document.createElement('option');
                option.value = time;
                option.textContent = time;
                appointmentTimeInput.appendChild(option);
            });
        });
    }

    if (showPopupButtons && formPopup && blurOverlay && closePopupBtn && workerNameField && workerIdField) {
        showPopupButtons.forEach(button => {
            button.addEventListener("click", (e) => {
                e.preventDefault();
                const workerName = button.getAttribute("data-worker-name");
                const workerId = button.getAttribute("data-worker-id");

                if (workerName && workerId) {
                    workerNameField.textContent = workerName;
                    workerIdField.value = workerId;
                    document.body.classList.add("show-popup");
                } else {
                    console.error("Worker name or ID not found in button attributes");
                }
            });
        });

        closePopupBtn.addEventListener("click", () => {
            document.body.classList.remove("show-popup");
        });

        blurOverlay.addEventListener("click", () => {
            document.body.classList.remove("show-popup");
        });

        const appointmentForm = document.getElementById("appointment-form");
        if (appointmentForm) {
            appointmentForm.addEventListener("submit", function (e) {
                e.preventDefault();
                const formData = new FormData(appointmentForm);

                fetch('../api/schedule_appointment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Appointment scheduled successfully!');
                        document.body.classList.remove("show-popup");
                        appointmentForm.reset(); // Reset form after successful submission
                    } else {
                        alert('Failed to schedule appointment: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error scheduling appointment:', error);
                    alert('An error occurred. Please try again.');
                });
            });
        } else {
            console.error("Appointment form not found");
        }
    } else {
        console.error("One or more required elements not found");
    }
});
