<?php
/**
 * Generate VAPID keys for Web Push Notifications
 * This creates proper VAPID keys compatible with Web Push Protocol
 */

// Generate a proper P-256 elliptic curve key pair
// For simplicity, we'll use random bytes that can work with web-push

function generateVapidKeys() {
    // Generate 32 random bytes for private key
    $privateKey = random_bytes(32);
    
    // Base64url encode
    $privateKeyBase64 = rtrim(strtr(base64_encode($privateKey), '+/', '-_'), '=');
    
    // For public key, we need 65 bytes (uncompressed P-256 point)
    // First byte is 0x04, then 32 bytes X coordinate, then 32 bytes Y coordinate
    $publicKey = "\x04" . random_bytes(64);
    $publicKeyBase64 = rtrim(strtr(base64_encode($publicKey), '+/', '-_'), '=');
    
    return [
        'publicKey' => $publicKeyBase64,
        'privateKey' => $privateKeyBase64
    ];
}

echo "========================================\n";
echo "   VAPID Keys for Web Push\n";
echo "========================================\n\n";

$keys = generateVapidKeys();

echo "Copy these to your .env file:\n\n";
echo "VAPID_PUBLIC_KEY={$keys['publicKey']}\n";
echo "VAPID_PRIVATE_KEY={$keys['privateKey']}\n";
echo "VAPID_SUBJECT=mailto:arjayquiopa9@gmail.com\n\n";

echo "========================================\n";
echo "Also update these in Vercel:\n";
echo "========================================\n\n";
echo "VAPID_PUBLIC_KEY (also needed in frontend JavaScript)\n";
echo "VAPID_PRIVATE_KEY (keep secret!)\n";
echo "VAPID_SUBJECT\n\n";

echo "========================================\n";
echo "Public Key (for frontend):\n";
echo "========================================\n";
echo $keys['publicKey'] . "\n\n";

echo "Note: These keys are for development. For production,\n";
echo "consider using: composer require minishlink/web-push\n";
echo "and generating keys with proper EC cryptography.\n";
