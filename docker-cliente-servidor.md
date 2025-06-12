# Configuración de entorno Ubuntu con servicios y Filebeat

Este entorno simula un servidor Ubuntu con Apache, PostgreSQL, y agentes de Filebeat y Auditbeat para el envío de logs al stack ELK.

>  Este entorno **debe ejecutarse luego de haber levantado la pila ELK**. Si no, fallará por dependencias de red o servicios.

## Estructura esperada

En el directorio `/ubuntu-server` se espera que exista una carpeta vacía llamada `apache-logs`:

```
/ubuntu-server
│
├── apache-logs/          # Carpeta vacía, donde Apache escribirá sus logs
├── www/                  # Archivos del sitio web (HTML/PHP)
├── apache-scripts/       # Scripts adicionales para Apache (opcional)
├── postgres/             # Configuración y scripts de PostgreSQL
├── filebeat/             # Configuración de Filebeat
├── auditbeat/            # Configuración de Auditbeat
└── docker-compose.yml    # Compose de este entorno
```

## Levantar los servicios

Desde el directorio `/ubuntu-server`, ejecutar:

```bash
docker compose up -d
```

## Servicios incluidos

* **syslog-ubuntu**: Contenedor Ubuntu que instala y configura `rsyslog` para enviar todos los logs a Filebeat.
* **apache**: Servidor web Apache con logs montados en la carpeta `apache-logs`.
* **postgres**: Base de datos PostgreSQL, con logs montados para ser recolectados.
* **filebeat**: Agente de Filebeat que reenvía los logs de Apache y PostgreSQL a Logstash.
* **auditbeat**: Agente que monitorea actividad del sistema (procesos, integridad, auditorías) y la envía al stack ELK.

## Notas adicionales

* El contenedor `filebeat` requiere acceso de solo lectura a los logs generados por Apache y PostgreSQL.
* `auditbeat` utiliza permisos especiales (`cap_add`) para leer datos del sistema simulado.
* Todos los contenedores están conectados a la red del stack ELK (`elk-stack_elk-net`) para enviar sus datos correctamente.

