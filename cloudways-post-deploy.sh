#!/bin/bash

# Cloudways Post-Deployment Script for Maintenance Signature System
# This script runs after Cloudways Git deployment to set up the WordPress plugin

echo "🚀 Starting post-deployment setup for Maintenance Signature System..."

# Set correct file permissions for WordPress
echo "📁 Setting file permissions..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Make sure wp-config.php is not writable by others (security)
if [ -f "../../wp-config.php" ]; then
    chmod 600 ../../wp-config.php
    echo "✅ Secured wp-config.php permissions"
fi

# Ensure plugin directory has correct ownership (if running as web user)
echo "👤 Setting ownership..."
# Note: This may not work in all Cloudways environments due to user restrictions
chown -R $(whoami):$(whoami) . 2>/dev/null || echo "ℹ️ Could not change ownership (not an error)"

# Clear any WordPress cache if cache plugins are present
echo "🧹 Clearing caches..."
if [ -d "../../wp-content/cache" ]; then
    rm -rf ../../wp-content/cache/*
    echo "✅ Cleared wp-content/cache"
fi

# Clear object cache if present
if [ -f "../../wp-content/object-cache.php" ]; then
    echo "🗑️ Object cache detected, may need manual clearing"
fi

# Log deployment information
echo "📝 Logging deployment..."
DEPLOY_LOG="../../wp-content/maintenance-signature-system-deploy.log"
echo "$(date): Maintenance Signature System deployed successfully" >> "$DEPLOY_LOG"
echo "Commit: ${GIT_COMMIT:-unknown}" >> "$DEPLOY_LOG"
echo "Branch: ${GIT_BRANCH:-unknown}" >> "$DEPLOY_LOG"
echo "---" >> "$DEPLOY_LOG"

# Check if WordPress CLI is available for additional setup
if command -v wp &> /dev/null; then
    echo "🔧 WordPress CLI detected, running additional setup..."
    
    # Navigate to WordPress root
    cd ../..
    
    # Check if plugin is already active
    if wp plugin is-active maintenance-signature-system 2>/dev/null; then
        echo "✅ Plugin is already active"
    else
        echo "🔌 Attempting to activate plugin..."
        wp plugin activate maintenance-signature-system 2>/dev/null || echo "⚠️ Could not auto-activate plugin (manual activation needed)"
    fi
    
    # Flush rewrite rules to ensure custom routes work
    wp rewrite flush 2>/dev/null || echo "ℹ️ Could not flush rewrite rules"
    
    # Check database tables
    wp db query "SHOW TABLES LIKE 'wp_mss_%'" 2>/dev/null || echo "ℹ️ Could not check database tables"
    
    cd wp-content/plugins/maintenance-signature-system
else
    echo "ℹ️ WordPress CLI not available, skipping advanced setup"
fi

# Create a deployment success marker
touch .deployment-success
echo "$(date)" > .deployment-success

# Display final status
echo ""
echo "🎉 Post-deployment setup completed!"
echo "📋 Summary:"
echo "   ✅ File permissions set"
echo "   ✅ Cache cleared"
echo "   ✅ Deployment logged"
echo "   📍 Plugin location: wp-content/plugins/maintenance-signature-system"
echo ""
echo "🔗 Next steps:"
echo "   1. Visit WordPress admin to activate the plugin if not auto-activated"
echo "   2. Go to 'Maintenance System' in admin menu to start using"
echo "   3. Configure initial settings and add constructor names"
echo ""
echo "📞 Support: Check GitHub repository for documentation"
echo "   Repository: https://github.com/jameslai-sparkofy/maintenance-signature-system"
echo ""

exit 0