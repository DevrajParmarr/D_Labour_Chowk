<?php
/**
 * Location Service for D Labour Chowk
 * Handles location-based filtering and distance calculations
 */

require_once 'config.php';

class LocationService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta/2) * sin($latDelta/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta/2) * sin($lonDelta/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Update user location
     */
    public function updateUserLocation($userId, $latitude, $longitude, $locationName = '', $city = '', $state = '') {
        $query = "INSERT INTO user_location (user_id, latitude, longitude, location_name, city, state)
                  VALUES (?, ?, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE
                  latitude = VALUES(latitude),
                  longitude = VALUES(longitude),
                  location_name = VALUES(location_name),
                  city = VALUES(city),
                  state = VALUES(state),
                  last_updated = CURRENT_TIMESTAMP";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iddsss", $userId, $latitude, $longitude, $locationName, $city, $state);
        return $stmt->execute();
    }

    /**
     * Find nearby workers
     */
    public function findNearbyWorkers($clientLat, $clientLng, $radiusKm = 10, $skillFilter = '', $limit = 20) {
        $query = "
            SELECT
                u.user_ID,
                u.user_name,
                u.email_id,
                u.mobile_no,
                lp.workType,
                lp.experience,
                lp.salary,
                lp.location,
                lp.city,
                lp.impath,
                ul.latitude,
                ul.longitude,
                ul.location_name,
                ROUND(
                    6371 * acos(
                        cos(radians(?)) * cos(radians(ul.latitude)) *
                        cos(radians(ul.longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(ul.latitude))
                    ), 1
                ) as distance_km
            FROM user u
            JOIN lab_post lp ON u.user_ID = lp.user_ID
            JOIN user_location ul ON u.user_ID = ul.user_id
            WHERE ul.is_active = TRUE
            AND lp.is_available = TRUE
            AND ROUND(
                6371 * acos(
                    cos(radians(?)) * cos(radians(ul.latitude)) *
                    cos(radians(ul.longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(ul.latitude))
                ), 1
            ) <= ?
        ";

        $params = [$clientLat, $clientLng, $clientLat, $clientLat, $clientLng, $clientLat, $radiusKm];
        $types = "ddddddd";

        if (!empty($skillFilter)) {
            $query .= " AND lp.workType LIKE ?";
            $params[] = "%$skillFilter%";
            $types .= "s";
        }

        $query .= " ORDER BY distance_km ASC LIMIT ?";
        $params[] = $limit;
        $types .= "i";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Find nearby jobs for workers
     */
    public function findNearbyJobs($workerLat, $workerLng, $radiusKm = 15, $skillFilter = '', $limit = 20) {
        $query = "
            SELECT
                jp.job_post_id,
                jp.jobTitle,
                jp.salary,
                jp.detail,
                jp.city,
                jp.location,
                jp.impath,
                jp.owner,
                u.user_name as client_name,
                u.mobile_no as client_mobile,
                ul.latitude,
                ul.longitude,
                ROUND(
                    6371 * acos(
                        cos(radians(?)) * cos(radians(ul.latitude)) *
                        cos(radians(ul.longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(ul.latitude))
                    ), 1
                ) as distance_km
            FROM job_post jp
            JOIN user u ON jp.owner = u.user_ID
            LEFT JOIN user_location ul ON jp.owner = ul.user_id
            WHERE jp.is_location_based = TRUE
            AND ROUND(
                6371 * acos(
                    cos(radians(?)) * cos(radians(ul.latitude)) *
                    cos(radians(ul.longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(ul.latitude))
                ), 1
            ) <= ?
        ";

        $params = [$workerLat, $workerLng, $workerLat, $workerLat, $workerLng, $workerLat, $radiusKm];
        $types = "ddddddd";

        if (!empty($skillFilter)) {
            $query .= " AND jp.jobTitle LIKE ?";
            $params[] = "%$skillFilter%";
            $types .= "s";
        }

        $query .= " ORDER BY distance_km ASC LIMIT ?";
        $params[] = $limit;
        $types .= "i";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Get user's current location
     */
    public function getUserLocation($userId) {
        $query = "SELECT * FROM user_location WHERE user_id = ? AND is_active = TRUE ORDER BY last_updated DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Log location search
     */
    public function logLocationSearch($userId, $lat, $lng, $radius, $searchType, $resultsCount) {
        $query = "INSERT INTO location_search_history (user_id, search_lat, search_lng, search_radius, search_type, search_results_count)
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iddisi", $userId, $lat, $lng, $radius, $searchType, $resultsCount);
        return $stmt->execute();
    }

    /**
     * Get location search history
     */
    public function getLocationSearchHistory($userId, $limit = 10) {
        $query = "SELECT * FROM location_search_history WHERE user_id = ? ORDER BY searched_at DESC LIMIT ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
}

// API Endpoints for location services
if (isset($_GET['action'])) {
    $locationService = new LocationService();
    header('Content-Type: application/json');

    switch ($_GET['action']) {
        case 'update_location':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
                exit;
            }

            $userId = getCurrentUserId();
            $lat = $_POST['latitude'] ?? 0;
            $lng = $_POST['longitude'] ?? 0;
            $locationName = $_POST['location_name'] ?? '';
            $city = $_POST['city'] ?? '';
            $state = $_POST['state'] ?? '';

            $success = $locationService->updateUserLocation($userId, $lat, $lng, $locationName, $city, $state);
            echo json_encode(['success' => $success]);
            break;

        case 'find_workers':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
                exit;
            }

            $lat = $_GET['lat'] ?? 0;
            $lng = $_GET['lng'] ?? 0;
            $radius = $_GET['radius'] ?? 10;
            $skill = $_GET['skill'] ?? '';

            $result = $locationService->findNearbyWorkers($lat, $lng, $radius, $skill);
            $workers = [];

            while ($row = $result->fetch_assoc()) {
                $workers[] = $row;
            }

            // Log the search
            $locationService->logLocationSearch(getCurrentUserId(), $lat, $lng, $radius, 'labour', count($workers));

            echo json_encode(['success' => true, 'workers' => $workers]);
            break;

        case 'find_jobs':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
                exit;
            }

            $lat = $_GET['lat'] ?? 0;
            $lng = $_GET['lng'] ?? 0;
            $radius = $_GET['radius'] ?? 15;
            $skill = $_GET['skill'] ?? '';

            $result = $locationService->findNearbyJobs($lat, $lng, $radius, $skill);
            $jobs = [];

            while ($row = $result->fetch_assoc()) {
                $jobs[] = $row;
            }

            // Log the search
            $locationService->logLocationSearch(getCurrentUserId(), $lat, $lng, $radius, 'jobs', count($jobs));

            echo json_encode(['success' => true, 'jobs' => $jobs]);
            break;

        case 'get_user_location':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
                exit;
            }

            $location = $locationService->getUserLocation(getCurrentUserId());
            echo json_encode(['success' => true, 'location' => $location]);
            break;
    }
    exit;
}
?>