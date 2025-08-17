#!/usr/bin/env sh

PLUGIN_NAME="cvs-for-wooCommerce"

# +---------------------------+
# |                           |
# |    Generating ZIP file    |
# |                           |
# +---------------------------+

PROJECT_PATH=$(pwd)
BUILD_PATH="${PROJECT_PATH}/build"
DEST_PATH="$BUILD_PATH/$PLUGIN_NAME"

echo "Generating build directory..."
rm -rf "$BUILD_PATH"
mkdir -p "$DEST_PATH"

# Fix: Ensure `find` works correctly and set a default value
composer_dependencies=$(find "$PROJECT_PATH" -maxdepth 1 -type f -name 'composer.json' -print0 | xargs -0 wc -l | awk '{print $1}')
composer_dependencies=${composer_dependencies:-0}  # Default to 0 if empty

if [ "$composer_dependencies" -gt 0 ]; then
	echo "Installing PHP dependencies..."
	composer install -d "$PROJECT_PATH" --no-dev || exit "$?"
fi

echo "Syncing files..."
if [ -f "$PROJECT_PATH/.distignore" ]; then
    rsync --recursive --checksum --include="$PROJECT_PATH/.env" --exclude-from="$PROJECT_PATH/.distignore" "$PROJECT_PATH/" "$DEST_PATH/" --delete --delete-excluded
else
    rsync --recursive --checksum --include="$PROJECT_PATH/.env" "$PROJECT_PATH/" "$DEST_PATH/" --delete
fi

echo "Generating zip file..."

cd "$BUILD_PATH" || exit
zip --quiet --recurse-paths "${PLUGIN_NAME}.zip" "$PLUGIN_NAME/"

cd "$PROJECT_PATH" || exit
echo "Moving zip to $(dirname "$PROJECT_PATH")"
mv "$BUILD_PATH/${PLUGIN_NAME}.zip" "$(dirname "$PROJECT_PATH")"
echo "${PLUGIN_NAME}.zip file generated!"

echo "Cleaning up..."
rm -r "${BUILD_PATH}"

printf "Build done!\n"
