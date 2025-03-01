// BMI Calculation and Display
document.getElementById('weight').addEventListener('input', function () {
    const height = document.getElementById('height').value;
    const weight = this.value;

    if (height && weight) {
        const bmi = (weight / ((height / 100) ** 2)).toFixed(2);

        // Display BMI in a dedicated section
        document.getElementById('bmiDisplay').innerText = `Your BMI: ${bmi}`;

        // Update BMI gauge chart
        const normalizedBmi = Math.min(bmi, 40); // Cap BMI at 40 for the chart
        bmiChart.data.datasets[0].data = [normalizedBmi, 40 - normalizedBmi];
        bmiChart.update();
    }
});

// Initialize BMI Gauge Chart
let bmiChart = new Chart(document.getElementById("bmi-gauge"), {
    type: "doughnut",
    data: {
        labels: ["BMI", "Remaining"],
        datasets: [
            {
                data: [0, 40], // Initial values
                backgroundColor: ["#4CAF50", "#ddd"], // Colors
                borderWidth: 0,
            },
        ],
    },
    options: {
        rotation: Math.PI, // Half-circle
        circumference: Math.PI, // Half-circle
        cutout: "80%", // Doughnut hole size
        plugins: {
            tooltip: { enabled: false },
        },
        plugins: {
            legend: {
           
