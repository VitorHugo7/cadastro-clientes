#!/bin/bash
set -e
cd -- "$(dirname -- "$0")"

# Usa PHP 8.3+ disponível no computador, inclusive instalações do MAMP.
php_executavel=""
for candidato in "$(command -v php || true)" /Applications/MAMP/bin/php/php8.*/bin/php; do
    if [ -x "$candidato" ] && "$candidato" -r 'exit(PHP_VERSION_ID >= 80300 && extension_loaded("pdo_sqlite") && extension_loaded("curl") ? 0 : 1);' 2>/dev/null; then
        php_executavel="$candidato"
        break
    fi
done

if [ -z "$php_executavel" ]; then
    echo 'É necessário PHP 8.3 ou superior, com pdo_sqlite e curl habilitados.'
    exit 1
fi

echo 'Cadastro de clientes: http://127.0.0.1:8085'
echo 'Mantenha este terminal aberto. Para encerrar, pressione Ctrl+C.'
exec "$php_executavel" -S 127.0.0.1:8085 -t public public/index.php
