# ELK / OpenSearch Logging & Monitoring Stack

## Introducción

Este proyecto es una implementación de la pila ELK (Elasticsearch, Logstash, Kibana), reemplazando Elasticsearch y Kibana por **OpenSearch** y **OpenSearch Dashboards**. El objetivo principal es centralizar, procesar y visualizar logs de manera eficiente, utilizando herramientas de código abierto.

## Contenido

* [Configuración de la pila ELK](./configuracion-elk.md)
* [Configuración de Docker (cliente-servidor / Ubuntu Server)](./docker-cliente-servidor.md)
* [Configuración de host Ubuntu con UFW](./ubuntu-ufw.md)
* [Alertas e informes en OpenSearch Dashboards](./opensearch-alertas-reportes.md)

---


## Requisitos

- Docker y Docker Compose
- Sistema operativo Linux/macOS (para rutas compartidas con logs)
- Puertos disponibles: `5601`, `9200`, `5044`, `8080`, `5432`, `6000` y `9600`.

---

## Cómo levantar el proyecto

1. Cloná este repositorio:

```bash
git clone https://github.com/Ian-Arnott/TPE-Redes.git
cd TPE-Redes
```
