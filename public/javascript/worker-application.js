document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const form = document.getElementById('workerApplicationForm');
    const sections = document.querySelectorAll('.form-section');
    const progressBar = document.getElementById('progressBar');
    const stepIndicator = document.getElementById('stepIndicator');
    const nextBtn1 = document.getElementById('nextBtn1');
    const nextBtn2 = document.getElementById('nextBtn2');
    const prevBtn1 = document.getElementById('prevBtn1');
    const prevBtn2 = document.getElementById('prevBtn2');
    const submitBtn = document.getElementById('submitBtn');
    const successMessage = document.getElementById('successMessage');
    const dobInput = document.getElementById('dob');

    // File Upload Elements
    const pdfUpload = document.getElementById('pdfUpload');
    const imageUpload = document.getElementById('imageUpload');
    const pdfInput = document.getElementById('pdf');
    const imageInput = document.getElementById('image');
    const pdfPreview = document.getElementById('pdfPreview');
    const imagePreview = document.getElementById('imagePreview');

    // Current step tracking
    let currentStep = 0;
    const totalSteps = 3;

    // Initialize form
    updateProgress();
    setupFileUploads();
    setupDatePicker();

    // Navigation functions
    function goToStep(step) {
        sections.forEach(section => section.classList.remove('active'));
        currentStep = step;
        sections[currentStep].classList.add('active');
        updateProgress();
    }

    function updateProgress() {
        const progress = (currentStep / totalSteps) * 100;
        progressBar.style.width = `${progress}%`;
        stepIndicator.textContent = `Step ${currentStep + 1} of ${totalSteps}`;
    }

    function setupDatePicker() {
        const today = new Date();
        const currentYear = today.getFullYear();
        const minDate = new Date();
        minDate.setFullYear(currentYear - 100);
        const maxDate = new Date();
        maxDate.setFullYear(currentYear - 20);
        maxDate.setDate(maxDate.getDate() - 1);
        
        dobInput.min = minDate.toISOString().split('T')[0];
        dobInput.max = maxDate.toISOString().split('T')[0];
        dobInput.addEventListener('change', validateDOB);
    }

    function validateDOB() {
        const dobError = document.getElementById('dobError');
        const dateString = this.value;
        
        if (!dateString) {
            dobError.textContent = 'Please select your date of birth';
            dobError.classList.add('active');
            return false;
        }
        
        const selectedDate = new Date(dateString);
        const maxDate = new Date(dobInput.max);
        
        if (selectedDate > maxDate) {
            dobError.textContent = `You must be at least 20 years old`;
            dobError.classList.add('active');
            return false;
        }
        
        dobError.classList.remove('active');
        return true;
    }

    // Validation functions
    function validateSection1() {
        let isValid = true;
        const fname = document.getElementById('fname').value.trim();
        const lname = document.getElementById('lname').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const dob = document.getElementById('dob').value;
        const address = document.getElementById('address').value.trim();
        const region = document.getElementById('region').value;

        document.querySelectorAll('#section1 .error-message').forEach(el => {
            el.classList.remove('active');
        });

        if (fname === '') {
            document.getElementById('nameError').classList.add('active');
            isValid = false;
        }
        if (lname === '') {
            document.getElementById('nameError').classList.add('active');
            isValid = false;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            document.getElementById('emailError').classList.add('active');
            isValid = false;
        }
        if (phone === '' || phone.length < 8) {
            document.getElementById('phoneError').classList.add('active');
            isValid = false;
        }
        if (dob === '') {
            document.getElementById('dobError').classList.add('active');
            isValid = false;
        }
        if (address === '') {
            document.getElementById('addressError').classList.add('active');
            isValid = false;
        }
        if (region === '') {
            document.getElementById('regionError').classList.add('active');
            isValid = false;
        }

        return isValid;
    }

    function validateSection2() {
        let isValid = true;
        const service_id = document.getElementById('service_id').value;
        const experience = document.getElementById('experience').value;
        const fees = document.getElementById('fees').value;

        document.querySelectorAll('#section2 .error-message').forEach(el => {
            el.classList.remove('active');
        });

        if (service_id === '') {
            document.getElementById('professionError').classList.add('active');
            isValid = false;
        }
        if (experience === '' || isNaN(experience) || experience < 0) {
            document.getElementById('experienceError').classList.add('active');
            isValid = false;
        }
        if (fees === '') {
            document.getElementById('feesError').classList.add('active');
            isValid = false;
        }

        return isValid;
    }

    function validateSection3() {
        let isValid = true;
        const pdf = pdfInput.files[0];
        const image = imageInput.files[0];
        const agreeTerms = document.getElementById('agreeTerms').checked;

        document.querySelectorAll('#section3 .error-message').forEach(el => {
            el.classList.remove('active');
        });

        if (!pdf) {
            document.getElementById('pdfError').classList.add('active');
            isValid = false;
        } else if (pdf.size > 5 * 1024 * 1024) {
            document.getElementById('pdfError').textContent = 'File size exceeds 5MB limit';
            document.getElementById('pdfError').classList.add('active');
            isValid = false;
        }

        if (!image) {
            document.getElementById('imageError').classList.add('active');
            isValid = false;
        } else if (image.size > 5 * 1024 * 1024) {
            document.getElementById('imageError').textContent = 'File size exceeds 5MB limit';
            document.getElementById('imageError').classList.add('active');
            isValid = false;
        }

        if (!agreeTerms) {
            document.getElementById('termsError').classList.add('active');
            isValid = false;
        }

        return isValid;
    }

    // File upload handling
    function setupFileUploads() {
        pdfUpload.addEventListener('click', () => pdfInput.click());
        pdfInput.addEventListener('change', () => handleFileUpload(pdfInput, pdfPreview, 'pdf'));

        imageUpload.addEventListener('click', () => imageInput.click());
        imageInput.addEventListener('change', () => handleFileUpload(imageInput, imagePreview, 'image'));
    }

    function handleFileUpload(input, previewContainer, type) {
        const files = input.files;
        if (!files || files.length === 0) return;

        previewContainer.innerHTML = '';
        previewContainer.classList.add('active');
        createFilePreview(files[0], previewContainer, type);
    }

    function createFilePreview(file, container, type) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        
        const fileIcon = document.createElement('div');
        fileIcon.className = 'file-icon';
        fileIcon.innerHTML = type === 'pdf' ? '<i class="fas fa-file-pdf"></i>' : '<i class="fas fa-image"></i>';
        
        const fileInfo = document.createElement('div');
        fileInfo.className = 'file-info';
        
        const fileName = document.createElement('div');
        fileName.className = 'file-name';
        fileName.textContent = file.name.length > 20 ? file.name.substring(0, 17) + '...' : file.name;
        
        const fileSize = document.createElement('div');
        fileSize.className = 'file-size';
        fileSize.textContent = formatFileSize(file.size);
        
        const fileRemove = document.createElement('div');
        fileRemove.className = 'file-remove';
        fileRemove.innerHTML = '<i class="fas fa-times"></i>';
        fileRemove.addEventListener('click', (e) => {
            e.stopPropagation();
            container.removeChild(fileItem);
            if (container.children.length === 0) {
                container.classList.remove('active');
            }
            if (type === 'pdf') {
                pdfInput.value = '';
            } else {
                imageInput.value = '';
            }
        });
        
        fileInfo.appendChild(fileName);
        fileInfo.appendChild(fileSize);
        fileItem.appendChild(fileIcon);
        fileItem.appendChild(fileInfo);
        fileItem.appendChild(fileRemove);
        container.appendChild(fileItem);
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Navigation event listeners
    nextBtn1.addEventListener('click', () => {
        if (validateSection1()) {
            goToStep(1);
        }
    });

    nextBtn2.addEventListener('click', () => {
        if (validateSection2()) {
            goToStep(2);
        }
    });

    prevBtn1.addEventListener('click', () => goToStep(0));
    prevBtn2.addEventListener('click', () => goToStep(1));

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validateSection3()) return;
    
        const formData = new FormData(form);
        formData.append('password', generateTempPassword());
    
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
        try {
            const response = await fetch('php/worker.php', {
                method: 'POST',
                body: formData
            });
    
            // First check if the response is OK (status 200-299)
            if (!response.ok) {
                throw new Error(`Server returned ${response.status}`);
            }
    
            // Then try to parse as JSON
            const result = await response.json();
    
            // Check if the JSON parsing was successful
            if (typeof result !== 'object' || result === null) {
                throw new Error('Invalid server response');
            }
    
            if (result.success) {
                sections[currentStep].classList.remove('active');
                successMessage.classList.add('active');
            } else {
                throw new Error(result.message || 'Registration failed');
            }
        } catch (error) {
            console.error('Submission error:', error);
            alert(error.message || 'An error occurred. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Application';
        }
    });

    function generateTempPassword() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let password = '';
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return password;
    }

    // Drag and drop functionality
    function setupDragAndDrop(dropZone, input) {
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#4361ee';
            dropZone.style.backgroundColor = '#e9ecef';
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.style.borderColor = '#ced4da';
            dropZone.style.backgroundColor = '#f8f9fa';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#ced4da';
            dropZone.style.backgroundColor = '#f8f9fa';
            
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                const event = new Event('change');
                input.dispatchEvent(event);
            }
        });
    }

    // Initialize drag and drop
    setupDragAndDrop(pdfUpload, pdfInput);
    setupDragAndDrop(imageUpload, imageInput);
});