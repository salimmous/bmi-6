/**
 * BMI Progress Tracker
 * This script creates and manages a progress chart to visualize BMI changes over time.
 */

let progressChart;

/**
 * Initialize the progress chart with sample or stored data
 */
function initProgressChart() {
    const progressCanvas = document.getElementById('bmi-progress-chart');
    if (!progressCanvas) {
        console.error('BMI Progress chart canvas not found.');
        return;
    }

    const context = progressCanvas.getContext('2d');
    
    // Try to get stored BMI history from localStorage
    let bmiHistory = getBmiHistory();
    
    // If no history exists, create sample data for demonstration
    if (bmiHistory.length === 0) {
        const today = new Date();
        bmiHistory = [
            { date: formatDate(new Date(today.setDate(today.getDate() - 30))), bmi: 26.5 },
            { date: formatDate(new Date(today.setDate(today.getDate() + 10))), bmi: 25.8 },
            { date: formatDate(new Date(today.setDate(today.getDate() + 10))), bmi: 25.2 },
            { date: formatDate(new Date()), bmi: 24.7 }
        ];
    }

    // Prepare data for the chart
    const labels = bmiHistory.map(entry => entry.date);
    const data = bmiHistory.map(entry => entry.bmi);

    // Create gradient for the line
    const gradient = context.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(46, 204, 113, 0.8)');
    gradient.addColorStop(1, 'rgba(46, 204, 113, 0.2)');

    // Initialize the chart
    progressChart = new Chart(context, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'BMI Progress',
                data: data,
                borderColor: '#2ecc71',
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#2ecc71',
                pointBorderColor: '#fff',
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `BMI: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: Math.min(...data) - 2 > 0 ? Math.min(...data) - 2 : 0,
                    max: Math.max(...data) + 2,
                    ticks: {
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(200, 200, 200, 0.2)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    });
}

/**
 * Update the progress chart with a new BMI value
 * @param {number} bmi - The new BMI value to add to the chart
 */
function updateProgressChart(bmi) {
    if (!progressChart) {
        initProgressChart();
        return;
    }

    // Get existing BMI history
    let bmiHistory = getBmiHistory();
    
    // Add new entry
    const today = new Date();
    bmiHistory.push({
        date: formatDate(today),
        bmi: bmi
    });
    
    // Keep only the last 10 entries if there are more
    if (bmiHistory.length > 10) {
        bmiHistory = bmiHistory.slice(bmiHistory.length - 10);
    }
    
    // Save updated history
    saveBmiHistory(bmiHistory);
    
    // Update chart data
    progressChart.data.labels = bmiHistory.map(entry => entry.date);
    progressChart.data.datasets[0].data = bmiHistory.map(entry => entry.bmi);
    
    // Update y-axis scale
    const data = bmiHistory.map(entry => entry.bmi);
    progressChart.options.scales.y.min = Math.min(...data) - 2 > 0 ? Math.min(...data) - 2 : 0;
    progressChart.options.scales.y.max = Math.max(...data) + 2;
    
    // Refresh the chart
    progressChart.update();
    
    // Update progress indicators
    updateProgressIndicators(bmiHistory);
}

/**
 * Update progress indicators showing change since last measurement
 * @param {Array} history - Array of BMI history objects
 */
function updateProgressIndicators(history) {
    if (history.length < 2) return;
    
    const currentBmi = history[history.length - 1].bmi;
    const previousBmi = history[history.length - 2].bmi;
    const difference = currentBmi - previousBmi;
    
    const progressIndicator = document.getElementById('bmi-progress-indicator');
    if (!progressIndicator) return;
    
    // Clear previous content
    progressIndicator.innerHTML = '';
    
    // Create indicator element
    const indicator = document.createElement('div');
    indicator.classList.add('progress-indicator');
    
    if (difference < 0) {
        // BMI decreased (improvement for overweight/obese)
        indicator.innerHTML = `
            <span class="indicator-value decrease">↓ ${Math.abs(difference).toFixed(1)}</span>
            <span class="indicator-label">Since last measurement</span>
        `;
        indicator.classList.add('positive');
    } else if (difference > 0) {
        // BMI increased
        indicator.innerHTML = `
            <span class="indicator-value increase">↑ ${difference.toFixed(1)}</span>
            <span class="indicator-label">Since last measurement</span>
        `;
        indicator.classList.add('negative');
    } else {
        // No change
        indicator.innerHTML = `
            <span class="indicator-value">No change</span>
            <span class="indicator-label">Since last measurement</span>
        `;
        indicator.classList.add('neutral');
    }
    
    progressIndicator.appendChild(indicator);
}

/**
 * Format a date object to a readable string (MM/DD/YYYY)
 * @param {Date} date - The date to format
 * @returns {string} Formatted date string
 */
function formatDate(date) {
    const month = date.getMonth() + 1;
    const day = date.getDate();
    const year = date.getFullYear();
    return `${month}/${day}/${year}`;
}

/**
 * Get BMI history from localStorage
 * @returns {Array} Array of BMI history objects
 */
function getBmiHistory() {
    const history = localStorage.getItem('bmiHistory');
    return history ? JSON.parse(history) : [];
}

/**
 * Save BMI history to localStorage
 * @param {Array} history - Array of BMI history objects
 */
function saveBmiHistory(history) {
    localStorage.setItem('bmiHistory', JSON.stringify(history));
}

// Initialize the progress chart when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit to ensure other scripts have loaded
    setTimeout(initProgressChart, 500);
    
    // Listen for form submission to update the progress chart
    const form = document.getElementById('bmi-calculator-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // The actual BMI value will be updated via the AJAX response
            // This is handled in bmi-ajax.js
        });
    }
});