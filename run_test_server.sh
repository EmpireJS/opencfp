#!/bin/bash

echo "                               __       "
echo "                              / _|      "
echo "   ___  _ __   ___ _ __   ___| |_ _ __  "
echo "  / _ \| '_ \ / _ \ '_ \ / __|  _| '_ \ "
echo " | (_) | |_) |  __/ | | | (__| | | |_) |"
echo "  \___/| .__/ \___|_| |_|\___|_| | .__/ "
echo "       | |                       | |    "
echo "       |_|                       |_|    "
echo ""
echo "Running PHP internal server..."
echo ""

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PUBLIC="${DIR}/web"

cd "${PUBLIC}"
php -S localhost:4000 -t "${PUBLIC}" "${PUBLIC}/_router.php"
