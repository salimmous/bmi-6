document.getElementById("bmi-calculator-form").addEventListener("submit", function (e) {
    e.preventDefault(); // Prevent form submission

    // Show loading indicator
    const submitBtn = document.querySelector('.submit-btn');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner"></span> Calculating...';
    submitBtn.disabled = true;

    // Serialize the form data
    const formData = new URLSearchParams(new FormData(this)).toString();
    
    // Create the data object for the AJAX request
    const requestData = new FormData();
    requestData.append('action', 'bmi_pro_calculate');
    requestData.append('nonce', document.getElementById('bmi-nonce').value);
    requestData.append('data', formData);

    // Send the AJAX request
    fetch(bmi_ajax_object.ajax_url, {
        method: 'POST',
        body: requestData,
    })
    .then((response) => response.json())
    .then((data) => {
        // Reset button state
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;

        if (data.success) {
            // Update the UI with the results
            const results = data.data;
            document.getElementById("bmi-value").innerText = results.bmi || "N/A";
            document.getElementById("bfp-value").innerText = results.bfp || "N/A";
            document.getElementById("bmr-value").innerText = results.bmr || "N/A";
            document.getElementById("ideal-weight").innerText = results.ideal_weight || "N/A";
            document.getElementById("recommendations").innerText = results.recommendations || "N/A";
            
            // Update the BMI gauge
            updateBMIGauge(parseFloat(results.bmi));
            
            // Update progress chart if it exists
            if (typeof updateProgressChart === 'function') {
                updateProgressChart(parseFloat(results.bmi));
            }
            
            // Show the results section with animation
            const resultSection = document.querySelector('.result-section');
            resultSection.classList.add('show-results');
            
            // Scroll to results
            resultSection.scrollIntoView({ behavior: 'smooth' });
        } else {
            alert("Error: " + (data.message || "An unexpected error occurred."));
        }
    })
    .catch((error) => {
        // Reset button state
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
        
        console.error("Error:", error);
        alert("An error occurred. Please try again.");
    });
});

// This code is replaced by the bmi-gauge.js implementation
// The gauge initialization and updates are now handled by updateBMIGauge function

