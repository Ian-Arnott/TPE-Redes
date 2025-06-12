# Configuración de UFW y Filebeat en una máquina Ubuntu

Este entorno se usa para asegurar que el tráfico de red y los logs generados sean reales. Ideal para probar reglas de firewall y monitoreo de eventos de seguridad.

## Requisitos

- Una máquina virtual o física con **Ubuntu 20.04 o superior**
- Acceso a internet
- Acceso root o permisos `sudo`


## Paso 1: Instalar y configurar UFW

### Instalación

```bash
sudo apt update
sudo apt install ufw -y
```

### Configuración básica

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw deny 12345
sudo ufw logging on
```

Activar UFW:

```bash
sudo ufw enable
```

Ver el estado de las reglas:

```bash
sudo ufw status verbose
```

Esto dejará habilitado el registro de los intentos de acceso denegado en `/var/log/ufw.log`.

## Paso 2: Instalar Filebeat

Instalamos Filebeat usando el repositorio oficial de Elastic:

```bash
curl -fsSL https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo gpg --dearmor -o /usr/share/keyrings/elastic-archive-keyring.gpg

echo "deb [signed-by=/usr/share/keyrings/elastic-archive-keyring.gpg] https://artifacts.elastic.co/packages/8.x/apt stable main" | \
  sudo tee /etc/apt/sources.list.d/elastic-8.x.list

sudo apt update
sudo apt install filebeat -y
```

## Paso 3: Configurar Filebeat para enviar logs de UFW

Editar el archivo `/etc/filebeat/filebeat.yml` con permisos de superusuario:

```bash
sudo nano /etc/filebeat/filebeat.yml
```

Agregar o reemplazar el contenido con lo siguiente:

```yaml
filebeat.inputs:
  - type: filestream
    id: ufw-logs
    enabled: true
    paths:
      - /var/log/ufw.log
    fields:
      log_type: firewall_ufw
      firewall_service: ufw
    fields_under_root: true
    tags: ["firewall", "ufw"]

output.logstash:
  hosts: ["localhost:5044"]

logging.level: info
```

## Paso 4: Activar y monitorear Filebeat

Habilitar Filebeat para que inicie con el sistema:

```bash
sudo systemctl enable filebeat
```

Iniciar el servicio:

```bash
sudo systemctl start filebeat
```

Verificar el estado:

```bash
sudo systemctl status filebeat
```

Logs de Filebeat:

```bash
sudo journalctl -u filebeat -f
```


