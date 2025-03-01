let bmiChart;

/**
 * Initialize or update the BMI gauge chart to show multiple BMI categories.
 * @param {number} bmi - The calculated BMI value.
 */
function updateBMIGauge(bmi) {
    const gaugeCanvas = document.getElementById('bmi-gauge');
    if (!gaugeCanvas) {
        console.error('BMI Gauge canvas not found.');
        return;
    }

    const context = gaugeCanvas.getContext('2d');

    // Define BMI ranges and their labels and colors
    const bmiLabels = ['Underweight', 'Normal', 'Overweight', 'Obese'];
    const bmiColors = ['#f1c40f', '#2ecc71', '#e67e22', '#e74c3c']; // Colors
    const bmiRanges = [18.5, 24.9, 29.9, 40]; // Upper limit for each category

    // Determine which category the BMI falls into
    let bmiCategoryIndex = 0;
    for (let i = 0; i < bmiRanges.length; i++) {
        if (bmi <= bmiRanges[i]) {
            bmiCategoryIndex = i;
            break;
        }
    }

    // Data for the chart: percentage for each category
    const data = bmiRanges.map((range, index) => {
        if (index === 0) return range;
        return range - bmiRanges[index - 1];
    });

    // Set the colors dynamically
    const activeColors = bmiColors.map((color, index) =>
        index === bmiCategoryIndex ? color : '#ddd'
    );

    // If the chart already exists, update it
    if (bmiChart) {
        bmiChart.data.datasets[0].data = data;
        bmiChart.data.datasets[0].backgroundColor = activeColors;
        bmiChart.update();
    } else {
        // Initialize the chart
        bmiChart = new Chart(context, {
            type: 'doughnut',
            data: {
                labels: bmiLabels,
                datasets: [
                    {
                        data: data,
                        backgroundColor: activeColors,
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (tooltipItem) {
                                return `${tooltipItem.label}: ${tooltipItem.raw.toFixed(1)}%`;
                            },
                        },
                    },
                    legend: {
                        display: true,
                        position: 'top', // Position of the legend
                        labels: {
                            usePointStyle: true,
                            font: {
                                size: 12,
                            },
                        },
                    },
                },
                rotation: Math.PI, // Start at the top
                circumference: Math.PI, // Half-circle
                cutout: '80%', // Doughnut hole size
            },
        });
    }

    // Display BMI value and category in the center
    displayBMIText(bmi, bmiLabels[bmiCategoryIndex], bmiColors[bmiCategoryIndex]);
}

/**
 * Function to display BMI value and category inside the chart.
 * @param {number} bmi - The calculated BMI value.
 * @param {string} category - The BMI category (e.g., Normal, Overweight).
 * @param {string} color - The color corresponding to the BMI category.
 */
function displayBMIText(bmi, category, color) {
    const gaugeContainer = document.getElementById('bmi-gauge-container');

    // Remove any existing text
    const existingText = gaugeContainer.querySelector('.bmi-text');
    if (existingText) existingText.remove();

    // Add new text
    const textDiv = document.createElement('div');
    textDiv.classList.add('bmi-text');
    textDiv.style.position = 'absolute';
    textDiv.style.top = '50%';
    textDiv.style.left = '50%';
    textDiv.style.transform = 'translate(-50%, -50%)';
    textDiv.style.textAlign = 'center';
    textDiv.style.fontSize = '20px';
    textDiv.style.fontWeight = 'bold';
    textDiv.style.color = color;

    textDiv.innerHTML = `
        <div>${bmi.toFixed(1)}</div>
        <div style="font-size: 14px; color: #555;">${category}</div>
    `;
    gaugeContainer.appendChild(textDiv);
}
