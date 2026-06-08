#!/bin/bash

# Production Image Upload Fix - Step by Step
# Run these commands on the production server

echo "🔧 Fixing git conflict and deploying image upload system..."
echo ""

cd /home/u710227726/domains/flightswap.co/public_html

# Step 1: Stash local changes (the .gitignore conflict)
echo "📍 Step 1: Resolving git conflict..."
git stash
echo "✅ Local changes stashed"
echo ""

# Step 2: Complete the git pull
echo "📍 Step 2: Pulling latest code..."
git pull origin main
echo "✅ Code updated"
echo ""

# Step 3: Clear Laravel caches
echo "📍 Step 3: Clearing Laravel caches..."
php artisan optimize:clear
echo "✅ Caches cleared"
echo ""

# Step 4: Run setup
echo "📍 Step 4: Setting up image upload system..."
php artisan setup:image-upload
echo "✅ Setup complete"
echo ""

# Step 5: Recache configuration
echo "📍 Step 5: Rebuilding cache..."
php artisan config:cache
echo "✅ Configuration cached"
echo ""

# Step 6: Verify
echo "📍 Step 6: Verifying setup..."
php artisan diagnose:image-upload
echo ""

echo "✅ All done! Image upload system is ready."
