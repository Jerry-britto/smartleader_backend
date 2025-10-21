<?php
include('../common/config.php'); // Include the database configuration file

class LanguageFetcher {
    public function GetLanguage() {
        global $conn; // Use the global DB connection

        $languages = [];

        // SQL query to fetch languages
        $query = "SELECT id, value FROM list";
        $result = mysqli_query($conn, $query);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $languages[] = $row;
            }
        } else {
            // Log the error or handle it appropriately
            error_log("Database query failed: " . mysqli_error($conn));
        }

        return $languages;
    }
    public function GetTags() {
        global $conn; // Use the global DB connection

        $tags = [];

        // SQL query to fetch languages
        $query = "SELECT id, tags FROM tags";
        $result = mysqli_query($conn, $query);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $tags[] = $row;
            }
        } else {
            // Log the error or handle it appropriately
            error_log("Database query failed: " . mysqli_error($conn));
        }

        return $tags;
    }
}
?>
