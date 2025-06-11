# ELK / OpenSearch Logging & Monitoring Stack

Este proyecto implementa una pila de monitoreo basada en **OpenSearch**, **Logstash**, **Filebeat**, **Auditbeat**, y servicios simulados como **Apache**, **PostgreSQL** y un sistema que genera eventos de firewall y de sistema de Linux. El objetivo es centralizar logs, detectar errores y generar dashboards con métricas y alertas.

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

2. Levantá todos los servicios:

```bash
./generate-certs.sh
docker-compose -f elk-stack/docker-compose.yaml up -d --build
docker-compose -f ubuntu-server/docker-compose.yaml up -d --build
```

3. Accedé a:

- **OpenSearch Dashboards:** http://localhost:5601  
- **Aplicación web de logs:** http://localhost:8080  
- **Logs de PostgreSQL y sistema:** gestionados por Filebeat desde sus volúmenes

---

## Casos de uso implementados

### 1. Errores HTTP (500, 503)

- Ir a http://localhost:8080
- Hacer clic en **"Return HTTP 500"**
- Se genera un error HTTP que debería loguearse en los logs de Apache y ser detectado por Filebeat

#### Alerta 

1. Ir a **Alerting → Monitors**
2. Crear un Monitor nuevo:
   - Tipo: **Query-level monitor**
   - Índice: `logstash-*`
   - Frecuencia: cada 1 minuto

3. Usar una query como esta para errores HTTP:

```json
{
  "size": 0,
  "query": {
    "bool": {
      "must": [
        { "match": { "log.level": "error" }},
        { "match_phrase": { "message": "500" }},
        { "range": { "@timestamp": { "gte": "now-1m" }} }
      ]
    }
  }
}
```

4. Crear una **alerta** asociada al monitor que envíe notificaciones 


### 2. Consultas lentas en PostgreSQL

- Entrar al contenedor de PostgreSQL:

```bash
docker exec -it postgres bash
```

- Ejecutar el script:

```bash
bash /scripts/generate_postgres_logs.sh
```

- Esto genera varias consultas, algunas con sleeps > 3 segundos para simular lentitud

### 3. Detección de servicios caídos

- Apagar el contenedor de servicio de Apache:

```bash
docker stop apache
```

- Esperar a que la alerta lo detecte en OpenSearch (ver sección de Monitores)

### 4. Detección de IPs desconocidas en inicio de sesion

  - Ir a http://localhost:8080
  - Hacer clic en **"Log In log simulator"** 
  - Hacer clic en **"Generate 10 random login logs"** 
  - Se genera un error HTTP que debería loguearse en los logs de Apache y ser detectado por Filebeat
---

## Dashboards y Visualizaciones

### Cómo crear dashboards:

1. Entrar a http://localhost:5601
2. Ir a **Visualize → Create Visualization**
3. Crear gráficos de tipo `Line`, `Bar`, `Pie` u otros con agregaciones como:
   - Número de errores HTTP 500 por minuto
   - Cantidad de consultas lentas en PostgreSQL
   - IPs bloqueadas por UFW

4. Combinar visualizaciones en **Dashboards → Create Dashboard**

---
