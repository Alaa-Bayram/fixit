document.addEventListener('DOMContentLoaded', function () {
    const dateElement = document.getElementById('date');
    const scheduleElement = document.getElementById('schedule');
    const prevDayButton = document.getElementById('prev-day');
    const nextDayButton = document.getElementById('next-day');
    const printButton = document.getElementById('print-schedule');
    const totalCostElement = document.getElementById('total-cost');

    let currentDate = new Date();
    let totalCost = 0;

    function updateDate() {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateElement.textContent = currentDate.toLocaleDateString(undefined, options);
    }

    function fetchSchedule(date) {
        const formattedDate = date.getFullYear() + '-' +
        String(date.getMonth() + 1).padStart(2, '0') + '-' +
        String(date.getDate()).padStart(2, '0');

        const workerId = '<?php echo $worker_id; ?>'; // Get worker ID from PHP
        
        // Construct the URL with both date and worker_id parameters
        const url = `../api/fetch_worker_schedule.php?date=${formattedDate}&worker_id=${workerId}`;
        
        console.log('Request URL:', url); // Log the URL for debugging
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(schedule => {
                populateSchedule(schedule);
            })
            .catch(error => {
                console.error('Error fetching schedule:', error);
            });
    }
    

function updateAppointmentStatus(id, type, isDone, cost) {
    console.log("Sending update:", {id, type, isDone, cost});
    
    // Validate cost
    cost = parseFloat(cost);
    if (isNaN(cost) || cost < 0 || cost % 0.5 !== 0) {
        alert('Please enter a valid cost (0.5, 1, 1.5, 2, etc.)');
        return;
    }

    // Create form data
    const formData = new FormData();
    formData.append('id', id);
    formData.append('type', type);
    formData.append('is_done', isDone ? '1' : '0');
    formData.append('cost', cost);

    // Add error handling for the response
    fetch('../api/update_appointment_sts.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json(); // Changed from text() to json()
    })
    .then(data => {
        console.log("Server response:", data);
        if (data.error) {
            alert('Error: ' + data.error);
        } else {
            fetchSchedule(currentDate); // Reload schedule after successful update
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred: ' + error.message);
    });
}
    
    function populateSchedule(schedule) {
        scheduleElement.innerHTML = '';
        totalCost = 0; // Reset totalCost
    
        if (schedule.length === 0) {
            const noAppointmentsDiv = document.createElement('div');
            noAppointmentsDiv.className = 'no-appointments';
            noAppointmentsDiv.textContent = 'No appointments or emergencies for today';
            scheduleElement.appendChild(noAppointmentsDiv);
    
            // Display total cost as 0 when there are no appointments
            totalCostElement.textContent = `Total Cost: $${totalCost.toFixed(2)}`;
            return;
        }
    
        function compareTime(a, b) {
            const timeA = a.time.split(':');
            const timeB = b.time.split(':');
            const hourA = parseInt(timeA[0], 10);
            const hourB = parseInt(timeB[0], 10);
            const minuteA = parseInt(timeA[1], 10);
            const minuteB = parseInt(timeB[1], 10);
    
            if (hourA !== hourB) {
                return hourA - hourB;
            } else {
                return minuteA - minuteB;
            }
        }
    
        schedule.sort(compareTime);
    
        const groupedSchedule = {};
    
        schedule.forEach(item => {
            const [eventHour, eventMinute] = item.time.split(':').map(Number);
            const eventMinuteRounded = Math.floor(eventMinute / 5) * 5;
            const timeSlot = `${eventHour}-${eventMinuteRounded < 10 ? '0' + eventMinuteRounded : eventMinuteRounded}`;
            if (!groupedSchedule[timeSlot]) {
                groupedSchedule[timeSlot] = [];
            }

            groupedSchedule[timeSlot].push(item);
        });
    
        Object.keys(groupedSchedule).forEach(timeSlot => {
            const [hour, minute] = timeSlot.split('-');
            const timeString = `${hour % 12 || 12}:${minute} ${hour < 12 ? 'AM' : 'PM'}`;
    
            const timeDiv = document.createElement('div');
            timeDiv.className = 'time-slot';
            timeDiv.textContent = timeString;
            scheduleElement.appendChild(timeDiv);
    
            groupedSchedule[timeSlot].forEach(item => {
                const eventDiv = document.createElement('div');
                eventDiv.className = 'event';
                let eventContent = '';
    
                if (item.type === 'emergency') {
                    eventContent = `Emergency: ${item.title || ''}<br>Client Name: ${item.client_fname} ${item.client_lname}<br>Address: ${item.address}<br>Phone: ${item.phone}`;
                } else if (item.type === 'appointment') {
                    eventContent = `Appointment: ${item.client_fname} ${item.client_lname}<br>Address: ${item.client_address}<br>Phone: ${item.client_phone}`;
                }
    
                eventDiv.innerHTML = eventContent;
                eventDiv.style.color = item.type === 'emergency' ? 'white' : 'white';
                eventDiv.style.backgroundColor = item.type === 'emergency' ? '#f68c5e' : '#79bcb1';
                scheduleElement.appendChild(eventDiv);
    
                const notesDiv = document.createElement('div');
                notesDiv.className = 'notes';
    
                const notesTextarea = document.createElement('textarea');
                notesTextarea.className = 'notes-input';
                notesTextarea.placeholder = 'Enter notes here...';
                notesTextarea.value = item.description || '';
    
                notesDiv.appendChild(notesTextarea);
                scheduleElement.appendChild(notesDiv);
    
                const costDiv = document.createElement('div');
                costDiv.className = 'cost-column';
    
                const costLabel = document.createElement('label');
                costLabel.textContent = 'Cost: ';
    
                const costInput = document.createElement('input');
                costInput.type = 'number';
                costInput.step = '0.5';
                costInput.min = '0';
                costInput.value = item.cost || '';
                
                // Disable cost input if appointment is done
                if (item.is_done) {
                    costInput.disabled = true;
                } else {
                    costInput.addEventListener('change', function () {
                        updateAppointmentStatus(item.id, item.type, item.is_done, costInput.value);
                    });
                }
    
                costLabel.appendChild(costInput);
                costDiv.appendChild(costLabel);
                scheduleElement.appendChild(costDiv);
    
                const doneDiv = document.createElement('div');
                doneDiv.className = 'done-column';
    
                const checkboxLabel = document.createElement('label');
                checkboxLabel.textContent = 'Done: ';
    
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.checked = item.is_done;
                checkbox.addEventListener('change', function () {
                    // Disable cost input when checkbox is checked
                    costInput.disabled = checkbox.checked;
                    updateAppointmentStatus(item.id, item.type, checkbox.checked, costInput.value);
                });
    
                checkboxLabel.appendChild(checkbox);
                doneDiv.appendChild(checkboxLabel);
                scheduleElement.appendChild(doneDiv);
    
                // Calculate and add to total cost if appointment is done
                if (item.is_done && !isNaN(parseFloat(item.cost))) {
                    totalCost += parseFloat(item.cost);
                }
            });
        });
    
        // Display total cost
        totalCostElement.textContent = `Total Earning: $${totalCost.toFixed(2)}`;
    }
    

    prevDayButton.addEventListener('click', function () {
        currentDate.setDate(currentDate.getDate() - 1);
        updateDate();
        fetchSchedule(currentDate);
    });

    nextDayButton.addEventListener('click', function () {
        currentDate.setDate(currentDate.getDate() + 1);
        updateDate();
        fetchSchedule(currentDate);
    });

    printButton.addEventListener('click', function () {
        window.print();
    });

    updateDate();
    fetchSchedule(currentDate);
});
