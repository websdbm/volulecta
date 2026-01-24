# Volulecta

Servizio di raccomandazioni librarie personalizzate con bibliofili esperti.

## Requisiti

- Docker & Docker Compose
- PHP 8.2+
- Composer

## Setup Rapido

1. Clona il repository e copia il file di ambiente:
```bash
cp .env.example .env
```

2. Genera un segreto applicativo sicuro e aggiornalo in `.env`:
```bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

3. Avvia i container Docker:
```bash
docker-compose up -d
```

4. Installa le dipendenze:
```bash
docker-compose exec php composer install
```

5. L'applicazione sarà disponibile su: http://localhost:8080

## Endpoint Disponibili

- `GET /` - Homepage
- `GET /health` - Health check

## Struttura Progetto

```
/public          - Entry point e assets pubblici
/config          - Configurazione app, routes, dependencies
/src
  /Application   - Actions, Middleware, Validators, Policies
  /Domain        - Entities, Services, Repositories
  /Infrastructure - Persistence, External APIs, Mail, Push
  /Views         - Template Twig
/docker          - Dockerfile e configurazioni Docker
/migrations      - Migrations database
/logs            - Log applicativi
/cache           - Cache Twig
```

## Comandi Utili

```bash
# Avviare i container
docker-compose up -d

# Fermare i container
docker-compose down

# Vedere i log
docker-compose logs -f

# Accedere al container PHP
docker-compose exec php sh

# Accedere al database
docker-compose exec db mysql -u volulecta -pvolulecta volulecta

# Eseguire i test
docker-compose exec php composer test
```

## Sviluppo

Questo progetto utilizza:
- **Framework**: Slim 4
- **Template Engine**: Twig
- **DI Container**: PHP-DI
- **Database**: MariaDB 10.11
- **Logger**: Monolog

## License

Proprietary
