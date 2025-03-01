document.addEventListener('DOMContentLoaded', function() {
    const adminSection = document.querySelector('.bmi-admin-section');
    if (adminSection) {
        const testContainer = document.createElement('div');
        testContainer.className = 'api-test-container';
        testContainer.style.marginBottom = '20px';

        // Create test button
        const testButton = document.createElement('button');
        testButton.id = 'test-ai-api';
        testButton.className = 'button button-primary';
        testButton.textContent = 'Test AI API Connection';
        testContainer.appendChild(testButton);

        // Create service selector
        const serviceSelect = document.createElement('select');
        serviceSelect.id = 'ai-service-select';
        serviceSelect.className = 'bmi-select';
        serviceSelect.style.marginLeft = '10px';
        
        const options = [
            { value: 'chatgpt', text: 'Test ChatGPT API' },
            { value: 'gemini', text: 'Test Gemini API' }
        ];
        
        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.text;
            serviceSelect.appendChild(option);
        });
        
        testContainer.appendChild(serviceSelect);

        // Add status display div with more detailed information
        const statusDiv = document.createElement('div');
        statusDiv.id = 'api-test-status';
        statusDiv.style.marginTop = '15px';
        statusDiv.style.padding = '15px';
        statusDiv.style.borderRadius = '4px';
        testContainer.appendChild(statusDiv);

        // Add response details section
        const responseDetails = document.createElement('pre');
        responseDetails.id = 'api-response-details';
        responseDetails.style.display = 'none';
        responseDetails.style.marginTop = '10px';
        responseDetails.style.padding = '10px';
        responseDetails.style.backgroundColor = '#f5f5f5';
        responseDetails.style.borderRadius = '4px';
        responseDetails.style.maxHeight = '200px';
        responseDetails.style.overflow = 'auto';
        testContainer.appendChild(responseDetails);

        adminSection.appendChild(testContainer);

        // Add click event listener
        testButton.addEventListener('click', testAIConnection);
    }
});

async function testAIConnection() {
    const statusDiv = document.getElementById('api-test-status');
    const responseDetails = document.getElementById('api-response-details');
    const selectedService = document.getElementById('ai-service-select').value;
    
    statusDiv.innerHTML = `<span class="dashicons dashicons-update-alt spin"></span> Testing ${selectedService.toUpperCase()} API connection...`;
    statusDiv.className = 'notice notice-info';
    responseDetails.style.display = 'none';

    try {
        const response = await fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'test_ai_connection',
                service: selectedService,
                nonce: bmiAjax.nonce
            })
        });

        const data = await response.json();

        if (data.success) {
            statusDiv.innerHTML = `
                <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                <strong>API Connection Successful!</strong><br>
                Service: ${data.service}<br>
                Status: ${data.message}
            `;
            statusDiv.className = 'notice notice-success';

            // Show sample response
            if (data.sample_response) {
                responseDetails.textContent = JSON.stringify(data.sample_response, null, 2);
                responseDetails.style.display = 'block';
            }
        } else {
            statusDiv.innerHTML = `
                <span class="dashicons dashicons-warning" style="color: #dc3232;"></span>
                <strong>API Connection Failed!</strong><br>
                Service: ${selectedService}<br>
                Error: ${data.message}
            `;
            statusDiv.className = 'notice notice-error';
        }
    } catch (error) {
        statusDiv.innerHTML = `
            <span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
            <strong>Test Failed!</strong><br>
            Error: ${error.message}
        `;
        statusDiv.className = 'notice notice-error';
    }
}
