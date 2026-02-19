#!/bin/bash

# Générer une clé secrète sécurisée pour JWT
echo "Génération d'une clé secrète JWT..."
SECRET_KEY=$(openssl rand -base64 32)

echo ""
echo "==================================================="
echo "Clé secrète générée:"
echo "==================================================="
echo ""
echo "$SECRET_KEY"
echo ""
echo "==================================================="
echo "Copiez cette clé et mettez à jour:"
echo "backend/config/JWTHandler.php"
echo "==================================================="
echo ""
echo "Recherchez la ligne:"
echo "    private \$secret = 'your-secret-key-change-this';"
echo ""
echo "Et remplacez par:"
echo "    private \$secret = '$SECRET_KEY';"
