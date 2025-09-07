<?php
/**
 * Location Map Component for D Labour Chowk
 * Interactive map for location-based filtering
 */

require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login_form.php');
}

$userType = getCurrentUserType();
$userId = getCurrentUserId();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Map - D Labour Chowk</title>
    <meta name="description" content="Find nearby workers or jobs using interactive location map">

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
        }

        .map-container {
            position: relative;
            height: calc(100vh - 80px);
            margin-top: 80px;
        }

        #map {
            height: 100%;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .map-controls {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            min-width: 300px;
        }

        .control-header {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1f2937;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 5px;
        }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-search {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.4);
            color: white;
        }

        .results-panel {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 350px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .result-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .result-item:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .result-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .result-info h6 {
            margin: 0 0 5px 0;
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
        }

        .result-info p {
            margin: 0;
            font-size: 0.85rem;
            color: #6b7280;
        }

        .result-distance {
            position: absolute;
            right: 15px;
            top: 15px;
            background: var(--accent-color);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .loading i {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-nav {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-nav:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(37, 99, 235, 0.3);
            color: white;
        }

        /* Custom Leaflet marker styles */
        .custom-marker {
            background: var(--gradient-primary);
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .custom-marker.worker {
            background: var(--success-color);
        }

        .custom-marker.job {
            background: var(--accent-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .map-controls {
                position: relative;
                top: auto;
                left: auto;
                margin: 20px;
                min-width: auto;
            }

            .results-panel {
                position: relative;
                top: auto;
                right: auto;
                margin: 20px;
                max-width: none;
            }

            .map-container {
                height: calc(100vh - 200px);
                margin-top: 160px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                🏗️ D Labour Chowk
            </a>

            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn-nav">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <a href="messages.php" class="btn-nav">
                    <i class="fas fa-comments me-2"></i>Messages
                </a>
            </div>
        </div>
    </nav>

    <!-- Map Controls -->
    <div class="map-controls">
        <div class="control-header">
            <i class="fas fa-map-marker-alt me-2"></i>
            <?php echo $userType === 'User' ? 'Find Nearby Workers' : 'Find Nearby Jobs'; ?>
        </div>

        <form id="searchForm">
            <div class="form-group">
                <label class="form-label">Search Radius (km)</label>
                <select class="form-control" id="searchRadius">
                    <option value="5">5 km</option>
                    <option value="10" selected>10 km</option>
                    <option value="15">15 km</option>
                    <option value="25">25 km</option>
                    <option value="50">50 km</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Skill/Service</label>
                <input type="text" class="form-control" id="skillFilter"
                       placeholder="e.g., plumber, electrician, carpenter">
            </div>

            <button type="submit" class="btn-search">
                <i class="fas fa-search me-2"></i>Search Nearby
            </button>
        </form>

        <div class="form-group mt-3">
            <button id="getCurrentLocation" class="btn btn-outline-primary w-100">
                <i class="fas fa-crosshairs me-2"></i>Use My Location
            </button>
        </div>
    </div>

    <!-- Results Panel -->
    <div class="results-panel" id="resultsPanel" style="display: none;">
        <div class="control-header">
            <i class="fas fa-list me-2"></i>Search Results
            <span id="resultsCount" class="badge bg-primary ms-2">0</span>
        </div>

        <div id="resultsList">
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Searching...</p>
            </div>
        </div>
    </div>

    <!-- Map Container -->
    <div class="map-container">
        <div id="map"></div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Initialize map
        let map;
        let userMarker;
        let resultMarkers = [];
        let currentLocation = null;

        // Default location (Delhi, India)
        const defaultLocation = [28.6139, 77.2090];

        function initMap() {
            map = L.map('map').setView(defaultLocation, 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add user location marker
            userMarker = L.marker(defaultLocation, {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: '<i class="fas fa-user"></i>',
                    iconSize: [40, 40],
                    iconAnchor: [20, 40]
                })
            }).addTo(map);

            // Try to get user's current location
            getCurrentLocation();
        }

        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        currentLocation = [position.coords.latitude, position.coords.longitude];
                        map.setView(currentLocation, 14);
                        userMarker.setLatLng(currentLocation);

                        // Reverse geocode to get location name
                        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${currentLocation[0]}&lon=${currentLocation[1]}`)
                            .then(response => response.json())
                            .then(data => {
                                const locationName = data.display_name || 'Your Location';
                                userMarker.bindPopup(`<b>Your Location</b><br>${locationName}`).openPopup();
                            });
                    },
                    function(error) {
                        console.log('Geolocation error:', error);
                        // Use default location if geolocation fails
                        currentLocation = defaultLocation;
                    }
                );
            } else {
                currentLocation = defaultLocation;
            }
        }

        function searchNearby() {
            if (!currentLocation) {
                alert('Please allow location access or set your location manually.');
                return;
            }

            const radius = document.getElementById('searchRadius').value;
            const skill = document.getElementById('skillFilter').value;

            // Show loading
            document.getElementById('resultsPanel').style.display = 'block';
            document.getElementById('resultsList').innerHTML = `
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Searching...</p>
                </div>
            `;

            // Clear previous markers
            resultMarkers.forEach(marker => map.removeLayer(marker));
            resultMarkers = [];

            const searchType = '<?php echo $userType === 'User' ? 'find_workers' : 'find_jobs'; ?>';
            const url = `location_service.php?action=${searchType}&lat=${currentLocation[0]}&lng=${currentLocation[1]}&radius=${radius}&skill=${encodeURIComponent(skill)}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayResults(data.<?php echo $userType === 'User' ? 'workers' : 'jobs'; ?>);
                    } else {
                        document.getElementById('resultsList').innerHTML = `
                            <div class="text-center p-3">
                                <i class="fas fa-exclamation-triangle text-warning fa-2x mb-2"></i>
                                <p>Search failed. Please try again.</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    document.getElementById('resultsList').innerHTML = `
                        <div class="text-center p-3">
                            <i class="fas fa-exclamation-triangle text-danger fa-2x mb-2"></i>
                            <p>Network error. Please check your connection.</p>
                        </div>
                    `;
                });
        }

        function displayResults(results) {
            document.getElementById('resultsCount').textContent = results.length;

            if (results.length === 0) {
                document.getElementById('resultsList').innerHTML = `
                    <div class="text-center p-4">
                        <i class="fas fa-search text-muted fa-3x mb-3"></i>
                        <h6>No results found</h6>
                        <p class="text-muted">Try increasing the search radius or changing your filters.</p>
                    </div>
                `;
                return;
            }

            let resultsHtml = '';
            results.forEach((result, index) => {
                const initials = '<?php echo $userType === 'User' ? 'result.user_name' : 'result.client_name'; ?>'.split(' ').map(n => n[0]).join('').toUpperCase();
                const title = '<?php echo $userType === 'User' ? 'result.user_name' : 'result.jobTitle'; ?>';
                const subtitle = '<?php echo $userType === 'User' ? 'result.workType' : 'result.client_name'; ?>';
                const distance = result.distance_km;

                resultsHtml += `
                    <div class="result-item" onclick="focusMarker(${index})">
                        <div class="result-avatar">${initials}</div>
                        <div class="result-info">
                            <h6>${title}</h6>
                            <p>${subtitle}</p>
                        </div>
                        <div class="result-distance">${distance} km</div>
                    </div>
                `;

                // Add marker to map
                const markerIcon = L.divIcon({
                    className: 'custom-marker <?php echo $userType === 'User' ? 'worker' : 'job'; ?>',
                    html: '<i class="fas fa-<?php echo $userType === 'User' ? 'user' : 'briefcase'; ?>"></i>',
                    iconSize: [35, 35],
                    iconAnchor: [17, 35]
                });

                const marker = L.marker([result.latitude, result.longitude], { icon: markerIcon })
                    .addTo(map)
                    .bindPopup(`
                        <b>${title}</b><br>
                        ${subtitle}<br>
                        <small>${distance} km away</small>
                    `);

                resultMarkers.push(marker);
            });

            document.getElementById('resultsList').innerHTML = resultsHtml;

            // Fit map to show all markers
            if (resultMarkers.length > 0) {
                const group = new L.featureGroup(resultMarkers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }

        function focusMarker(index) {
            if (resultMarkers[index]) {
                map.setView(resultMarkers[index].getLatLng(), 15);
                resultMarkers[index].openPopup();
            }
        }

        // Event listeners
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            searchNearby();
        });

        document.getElementById('getCurrentLocation').addEventListener('click', function() {
            getCurrentLocation();
        });

        // Initialize map when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
        });
    </script>
</body>
</html>