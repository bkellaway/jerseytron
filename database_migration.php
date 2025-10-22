<?php
/**
 * Hockey Jersey System Database Migrations
 * File: hockey_system/migrations/install_database.php
 * 
 * Run this file via browser ONCE after UserSpice installation
 * DELETE THIS FILE after successful migration for security
 */

// Include UserSpice database connection
require_once '../users/init.php';

// Set longer execution time for migration
set_time_limit(300);
ini_set('memory_limit', '256M');

echo "<h1>Hockey Jersey System - Database Migration</h1>";
echo "<pre>";

try {
    // Check if UserSpice is properly installed
    $userSpiceCheck = $db->query("SHOW TABLES LIKE 'users'");
    if ($userSpiceCheck->count() == 0) {
        throw new Exception("UserSpice not properly installed. Please install UserSpice first.");
    }
    
    echo "? UserSpice installation verified\n";
    
    // 1. Add Google OAuth column to users table if not exists
    echo "\n1. Updating users table for Google OAuth...\n";
    
    $columns = $db->query("SHOW COLUMNS FROM users LIKE 'google_id'");
    if ($columns->count() == 0) {
        $db->query("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER email");
        echo "  ? Added google_id column\n";
    } else {
        echo "  - google_id column already exists\n";
    }
    
    // 2. Create jersey_designs table
    echo "\n2. Creating jersey_designs table...\n";
    
    $createJerseyDesigns = "
    CREATE TABLE IF NOT EXISTS jersey_designs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        team_name VARCHAR(255) NULL,
        image_path VARCHAR(500) NOT NULL,
        prompt TEXT NOT NULL,
        is_public BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_designs (user_id),
        INDEX idx_public_designs (is_public),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->query($createJerseyDesigns);
    echo "  ? jersey_designs table created\n";
    
    // 3. Create orders table
    echo "\n3. Creating orders table...\n";
    
    $createOrders = "
    CREATE TABLE IF NOT EXISTS orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        design_id INT NOT NULL,
        team_name VARCHAR(255) NOT NULL,
        total_quantity INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        pricing_type ENUM('tiered', 'non_tiered') NOT NULL DEFAULT 'non_tiered',
        status ENUM('draft', 'pending', 'processing', 'completed', 'cancelled') DEFAULT 'draft',
        shopify_order_id VARCHAR(255) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
        FOREIGN KEY (design_id) REFERENCES jersey_designs(id) ON DELETE RESTRICT,
        INDEX idx_user_orders (user_id),
        INDEX idx_order_status (status),
        INDEX idx_created_at (created_at),
        INDEX idx_shopify_orders (shopify_order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->query($createOrders);
    echo "  ? orders table created\n";
    
    // 4. Create order_items table
    echo "\n4. Creating order_items table...\n";
    
    $createOrderItems = "
    CREATE TABLE IF NOT EXISTS order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        player_first_name VARCHAR(255) NOT NULL,
        player_last_name VARCHAR(255) NOT NULL,
        jersey_number VARCHAR(10) NOT NULL,
        jersey_size VARCHAR(50) NOT NULL,
        jersey_type ENUM('Classic Cut', 'Goalie Cut') NOT NULL DEFAULT 'Classic Cut',
        unit_price DECIMAL(8,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        INDEX idx_order_items (order_id),
        INDEX idx_player_names (player_last_name, player_first_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->query($createOrderItems);
    echo "  ? order_items table created\n";
    
    // 5. Create system_settings table
    echo "\n5. Creating system_settings table...\n";
    
    $createSystemSettings = "
    CREATE TABLE IF NOT EXISTS system_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT NOT NULL,
        description TEXT NULL,
        setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_setting_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->query($createSystemSettings);
    echo "  ? system_settings table created\n";
    
    // 6. Insert default system settings
    echo "\n6. Inserting default system settings...\n";
    
    $defaultSettings = [
        ['pricing_mode', 'non_tiered', 'Current pricing mode: tiered or non_tiered', 'text'],
        ['classic_price_base', '35.00', 'Base price for Classic Cut jerseys', 'number'],
        ['goalie_price_base', '35.00', 'Base price for Goalie Cut jerseys', 'number'],
        ['tier_1_quantity', '10', 'Tier 1 maximum quantity (5-10)', 'number'],
        ['tier_1_price', '35.00', 'Price per jersey for 5-10 jerseys', 'number'],
        ['tier_2_quantity', '20', 'Tier 2 maximum quantity (11-20)', 'number'],
        ['tier_2_price', '33.00', 'Price per jersey for 11-20 jerseys', 'number'],
        ['tier_3_price', '31.00', 'Price per jersey for 21+ jerseys', 'number'],
        ['minimum_order_new', '5', 'Minimum jerseys for new customers', 'number'],
        ['minimum_order_reorder', '1', 'Minimum jerseys for reorders', 'number'],
        ['free_shipping_threshold', '100.00', 'Free shipping minimum order amount', 'number'],
        ['delivery_timeline', '4-5 weeks', 'Standard delivery time', 'text'],
        ['notification_emails', 'orders@hockeytron.com,admin@hockeytron.com', 'Comma-separated notification emails', 'text'],
        ['shopify_store_url', '', 'Shopify store URL (e.g., yourstore.myshopify.com)', 'text'],
        ['shopify_access_token', '', 'Shopify private app access token', 'text'],
        ['company_name', 'HockeyTron', 'Company name for emails and orders', 'text'],
        ['company_email', 'orders@hockeytron.com', 'Primary company email', 'text'],
        ['order_prefix', 'HT', 'Order number prefix (e.g., HT-2024-001)', 'text']
    ];
    
    foreach ($defaultSettings as $setting) {
        // Check if setting already exists
        $existing = $db->query("SELECT id FROM system_settings WHERE setting_key = ?", [$setting[0]]);
        
        if ($existing->count() == 0) {
            $db->insert('system_settings', [
                'setting_key' => $setting[0],
                'setting_value' => $setting[1],
                'description' => $setting[2],
                'setting_type' => $setting[3]
            ]);
            echo "  ? Added setting: {$setting[0]}\n";
        } else {
            echo "  - Setting already exists: {$setting[0]}\n";
        }
    }
    
    // 7. Create admin permission for jersey system
    echo "\n7. Setting up admin permissions...\n";
    
    // Check if permission exists
    $permissionCheck = $db->query("SELECT id FROM permissions WHERE name = 'Jersey System Admin'");
    
    if ($permissionCheck->count() == 0) {
        $db->insert('permissions', [
            'name' => 'Jersey System Admin',
            'description' => 'Full access to hockey jersey system administration'
        ]);
        $adminPermissionId = $db->lastId();
        echo "  ? Created Jersey System Admin permission (ID: $adminPermissionId)\n";
        
        // Assign to user ID 1 (typically the main admin)
        $adminUser = $db->query("SELECT id FROM users WHERE id = 1");
        if ($adminUser->count() > 0) {
            $existing = $db->query("SELECT id FROM user_permission_matches WHERE user_id = 1 AND permission_id = ?", [$adminPermissionId]);
            if ($existing->count() == 0) {
                $db->insert('user_permission_matches', [
                    'user_id' => 1,
                    'permission_id' => $adminPermissionId
                ]);
                echo "  ? Assigned admin permission to user ID 1\n";
            }
        }
    } else {
        echo "  - Jersey System Admin permission already exists\n";
    }
    
    // 8. Create sample data (optional)
    echo "\n8. Creating sample data...\n";
    
    // Create a sample public design (using existing generated image if available)
    $sampleDesigns = $db->query("SELECT id FROM jersey_designs WHERE team_name = 'Sample Team'");
    if ($sampleDesigns->count() == 0) {
        $db->insert('jersey_designs', [
            'user_id' => 1, // Admin user
            'team_name' => 'Sample Team',
            'image_path' => 'generated_images/sample_jersey.png',
            'prompt' => 'Red jersey with white stripes and a fierce lion logo',
            'is_public' => 1
        ]);
        echo "  ? Created sample jersey design\n";
    } else {
        echo "  - Sample jersey design already exists\n";
    }
    
    // 9. Verify installation
    echo "\n9. Verifying installation...\n";
    
    $tables = ['jersey_designs', 'orders', 'order_items', 'system_settings'];
    foreach ($tables as $table) {
        $check = $db->query("SHOW TABLES LIKE '$table'");
        if ($check->count() > 0) {
            $count = $db->query("SELECT COUNT(*) as count FROM $table")->first();
            echo "  ? Table '$table' exists with {$count->count} records\n";
        } else {
            echo "  ? Table '$table' missing!\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "?? DATABASE MIGRATION COMPLETED SUCCESSFULLY!\n";
    echo str_repeat("=", 60) . "\n";
    
    echo "\nNext steps:\n";
    echo "1. DELETE this file (install_database.php) for security\n";
    echo "2. Configure Google OAuth credentials in your config\n";
    echo "3. Test user registration and login\n";
    echo "4. Configure system settings via admin panel\n";
    echo "5. Upload hockey jersey system files\n";
    
    echo "\nAdmin Access:\n";
    echo "- Login to your UserSpice admin panel\n";
    echo "- User with ID 1 has Jersey System Admin permissions\n";
    echo "- Access system settings and order management features\n";
    
} catch (Exception $e) {
    echo "\n? MIGRATION FAILED!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nPlease fix the issue and run this script again.\n";
    
    // Try to show helpful information
    if (strpos($e->getMessage(), 'Connection') !== false) {
        echo "\nDatabase connection issue. Check:\n";
        echo "- Database credentials in UserSpice config\n";
        echo "- Database server is running\n";
        echo "- Database exists and is accessible\n";
    }
}

echo "</pre>";

// Show database status
echo "<h2>Current Database Status:</h2>";
try {
    $tables = $db->query("SHOW TABLES");
    echo "<ul>";
    while ($table = $tables->results()) {
        foreach ($table as $tableName) {
            $count = $db->query("SELECT COUNT(*) as count FROM `$tableName`")->first();
            echo "<li><strong>$tableName</strong>: {$count->count} records</li>";
        }
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Could not retrieve database status: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
h1, h2 { color: #333; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>

<?php
/**
 * System Settings Management Class
 * File: hockey_system/classes/SystemSettings.php
 */

class SystemSettings {
    private $db;
    private $cache = [];
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Get a system setting value
     */
    public function get($key, $default = null) {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        
        $result = $this->db->query("SELECT setting_value, setting_type FROM system_settings WHERE setting_key = ?", [$key]);
        
        if ($result->count() > 0) {
            $setting = $result->first();
            $value = $this->castValue($setting->setting_value, $setting->setting_type);
            $this->cache[$key] = $value;
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Set a system setting value
     */
    public function set($key, $value, $description = null) {
        // Determine type
        $type = $this->determineType($value);
        $stringValue = $this->valueToString($value);
        
        // Check if setting exists
        $existing = $this->db->query("SELECT id FROM system_settings WHERE setting_key = ?", [$key]);
        
        if ($existing->count() > 0) {
            // Update existing
            $updateData = [
                'setting_value' => $stringValue,
                'setting_type' => $type
            ];
            if ($description !== null) {
                $updateData['description'] = $description;
            }
            
            $this->db->update('system_settings', $existing->first()->id, $updateData);
        } else {
            // Create new
            $this->db->insert('system_settings', [
                'setting_key' => $key,
                'setting_value' => $stringValue,
                'setting_type' => $type,
                'description' => $description
            ]);
        }
        
        // Update cache
        $this->cache[$key] = $value;
        return true;
    }
    
    /**
     * Get all settings as array
     */
    public function getAll() {
        $result = $this->db->query("SELECT * FROM system_settings ORDER BY setting_key");
        $settings = [];
        
        foreach ($result->results() as $setting) {
            $settings[$setting->setting_key] = [
                'value' => $this->castValue($setting->setting_value, $setting->setting_type),
                'type' => $setting->setting_type,
                'description' => $setting->description
            ];
        }
        
        return $settings;
    }
    
    /**
     * Cast string value to appropriate type
     */
    private function castValue($value, $type) {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($value) ? (float)$value : 0;
            case 'json':
                return json_decode($value, true) ?? [];
            default:
                return $value;
        }
    }
    
    /**
     * Convert value to string for storage
     */
    private function valueToString($value) {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        } elseif (is_array($value) || is_object($value)) {
            return json_encode($value);
        } else {
            return (string)$value;
        }
    }
    
    /**
     * Determine the type of a value
     */
    private function determineType($value) {
        if (is_bool($value)) {
            return 'boolean';
        } elseif (is_numeric($value)) {
            return 'number';
        } elseif (is_array($value) || is_object($value)) {
            return 'json';
        } else {
            return 'text';
        }
    }
}

/**
 * Database Maintenance Script
 * File: hockey_system/maintenance/cleanup.php
 * Run periodically to clean up old data
 */

function cleanupOldData() {
    global $db;
    
    // Delete draft orders older than 30 days
    $db->query("DELETE FROM orders WHERE status = 'draft' AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    
    // Delete orphaned order items (shouldn't happen with foreign keys, but just in case)
    $db->query("DELETE oi FROM order_items oi LEFT JOIN orders o ON oi.order_id = o.id WHERE o.id IS NULL");
    
    // Clean up old generated images that aren't referenced
    $db->query("DELETE FROM jersey_designs WHERE image_path NOT LIKE 'generated_images/%' OR image_path = ''");
    
    echo "Database cleanup completed\n";
}
?>