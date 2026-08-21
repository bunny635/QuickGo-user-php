<?php
// pages/fix_db.php
require_once '../config/database.php';

echo "<h2>QuickGo Database Automatic Repair Tool</h2>";

try {
    // 1. Check if 'users' table has a provider
    $providerCheck = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'provider'")->fetchColumn();

    if ($providerCheck == 0) {
        // Insert a demo provider user
        $passHash = password_hash('password123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (id, name, email, phone, password_hash, role, status, address) VALUES (101, 'Rajesh Kumar', 'rajesh.clean@quickgo.com', '+919876511111', '$passHash', 'provider', 'active', 'Surat')");

        // Insert into providers table
        $pdo->exec("INSERT INTO providers (id, user_id, availability, verification_status, rating) VALUES (1, 101, 'Online', 'Approved', 4.9)");
        echo "<p style='color:green;'>✓ Added demo provider successfully.</p>";
    } else {
        echo "<p style='color:blue;'>ℹ Providers already exist.</p>";
    }

    // 2. Check if 'services' table has active services
    $serviceCheck = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();

    if ($serviceCheck == 0) {
        // Insert standard demo services
        $pdo->exec("
            INSERT INTO services (provider_id, title, category, description, hourly_pay, price, duration, location, is_active, images) VALUES
            (1, 'Deep Home Cleaning', 'Home Cleaning', 'Complete residential deep cleaning including sanitation and kitchen detailing.', 120.00, 120.00, '3 hours', 'Surat', 1, '[\"../assets/images/homecleaning.jpg\"]'),
            (1, 'Garden Care & Landscaping', 'Garden Care', 'Professional lawn mowing, hedge trimming, and outdoor maintenance.', 90.00, 90.00, '2 hours', 'Surat', 1, '[\"../assets/images/GardenCleaning.jpg\"]'),
            (1, 'Electrical Repair & Setup', 'Electrician', 'Certified expert for wiring fixes, switchboard repairs, and setups.', 85.00, 85.00, '1.5 hours', 'Surat', 1, '[\"../assets/images/ElectricianService.jpg\"]')
        ");
        echo "<p style='color:green;'>✓ Added demo services successfully.</p>";
    } else {
        // Force them to be active and linked to provider 1
        $pdo->exec("UPDATE services SET is_active = 1, provider_id = 1");
        echo "<p style='color:blue;'>ℹ Services already exist. Updated status to active.</p>";
    }

    echo "<h3 style='color:green;'>Database repair complete! <a href='services.php'>Click here to view your Services Catalog</a></h3>";
} catch (PDOException $e) {
    echo "<p style='color:red;'>Database Error: " . $e->getMessage() . "</p>";
    echo "<p><b>Tip:</b> Make sure you ran the master `schema.sql` script first to create the tables!</p>";
}
