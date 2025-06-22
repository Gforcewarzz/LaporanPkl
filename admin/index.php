<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

<?php include 'partials/head.php' ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <div class="col-lg-6 col-md-12 col-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-column align-items-start">
                                        <div class="avatar flex-shrink-0 mb-3">
                                            <img src="./assets/img/icons/unicons/user.png" alt="chart success"
                                                class="rounded" />
                                        </div>
                                        <span class="cards-title fw-semibold d-block mb-1">Total Siswa</span>
                                        <h3 class="card-title fw-bold mb-0">125</h3>
                                        <small class="text-muted">Currently active</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12 col-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-column align-items-start">
                                        <div class="avatar flex-shrink-0 mb-3">
                                            <img src="./assets/img/icons/unicons/user.png" alt="chart success"
                                                class="rounded" />
                                        </div>
                                        <span class="cards-title fw-semibold d-block mb-1">Total Guru Pembimbing</span>
                                        <h3 class="card-title fw-bold mb-0">50</h3>
                                        <small class="text-muted">Available mentors</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="layout-overlay layout-menu-toggle"></div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

                <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


                <?php include './partials/script.php'; ?>
                <script>
                const chartData = {
                    harian: {
                        selector: "#chartHarian",
                        series: [{
                            name: 'Barang Keluar',
                            data: <?= json_encode($dataHarian); ?>
                        }],
                        categories: <?= json_encode($labelsHarian); ?>
                    },
                    bulanan: {
                        selector: "#chartBulanan",
                        series: [{
                            name: 'Barang Keluar',
                            data: <?= json_encode($dataBulanan); ?>
                        }],
                        categories: <?= json_encode($labelsBulanan); ?>
                    },
                    tahunan: {
                        selector: "#chartTahunan",
                        series: [{
                            name: 'Barang Keluar',
                            data: <?= json_encode($dataTahunan); ?>
                        }],
                        categories: <?= json_encode($labelsTahunan); ?>
                    }
                };

                const chartInstances = {};

                const renderChart = (key) => {
                    const {
                        selector,
                        series,
                        categories
                    } = chartData[key];
                    if (chartInstances[key]) return;

                    const options = {
                        chart: {
                            type: 'area',
                            height: 300
                        },
                        colors: ['#FF4560', '#00E396', '#008FFB'],
                        series,
                        xaxis: {
                            categories
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 4
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shade: 'light',
                                shadeIntensity: 0.5,
                                inverseColors: false,
                                opacityFrom: 0.4,
                                opacityTo: 0.7,
                                stops: [0, 90, 100]
                            }
                        },
                        markers: {
                            size: 4,
                            colors: ['#fff'],
                            strokeColors: ['#FF4560', '#00E396', '#008FFB'],
                            strokeWidth: 2,
                            hover: {
                                size: 6
                            }
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'left'
                        }
                    };

                    chartInstances[key] = new ApexCharts(document.querySelector(selector), options);
                    chartInstances[key].render();
                };

                // Render all charts initially
                renderChart('harian');
                renderChart('bulanan');
                renderChart('tahunan');

                // Attach event listeners for tab clicks
                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelector('#pengeluaranHarian button').addEventListener('click', () =>
                        renderChart(
                            'harian'));
                    document.querySelector('#pengeluaranBulanan button').addEventListener('click', () =>
                        renderChart('bulanan'));
                    document.querySelector('#pengeluaranTahunan button').addEventListener('click', () =>
                        renderChart('tahunan'));
                });
                </script>
</body>

</html>