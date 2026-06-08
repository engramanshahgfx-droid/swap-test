#!/bin/bash

# Image Upload Fix Script
# Run this script on the production server after deployment

set -e

echo ""
echo "🛠️  Image Upload System Fix"
echo ""

cd "$(dirname "$0")" || exit 1

echo "📍 Current directory: $(pwd)"
echo ""

# Check if Laravel is installed
if [ ! -f "artisan" ]; then
    echo "❌ Laravel installation not found"
    exit 1
fi

echo "✅ Laravel installation found"
echo ""

# Run setup
echo "Running setup..."
php artisan setup:image-upload
echo ""

# Run diagnostics
echo "Running diagnostics..."
php artisan diagnose:image-upload
echo ""

echo "✅ Image upload system is configured and ready!"
echo ""
echo "📝 Next steps:"
echo "  1. Clear caches: php artisan config:cache"
echo "  2. Test upload with: php artisan tinker"
echo "  3. Check logs: tail -f storage/logs/laravel.log | grep -i image"
echo ""
