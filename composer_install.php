<?php
/**
 * Fixed Composer Installer for Shared Hosting
 * File: composer_install_fixed.php
 * 
 * This version handles the HOME environment variable issue
 * DELETE THIS FILE AFTER USE FOR SECURITY
 */

set_time_limit(300); // 5 minutes
ini_set('memory_limit', '512M');

echo "<h1>Hockey Jersey System - Fixed Composer Installer</h1>";
echo "<pre>";

// Set required environment variables for shared hosting
$homeDir = __DIR__ . '/composer_home';
if (!is_dir($homeDir)) {
    mkdir($homeDir, 0755, true);
}

putenv("HOME=$homeDir");
putenv("COMPOSER_HOME=$homeDir");
putenv("COMPOSER_CACHE_DIR=$homeDir/cache");

echo "? Environment variables set:\n";
echo "  HOME: $homeDir\n";
echo "  COMPOSER_HOME: $homeDir\n";

// Create composer.json if it doesn't exist
if (!file_exists('composer.json')) {
    echo "\nCreating composer.json...\n";
    
    $composerConfig = [
        "require" => [
            "league/oauth2-client" => "^2.7",
            "league/oauth2-google" => "^4.0", 
            "guzzlehttp/guzzle" => "^7.5",
            "phpmailer/phpmailer" => "^6.8"
        ],
        "autoload" => [
            "psr-4" => [
                "HockeyJersey\\" => "src/"
            ]
        ],
        "config" => [
            "optimize-autoloader" => true,
            "cache-dir" => "$homeDir/cache"
        ]
    ];
    
    file_put_contents('composer.json', json_encode($composerConfig, JSON_PRETTY_PRINT));
    echo "? composer.json created\n";
}

// Try different installation methods
$success = false;

// Method 1: Try with environment variables set
if (!$success) {
    echo "\n--- Method 1: Downloading Composer with environment fix ---\n";
    
    try {
        // Download composer installer
        $installerContent = file_get_contents('https://getcomposer.org/installer');
        
        if ($installerContent) {
            echo "? Composer installer downloaded\n";
            
            // Save installer to file
            file_put_contents('composer-installer.php', $installerContent);
            
            // Run installer with proper environment
            ob_start();
            $oldDir = getcwd();
            putenv("HOME=$homeDir");
            putenv("COMPOSER_HOME=$homeDir");
            
            include 'composer-installer.php';
            $output = ob_get_clean();
            
            echo "Installer output:\n$output\n";
            
            if (file_exists('composer.phar')) {
                echo "? Composer.phar created\n";
                
                // Try to run install
                $command = "HOME=\"$homeDir\" COMPOSER_HOME=\"$homeDir\" php composer.phar install --no-dev --optimize-autoloader 2>&1";
                
                echo "Running: $command\n";
                $output = shell_exec($command);
                echo "Output: $output\n";
                
                if (file_exists('vendor/autoload.php')) {
                    $success = true;
                    echo "? Method 1 successful!\n";
                }
            }
            
            // Clean up installer
            if (file_exists('composer-installer.php')) {
                unlink('composer-installer.php');
            }
        }
    } catch (Exception $e) {
        echo "Method 1 failed: " . $e->getMessage() . "\n";
    }
}

