#!/bin/bash

# Esperar a que MySQL esté listo
echo "⏳ Esperando a que MySQL esté disponible..."
while ! nc -z mysql 3306 2>/dev/null; do
    echo "  MySQL no está listo, esperando 2 segundos..."
    sleep 2
done

echo "✅ MySQL está disponible"

# Ejecutar setup.php si existe
if [ -f "/var/www/html/cli/setup.php" ]; then
    echo "🚀 Ejecutando setup de base de datos..."
    php /var/www/html/cli/setup.php
fi

echo "✅ Iniciando Apache..."

# Iniciar Apache
exec apache2-foreground
