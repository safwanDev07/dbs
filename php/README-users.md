# Database Users Setup

Dit project gebruikt twee verschillende SQL gebruikers met verschillende rechten voor betere beveiliging.

## Gebruikers

### 1. Admin User (`admin_user`)
- **Wachtwoord:** `admin_password_secure`
- **Rechten:** Alle rechten BEHALVE DROP
- **Kan wel:**
  - SELECT, INSERT, UPDATE, DELETE
  - CREATE (tabellen, databases)
  - ALTER (tabellen wijzigen)
  - INDEX (indexen maken/verwijderen)
  - REFERENCES (foreign keys)
  - CREATE TEMPORARY TABLES
  - LOCK TABLES
  - CREATE ROUTINE (stored procedures/functions)
  - ALTER ROUTINE
  - EXECUTE (stored procedures uitvoeren)
- **Kan niet:**
  - DROP (tabellen/databases verwijderen) - voor extra veiligheid

### 2. App User (`app_user`)
- **Wachtwoord:** `user_password_secure`
- **Rechten:** Alleen CRUD operaties
- **Kan wel:**
  - SELECT (gegevens lezen)
  - INSERT (nieuwe gegevens toevoegen)
  - UPDATE (gegevens wijzigen)
  - DELETE (gegevens verwijderen)
- **Kan niet:**
  - CREATE, DROP, ALTER (geen structurele wijzigingen)
  - INDEX, REFERENCES
  - CREATE ROUTINE, ALTER ROUTINE, EXECUTE
  - LOCK TABLES, CREATE TEMPORARY TABLES

## Waarom deze setup?

1. **Beveiliging:** De app gebruikt een gebruiker met minimale rechten nodig voor normaal gebruik
2. **Voorkomen van ongelukken:** Admin kan geen tabellen/databases per ongeluk verwijderen
3. **Principle of Least Privilege:** Elke gebruiker heeft alleen de rechten die nodig zijn

## Gebruik in code

### Voor normale app operaties (CRUD):
```php
$conn = new mysqli("localhost", "app_user", "user_password_secure", "dbsp2");
// Kan SELECT, INSERT, UPDATE, DELETE uitvoeren
```

### Voor admin taken (database beheer):
```php
$conn = new mysqli("localhost", "admin_user", "admin_password_secure", "dbsp2");
// Kan alles behalve DROP
```

## Setup uitvoeren

1. Zorg dat je een `.env` bestand hebt met root credentials:
```
DB_HOST=localhost
DB_USER=root
DB_PASS=root
DB_NAME=dbsp2
```

2. Voer het setup script uit in je browser:
   - http://localhost/Jaar%202/periode_2/dbs/php/setup-users.php

3. Het script maakt automatisch beide gebruikers aan en test hun rechten.

## Belangrijke opmerkingen

- De `app_user` wordt gebruikt in `login.php` en `regristreer.php`
- De `admin_user` kan gebruikt worden voor onderhoudstaken
- Root wordt alleen gebruikt voor de initiële setup
- Wachtwoorden zijn voorbeelden - verander ze in productie!