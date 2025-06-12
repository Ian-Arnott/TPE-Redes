# Configuración del OpenSearch Stack

## Requisitos

Antes de comenzar, es necesario tener instalados:

* [Docker](https://docs.docker.com/get-docker/)
* [Docker Compose](https://docs.docker.com/compose/install/)

## Paso 1: Generación de certificados SSL

Desde la raíz del proyecto, ejecutar el siguiente script para generar los certificados necesarios:

```bash
./generate-certs.sh
```

>  Si aparece un error de permisos, se puede solucionar con:

```bash
chmod +x generate-certs.sh
```

Esto generará los archivos de certificados necesarios en el directorio `certs/`, utilizados por OpenSearch, Dashboards y Logstash para cifrar las comunicaciones.

## Paso 2: Levantar los servicios de ELK

Una vez generados los certificados, ejecutar Docker Compose desde el directorio raíz utilizando el archivo ubicado en `elk-stack/docker-compose.yml`:

```bash
docker compose -f elk-stack/docker-compose.yml up -d
```

Esto levantará los siguientes servicios:

* `opensearch`
* `opensearch-dashboards`
* `logstash`
* `smtp-relay` (para alertas por mail desde Dashboards)

## Paso 3: Configuración del correo electrónico para alertas

El servicio `smtp-relay` utiliza Gmail como servidor de correo para enviar alertas desde OpenSearch Dashboards. Para que esto funcione correctamente, debés generar una **contraseña de aplicación** desde tu cuenta de Google. Acá te dejamos los pasos:

### Cómo generar una contraseña de aplicación en Gmail

1. Ingresá a [tu cuenta de Google](https://myaccount.google.com/)
2. Ir a la sección **Seguridad**
3. Activá la **verificación en dos pasos** si aún no lo hiciste
4. Una vez activada, aparecerá la opción **Contraseñas de aplicación**
5. Seleccioná una aplicación (por ejemplo, “Correo”) y un dispositivo (por ejemplo, “Servidor Docker”)
6. Google te mostrará una contraseña de 16 caracteres
7. Copiá esa contraseña y reemplazá el valor de `RELAY_PASSWORD` en el `docker-compose.yml`

>  Asegurate también de reemplazar los campos `RELAY_USERNAME` y `OPENSEARCH_DASHBOARDS_SMTP_FROM` con tu propia dirección de correo electrónico.

## Nota sobre el arranque

Tené en cuenta que los servicios de OpenSearch pueden tardar varios minutos en iniciar completamente, especialmente la primera vez.

Para verificar si los servicios están corriendo correctamente:

```bash
docker logs -f opensearch
docker logs -f opensearch-dashboards
```

Una vez iniciados, podés acceder al panel de visualización en tu navegador en:

```
https://localhost:5601
```

Es posible que tu navegador muestre una advertencia de certificado no válido (por ser auto-firmado). Podés continuar de todos modos.

