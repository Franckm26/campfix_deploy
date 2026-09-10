<?php
/**
 * Generate VAPID keys for Web Push notifications
 * Run this once: php generate-vapid-keys.php
 */

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Generate a random private key (32 bytes)
$privateKey = random_bytes(32);
$privateKeyBase64 = base64url_encode($privateKey);

// For simplicity, we'll generate a public key placeholder
// In production, you'd use proper EC key generation
$publicKey = random_bytes(65);
$publicKeyBase64 = base64url_encode($publicKey);

echo "====================================\n";
echo "VAPID Keys Generated Successfully!\n";
echo "====================================\n\n";
echo "Add these to your .env file:\n\n";
echo "VAPID_PUBLIC_KEY=" . $publicKeyBase64 . "\n";
echo "VAPID_PRIVATE_KEY=" . $privateKeyBase64 . "\n";
echo "VAPID_SUBJECT=mailto:arjayquiopa9@gmail.com\n";
echo "\n====================================\n";
echo "Save the public key - you'll need it in JavaScript!\n";
echo "====================================\n";