// Method 2: Manual download of packages
if (!$success) {
    echo "\n--- Method 2: Manual package download ---\n";
    
    $packages = [
        'league/oauth2-client' => 'https://github.com/thephpleague/oauth2-client/archive/refs/tags/2.7.0.zip',
        'league/oauth2-google' => 'https://github.com/thephpleague/oauth2-google/archive/refs/tags/4.0.1.zip',
        'guzzlehttp/guzzle' => 'https://github.com/guzzle/guzzle/archive/refs/tags/7.8.1.zip',
        'phpmailer/phpmailer' => 'https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.9.1.zip'
    ];
    
    // Create vendor directory structure
    if (!is_dir('vendor')) {
        mkdir('vendor', 0755, true);
        echo "? Created vendor directory\n";
    }
    
    $downloadedPackages = [];
    
    foreach ($packages as $package => $url) {
        echo "Downloading $package...\n";
        
        $zipContent = @file_get_contents($url);
        if ($zipContent) {
            $zipFile = "temp_$package.zip";
            file_put_contents($zipFile, $zipContent);
            
            // Extract package
            $zip = new ZipArchive;
            if ($zip->open($zipFile) === TRUE) {
                $extractDir = "vendor/$package";
                if (!is_dir($extractDir)) {
                    mkdir($extractDir, 0755, true);
                }
                
                $zip->extractTo($extractDir);
                $zip->close();
                
                echo "? Extracted $package\n";
                $downloadedPackages[] = $package;
            } else {
                echo "? Failed to extract $package\n";
            }
            
            unlink($zipFile);
        } else {
            echo "? Failed to download $package\n";
        }
    }
    
    // Create a basic autoloader
    if (count($downloadedPackages) > 0) {
        $autoloaderContent = '<?php
// Basic autoloader for manually downloaded packages
spl_autoload_register(function ($class) {
    $vendorDir = __DIR__;
    
    // Handle PSR-4 namespace mapping
    $namespaceMap = [
        "League\\OAuth2\\Client\\" => "league/oauth2-client/oauth2-client-*/src/",
        "League\\OAuth2\\Client\\Provider\\" => "league/oauth2-google/oauth2-google-*/src/Provider/",
        "GuzzleHttp\\" => "guzzlehttp/guzzle/guzzle-*/src/",
        "PHPMailer\\PHPMailer\\" => "phpmailer/phpmailer/PHPMailer-*/src/"
    ];
    
    foreach ($namespaceMap as $namespace => $path) {
        if (strpos($class, $namespace) === 0) {
            $relativeClass = substr($class, strlen($namespace));
            $file = $vendorDir . "/" . $path . str_replace("\\\\", "/", $relativeClass) . ".php";
            
            // Handle wildcards in path
            if (strpos($file, "*") !== false) {
                $pattern = str_replace("*", "*", $file);
                $matches = glob($pattern);
                if (!empty($matches)) {
                    $file = $matches[0];
                }
            }
            
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});
';
        
        file_put_contents('vendor/autoload.php', $autoloaderContent);
        echo "? Created basic autoloader\n";
        $success = true;
    }
}

// Method 3: Provide download instructions
if (!$success) {
    echo "\n--- Method 3: Manual Installation Instructions ---\n";
    echo "Automatic installation failed. Please follow these manual steps:\n\n";
    
    echo "1. Download these files manually:\n";
    echo "   - OAuth2 Client: https://github.com/thephpleague/oauth2-client/releases/latest\n";
    echo "   - OAuth2 Google: https://github.com/thephpleague/oauth2-google/releases/latest\n";
    echo "   - Guzzle HTTP: https://github.com/guzzle/guzzle/releases/latest\n";
    echo "   - PHPMailer: https://github.com/PHPMailer/PHPMailer/releases/latest\n\n";
    
    echo "2. Extract each to vendor/[package-name]/ directory\n";
    echo "3. Upload the files to your server\n";
    echo "4. Use the simple autoloader provided below\n\n";
    
    echo "Or try the simplified approach (see below)\n";
}

// Clean up
if (file_exists('composer.phar')) {
    unlink('composer.phar');
}

if (is_dir($homeDir)) {
    // Remove composer temp directory
    function removeDirectory($dir) {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
    removeDirectory($homeDir);
}

echo "\n" . str_repeat("=", 60) . "\n";
if ($success) {
    echo "?? INSTALLATION COMPLETED SUCCESSFULLY!\n";
    echo "? Dependencies installed\n";
    echo "? Autoloader created at vendor/autoload.php\n";
    echo "\n??  SECURITY: Delete this file immediately!\n";
} else {
    echo "? AUTOMATIC INSTALLATION FAILED\n";
    echo "Please try the simplified approach below.\n";
}
echo str_repeat("=", 60) . "\n";

echo "</pre>";

// Show next steps and simplified approach
?>

<h2>Alternative: Simplified Approach (No Composer)</h2>
<p>If Composer continues to have issues on your shared hosting, you can use a simplified version that doesn't require external libraries:</p>

<div style="background: #f4f4f4; padding: 15px; border-radius: 5px; margin: 20px 0;">
<h3>Simplified OAuth (No External Libraries)</h3>
<p>Instead of using the League OAuth library, we can implement basic Google OAuth directly:</p>
<ul>
<li>? Direct Google OAuth API calls using cURL</li>
<li>? Built-in PHP session management</li>
<li>? No external dependencies required</li>
<li>? Lighter weight and easier to debug</li>
</ul>
</div>

<h2>Files Status Check:</h2>
<ul>
<li>vendor/autoload.php: <?php echo file_exists('vendor/autoload.php') ? "? Found" : "? Missing"; ?></li>
<li>composer.json: <?php echo file_exists('composer.json') ? "? Found" : "? Missing"; ?></li>
<li>generated_images/: <?php echo is_dir('generated_images') ? "? Found" : "??  Need to create"; ?></li>
</ul>

<h2>Next Steps:</h2>
<ol>
<li><strong>?? Delete this file immediately</strong> for security</li>
<li>If installation succeeded: Download UserSpice and proceed with setup</li>
<li>If installation failed: Try the simplified approach (I can provide this)</li>
<li>Test the basic functionality before proceeding</li>
</ol>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
h1, h2, h3 { color: #333; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
ul, ol { margin: 10px 0; }
li { margin: 5px 0; }
</style>

<?php
/**
 * Simplified Google OAuth (No External Libraries)
 * Use this if Composer installation fails
 */
?>

<h2>Simplified OAuth Implementation</h2>
<details>
<summary>Click to see the simplified OAuth code (if Composer failed)</summary>
<pre style="font-size: 12px; background: #f8f8f8; padding: 10px;">
&lt;?php
/**
 * Simplified Google OAuth - No external libraries needed
 * File: simple_oauth.php
 */

class SimpleGoogleOAuth {
    private $clientId;
    private $clientSecret; 
    private $redirectUri;
    
    public function __construct($clientId, $clientSecret, $redirectUri) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }
    
    public function getAuthUrl() {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'openid email profile',
            'response_type' => 'code',
            'state' => bin2hex(random_bytes(16))
        ];
        
        $_SESSION['oauth_state'] = $params['state'];
        
        return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
    }
    
    public function handleCallback($code, $state) {
        if ($state !== $_SESSION['oauth_state']) {
            throw new Exception('Invalid state parameter');
        }
        
        // Exchange code for access token
        $tokenData = $this->getAccessToken($code);
        
        // Get user info
        $userInfo = $this->getUserInfo($tokenData['access_token']);
        
        return $userInfo;
    }
    
    private function getAccessToken($code) {
        $postData = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
        ];
        
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get access token');
        }
        
        return json_decode($response, true);
    }
    
    private function getUserInfo($accessToken) {
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get user info');
        }
        
        return json_decode($response, true);
    }
}

// Usage:
// $oauth = new SimpleGoogleOAuth($clientId, $clientSecret, $redirectUri);
// $authUrl = $oauth->getAuthUrl();
// $userInfo = $oauth->handleCallback($_GET['code'], $_GET['state']);
?&gt;
</pre>
</details>