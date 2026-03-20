# Architecture: polyfill-intl-normalizer

## Purpose

Provides a pure-PHP fallback for the `Normalizer` class from the `intl` extension,
enabling Unicode normalization (NFC, NFD, NFKC, NFKD) on systems without `intl`.

## Directory Structure

```
Normalizer.php   # Pure-PHP implementation wrapping or emulating Normalizer::normalize()
bootstrap.php    # Conditionally defines the Normalizer class if intl is absent
Resources/       # Unicode normalization data tables
```

## Key Design Decisions

The polyfill first attempts to use the native `intl` Normalizer; it only falls back to
the pure-PHP implementation when the extension is absent. Unicode normalization data is
precompiled into PHP arrays for fast lookup.

## Extension Points

None — drop-in class polyfill matching the native `Normalizer` class API.
