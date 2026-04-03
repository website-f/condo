# Agent Portal Structure

This repo now contains both applications on one domain without extra web-server rules.

## Public URLs

- `/` is WordPress
- `/agent` is the Laravel agent portal

WordPress keeps control of the root site. The real `agent/` directory is served before WordPress rewrites, so `/agent/*` does not clash with WordPress URLs.

## Folder Layout

- `agent/` contains only the Laravel public entry files and public uploads
- `_agent/` contains the full Laravel application

## Important Files

- `agent/index.php` boots the Laravel app from `_agent`
- `_agent/bootstrap/app.php` forces Laravel `public_path()` to use `agent/`
- `_agent/config/filesystems.php` writes public uploads directly into `agent/storage`

## Deployment Notes

Place the `condo` folder contents inside `public_html`.

Common Laravel commands should now be run from `_agent/`, for example:

```bash
cd _agent
composer install
php artisan optimize:clear
```

If you build frontend assets later, run the build from `_agent/`. The compiled files will be written into `agent/build`.
