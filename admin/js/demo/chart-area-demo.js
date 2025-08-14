document.addEventListener("DOMContentLoaded", function () {
  // Monthly Revenue Area Chart
  const areaCtx = document.getElementById("myAreaChart");
  if (areaCtx) {
    const revenueData = JSON.parse(areaCtx.getAttribute("data-chart"));
    new Chart(areaCtx, {
      type: 'line',
      data: {
        labels: [
          "Jan", "Feb", "Mar", "Apr", "May", "Jun",
          "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ],
        datasets: [{
          label: "Monthly Revenue",
          data: revenueData,
          fill: true,
          backgroundColor: "rgba(78, 115, 223, 0.1)",
          borderColor: "rgba(78, 115, 223, 1)",
          tension: 0.3,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: { grid: { display: false } },
          y: { beginAtZero: true }
        }
      }
    });
  }

  // Product Stock vs Sold Bar Chart
  const barCtx = document.getElementById("stockSoldBarChart");
  if (barCtx) {
    const barData = JSON.parse(barCtx.getAttribute("data-bar"));
    new Chart(barCtx, {
      type: 'bar',
      data: {
        labels: ["In Stock", "Sold"],
        datasets: [{
          label: 'Quantity',
          data: [barData.stock, barData.sold],
          backgroundColor: [
            'rgba(54, 185, 204, 0.8)',
            'rgba(231, 74, 59, 0.8)'
          ],
          borderColor: [
            'rgba(54, 185, 204, 1)',
            'rgba(231, 74, 59, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true
          }
        },
        plugins: {
          legend: {
            display: true
          }
        }
      }
    });
  }
});
