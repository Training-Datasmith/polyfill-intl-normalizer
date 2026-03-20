<?php

declare(strict_types=1);

/**
 * Example: Unicode normalization with polyfill-intl-normalizer.
 *
 * Install:
 *   composer require symfony/polyfill-intl-normalizer
 *
 * Normalization ensures that equivalent Unicode strings have an identical binary representation.
 * Use NFC for general text storage; NFD for canonical decomposition; NFKC for compatibility.
 */

// Normalize to NFC (Canonical Decomposition, followed by Canonical Composition)
$nfc = Normalizer::normalize("é", Normalizer::NFC);

// Check if a string is already normalized
if (Normalizer::isNormalized("café", Normalizer::NFC)) {
    echo "Already NFC normalized\n";
}

// Normalize user input before database storage to ensure consistent comparison
$rawInput = "\u{0065}\u{0301}"; // 'e' + combining acute accent
$normalized = Normalizer::normalize($rawInput, Normalizer::NFC);
// Now $normalized === 'é' (single precomposed character U+00E9)
