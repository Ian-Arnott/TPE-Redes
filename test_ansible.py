import socket
import json
from datetime import datetime, timedelta
import random

def send_ansible_log(data):
    try:
        # Usamos TCP (SOCK_STREAM)
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.connect(('localhost', 6000))  # conexión TCP explícita
        sock.sendall(json.dumps(data).encode() + b'\n')  # newline para delimitar eventos
        sock.close()
        return True
    except Exception as e:
        print(f'Error: {e}')
        return False

hosts = ['web-server-01', 'web-server-02', 'db-server-01', 'lb-server-01']
plays = ['Deploy Application', 'Configure Database', 'Update System', 'Install Security Patches']
tasks = ['Install packages', 'Copy files', 'Restart services', 'Update configuration']
statuses = ['ok', 'changed', 'failed', 'skipped']

print('Enviando logs de Ansible a Logstash...')

for i in range(10):
    timestamp = datetime.utcnow() - timedelta(minutes=random.randint(0, 60))

    data = {
        'message': f'Ansible task execution #{i+1}',
        'host': random.choice(hosts),
        'play': random.choice(plays),
        'task': random.choice(tasks),
        'status': random.choice(statuses),
        '@timestamp': timestamp.isoformat() + 'Z',
        'ansible_playbook': f'playbook-{random.randint(1, 3)}.yml',
        'changed': random.choice([True, False]),
        'duration': round(random.uniform(0.5, 10.0), 2)
    }

    if send_ansible_log(data):
        print(f'✓ Enviado: {data["host"]} - {data["task"]} - {data["status"]}')

print('\n¡Datos enviados! Esperando procesamiento...')
