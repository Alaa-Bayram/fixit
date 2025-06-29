<?php 
  session_start();
  include_once "php/db.php";

  // Fetch services from the database
  $query = "SELECT * FROM services";
  $result = mysqli_query($conn, $query);

  // Debugging: Check if query was successful
  if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Professional Application | FIXIT</title>
  <link rel="stylesheet" href="css/worker-application.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="worker-container">
    <form id="workerApplicationForm" class="application-form" enctype="multipart/form-data">
      <div class="progress-bar" id="progressBar"></div>

      <input type="hidden" name="usertype" value="worker">
      <input type="hidden" name="access_status" value="pending">

      <div class="form-header">
        <a href="welcome.html" class="back-link"><i class="fas fa-arrow-left"></i> Back</a>
        <h1 class="title">Professional Application</h1>
        <p class="subtitle">Join our network of skilled professionals</p>
      </div>

      <!-- Step Indicator -->
      <div class="step-indicator" id="stepIndicator">Step 1 of 3</div>

      <!-- Personal Information Section -->
      <div class="form-section active" id="section1">
        <h3 class="section-title"><i class="fas fa-user-tie"></i> Personal Information</h3>
        
        <div class="form-row">
          <div class="form-group">
            <label for="fname" class="form-label">First Name</label>
            <input type="text" id="fname" name="fname" class="form-control" required>
            <div class="error-message" id="nameError">Please enter your first name</div>
          </div>
          <div class="form-group">
            <label for="lname" class="form-label">Last Name</label>
            <input type="text" id="lname" name="lname" class="form-control" required>
            <div class="error-message" id="nameError">Please enter your last name</div>
          </div>
          <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
            <div class="error-message" id="emailError">Please enter a valid email</div>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="form-control" required>
            <div class="error-message" id="phoneError">Please enter a valid phone number</div>
          </div>
          <div class="form-group">
            <label for="dob" class="form-label">Date of Birth</label>
            <input type="date" id="dob" name="dob" class="form-control" required>
            <div class="error-message" id="dobError">You must be at least 20 years old</div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="address" class="form-label">Address</label>
            <input type="text" id="address" name="address" class="form-control" required>
            <div class="error-message" id="addressError">Please enter your address</div>
          </div>

          <div class="form-group">
            <label for="region" class="form-label">Region</label>
            <select id="region" name="region" class="form-control" required>
                <option value="" disabled selected>Select region</option>
                <option value="Beirut">Beirut</option>
                <option value="Baalbek-Hermel">Baalbek-Hermel</option>
                <option value="Bekaa">Bekaa</option>
                <option value="South Lebanon">South Lebanon</option>
                <option value="Nabatieh">Nabatieh</option>
                <option value="Mount Lebanon">Mount Lebanon</option>
                <option value="North Lebanon">North Lebanon</option>
                <option value="Akkar">Akkar</option>
            </select>
            <div class="error-message" id="regionError">Please select your region</div>
          </div>
        </div>
        <div class="form-navigation">
          <button type="button" class="btn btn-outline" disabled>Previous</button>
          <button type="button" class="btn btn-primary" id="nextBtn1">Next</button>
        </div>
      </div>

      <!-- Professional Information Section -->
      <div class="form-section" id="section2">
        <h3 class="section-title"><i class="fas fa-briefcase"></i> Professional Information</h3>
        
        <div class="form-row">
          <div class="form-group">
            <label for="service_id" class="form-label">Profession</label>
            <select id="service_id" name="service_id" class="form-control" required>
                <option value="" disabled selected>Select your skill</option>
                <?php
                  while ($row = mysqli_fetch_assoc($result)) {
                    echo '<option value="' . htmlspecialchars($row['service_id']) . '">' . htmlspecialchars($row['title']) . '</option>';
                  }
                ?>
            </select>
            <div class="error-message" id="professionError">Please select your profession</div>
          </div>
          <div class="form-group">
            <label for="experience" class="form-label">Years of Experience</label>
            <input type="number" id="experience" name="experience" class="form-control" min="0" required>
            <div class="error-message" id="experienceError">Please enter your years of experience</div>
          </div>
        </div>

        <div class="form-group">
          <label for="fees" class="form-label">Choosen Plan</label>
          <p style="font-size: small; font-style: italic; color:grey">
            <a href="fees.php" target="_blank">Read More About Plans.</a>
</p>

          <select id="fees" name="fees" class="form-control" required>
          <option value="">Select your choosen plan</option>
          <option value="Starter">$50 / 6 months</option>
          <option value="Professional">$90 / 1 year</option>
          <option value="Customer Percentage">5% commission on each customer</option>

</select>

          <div class="error-message" id="feesError">Please select your choosen plan</div>
        </div>
        
        <div class="form-navigation">
          <button type="button" class="btn btn-outline" id="prevBtn1">Previous</button>
          <button type="button" class="btn btn-primary" id="nextBtn2">Next</button>
        </div>
      </div>

      <!-- Documents Section -->
      <div class="form-section" id="section3">
        <h3 class="section-title"><i class="fas fa-file-alt"></i> Documents & Verification</h3>
        
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Upload CV/Resume (PDF)</label>
            <div class="file-upload" id="pdfUpload">
              <i class="fas fa-file-pdf"></i>
              <p>Drag & drop your CV or click to browse</p>
              <small>PDF document (max 5MB)</small>
              <input type="file" id="pdf" name="pdf" accept=".pdf" style="display: none;" required>
            </div>
            <div class="file-preview" id="pdfPreview"></div>
            <div class="error-message" id="pdfError">Please upload your CV</div>
          </div>
          
          <div class="form-group">
            <label class="form-label">Upload ID Proof</label>
            <div class="file-upload" id="imageUpload">
              <i class="fas fa-id-card"></i>
              <p>Drag & drop your ID or click to browse</p>
              <small>Image (max 5MB)</small>
              <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png" style="display: none;" required>
            </div>
            <div class="file-preview" id="imagePreview"></div>
            <div class="error-message" id="imageError">Please upload your ID proof</div>
          </div>
        </div>
        
        <div class="checkbox-group">
          <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
          <label for="agreeTerms" class="checkbox-label">
            I agree to the <a href="policy.php" target="_blank">Terms of Service</a> and <a href="policy.php" target="_blank">Privacy Policy</a>
          </label>
          <div class="error-message" id="termsError">You must agree to the terms</div>
        </div>
        
        <div class="form-navigation">
          <button type="button" class="btn btn-outline" id="prevBtn2">Previous</button>
          <button type="submit" class="btn btn-primary" id="submitBtn">Submit Application</button>
        </div>
      </div>

      <!-- Success Message -->
      <div class="success-message" id="successMessage">
        <i class="fas fa-check-circle success-icon"></i>
        <h3 class="success-title">Application Submitted Successfully!</h3>
        <p class="success-text">Thank you for applying. We'll review your application and get back to you within 5-7 business days.</p>
        <a href="welcome.html" class="btn btn-primary">Return to Home</a>
      </div>
    </form>
  </div>

  <script src="javascript/worker-application.js"></script>
</body>
</html>