# Backend

Symfony/API Platform backend projekt.

## Portok

- Backend: `http://localhost:${BACKEND_HTTP_PORT}`
- phpMyAdmin: `http://localhost:${PHPMYADMIN_PORT}`

## Inditas

1. A repo gyokerbol egyszer futtasd a bootstrapet:

```sh
cp project.config.dist project.config
sh scripts/project-init.sh
```

Ez legeneralja a `backend/.env` fajlt (a Docker Compose innen olvassa a valtozokat).

2. Inditas a backend mappabol:

```sh
cd backend
docker compose up -d --build
```

## Megjegyzesek

- App gyoker: `backend/site`
- Docker valtozok forrasa: `backend/.env`
- Template: `backend/docker/.env.dist`
- Pinelt verziok: `mysql:8.4.8`, `redis:8.4.0`, `elasticsearch:8.19.11`
- Feltoltesi limitek (Nginx): `client_max_body_size 50M`
- Feltoltesi limitek (PHP): `upload_max_filesize=15M`, `post_max_size=50M`, `max_file_uploads=25`
