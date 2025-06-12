# Configuración de Alertas y Reportes en OpenSearch Dashboards

Este archivo explica cómo configurar notificaciones por correo electrónico en OpenSearch Dashboards, crear alertas automatizadas y generar reportes programados usando el servicio `smtp-relay` incluido en el stack.


## Paso 1: Crear un Email Sender

1. Ingresar a [https://localhost:5601](https://localhost:5601)
2. En el menú lateral, ir a **Management → Notifications**
3. Hacer clic en la pestaña **Email senders** y luego en **Create sender**
4. Completar los siguientes campos:

   - **Name**: `Gmail Sender`
   - **Email address**: `tu-email@gmail.com`
   - **SMTP host**: `smtp-relay`
   - **SMTP port**: `25`
   - **Encryption method**: None
   - **Sender type**: `SMTP`
5. Guardar.


##  Paso 2: Crear un Recipient Group

1. En la misma sección de **Notifications**, ir a la pestaña **Recipient groups**
2. Hacer clic en **Create recipient group**
3. Asignar un nombre, por ejemplo: `Admin Group`
4. Agregar las direcciones de correo que recibirán las alertas (por ejemplo: `tu-email@gmail.com`)
5. Guardar.

##  Paso 3: Crear un Notification Channel

1. Ir a la pestaña **Notification channels**
2. Hacer clic en **Create channel**
3. Configurar lo siguiente:

   - **Name**: `Email Alerts`
   - **Channel type**: `Email`
   - **Sender**: Elegir el sender creado (`Gmail Sender`)
   - **Recipients**: Seleccionar el grupo (`Admin Group`)

4. Guardar.

---

## Crear Alerta – Login desde IP inusual

### Objetivo

Detectar si una misma cuenta de email ha iniciado sesión desde más de una dirección IP en el último minuto.

### Pasos

1. Ir a **Opensearch Plugins → Alerting**
2. Hacer clic en **Monitors → Create Monitor**
3. Elegir:

   - **Monitor type**: `Per query monitor`
   - **Monitor defining method**: `Extraction query editor`
   - **Schedule**: Cada 1 minuto
   - **Data source**: `Index` → seleccionar índice relevante (`logstash-*`)
   - **Monitor name**: `Login desde IP inusual`

4. Pegar la siguiente consulta en la sección "Define query":

```json
{
    "size": 0,
    "query": {
        "bool": {
            "must": [
                {
                    "range": {
                        "@timestamp": {
                            "from": "now-1m",
                            "to": null
                        }
                    }
                },
                {
                    "exists": {
                        "field": "email"
                    }
                }
            ]
        }
    },
    "aggregations": {
        "emails": {
            "terms": {
                "field": "email.keyword",
                "size": 1000
            },
            "aggregations": {
                "unique_ips": {
                    "cardinality": {
                        "field": "ip_address.keyword"
                    }
                }
            }
        }
    }
}
```

5. Hacer clic en **Add trigger**, asignar un nombre (ej. `IPs múltiples`) y seleccionar condición basada en `aggregations`.

   * Condición: `ctx.results[0].aggregations.email.buckets.stream().anyMatch(bucket -> bucket.unique_ips.value > 1)`

6. En la sección de **Actions**, seleccionar el canal `Email Alerts` creado antes.

7. Guardar el monitor.

### Probar la alerta

Ir a [http://localhost:8080](http://localhost:8080) y probar el simulador de logs de login.

## Crear Alerta – UFW Blocked Connection Alert

### Objetivo

Detectar conexiones bloqueadas por el firewall UFW en el último minuto.

### Pasos

1. Ir a **Opensearch Plugins → Alerting**

2. Hacer clic en **Monitors → Create Monitor**

3. Elegir:

   * **Monitor type**: `Per query monitor`
   * **Monitor defining method**: `Extraction query editor`
   * **Schedule**: Cada 1 minuto
   * **Data source**: `Index` → seleccionar índice relevante (`logstash-*`)
   * **Monitor name**: `Conexión bloqueada por UFW`

4. Pegar la siguiente consulta en la sección "Define query":

```json
{
  "query": {
    "bool": {
      "must": [
        {
          "match": {
            "message": {
              "query": "UFW BLOCK",
              "operator": "OR",
              "prefix_length": 0,
              "max_expansions": 50,
              "fuzzy_transpositions": true,
              "lenient": false,
              "zero_terms_query": "NONE",
              "auto_generate_synonyms_phrase_query": true,
              "boost": 1
            }
          }
        },
        {
          "range": {
            "@timestamp": {
              "from": "now-1m",
              "to": null,
              "include_lower": true,
              "include_upper": true,
              "boost": 1
            }
          }
        }
      ],
      "adjust_pure_negative": true,
      "boost": 1
    }
  }
}
```

5. Hacer clic en **Add trigger**, asignar un nombre (ej. `UFW bloqueó intento`) y seleccionar condición:

   * **Trigger condition**: `ctx.results[0].hits.total.value > 0`

6. En la sección de **Actions**, seleccionar el canal `Email Alerts` previamente configurado.

7. Guardar el monitor.

### Probar la alerta

En otra máquina o contenedor, ejecutar el siguiente comando para simular una conexión bloqueada por UFW al puerto 12345:

```bash
nc -v <IP_DEL_HOST_UFW> 12345
```

Asegurate de que:

* El puerto 12345 esté bloqueado y logueado por UFW.
* UFW esté configurado para registrar eventos en `/var/log/ufw.log`.
* Filebeat esté monitoreando ese archivo y enviando los eventos al Logstash.

Perfecto, acá te dejo la sección para la alerta de consultas largas en PostgreSQL, siguiendo el mismo formato:

## Crear Alerta – Consultas PostgreSQL que duran más de 3 segundos

### Objetivo

Detectar consultas a PostgreSQL cuya duración sea mayor a 3 segundos en los últimos 5 minutos.

### Pasos

1. Ir a **Opensearch Plugins → Alerting**

2. Hacer clic en **Monitors → Create Monitor**

3. Elegir:

   * **Monitor type**: `Per query monitor`
   * **Monitor defining method**: `Extraction query editor`
   * **Schedule**: Cada 1 minuto
   * **Data source**: `Index` → seleccionar índice relevante (`logstash-*`)
   * **Monitor name**: `Consultas largas en PostgreSQL`

4. Pegar la siguiente consulta en la sección "Define query":

```json
{
  "query": {
    "bool": {
      "must": [
        {
          "regexp": {
            "message": {
              "value": "duration: [3-9][0-9]{3,}",
              "flags_value": 255,
              "max_determinized_states": 10000,
              "boost": 1
            }
          }
        },
        {
          "range": {
            "@timestamp": {
              "from": "now-5m",
              "to": null,
              "include_lower": true,
              "include_upper": true,
              "boost": 1
            }
          }
        }
      ],
      "adjust_pure_negative": true,
      "boost": 1
    }
  }
}
```

5. Hacer clic en **Add trigger**, asignar un nombre (ej. `Consulta > 3s`) y seleccionar condición:

   * **Trigger condition**: `ctx.results[0].hits.total.value > 0`

6. En la sección de **Actions**, seleccionar el canal `Email Alerts` configurado previamente.

7. Guardar el monitor.

### Probar la alerta

Para probar esta alerta, ingresar al contenedor Docker de PostgreSQL y ejecutar el script generador de logs de consultas largas:

```bash
docker exec -it postgres /bin/bash
/scripts/generate_postgres_logs.sh
```

Este script genera entradas en el log con duración de consultas mayor a 3 segundos, lo que debería activar la alerta configurada.

Claro, acá te dejo la sección para la alerta de modificación del archivo `/etc/passwd` usando syslog-ubuntu:

---

## Crear Alerta – Modificación de un Archivo Critico

### Objetivo

Detectar cualquier modificación en el archivo `/etc/passwd` monitoreado a través de syslog-ubuntu y auditbeat.

### Pasos

1. Ir a **Opensearch Plugins → Alerting**

2. Hacer clic en **Monitors → Create Monitor**

3. Elegir:

   * **Monitor type**: `Per query monitor`
   * **Monitor defining method**: `Extraction query editor`
   * **Schedule**: Cada 1 minuto
   * **Data source**: `Index` → seleccionar índice relevante (`logstash-*`)
   * **Monitor name**: `Modificación en /etc/passwd`

4. Pegar la siguiente consulta en la sección "Define query":

```json
{
  "size": 1,
  "query": {
    "bool": {
      "must": [
        {
          "match": {
            "event.dataset": "file"
          }
        },
        {
          "match": {
            "event.action": "attributes_modified"
          }
        },
        {
          "match": {
            "event.module": "file_integrity"
          }
        },
        {
          "match": {
            "file.path": "/hostfs/etc/passwd"
          }
        }
      ]
    }
  },
  "sort": [
    {
      "@timestamp": {
        "order": "desc"
      }
    }
  ]
}
```

5. Hacer clic en **Add trigger**, asignar un nombre (ej. `Modificación passwd`) y seleccionar condición:

   * **Trigger condition**: `ctx.results[0].hits.total.value > 0`

6. En la sección de **Actions**, seleccionar el canal `Email Alerts` configurado previamente.

7. Guardar el monitor.


### Probar la alerta

Para probar esta alerta, ejecutar el siguiente comando en la máquina Ubuntu donde corre `syslog-ubuntu`:

```bash
echo "mensaje de modificación" | sudo tee -a /etc/passwd
```

Esto debería generar un evento de modificación en el log que activará la alerta.

## Crear Alerta – Apache No Disponible

### Objetivo

Detectar si no hay eventos recientes provenientes del servidor Apache en los últimos 5 minutos, lo cual podría indicar que el servidor está caído o no enviando logs.


### Pasos

1. Ir a **OpenSearch Plugins → Alerting**

2. Clic en **Monitors → Create Monitor**

3. Seleccionar:

   * **Monitor type**: `Per query monitor`
   * **Monitor defining method**: `Visual editor`
   * **Index**: `logstash-*` (o el índice donde se recolectan los logs de Apache)
   * **Time field**: `@timestamp`
   * **Monitor name**: `Chequeo de salud Apache`

4. En la sección de agregaciones:

   * Agregación: `COUNT`
   * Campo: `@timestamp`

5. En la sección de rango temporal:

   * **Time range for the last**: `5 minutes`

6. En la sección de filtros de datos:

   * Campo: `log_type`
   * Condición: `is`
   * Valor: `apache`

### Crear Trigger

1. En la sección **Triggers**, clic en **Add trigger**

2. Nombre del trigger: `Apache sin actividad`

3. Condición:

   * Trigger cuando: **COUNT is below 1**

4. Acción: seleccionar el canal de notificación, como el canal de correo electrónico configurado previamente.


### Probar la alerta

Podés probar esta alerta deteniendo temporalmente el contenedor de Apache:

```bash
docker stop apache-server
```

Esperá 5 minutos y deberías recibir la alerta de que Apache no está enviando logs.

Para reactivarlo:

```bash
docker start apache-server
```


## Crear Alerta – Errores 500/503 en Apache

### Objetivo

Detectar si se han producido errores HTTP `500` o `503` en los últimos 5 minutos, lo cual puede indicar una falla crítica en el servidor o en la aplicación web.

### Pasos

1. Ir a **OpenSearch Plugins → Alerting**

2. Hacer clic en **Monitors → Create Monitor**

3. Configurar lo siguiente:

   * **Monitor type**: `Per query monitor`
   * **Monitor defining method**: `Extraction query editor`
   * **Schedule**: Cada 1 minuto
   * **Data source**: `Index` → seleccionar `logstash-*` (o el índice donde llegan los logs de Apache)
   * **Monitor name**: `Errores 500/503 en Apache`

4. En la sección **Define query**, pegar la siguiente consulta:

```json
{
    "size": 0,
    "query": {
        "bool": {
            "filter": [
                {
                    "range": {
                        "@timestamp": {
                            "from": "now-5m",
                            "to": null,
                            "include_lower": true,
                            "include_upper": true
                        }
                    }
                }
            ],
            "should": [
                {
                    "match_phrase": {
                        "message": {
                            "query": " 500 "
                        }
                    }
                },
                {
                    "match_phrase": {
                        "message": {
                            "query": " 503 "
                        }
                    }
                }
            ],
            "minimum_should_match": "1"
        }
    }
}
```

### Crear Trigger

1. En la sección **Triggers**, hacer clic en **Add trigger**

2. Nombre del trigger: `Errores HTTP 500/503`

3. Condición: **ctx.results\[0].hits.total.value > 0**

4. Acción: Seleccionar el canal de notificación previamente creado (como correo electrónico)

### Probar la alerta

Ir a [http://localhost:8080](http://localhost:8080) y presionar alguno de los botones del simulador que genera un error `500`. Esperar un minuto para que se dispare la alerta.

Claro, acá te dejo una guía clara, organizada y con buena redacción para crear **reportes automáticos** en OpenSearch Dashboards que incluyan logs de los últimos 3 días. Esta guía combina la creación de visualizaciones útiles con la configuración de un reporte recurrente:

---

## Crear Reportes de Logs – Últimos 3 Días

### Objetivo

Generar reportes automáticos que resuman visualmente la actividad de logs (por ejemplo: accesos, errores, bloqueos de firewall, etc.) de los últimos **3 días**, y que se envíen o guarden automáticamente cada 3 días.


### 1. Crear Visualización

1. Ir a **OpenSearch Dashboards → Visualize**.
2. Hacer clic en **Create visualization**.
3. Elegir el tipo de visualización que se desea (ej. Pie chart, Line chart, Bar chart, Data Table).
4. En **Data source**, seleccionar el índice relevante (por ejemplo: `logstash-*`, `filebeat-*`, o `auditbeat-*`).
5. Configurar los campos que te interese observar:

   * Ejemplo: número de accesos por dirección IP.
   * O: cantidad de errores HTTP por día.
6. Ajustar el **Time range** (arriba a la derecha) a **Last 3 days** para comprobar que la visualización tenga datos relevantes.
7. Hacer clic en **Save** y darle un nombre descriptivo, como `Errores Apache - Últimos 3 días`.

### 2. Crear Reporte

1. Con la visualización abierta, hacer clic en el menú de **Reporting** (ícono de impresora o en el menú superior derecho).
2. Seleccionar **Create report definition**.
3. Configurar el reporte:

   * **Name**: Algo descriptivo como `Reporte de Errores Apache - Últimos 3 días`.
   * **Report source**: Automáticamente se selecciona la visualización actual.
   * **Time range**: Seleccionar **Last 3 days** (esto asegura que siempre genere reportes con ese rango).
   * **Format**: PDF (o PNG si se prefiere como imagen).
4. Habilitar la opción **Schedule** para automatizar el reporte.

   * **Repeat every**: `3 days`
   * Elegir la hora deseada para la ejecución (ej. 09:00 AM).
5. Guardar el reporte.


Cada 3 días, se generará automáticamente un reporte visual con los datos de logs correspondientes a los últimos 3 días. Este puede descargarse desde el panel de **Reporting → Reports**.