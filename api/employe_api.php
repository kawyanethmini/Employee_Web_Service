<?php
// Set headers for CORS and JSON response
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

// File where employee data is stored
$filename = "employees.json";

// If the file doesn't exist, create it with sample data
if (!file_exists($filename)) {
    $initialData = [
        "1" => ["name" => "Alice", "department" => "HR", "role" => "Manager"],
        "2" => ["name" => "Bob", "department" => "IT", "role" => "Developer"]
    ];
    file_put_contents($filename, json_encode($initialData, JSON_PRETTY_PRINT));
}

// Read data from JSON file
$data = json_decode(file_get_contents($filename), true);

// === Handle GET request ===
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    echo json_encode($data);
    exit();
}

// === Handle POST request ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    // JSON error handling
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            "message" => "Invalid JSON",
            "error" => json_last_error_msg()
        ]);
        exit();
    }

    // === Update Existing Employee ===
    if (isset($input["update_id"], $input["name"], $input["department"], $input["role"])) {
        $id = $input["update_id"];
        if (isset($data[$id])) {
            $data[$id] = [
                "name" => $input["name"],
                "department" => $input["department"],
                "role" => $input["role"]
            ];
            file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(["message" => "Employee ID $id updated"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Employee not found"]);
        }
        exit();
    }

    // === Add New Employee ===
    if (isset($input["name"], $input["department"], $input["role"])) {
        $newId = empty($data) ? 1 : (max(array_keys($data)) + 1);
        $data[$newId] = [
            "name" => $input["name"],
            "department" => $input["department"],
            "role" => $input["role"]
        ];
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode([
            "id" => $newId,
            "message" => "Employee added successfully"
        ]);
        exit();
    }

    // === Fallback: Invalid Input ===
    http_response_code(400);
    echo json_encode(["message" => "Invalid input structure"]);
    exit();
}
?>
