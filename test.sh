#!/bin/bash

echo "=== Simple ELK Test ==="

# Test 1: Check if OpenSearch is running
echo "1. Testing OpenSearch..."
curl -k -u admin:YourStrongSuperPassword123! https://localhost:9200 && echo "✅ OpenSearch OK" || echo "❌ OpenSearch Failed"

# Test 2: Check web app
echo "2. Testing web application..."
curl -s http://localhost:8080 > /dev/null && echo "✅ Web app OK" || echo "❌ Web app Failed"

# Test 3: Generate some logs
echo "3. Generating test logs..."
curl -s http://localhost:8080/ > /dev/null
curl -s http://localhost:8080/?test=1 > /dev/null
curl -s http://localhost:8080/?page=about > /dev/null

echo "4. Waiting 30 seconds for log processing..."
sleep 30

# Test 4: Check for logs in OpenSearch
echo "5. Checking for logs..."
curl -k -s -u admin:YourStrongSuperPassword123! "https://localhost:9200/_cat/indices" | grep logstash && echo "✅ Logs found!" || echo "❌ No logs yet"

echo "Done! Check http://localhost:8080 and http://localhost:5601"